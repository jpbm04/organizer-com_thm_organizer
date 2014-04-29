<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        TreeView
 * @description TreeView file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . "/components/com_thm_organizer/assets/classes/TreeNode.php";

/**
 * Class TreeView for component com_thm_organizer
 * Class provides methods to create the tree view for mysched
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelScheduler_Tree extends JModelLegacy
{
    /**
     * Joomla data abstraction
     *
     * @var    DataAbstraction
     */
    private $_JDA = null;

    /**
     * Config
     *
     * @var    Object
     */
    private $_cfg = null;

    /**
     * Checked
     *
     * @var    String
     */
    private $_checked = null;

    /**
     * Public default node
     *
     * @var    Array
     */
    private $_publicDefault = null;

    /**
     * Hide the checkboxes
     *
     * @var    Boolean
     */
    private $_hideCheckBox = null;

    /**
     * Which schedules are in the tree
     *
     * @var    Object
     */
    private $_inTree = array();

    /**
     * The tree data
     *
     * @var    Array
     */
    private $_treeData = array();

    /**
     * The pubic default node
     *
     * @var    Object
     */
    private $_publicDefaultNode = null;

    /**
     * Active schedule data
     *
     * @var    Object
     */
    private $_activeScheduleData = null;

    /**
     * Constructor with the joomla data abstraction object and configuration object
     *
     * @param   DataAbstraction  $JDA      A object to abstract the joomla methods
     * @param   MySchedConfig    $CFG      A object which has configurations including
     * @param   Array            $options  An Array with some options
     */
    public function __construct($JDA, $CFG, $options = array())
    {
        $this->_JDA = $JDA;
        $this->_cfg = $CFG->getCFG();
 
        $menuid = JFactory::getApplication()->input->getInt("menuID", 0);
 
        $site = new JSite;
        $menu = $site->getMenu();
 
        if ($menuid != 0)
        {
            $menuparams = $menu->getParams($menuid);
        }
        else
        {
            $menuparams = $menu->getParams($menu->getActive()->id);
            $options["hide"] = true;
        }
 
        if (isset($options["path"]))
        {
            $this->_checked = (array) $options["path"];
        }
        else
        {
            $treeIDs = JFactory::getApplication()->input->getString('treeIDs');
            $treeIDsData = json_decode($treeIDs);
            if ($treeIDsData != null)
            {
                $this->_checked = (array) $treeIDsData;
            }
            else
            {
                $this->_checked = (array) json_decode($menuparams->get("id"));
            }
        }
 
        if (isset($options["publicDefault"]))
        {
            $this->_publicDefault = (array) $options["publicDefault"];
        }
        else
        {
            $publicDefaultID = json_decode(JFactory::getApplication()->input->getString('publicDefaultID'));
            if ($publicDefaultID != null)
            {
                $this->_publicDefault = (array) $publicDefaultID;
            }
            else
            {
                $this->_publicDefault = (array) json_decode($menuparams->get("publicDefaultID"));
            }
        }
 
        if (isset($options["hide"]))
        {
            $this->_hideCheckBox = $options["hide"];
        }
        else
        {
            $this->_hideCheckBox = false;
        }
 
        if (JFactory::getApplication()->input->getString('departmentSemesterSelection') == "")
        {
            if (isset($options["departmentSemesterSelection"]))
            {
                $this->departmentSemesterSelection = $options["departmentSemesterSelection"];
            }
            else
            {
                $this->departmentSemesterSelection    = $menuparams->get("departmentSemesterSelection");
            }
        }
        else
        {
            $this->departmentSemesterSelection = JFactory::getApplication()->input->getString('departmentSemesterSelection');
        }
    }

    /**
     * Method to create a tree node
     *
     * @param   Integer  $nodeID             The node id
     * @param   String   $text               The node text
     * @param   String   $iconCls            The nodes icon class
     * @param   Boolean  $leaf               Is the node leaf
     * @param   Boolean  $draggable          Is the node dragable
     * @param   Boolean  $singleClickExpand  Should the node expand on single click
     * @param   String   $gpuntisID          The gpuntis id for this node
     * @param   String   $type               The nodes type (room, teacher, class)
     * @param   Object   $children           The nodes children
     * @param   Integer  $semesterID         In which semester is this node
     * @param   String   $nodeKey            The node key
     *
     * @return Tree nodes
     */
    private function createTreeNode($nodeID,
     $text,
     $iconCls,
     $leaf,
     $draggable,
     $singleClickExpand,
     $gpuntisID,
     $type,
     $children,
     $semesterID,
     $nodeKey)
    {

        $checked = null;
        $publicDefault = null;
        $treeNode = null;
 
        if ($this->_hideCheckBox == true)
        {
            $checked = null;
        }
        else
        {
            if ($this->_checked != null)
            {
                if (isset($this->_checked[$nodeID]))
                {
                    $checked = $this->_checked[$nodeID];
                }
                else
                {
                    $checked = "unchecked";
                }
            }
            else
            {
                $checked = "unchecked";
            }
        }

        $expanded = false;

        if ($this->_publicDefault != null)
        {
            $publicDefaultArray = $this->_publicDefault;
            $firstValue = each($publicDefaultArray);

            if (strpos($firstValue["key"], $nodeID) === 0)
            {
                $expanded = true;
            }
            if ($leaf === true)
            {
                if (isset($this->_publicDefault[$nodeID]))
                {
                    $publicDefault = $this->_publicDefault[$nodeID];
                }
                else
                {
                    $publicDefault = "notdefault";
                }
            }
        }
        elseif ($leaf === true)
        {
            $publicDefault = "notdefault";
        }

        if ($this->_hideCheckBox == true)
        {
            if ($this->nodeStatus($nodeID))
            {
                $treeNode = new THMTreeNode(
                        $nodeID,
                        $text,
                        $iconCls,
                        $leaf,
                        $draggable,
                        $singleClickExpand,
                        $gpuntisID,
                        $type,
                        $children,
                        $semesterID,
                        $checked,
                        $publicDefault,
                        $nodeKey,
                        $expanded
                );
                $this->_inTree[] = $gpuntisID;
            }
        }
        else
        {
            $treeNode = new THMTreeNode(
                    $nodeID,
                    $text,
                    $iconCls,
                    $leaf,
                    $draggable,
                    $singleClickExpand,
                    $gpuntisID,
                    $type,
                    $children,
                    $semesterID,
                    $checked,
                    $publicDefault,
                    $nodeKey,
                    $expanded
            );
        }

        if ($publicDefault === "default")
        {
            if ($treeNode != null)
            {
                $this->_publicDefaultNode = $treeNode;
            }
            else
            {
                $this->_publicDefaultNode = new THMTreeNode(
                        $nodeID,
                        $text,
                        $iconCls,
                        $leaf,
                        $draggable,
                        $singleClickExpand,
                        $gpuntisID,
                        $type,
                        $children,
                        $semesterID,
                        $checked,
                        $publicDefault,
                        $nodeKey,
                        $expanded
                );
            }
        }

        if ($treeNode == null)
        {
            return $children;
        }
        return $treeNode;
    }

    /**
     * Method to check if the node is checked
     *
     * @param   Integer  $nodeID  The node id
     *
     * @return Boolean true if the node is checked unless false
     */
    private function nodeStatus($nodeID)
    {
        if (isset($this->_checked[$nodeID]))
        {
            if ($this->_checked[$nodeID] === "checked" || $this->_checked[$nodeID] === "intermediate")
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            foreach ($this->_checked as $checkedKey => $checkedValue)
            {
                if (strpos($nodeID, $checkedKey) !== false)
                {
                    if ($checkedValue === "selected" || $checkedValue === "intermediate")
                    {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Method to create the tree
     *
     * @return Array An array with the tree data
     */
    public function load()
    {
        $semesterJahrNode = array();

        $activeSchedule = $this->getActiveSchedule();
 
        if (is_object($activeSchedule) && is_string($activeSchedule->schedule))
        {
            $activeScheduleData = json_decode($activeSchedule->schedule);
 
            // To save memory unset schedule
            unset($activeSchedule->schedule);

            if ($activeScheduleData != null)
            {
                $this->_activeScheduleData = $activeScheduleData;
                $this->_treeData["module"] = $activeScheduleData->modules;
                $this->_treeData["room"] = $activeScheduleData->rooms;
                $this->_treeData["teacher"] = $activeScheduleData->teachers;
                $this->_treeData["subject"] = $activeScheduleData->subjects;
                $this->_treeData["roomtype"] = $activeScheduleData->roomtypes;
                $this->_treeData["degree"] = $activeScheduleData->degrees;
                $this->_treeData["field"] = $activeScheduleData->fields;
            }
            else
            {
                // Cant decode json
                return JError::raiseWarning(404, JText::_('COM_THM_ORGANIZER_SCHEDULER_DATA_FLAWED'));
            }
 
            // Get ids for teachers and rooms
            $schedulerModel = JModel::getInstance('scheduler', 'thm_organizerModel', array('ignore_request' => false, 'display_type' => 4));
            $rooms = $schedulerModel->getRooms();
            $teachers = $schedulerModel->getTeachers();
 
            foreach ($this->_treeData["room"] as $roomValue)
            {
                foreach ($rooms as $databaseRooms)
                {
                    if ($roomValue->gpuntisID === $databaseRooms->gpuntisID)
                    {
                        $roomValue->dbID = $databaseRooms->id;
                    }
                }
            }
 
            foreach ($this->_treeData["teacher"] as $teacherValue)
            {
                foreach ($teachers as $databaseTeachers)
                {
                    if ($teacherValue->gpuntisID === $databaseTeachers->gpuntisID)
                    {
                        $teacherValue->dbID = $databaseTeachers->id;
                    }
                }
            }
 
        }
        else
        {
            return array("success" => false,"data" => array("tree" => array(), "treeData" => array(), "treePublicDefault" => ""));
        }

        $temp = $this->createTreeNode(
                $this->departmentSemesterSelection,
                $activeSchedule->semestername,
                'semesterjahr' . '-root',
                false,
                false,
                true,
                $activeSchedule->id,
                null,
                null,
                $activeSchedule->id,
                $activeSchedule->id
        );
        $children = $this->StundenplanView($this->departmentSemesterSelection, $activeSchedule->id);
 
        if ($temp != null && !empty($temp))
        {
            $temp->setChildren($children);
 
            if (count($temp) == 1)
            {
                $semesterJahrNode = $temp;
            }
            else
            {
                $semesterJahrNode[] = $temp;
            }
        }
        elseif (!empty($children))
        {
            $semesterJahrNode = $children;
        }

        $this->expandSingleNode($semesterJahrNode);

        if (!isset($this->_publicDefaultNode))
        {
            $this->_publicDefaultNode = null;
        }
 
        return array("success" => true,"data" => array("tree" => $semesterJahrNode, "treeData" => $this->_treeData,
                "treePublicDefault" => $this->_publicDefaultNode)
        );
    }

    /**
     * Method to create the schedule nodes (teacher, room, class)
     *
     * @param   Integer  $key         The node key
     * @param   Integer  $semesterID  The semester id
     *
     * @return The schedule node
     */
    private function StundenplanView($key, $semesterID)
    {
        $scheduleTypes = array("teacher", "room", "module", "subject");
        $viewNode = array();

        foreach ($scheduleTypes as $scheduleType)
        {
            $nodeKey = $key . ";" . $scheduleType;
            $textConstant = 'COM_THM_ORGANIZER_SCHEDULER_' . $scheduleType . 'PLAN';
            $temp = $this->createTreeNode(
                    $nodeKey,
                    JText::_($textConstant),
                    'view' . '-root',
                    false,
                    false,
                    true,
                    $scheduleType,
                    null,
                    null,
                    $semesterID,
                    $nodeKey
            );
            $children = $this->getStundenplan($nodeKey, $scheduleType, $semesterID);
 
            if ($temp != null && !empty($temp))
            {
                $temp->setChildren($children);
                $viewNode[] = $temp;
            }
            elseif (!empty($children))
            {
                if (count($children) == 1)
                {
                    $viewNode = $children;
                }
                else
                {
                    $viewNode[] = $children;
                }
            }
        }

        return $viewNode;
    }

    /**
     * Method to get the schedule lessons
     *
     * @param   Integer  $key           The node key
     * @param   String   $scheduleType  The schedule type
     * @param   Integer  $semesterID    The semester id
     *
     * @return A tree node
     */
    private function getStundenplan($key, $scheduleType, $semesterID)
    {
        $treeNode = array();
        $descriptions = array();
        $data = $this->_treeData[$scheduleType];

        foreach ($data as $item)
        {
            if ($scheduleType === "teacher")
            {
                if (isset($item->description))
                {
                    $itemField = $item->description;
                    $itemFieldType = $this->_activeScheduleData->fields;
                }
                else
                {
                    continue;
                }
            }
            elseif ($scheduleType === "room")
            {
                if (isset($item->description))
                {
                    $itemField = $item->description;
                    $itemFieldType = $this->_activeScheduleData->roomtypes;
                }
                else
                {
                    continue;
                }
            }
            elseif ($scheduleType === "module")
            {
                if (isset($item->degree))
                {
                    $itemField = $item->degree;
                    $itemFieldType = $this->_activeScheduleData->degrees;
                }
                else
                {
                    continue;
                }
            }
            elseif ($scheduleType === "subject")
            {
                if (isset($item->description))
                {
                    $itemField = $item->description;
                    $itemFieldType = $this->_activeScheduleData->fields;
                }
                else
                {
                    continue;
                }
            }
 
            if (!empty($itemField) && !in_array($itemField, $descriptions))
            {
                $descriptions[$itemField] = $itemFieldType->{$itemField};
            }
        }

        foreach ($descriptions as $descriptionKey => $descriptionValue)
        {
            $descType = $descriptionKey;
 
            // Get data for the current description
            $filteredData = array_filter(
                    (array) $data, function ($item) use (&$descType, &$scheduleType) {
                $itemField = null;
                if ($scheduleType === "teacher")
                {
                    if (isset($item->description))
                    {
                        $itemField = $item->description;
                    }
                }
                elseif ($scheduleType === "room")
                {
                    if (isset($item->description))
                    {
                        $itemField = $item->description;
                    }
                }
                elseif ($scheduleType === "module")
                {
                    if (isset($item->degree))
                    {
                        $itemField = $item->degree;
                    }
                }
                elseif ($scheduleType === "subject")
                {
                    if (isset($item->description))
                    {
                        $itemField = $item->description;
                    }
                }

                if ($itemField === $descType)
                {
                    return true;
                }

                return false;
                    }
            );
 
            $childNodes = array();
            $descriptionID = $key . ";" . $descriptionKey;
 
            foreach ($filteredData as $childKey => $childValue)
            {
                $nodeID = $descriptionID . ";" . $childKey;
                if ($scheduleType === "teacher")
                {
                    if (strlen($childValue->surname) > 0)
                    {
                        $nodeName = $childValue->surname;
                    }
                    else
                    {
                        $nodeName = $childKey;
                    }
 
                    if (strlen($childValue->firstname) > 0)
                    {
                        $nodeName .= ", " . $childValue->firstname{0} . ".";
                    }
                }
                elseif ($scheduleType === "room")
                {
                    if (strlen($childValue->longname) > 0)
                    {
                        $nodeName = $childValue->longname;
                    }
                    else
                    {
                        $nodeName = $childKey;
                    }
                }
                elseif ($scheduleType === "module")
                {
                    if (strlen($childValue->restriction) > 0)
                    {
                        $nodeName = $childValue->restriction;
                    }
                    else
                    {
                        $nodeName = $childKey;
                    }
                }
                elseif ($scheduleType === "subject")
                {
                    if (strlen($childValue->longname) > 0)
                    {
                        $nodeName = $childValue->longname;
                    }
                    else
                    {
                        $nodeName = $childKey;
                    }
                }
                else
                {
                    $nodeName = $childValue->gpuntisID;
                }

                // Überprüfung ob der Plan Veranstaltungen hat
                if ($this->_hideCheckBox == false)
                {
                    $hasLessons = true;
                }
                else
                {
                    $hasLessons = $this->treeNodeHasLessons($childKey, $scheduleType);
                }
 
                // Erstmal immer true!
//                 $hasLessons = true;
 
                $childNode = null;
                if ($hasLessons)
                {
                    $childNode = $this->createTreeNode(
                            $nodeID,
                            $nodeName,
                            "leaf" . "-node",
                            true,
                            true,
                            false,
                            $childValue->gpuntisID,
                            $scheduleType,
                            null,
                            $semesterID,
                            $childKey
                    );
                }
                if (is_object($childNode))
                {
                    $childNodes[] = $childNode;
                }
            }
 
            if (empty($childNodes))
            {
                $childNodes = null;
            }
            $descriptionNode = null;
            if ($childNodes != null)
            {
                $descriptionNode = $this->createTreeNode(
                        $descriptionID,
                        $descriptionValue->name,
                        "studiengang-root",
                        false,
                        true,
                        false,
                        $descriptionValue->gpuntisID,
                        $scheduleType,
                        $childNodes,
                        $semesterID,
                        $descriptionKey
                );
            }
 
            if (!is_null($descriptionNode) && is_object($descriptionNode))
            {
                $treeNode[] = $descriptionNode;
            }
        }
 
        return $treeNode;
    }

    /**
     * Method to mark a single node as expanded
     *
     * @param   Array  &$arr  An reference to a node child
     *
     * @return void
     */
    private function expandSingleNode(& $arr)
    {
        if (gettype($arr) !== "object" && gettype($arr) !== "array")
        {
            return;
        }

        foreach ($arr as $v)
        {
            if (!isset($v->children))
            {
                $this->expandSingleNode($v);
            }
            elseif (is_array($v->children))
            {
                if (count($arr) > 1)
                {
                    return;
                }

                $v->expanded = true;

                $this->expandSingleNode($v->children);
            }
        }
    }

    /**
     * Method to get the active schedule
     *
     * @return mixed  The active schedule as object or false
     */
    public function getActiveSchedule()
    {
        $departmentSemester = explode(";", $this->departmentSemesterSelection);
        if (count($departmentSemester) == 2)
        {
            $department = $departmentSemester[0];
            $semester = $departmentSemester[1];
        }
        else
        {
            return false;
        }
 
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('*');
        $query->from('#__thm_organizer_schedules');
        $query->where('departmentname = ' . $dbo->quote($department));
        $query->where('semestername = ' . $dbo->quote($semester));
        $query->where('active = 1');
        $dbo->setQuery($query);
 
        if ($dbo->getErrorMsg())
        {
            return false;
        }

        $result = $dbo->loadObject();
 
        if ($result === null)
        {
            return false;
        }
        return $result;
    }
 
    /**
     * Method to check if an tree node has lessons
     *
     * @param   Object  $nodeID  The tree node id
     * @param   String  $type    The tree node type
     *
     * @return  boolean
     */
    private function treeNodeHasLessons($nodeID, $type)
    {
        foreach ($this->_activeScheduleData->calendar as $calendarValue)
        {
            if (is_object($calendarValue))
            {
                foreach ($calendarValue as $blockValue)
                {
                    foreach ($blockValue as $lessonKey => $lessonValue)
                    {
                        if ($type == "subject" || $type == "module" || $type == "teacher")
                        {
                            $fieldType = $type . "s";
                            foreach (array_keys((array) $this->_activeScheduleData->lessons->{$lessonKey}->{$fieldType}) as $typeKey)
                            {
                                if ($typeKey == $nodeID)
                                {
                                    return true;
                                }
                            }
                        }
                        elseif ($type == "room")
                        {
                            foreach (array_keys((array) $lessonValue) as $roomKey)
                            {
                                if ($roomKey == $nodeID)
                                {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }
        return false;
    }
}
