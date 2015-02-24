<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewDepartment_Edit
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2015 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.edit.view');

/**
 * Class THM_OrganizerViewField for component com_thm_organizer
 *
 * Class provides methods to display the view field edit
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewDepartment_Edit extends THM_CoreViewEdit
{
    /**
     * Method to get display
     *
     * @param   Object  $tpl  template  (default: null)
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        parent::display($tpl);
    }

    /**
     * Method to generate buttons for user interaction
     *
     * @return  void
     */
    protected function addToolBar()
    {
        $isNew = ($this->item->id == 0);
        $title = $isNew ? JText::_('COM_THM_ORGANIZER_DEPARTMENT_EDIT_NEW_VIEW_TITLE') : JText::_('COM_THM_ORGANIZER_DEPARTMENT_EDIT_EDIT_VIEW_TITLE');
        JToolbarHelper::title($title, 'organizer_departments');
        JToolbarHelper::apply('department.apply');
        JToolbarHelper::save('department.save');
        JToolbarHelper::save2new('department.save2new');
        JToolbarHelper::cancel('department.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}