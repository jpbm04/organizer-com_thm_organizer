<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewmonitor_manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.view');

/**
 * Class loading a list of persistent monitor entries into the view context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewMonitor_Manager extends THM_CoreViewList
{
    public $items;

    public $pagination;

    public $state;

    /**
     * Loads data from the model into the view context
     *
     * @param   string  $tpl  the name of the template to be used
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        parent::display($tpl);
    }

    /**
     * creates joomla toolbar elements
     *
     * @return void
     */
    private function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_MON_TOOLBAR_TITLE'), 'organizer_monitors');
        JToolbarHelper::addNew('monitor.add');
        JToolbarHelper::editList('monitor.edit');
        JToolbarHelper::deleteList(JText::_('COM_THM_ORGANIZER_MON_DELETE_CONFIRM'), 'monitor.delete');
        JToolbarHelper::divider();
        JToolbarHelper::preferences('com_thm_organizer');
    }
}
