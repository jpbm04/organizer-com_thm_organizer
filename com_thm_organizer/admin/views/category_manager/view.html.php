<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewcategory_manager
 * @description view output file for event category lists
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @author      Melih Cakir, <melih.cakir@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_list.thm_list');
/**
 * Class which loads data into the view output context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v0.1.0
 */
class THM_OrganizerViewCategory_Manager extends JViewLegacy
{
    /**
     * loads persistent information into the view context
     *
     * @param   string  $tpl  the name of the template to be used
     *
     * @return void
     */
    public function display($tpl = null)
    {
        JHtml::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root() . 'media/com_thm_organizer/css/category_manager.css');

        $this->model = $this->getModel();
        $this->items = $this->get('Items');
        $this->state = $this->get('State');
        $this->filters = $this->get('Filters');
        $this->headers = $this->get('Headers');
        $this->pagination = $this->get('Pagination');
        $this->addToolBar();

        parent::display($tpl);
    }

    /**
     * generates joomla toolbar elements
     *
     * @return void
     */
    private function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER') . ': ' . JText::_('COM_THM_ORGANIZER_CAT_TITLE');
        JToolbarHelper::title($title, 'organizer_categories');
        JToolbarHelper::addNew('category.add');
        JToolbarHelper::editList('category.edit');
        JToolbarHelper::deleteList(JText::_('COM_THM_ORGANIZER_CAT_DELETE_CONFIRM'), 'category.delete');
        JToolbarHelper::divider();
        JToolbarHelper::preferences('com_thm_organizer');
    }
}
