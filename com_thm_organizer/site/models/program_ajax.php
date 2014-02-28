<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelProgram_Ajax
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_ADMINISTRATOR . '/components/com_thm_organizer/assets/helpers/mapping.php';

/**
 * Class provides methods for retrieving program data
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelProgram_Ajax extends JModel
{
    /**
     * Constructor to set up the class variables and call the parent constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retrieves subject entries from the database
     * 
     * @return  string  the subjects which fit the selected resource
     */
    public function programsByTeacher()
    {
        $dbo = JFactory::getDbo();
        $language = explode('-', JFactory::getLanguage()->getTag());
        $query = $dbo->getQuery(true);
        $query->select("dp.id, CONCAT( dp.subject_{$language[0]}, ', (', d.abbreviation, ' ', dp.version, ')') AS name");
        $query->from('#__thm_organizer_programs AS dp');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.programID = dp.id');
        $query->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');

        $teacherClauses = THM_OrganizerHelperMapping::getTeacherMappingClauses();
        if (!empty($teacherClauses))
        {
            $query->where("( ( " . implode(') OR (', $teacherClauses) . ") )");
        }

        $query->order('name');
        $dbo->setQuery((string) $query);
        $programs = $dbo->loadObjectList();
        return json_encode($programs);
    }
}
