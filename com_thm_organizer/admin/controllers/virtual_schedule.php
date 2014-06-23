<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin.controller
 * @name        THM_OrganizerControllervirtual_schedule
 * @description perform tasks that affects virtual schedules
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class performs access checks, redirects and model function calls for data persistence
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerControllerVirtual_Schedule extends JControllerAdmin
{
    /**
     * Performs access checks and redirects to the virtual schedule edit view
     *
     * @return void
     */
    public function add()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return;
        }
        JFactory::getApplication()->input->set('id', '0');
        $this->setRedirect("index.php?option=com_thm_organizer&view=virtual_schedule_edit");
    }

    /**
     * Performs access checks and redirects to the virtual schedule edit view
     *
     * @return void
     */
    public function edit()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return;
        }
        $this->setRedirect("index.php?option=com_thm_organizer&view=virtual_schedule_edit");
    }
    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the virtual schedule manager view
     *
     * @return void
     * 
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function save()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return;
        }
        $url = 'index.php?option=com_thm_organizer';
        $this->setRedirect($url, 'How did you get here?');
    }

    /**
     * Performs access checks, makes call to the models's delete function, and
     * redirects to the virtual schedule manager view
     *
     * @return void
     */
    public function delete()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return;
        }
        $cid = JRequest::getVar('cid',   array(), 'post', 'array');
        $cids = "'" . implode("', '", $cid) . "'";

        $dbo = JFactory::getDBO();
        $scheduleQuery = $dbo->getQuery(true);
        $scheduleQuery->delete('#__thm_organizer_virtual_schedules');
        $scheduleQuery->where("vid IN ( $cids )");
        $dbo->setQuery((string) $scheduleQuery);
        
        try 
        {
            $dbo->query();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_ERROR_DELETING"), 500);
        }

        if (!$dbo->getErrorNum())
        {
            $elementQuery = $dbo->getQuery(true);
            $elementQuery->delete('#__thm_organizer_virtual_schedules_elements');
            $elementQuery->where("vid IN ( $cids )");
            $dbo->setQuery((string) $elementQuery);
            
            try
            {
                $dbo->query();
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
            }
        }

        if (count($cid) > 1)
        {
            $msg = JText::sprintf('COM_THM_ORGANIZER_VSE_DELETE_SUCCESSES', implode(', ', $cid));
        }
        else
        {
            $msg = JText::sprintf('COM_THM_ORGANIZER_VSE_DELETE_SUCCESS', implode(', ', $cid));
        }

        $this->setRedirect('index.php?option=com_thm_organizer&view=virtual_schedule_manager', $msg);

    }

    /**
     * Method to cancel an edit.
     *
     * @return  void
     */
    public function cancel()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return;
        }
        $this->setRedirect('index.php?option=com_thm_organizer&view=virtual_schedule_manager');
    }
}
