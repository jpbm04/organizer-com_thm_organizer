<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerControllermonitor
 * @author      James Antrim, <James.Antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
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
class THM_OrganizerControllermonitor extends JControllerLegacy
{
    /**
     * Performs access checks and redirects to the monitor edit view
     *
     * @return void
     */
    public function add()
    {
        $this->setRedirect("index.php?option=com_thm_organizer&view=monitor_edit");
    }

    /**
     * Performs access checks and redirects to the monitor edit view
     *
     * @return void
     */
    public function edit()
    {
        $cid = $this->input->post->get('cid', array(), 'array');

        // Only edit the first id in the list
        if (count($cid) > 0)
        {
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=monitor_edit&id=$cid[0]", false));
        }
        else
        {
            $this->setRedirect("index.php?option=com_thm_organizer&view=monitor_edit");
        }
    }

    /**
     * Performs access checks, saves the monitor currently being edited and
     * redirects to the monitor manager view
     *
     * @return void
     */
    public function save()
    {
        $model = $this->getModel('monitor');
        $result = $model->save();
        if ($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_manager', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_manager', $msg, 'error');
        }
    }
    /**
     * Performs access checks, saves the monitor currently being edited and
     * redirects to the monitor manager view
     *
     * @return void
     */
    public function saveDefaultBehaviour()
    {
        $model = $this->getModel('monitor');
        $result = $model->saveDefaultBehaviour();
        if ($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_manager', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_manager', $msg, 'error');
        }
    }

    /**
     * Performs access checks, saves the monitor currently being edited and
     * redirects to the monitor edit view
     *
     * @return void
     */
    public function save2new()
    {
        $result = $this->getModel('monitor')->save();
        $url = 'index.php?option=com_thm_organizer&view=monitor_edit';
        if ($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS");
            $this->setRedirect($url, $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL");
            $this->setRedirect($url, $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's delete function, and
     * redirects to the monitor manager view
     *
     * @return  void
     */
    public function delete()
    {
        $result = $this->getModel('monitor')->delete();
        if ($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_MESSAGE_DELETE_SUCCESS");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_manager', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_MESSAGE_DELETE_FAIL");
            $this->setRedirect('index.php?option=com_thm_organizer&view=monitor_manager', $msg, 'error');
        }
    }

    /**
     * Method to cancel an edit.
     *
     * @return  void
     */
    public function cancel()
    {
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=monitor_manager', false));
    }


    /**
     * Toggles category behaviour properties
     *
     * @return void
     */
    public function toggle()
    {
        $model = $this->getModel('monitor');
        $success = $model->toggle();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $type = 'message';
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $type = 'error';
        }
        $this->setRedirect("index.php?option=com_thm_organizer&view=monitor_manager", $msg, $type);
    }
}
