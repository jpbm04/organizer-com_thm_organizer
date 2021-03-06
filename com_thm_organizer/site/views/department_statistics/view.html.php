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

define('K_PATH_IMAGES', JPATH_ROOT . '/media/com_thm_organizer/images/');
jimport('tcpdf.tcpdf');

/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/schedule.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/component.php';

/**
 * Class loads department statistics into the display context.
 */
class THM_OrganizerViewDepartment_Statistics extends JViewLegacy
{
    public $fields = [];

    /**
     * Sets context variables and renders the view.
     *
     * @param string $tpl template
     *
     * @return void sets context variables and uses the parent's display method
     */
    public function display($tpl = null)
    {
        $this->modifyDocument();

        $this->lang = THM_OrganizerHelperLanguage::getLanguage();

        $this->model = $this->getModel();

        $this->setBaseFields();
        $this->setFilterFields();

        parent::display($tpl);
    }

    /**
     * Modifies document variables and adds links to external files
     *
     * @return void
     */
    private function modifyDocument()
    {
        JHtml::_('bootstrap.framework');
        JHtml::_('bootstrap.tooltip');
        JHtml::_('jquery.ui');
        JHtml::_('formbehavior.chosen', 'select');

        $document = JFactory::getDocument();
        $document->addScript(JUri::root() . '/media/com_thm_organizer/js/department_statistics.js');
        $document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/department_statistics.css');
    }

    private function setBaseFields()
    {
        $attribs                      = [];
        $this->fields['baseSettings'] = [];

        $options  = $this->model->getYearOptions();
        $default  = date('Y');
        $ppSelect = JHtml::_('select.genericlist', $options, 'year', $attribs, 'value', 'text', $default);

        $this->fields['baseSettings']['planningPeriodIDs'] = [
            'label'       => JText::_('COM_THM_ORGANIZER_YEAR'),
            'description' => JText::_('COM_THM_ORGANIZER_YEAR_EXPORT_DESC'),
            'input'       => $ppSelect
        ];
    }

    /**
     * Creates resource selection fields for the form
     *
     * @return void sets indexes in $this->fields['resouceSettings'] with html content
     */
    private function setFilterFields()
    {
        $this->fields['filterFields'] = [];
        $attribs                      = ['multiple' => 'multiple'];

        $roomAttribs                             = $attribs;
        $roomAttribs['data-placeholder']         = JText::_('COM_THM_ORGANIZER_ROOM_SELECT_PLACEHOLDER');
        $planRoomOptions                         = $this->model->getRoomOptions();
        $roomSelect                              = JHtml::_('select.genericlist', $planRoomOptions, 'roomIDs[]',
            $roomAttribs, 'value', 'text');
        $this->fields['filterFields']['roomIDs'] = [
            'label'       => JText::_('COM_THM_ORGANIZER_ROOMS'),
            'description' => JText::_('COM_THM_ORGANIZER_ROOMS_EXPORT_DESC'),
            'input'       => $roomSelect
        ];

        $roomTypeAttribs                         = $attribs;
        $roomTypeAttribs['onChange']             = 'repopulateRooms();';
        $roomTypeAttribs['data-placeholder']     = JText::_('COM_THM_ORGANIZER_ROOM_TYPE_SELECT_PLACEHOLDER');
        $typeOptions                             = $this->model->getRoomTypeOptions();
        $roomTypeSelect                          = JHtml::_('select.genericlist', $typeOptions, 'typeIDs[]',
            $roomTypeAttribs, 'value', 'text');
        $this->fields['filterFields']['typeIDs'] = [
            'label'       => JText::_('COM_THM_ORGANIZER_ROOM_TYPES'),
            'description' => JText::_('COM_THM_ORGANIZER_ROOMS_EXPORT_DESC'),
            'input'       => $roomTypeSelect
        ];
    }
}
