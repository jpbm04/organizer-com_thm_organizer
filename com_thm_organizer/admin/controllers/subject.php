<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerSubject
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');

/**
 * Class performs access checks, redirects and model function calls for data persistence
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerControllerSubject extends JController
{
    /**
     * Performs access checks, sets the id variable to 0, and redirects to the
     * subject edit view
     * 
     * @return void 
     */
    public function add()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        JRequest::setVar('view', 'subject_edit');
        JRequest::setVar('id', '0');
        parent::display();
    }

    /**
     * Performs access checks and redirects to the subject edit view
     *
     * @return  void
     */
    public function edit()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        JRequest::setVar('view', 'subject_edit');
        parent::display();
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the subject manager view
     *
     * @return  void
     */
    public function apply()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $success = $this->getModel('subject')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_SUM_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=subject_edit&id=$success", false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_SUM_SAVE_FAIL');
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=subject_edit&id=0", false), $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the subject manager view
     *
     * @return  void
     */
    public function save()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $success = $this->getModel('subject')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_SUM_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=subject_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_SUM_SAVE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=subject_manager', false), $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the subject manager view
     *
     * @return  void
     */
    public function save2new()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $success = $this->getModel('subject')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_SUM_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=subject_edit&id=0", false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_SUM_SAVE_FAIL');
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=subject_edit&id=0", false), $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's delete function, and
     * redirects to the subject manager view
     *
     * @return  void
     */
    public function delete()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $success = $this->getModel('subject')->delete();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_SUM_DELETE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=subject_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_SUM_DELETE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=subject_manager', false), $msg, 'error');
        }
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
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=subject_manager', false));
    }
    
    /**
	 * Perfoerms access checks and makes function calls for importing LSF Data
	 *
	 * @return  void
	 */
    public function importLSFData()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
		$success = $this->getModel('subject')->importLSFDataBatch();
		if ($success)
		{
			$msg = JText::_('COM_THM_ORGANIZER_SUM_FILL_SUCCESS');
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=subject_manager', false), $msg);
		}
		else
		{
			$msg = JText::_('COM_THM_ORGANIZER_SUM_FILL_FAIL');
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=subject_manager', false), $msg, 'error');
		}
    }
}
