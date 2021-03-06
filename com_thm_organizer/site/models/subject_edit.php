<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/models/edit.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/component.php';

/**
 * Class loads a form for editing data.
 */
class THM_OrganizerModelSubject_Edit extends THM_OrganizerModelEdit
{
    /**
     * Method to get a table object, load it if necessary.
     *
     * @param string $name    The table name. Optional.
     * @param string $prefix  The class prefix. Optional.
     * @param array  $options Configuration array for model. Optional.
     *
     * @return JTable  A JTable object
     */
    public function getTable($name = '', $prefix = 'THM_OrganizerTable', $options = [])
    {
        JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_thm_organizer/tables');

        return JTable::getInstance("subjects", $prefix, $options);
    }
}
