<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin.view
 * @name        THM_OrganizerViewVirtual_Schedule_Edit
 * @description provides a form for editing a virtual schedule
 * @author      James Antrim, <James.Antrim@mni.thm.de>
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class THM_OrganizerViewVirtual_Schedule_Edit for component com_thm_organizer
 * Class provides methods to display a virtual schedule edit form
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin.view
 * @link        www.mni.thm.de
 */
class THM_OrganizerViewVirtual_Schedule_Edit extends JViewLegacy
{
    /**
     * Method to get display
     *
     * @param   Object  $tpl  template  (Default: null)
     *
     * @return void
     */
    public function display($tpl = null)
    {
        JHtml::_('behavior.framework', true);
        JHTML::_('behavior.formvalidation');
        JHTML::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root() . 'media/com_thm_organizer/css/thm_organizer.css');
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/virtual_schedule_edit.js'));

        $model = $this->getModel();
        $this->form = $this->get('Form');

        $cid = $model->getID();

        $title = JText::_('COM_THM_ORGANIZER') . ': ';
        $this->setLayout('default');
        $title = JText::_('COM_THM_ORGANIZER') . ': ';
        $title .= ($cid)? JText::_('JTOOLBAR_EDIT') : JText::_('JTOOLBAR_NEW');
        $title .= " " . JText::_('COM_THM_ORGANIZER_VIRTUAL_SCHEDULE');
        JToolbarHelper::title($title, "organizer_virtual_schedules");
        $this->addToolBar();

        $this->legend = ($cid)? JText::_('JTOOLBAR_EDIT') : JText::_('JTOOLBAR_NEW');

        parent::display($tpl);
    }

    /**
     * Method to add the toolbar
     *
     * @return void
     */
    private function addToolBar()
    {
        JToolbarHelper::save('virtual_schedule.save');
        JToolbarHelper::cancel('virtual_schedule.cancel');
    }
}
