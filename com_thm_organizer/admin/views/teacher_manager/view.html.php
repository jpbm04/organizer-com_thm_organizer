<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/list.php';

/**
 * Class loads persistent information a filtered set of teachers into the display context.
 */
class THM_OrganizerViewTeacher_Manager extends THM_OrganizerViewList
{
    public $items;

    public $pagination;

    public $state;

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $actions = $this->getModel()->actions;

        if (!$actions->{'core.admin'} and !$actions->{'organizer.hr'}) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        parent::display($tpl);
    }

    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_TEACHER_MANAGER_VIEW_TITLE'), 'organizer_teachers');
        JToolbarHelper::addNew('teacher.add');
        JToolbarHelper::editList('teacher.edit');
        JToolbarHelper::custom('teacher.mergeView', 'merge', 'merge', 'COM_THM_ORGANIZER_ACTION_MERGE', true);
        JToolbarHelper::deleteList('', 'teacher.delete');

        if ($this->getModel()->actions->{'core.admin'}) {
            JToolbarHelper::divider();
            JToolbarHelper::preferences('com_thm_organizer');
        }
    }
}
