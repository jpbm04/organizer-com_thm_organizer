<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewTeacher_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
/**
 * Class loads persistent teacher into a display list context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewTeacher_Manager extends JViewLegacy
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
        JHtml::_('behavior.tooltip');
        $doc = JFactory::getDocument();
        $doc->addStyleSheet(JURI::root() . 'media/com_thm_organizer/css/subject_list.css');

        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');

        $this->addToolBar();
        parent::display($tpl);
    }

    /**
     * Method to generate buttons for user interaction
     *
     * @return  void
     */
    protected function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_TRM_TOOLBAR_TITLE'), 'organizer_teachers');
        JToolbarHelper::addNew('teacher.add', 'JTOOLBAR_NEW');
        JToolbarHelper::editList('teacher.edit', 'JTOOLBAR_EDIT');
        JToolbarHelper::custom('teacher.mergeAll', 'merge', 'merge', 'COM_THM_ORGANIZER_MERGE_ALL', false);
        JToolbarHelper::custom('teacher.mergeView', 'merge', 'merge', 'COM_THM_ORGANIZER_MERGE', true);
        JToolbarHelper::deleteList('', 'teacher.delete', 'JTOOLBAR_DELETE');
        JToolbarHelper::divider();
        JToolbarHelper::preferences('com_thm_organizer');
    }
}
