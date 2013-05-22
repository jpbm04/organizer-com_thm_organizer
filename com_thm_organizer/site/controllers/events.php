<?php
/**
 * @version     v0.1.0 
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerControllerEvents
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') OR die('Restricted access');
jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT . "/assets/classes/eventAccess.php";

/**
 * Performs access checks and user actions for events and associated resources
 * 
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class THM_OrganizerControllerEvents extends JController
{
    /**
     * edit
     *
     * performs access checks for the current user against the id of the event
     * to be edited, or content (event) creation access if id is missing or 0
     * 
     * @return void
     */
    public function edit()
    {
        $eventID = JRequest::getInt('eventID', null);
        $eventIDs = JRequest::getVar('eventIDs', null);
        $menuID = JRequest::getInt('Itemid');
        $access = false;
        if (!isset($eventID) and isset($eventIDs))
        {
            $eventID = 0;
            foreach ($eventIDs as $selectedID)
            {
                if ($selectedID)
                {
                    $eventID = $selectedID;
                    break;
                }
            }
        }
        if (isset($eventID) and $eventID > 0)
        {
            $access = THMEventAccess::canEdit($eventID) or THMEventAccess::canEditOwn($eventID);
        }
        else
        {
            $access = THMEventAccess::canCreate();
        }
        if ($access)
        {
            $url = "index.php?option=com_thm_organizer&view=event_edit";
            $url .= ($eventID)? "&eventID=$eventID" : "";
            $url .= (isset($menuID))? "&Itemid=$menuID" : "";
            $this->setRedirect(JRoute::_($url, false));
        }
        else
        {
            THMEventAccess::noAccess();
        }
    }

    /**
     * save
     *
     * performs access checks and calls the save function of the events model
     * reroutes to the single event view of the created event upon success
     * 
     * @return void
     */
    public function save()
    {
        $eventID = JRequest::getInt('eventID', 0);
        $menuID = JRequest::getVar('Itemid');

        if ($eventID == 0)
        {
            $canSave = THMEventAccess::canCreate();
        }
        else
        {
            $canSave = THMEventAccess::canEdit($eventID);
        }

        if ($canSave)
        {
            $schedulerCall = JRequest::getVar('schedulerCall');
            $model = $this->getModel('events');
            $eventID = $model->save();

            if ($eventID)
            {
                $msg = JText::_('COM_THM_ORGANIZER_EVENT_SAVED');
                if ($schedulerCall)
                {
                    $link = JRoute::_("index.php?option=com_thm_organizer&view=event&eventID=$eventID&tmpl=component", false);
                }
                else
                {
                    $link = JRoute::_("index.php?option=com_thm_organizer&view=event&eventID=$eventID&Itemid=$menuID", false);
                }
                $this->setRedirect($link, $msg);
            }
            else
            {
                $msg = JText::_('COM_THM_ORGANIZER_EVENT_SAVE_FAILED');
                if ($schedulerCall)
                {
                    $link = JRoute::_('index.php?option=com_thm_organizer&view=event_edit&eventID=0&tmpl=component', false);
                }
                else
                {
                    $link = JRoute::_("index.php?option=com_thm_organizer&view=event_edit&Itemid=$menuID", false);
                }
                $this->setRedirect($link, $msg, 'error');
            }
        }
        else
        {
            THMEventAccess::noAccess();
        }
    }

    /**
     * save2new
     *
     * performs access checks and calls the save function of the events model
     * reroutes to the event editing view for the creation of a new event upon
     * success
     * 
     * @return void
     */
    public function save2new()
    {
        $eventID = JRequest::getInt('id', 0);
        $menuID = JRequest::getVar('Itemid');

        if ($eventID == 0)
        {
            $canSave = THMEventAccess::canCreate();
        }
        else
        {
            $isAuthor = THMEventAccess::isAuthor($eventID);
            $canEditOwn = ($isAuthor)? THMEventAccess::canEditOwn($eventID) : false;
            $canSave = THMEventAccess::canEdit($eventID) or $canEditOwn;
        }

        if ($canSave)
        {
            $schedulerCall = JRequest::getVar('schedulerCall');
            $model = $this->getModel('events');
            $eventID = $model->save();

            if ($eventID)
            {
                $msg = JText::_('COM_THM_ORGANIZER_EVENT_SAVED');
                if ($schedulerCall)
                {
                    $link = JRoute::_('index.php?option=com_thm_organizer&view=event_edit&eventID=0&tmpl=component', false);
                }
                else
                {
                    $link = JRoute::_("index.php?option=com_thm_organizer&view=event_edit&eventID=0&Itemid=$menuID", false);
                }
                $this->setRedirect($link, $msg);
            }
            else
            {
                $msg = JText::_('COM_THM_ORGANIZER_EVENT_SAVE_FAILED');
                if ($schedulerCall)
                {
                    $link = JRoute::_('index.php?option=com_thm_organizer&view=event_edit&eventID=0&tmpl=component', false);
                }
                else
                {
                    $link = JRoute::_("index.php?option=com_thm_organizer&view=event_edit&eventID=0&Itemid=$menuID", false);
                }
                $this->setRedirect($link, $msg, 'error');
            }
        }
        else
        {
            THMEventAccess::noAccess();
        }
    }

    /**
     * delete
     *
     * performs access checks calls the delete function of the event model for
     * one or multiple eventsted items
     * 
     * @return void
     */
    public function delete()
    {
        $eventID = JRequest::getInt('eventID');
        $eventIDs = JRequest::getVar('eventIDs');
        $menuID = JRequest::getVar('Itemid');
        $success = false;
        $model = $this->getModel('events');
        if (isset($eventID) && $eventID != 0)
        {
            (THMEventAccess::canDelete($eventID))?
                $success = $model->delete($eventID) : THMEventAccess::noAccess();
        }
        elseif (isset($eventIDs) and count($eventIDs))
        {
            foreach ($eventIDs as $id)
            {
                if (THMEventAccess::canDelete($id))
                {
                    $success = $model->delete($id);
                }
                else
                {
                    THMEventAccess::noAccess();
                }
            }
        }
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_EVENT_DELETED');
            $link = JRoute::_("index.php?option=com_thm_organizer&view=event_list&Itemid=$menuID", false);
            $this->setRedirect($link, $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_EVENT_DELETE_FAILED');
            $link = JRoute::_("index.php?option=com_thm_organizer&view=event_list&Itemid=$menuID", false);
            $this->setRedirect($link, $msg, 'error');
        }
    }

    /**
     * function search
     *
     * redirects to the event_list view which reformats its sql restriction
     * 
     * @return void
     */
    public function search()
    {
        $menuID = JRequest::getVar('Itemid');
        $link = JRoute::_("index.php?option=com_thm_organizer&view=event_list&Itemid=$menuID", false);
        $this->setRedirect($link);
    }
}
