<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerTeacher
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controllerform');

/**
 * Class performs access checks, redirects and model function calls for data persistence
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerControllerTeacher extends JControllerForm
{
    /**
     * Performs access checks and redirects to the teacher edit view
     * 
     * @return void 
     */
    public function add()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        JRequest::setVar('view', 'teacher_edit');
        JRequest::setVar('id', '0');
        parent::display();
    }

	/**
	 * Performs access checks and redirects to the teacher edit view
	 *
	 * @param   Object  $key     Key		   (default: null)
	 * @param   Object  $urlVar  Url variable  (default: null)
	 *
	 * @return  void
	 */
	public function edit($key = null, $urlVar = null)
	{
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        JRequest::setVar('view', 'teacher_edit');
        parent::display();
	}
	
	/**
	 * Performs access checks and redirects to the teacher edit view
	 *
	 * @return  void
	 */
	public function mergeView()
	{
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
		if (count(JRequest::getVar('cid', array(), 'post', 'array')) == 1)
		{
			$msg = JText::_('COM_THM_ORGANIZER_TRM_MERGE_TOOFEW');
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=teacher_manager', false), $msg, 'warning');
		}
		else
		{
			$model = $this->getModel('teacher');
			$success = $model->autoMerge();
			if ($success)
			{
				$msg = JText::_('COM_THM_ORGANIZER_TRM_MERGE_SUCCESS');
				$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=teacher_manager', false), $msg);
			}
			else
			{
				JRequest::setVar('view', 'teacher_merge');
				parent::display();
			}
		}
	}

	/**
	 * Performs access checks, makes call to the models's save function, and
	 * redirects to the teacher manager view
	 *
	 * @param   Object  $key     Key		   (default: null)
	 * @param   Object  $urlVar  Url variable  (default: null)
	 *
	 * @return  void
	 */
	public function save($key = null, $urlVar = null)
	{
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
		$success = $this->getModel('teacher')->save();
		if ($success)
		{
			$msg = JText::_('COM_THM_ORGANIZER_TRM_SAVE_SUCCESS');
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=teacher_manager', false), $msg);
		}
		else
		{
			$msg = JText::_('COM_THM_ORGANIZER_TRM_SAVE_FAIL');
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=teacher_manager', false), $msg, 'error');
		}
	}

	/**
	 * Performs access checks, makes call to the models's dmerge function, and
	 * redirects to the teacher manager view
	 *
	 * @return  void
	 */
	public function merge()
	{
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
		$success = $this->getModel('teacher')->merge();
		if ($success)
		{
			$msg = JText::_('COM_THM_ORGANIZER_TRM_MERGE_SUCCESS');
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=teacher_manager', false), $msg);
		}
		else
		{
			$msg = JText::_('COM_THM_ORGANIZER_TRM_MERGE_FAIL');
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=teacher_manager', false), $msg, 'error');
		}
	}

	/**
	 * Performs access checks, makes call to the models's delete function, and
	 * redirects to the teacher manager view
	 *
	 * @return  void
	 */
	public function delete()
	{
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
		$success = $this->getModel('teacher')->delete();
		if ($success)
		{
			$msg = JText::_('COM_THM_ORGANIZER_TRM_DELETE_SUCCESS');
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=teacher_manager', false), $msg);
		}
		else
		{
			$msg = JText::_('COM_THM_ORGANIZER_TRM_DELETE_FAIL');
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=teacher_manager', false), $msg, 'error');
		}
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  void
	 */
	public function cancel($key = null)
	{
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
		$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=teacher_manager', false));
	}
}
