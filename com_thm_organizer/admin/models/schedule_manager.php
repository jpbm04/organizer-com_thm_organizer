<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        business logic and database abstraction specific to listing schedules
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');
/**
 * Class defining functions to be used for lesson resources
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v0.1.0
 */
class THM_OrganizerModelSchedule_Manager extends JModelList
{
    /**
     * array of departmentnames
     *
     * @var array
     */
    public $departments = null;

    /**
     * array of semesternames
     *
     * @var array
     */
    public $semesters = null;

    /**
     * sets variables and configuration data
     *
     * @param   array  $config  the configuration parameters
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                    'department', 'departmentname',
                    'semester', 'semestername',
                    'creationdate', 'creationdate',
                    'state', 'active', 'creationtime'
                );
        }
        parent::__construct($config);
        $this->departments = $this->getDepartments();
        $this->semesters = $this->getSemesters();
    }

    /**
     * generates the query to be used to fill the output list
     *
     * @return JDatabaseQuery
     */
    protected function getListQuery()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);

        $select = "id, departmentname, semestername, active, description, ";
        $select .= "creationdate AS creationdate, ";
        $select .= "creationtime AS creationtime, ";
        $select .= "startdate AS startdate, ";
        $select .= "enddate AS enddate ";
        $query->select($this->getState("list.select", $select));
        $query->from("#__thm_organizer_schedules");

        $state = $this->getState('filter.state');
        if ($state === '0')
        {
            $query->where("active = 0");
        }
        if ($state === '1')
        {
            $query->where("active = 1");
        }

        $semester = $this->getState('filter.semester');
        if (!empty($semester) and $semester != '*')
        {
            $query->where("semestername = '$semester'");
        }

        $department = $this->getState('filter.department');
        if (!empty($department) and $department != '*')
        {
            $query->where("departmentname = '$department'");
        }
        
        $stateResult = $this->get('state');
        $ordering = $dbo->escape($stateResult->get('list.ordering', 'creationdate'));
        $direction = $dbo->escape($stateResult->get('list.direction', 'DESC'));
        $query->order("$ordering $direction");

        return $query;
    }

    /**
     * takes user filter parameters and adds them to the view state
     *
     * @param   string  $ordering   the filter parameter to be used for ordering
     * @param   string  $direction  the direction in which results are to be ordered
     *
     * @return void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication('administrator');
        $dbo = $this->getDbo();

        $ordering = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'creationdate');
        $this->setState('list.ordering', $ordering);

        $direction = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'ASC');
        $this->setState('list.direction', $direction);

        $limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', '');
        $this->setState('limit', $limit);

        $state = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state');
        $this->setState('filter.state', $state);

        $semester = $dbo->escape($app->getUserStateFromRequest($this->context . '.filter.semester', 'filter_semester'));
        $this->setState('filter.semester', $semester);

        $department = $dbo->escape($app->getUserStateFromRequest($this->context . '.filter.department', 'filter_department'));
        $this->setState('filter.department', $department);

        parent::populateState($ordering, $direction);
    }

    /**
     * retrieves an array of named semesters from the database
     *
     * @return array filled with semester names or empty
     */
    private function getDepartments()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT departmentname AS name");
        $query->from("#__thm_organizer_schedules");
        $dbo->setQuery((string) $query);
        
        try 
        {
            $departments = $dbo->loadAssocList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        return (count($departments))? $departments : array();
    }

    /**
     * Retrieves an array of named semesters from the database
     *
     * @return array filled with semester names or empty
     */
    private function getSemesters()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT semestername AS name");
        $query->from("#__thm_organizer_schedules");
        $dbo->setQuery((string) $query);
        
        try 
        {
            $semesters = $dbo->loadAssocList();
            return (count($semesters))? $semesters : array();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }
    }
}
