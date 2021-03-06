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
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/component.php';

/**
 * Class creates a PDF file for the display of the filtered schedule information.
 */
class THM_OrganizerViewSchedule_Export extends JViewLegacy
{
    public $document;

    /**
     * Sets context variables and renders the view.
     *
     * @param string $tpl template
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $libraryInstalled = $this->checkLibraries();

        if (!$libraryInstalled) {
            return;
        }

        $model      = $this->getModel();
        $parameters = $model->parameters;
        $grid       = empty($model->grid) ? null : $model->grid;
        $lessons    = $model->lessons;

        $fileName = $parameters['documentFormat'] . '_' . $parameters['displayFormat'] . '_' . $parameters['pdfWeekFormat'];
        require_once __DIR__ . "/tmpl/$fileName.php";
        new THM_OrganizerTemplateSchedule_Export_PDF($parameters, $lessons, $grid);
    }

    /**
     * Imports libraries and sets library variables
     *
     * @return bool true if the tcpdf library is installed, otherwise false
     * @throws Exception
     */
    private function checkLibraries()
    {
        $this->compiler = jimport('tcpdf.tcpdf');

        if (!$this->compiler) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_501'), 501);
        }

        return true;
    }
}
