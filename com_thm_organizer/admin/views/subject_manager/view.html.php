<?php

/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewSubject_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
/**
 * Retrieves a list of subjects and loads data into context.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewSubject_Manager extends JView
{
    /**
     * Retrieves display items and loads them into context.
     * 
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     * 
     * @return  void
     */
    public function display($tpl = null)
    {
        JHtml::_('behavior.tooltip');
        JHTML::_('behavior.modal', 'a.modal-button');

        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->programSelect = $this->getProgramSelect($this->getModel()->programs);

        $this->addToolBar();
        parent::display($tpl);

    }

    /**
     * Sets Joomla view title and action buttons
     * 
     * @return  void
     */
    public function addToolBar()
    {
        JToolBarHelper::title(JText::_('COM_THM_ORGANIZER_SUM_TOOLBAR_TITLE'), 'generic.png');
        JToolBarHelper::addNew('subject.add');
        JToolBarHelper::editList('subject.edit');
		JToolBarHelper::custom(
							   'subject.importLSFData',
							   'export',
							   '',
							   'COM_THM_ORGANIZER_PRM_IMPORT',
							   true,
							   true
							  );
        JToolBarHelper::deleteList('COM_THM_ORGANIZER_SUM_DELETE_CONFIRM', 'subject.delete');
    }

    /**
     * Retrieves a select box with the mapped programs
     * 
     * @param   array  $programs  the mapped programs
     * 
     * @return  string  html select box
     */
    private function getProgramSelect($programs)
    {
        $selectPrograms = array();
        $selectPrograms[] = array('id' => '-1', 'name' => JText::_('COM_THM_ORGANIZER_POM_SEARCH_PROGRAM'));
        $selectPrograms[] = array('id' => '-1', 'name' => JText::_('COM_THM_ORGANIZER_POM_ALL_PROGRAMS'));
        $programs = array_merge($selectPrograms, $programs);
        return JHTML::_('select.genericlist', $programs, 'filter_program', 'onchange="this.form.submit();"', 'id', 'name', $this->state->get('filter.program'));
    }
}
