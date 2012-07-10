<?php
/**
 *@category    Joomla component
 *
 *@package     THM_Organizer
 *
 *@subpackage  com_thm_organizer.site
 *@name		   Ressource
 *@description Ressource file from com_thm_organizer
 *@author	   Wolf Rost, wolf.rost@mni.thm.de
 *
 *@copyright   2012 TH Mittelhessen
 *
 *@license     GNU GPL v.2
 *@link		   www.mni.thm.de
 *@version	   1.0
 */

defined('_JEXEC') or die;

/**
 * Class Ressource for component com_thm_organizer
 *
 * Class provides methods to load a schedule
 *
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       1.5
 */
class Ressource
{
	/**
	 * Joomla data abstraction
	 *
	 * @var    DataAbstraction
	 * @since  1.0
	 */
	private $_JDA = null;

	/**
	 * Configuration object
	 *
	 * @var    MySchedConfig
	 * @since  1.0
	 */
	private $_CFG = null;

	/**
	 * GPUntis id
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_gpuntisID = null;

	/**
	 * Node id
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_nodeID = null;

	/**
	 * Node key
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_nodeKey = null;

	/**
	 * Semester id
	 *
	 * @var    Integer
	 * @since  1.0
	 */
	private $_semID = null;

	/**
	 * Type
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_type = null;

	/**
	 * Plantype id
	 *
	 * @var    Integer
	 * @since  1.0
	 */
	private $_plantypeID = null;

	/**
	 * Constructor with the joomla data abstraction object and configuration object
	 *
	 * @param   DataAbstraction  $JDA  A object to abstract the joomla methods
	 * @param   MySchedConfig    $CFG  A object which has configurations including
	 *
	 * @since  1.5
	 *
	 */
	public function __construct($JDA, $CFG)
	{
		$this->JDA = $JDA;
		$this->nodeID = $JDA->getRequest("nodeID");
		$this->gpuntisID = $JDA->getRequest("gpuntisID");
		$this->nodeKey = $JDA->getRequest("nodeKey");
		$this->plantypeID = $JDA->getRequest("plantypeID");
		$this->type = $JDA->getRequest("type");
		$this->semID = $JDA->getSemID();
	}

	/**
	 * Method to load a schedule
	 *
	 * @return Array An array with information about the status of the creation
	 */
	public function load()
	{
		if (isset($this->gpuntisID) && isset($this->semID) && isset($this->nodeID) && isset($this->nodeKey))
		{
			$elements   = null;
			$lessons    = array();
			$retlessons = array();

			if (stripos($this->gpuntisID, "VS_") === 0)
			{
				$elements = $this->getElements($this->nodeKey, $this->semID, $this->type);
				$retlessons["elements"] = "";

				$elementsGpuntisIDs = "";

				foreach ($elements as $k => $v)
				{
					if ($elementsGpuntisIDs == "")
					{
						$elementsGpuntisIDs = $v->gpuntisID;
					}
					else
					{
						$elementsGpuntisIDs .= "', '" . $v->gpuntisID;
					}

					$elementIDs = $this->GpuntisIDToid($v->gpuntisID, $this->type);
					$elementID = $elementIDs[0]->id;
					if ($retlessons["elements"] == "")
					{
						$retlessons["elements"] .= $elementID;
					}
					else
					{
						$retlessons["elements"] .= ";" . $elementID;
					}
				}

				$lessons = $this->getResourcePlan($elementsGpuntisIDs, $this->semID, $this->type);

			}
			else
			{
				$lessons = $this->getResourcePlan($this->nodeKey, $this->semID, $this->type);
			}

			if (is_array($lessons))
			{
				foreach ($lessons as $item)
				{
					$key = $item->lid . " " . $item->tpid;
					if (!isset($retlessons[$key]))
					{
						$retlessons[$key] = array();
					}
					$retlessons[$key]["type"]    = $item->type;
					$retlessons[$key]["id"]      = $item->id;
					$retlessons[$key]["subject"] = $item->subject;
					$retlessons[$key]["dow"]     = $item->dow;
					$retlessons[$key]["block"]   = $item->block;

					if (isset($retlessons[$key]["clas"]))
					{
						$arr = explode(" ", $retlessons[$key]["clas"]);
						if (!in_array($item->clas, $arr))
						{
							$retlessons[$key]["clas"] = $retlessons[$key]["clas"] . " " . $item->clas;
						}
					}
					else
					{
						$retlessons[$key]["clas"] = $item->clas;
					}

					if (isset($retlessons[$key]["doz"]))
					{
						$arr = explode(" ", $retlessons[$key]["doz"]);
						if (!in_array($item->doz, $arr))
						{
							$retlessons[$key]["doz"] = $retlessons[$key]["doz"] . " " . $item->doz;
						}
					}
					else
					{
						$retlessons[$key]["doz"] = $item->doz;
					}

					if (isset($retlessons[$key]["room"]))
					{
						$arr = explode(" ", $retlessons[$key]["room"]);
						if (!in_array($item->room, $arr))
						{
							$retlessons[$key]["room"] = $retlessons[$key]["room"] . " " . $item->room;
						}
					}
					else
					{
						$retlessons[$key]["room"] = $item->room;
					}

					$retlessons[$key]["category"] = $item->category;
					$retlessons[$key]["key"]      = $this->semID . "." . $this->plantypeID . "." . $key;
					$retlessons[$key]["owner"]    = "gpuntis";
					$retlessons[$key]["showtime"] = "none";
					$retlessons[$key]["etime"]    = null;
					$retlessons[$key]["stime"]    = null;
					$retlessons[$key]["name"]     = $item->name;
					$retlessons[$key]["desc"]     = $item->description;
					$retlessons[$key]["cell"]     = "";
					$retlessons[$key]["css"]      = "";
					$retlessons[$key]["longname"] = $item->longname;
					$retlessons[$key]["plantypeID"] = $this->plantypeID;
					$retlessons[$key]["semesterID"] = $this->semID;
					$retlessons[$key]["moduleID"] = $item->moduleID;
					$retlessons[$key]["comment"] = $item->comment;
					$retlessons[$key]["ecollaborationLink"] = $this->getEcollaborationLink($this->nodeKey, $item->moduleID);
				}
			}

			return array("success" => true,"data" => $retlessons);
		}
	}

	/**
	 * Method to get the ecollaboration link
	 *
	 * @param   String  $res 	   The resource pguntis id
	 * @param   String  $moduleID  The resource module id
	 *
	 * @return Object Returns the ecollaboration link or null
	 */
	private function getEcollaborationLink($res, $moduleID)
	{
		if ($this->JDA->isComponentavailable("com_thm_curriculum"))
		{
			$organizer_major = "";
			$query = "SELECT major " .
					"FROM #__thm_organizer_classes " .
					"WHERE gpuntisID = '" . $res . "'";
			$ret   = $this->JDA->query($query);

			if (isset($ret[0]))
			{
				$organizer_major = $ret[0]->major;
			}
			else
			{
				return null;
			}

			$query = "SELECT ecollaboration_link as ecolLink " .
					"FROM #__thm_curriculum_assets_tree " .
					"INNER JOIN #__thm_curriculum_assets ON #__thm_curriculum_assets.id = #__thm_curriculum_assets_tree.asset " .
					"INNER JOIN #__thm_curriculum_assets_semesters " .
					"ON #__thm_curriculum_assets_tree.id = #__thm_curriculum_assets_semesters.assets_tree_id " .
					"INNER JOIN #__thm_curriculum_semesters_majors " .
					"ON #__thm_curriculum_assets_semesters.semesters_majors_id = #__thm_curriculum_semesters_majors.id " .
					"INNER JOIN #__thm_curriculum_majors " .
					"ON #__thm_curriculum_majors.id = #__thm_curriculum_semesters_majors.major_id " .
					"WHERE #__thm_curriculum_majors.organizer_major = '" . $organizer_major . "'" .
					"AND LOWER(#__thm_curriculum_assets.lsf_course_code) = LOWER('" . $moduleID . "')";

			$ret   = $this->JDA->query($query);

			if (isset($ret[0]))
			{
				if (!empty($ret[0]->ecolLink))
				{
					return $ret[0]->ecolLink;
				}
			}
			return null;
		}
		return null;
	}

	/**
	 * Method to get the elements of a virtual schedule
	 *
	 * @param   String   $id 	The virtual schedule id
	 * @param   Integer  $sid   The semester id
	 * @param   String   $type 	The virtual schedule type
	 *
	 * @return Array An array with the virtual schedule elements
	 */
	private function getElements($id, $sid, $type)
	{
		$query = "SELECT eid as gpuntisID " .
				"FROM #__thm_organizer_virtual_schedules_elements ";
		$query .= "WHERE vid = '" . $id . "'";
		$ret   = $this->JDA->query($query);
		return $ret;
	}

	/**
	 * Method to transform a gpunits id to an id
	 *
	 * @param   String  $gpuntisID 	The gpunits id
	 * @param   String  $type  		The type
	 *
	 * @return An result with the id
	 */
	private function GpuntisIDToid($gpuntisID, $type)
	{
		$query = "SELECT id ";
		if ($type == "room")
		{
			$query .= "FROM #__thm_organizer_rooms ";
		}
		elseif ($type == "clas")
		{
			$query .= "FROM #__thm_organizer_classes ";
		}
		elseif ($type == "doz")
		{
			$query .= "FROM #__thm_organizer_teachers ";
		}
		$query .= "WHERE gpuntisID = '" . $gpuntisID . "'";
		$ret   = $this->JDA->query($query);
		return $ret;
	}

	/**
	 * Method to get the lesson for a resource
	 *
	 * @param   String   $ressourcename  The resource name
	 * @param   Integer  $fachsemester   The semester id
	 * @param   String   $type 	   		 The semester type
	 *
	 * @return Array An array with the lessons
	 */
	private function getResourcePlan($ressourcename, $fachsemester, $type)
	{
		$query = "SELECT " .
				"#__thm_organizer_lessons.gpuntisID AS lid, " .
				"#__thm_organizer_periods.gpuntisID AS tpid, " .
				"#__thm_organizer_lessons.gpuntisID AS id, " .
				"#__thm_organizer_subjects.alias AS description, " .
				"#__thm_organizer_subjects.id AS subject, " .
				"#__thm_organizer_lessons.type AS category, " .
				"#__thm_organizer_subjects.name AS name, " .
				"#__thm_organizer_classes.id AS clas, " .
				"#__thm_organizer_teachers.id AS doz, " .
				"#__thm_organizer_rooms.id AS room, " .
				"#__thm_organizer_periods.day AS dow, " .
				"#__thm_organizer_periods.period AS block, " .
				"#__thm_organizer_subjects.moduleID as moduleID, " .
				"(SELECT 'cyclic') AS type, " .
				"#__thm_organizer_lessons.comment AS comment, ";

		if ($this->JDA->isComponentavailable("com_thm_curriculum"))
		{
			$query .= " IF(#__thm_organizer_subjects.moduleID='','',mo.title_de) AS longname ";
		}
		else
		{
			$query .= " '' AS longname ";
		}
		$query .= "FROM #__thm_organizer_lessons " .
				"LEFT JOIN #__thm_organizer_lesson_times ON #__thm_organizer_lessons.id = #__thm_organizer_lesson_times.lessonID " .
				"LEFT JOIN #__thm_organizer_periods ON #__thm_organizer_lesson_times.periodID = #__thm_organizer_periods.id " .
				"LEFT JOIN #__thm_organizer_rooms ON #__thm_organizer_lesson_times.roomID = #__thm_organizer_rooms.id " .
		 	"LEFT JOIN #__thm_organizer_lesson_teachers ON #__thm_organizer_lesson_teachers.lessonID = #__thm_organizer_lessons.id " .
		 	"LEFT JOIN #__thm_organizer_teachers ON #__thm_organizer_lesson_teachers.teacherID = #__thm_organizer_teachers.id " .
		 	"LEFT JOIN #__thm_organizer_lesson_classes ON #__thm_organizer_lesson_classes.lessonID = #__thm_organizer_lessons.id " .
		 	"LEFT JOIN #__thm_organizer_classes ON #__thm_organizer_lesson_classes.classID = #__thm_organizer_classes.id " .
		 	"LEFT JOIN #__thm_organizer_subjects ON #__thm_organizer_lessons.subjectID = #__thm_organizer_subjects.id ";
		if ($this->JDA->isComponentavailable("com_thm_curriculum"))
		{
			$query .= "LEFT JOIN #__thm_curriculum_assets AS mo ON LOWER(#__thm_organizer_subjects.moduleID) = LOWER(mo.lsf_course_code) ";
		}
		else
		{

		}
		$query .= "WHERE #__thm_organizer_lessons.semesterID = " . $fachsemester . " " .
				"AND #__thm_organizer_lessons.plantypeID = " . $this->plantypeID . " " .
				"AND (";
		if ($type === "clas")
		{
			$query .= "(#__thm_organizer_classes.id IN ('" . $ressourcename . "'))" .
					" OR (#__thm_organizer_classes.gpuntisID IN ('" . $ressourcename . "'))";
		}
		elseif ($type === "room")
		{
			$query .= "(#__thm_organizer_rooms.id IN ('" . $ressourcename . "'))" .
					" OR (#__thm_organizer_rooms.gpuntisID IN ('" . $ressourcename . "'))";
		}
		elseif ($type === "doz")
		{
			$query .= "(#__thm_organizer_teachers.id IN ('" . $ressourcename . "'))" .
					" OR (#__thm_organizer_teachers.gpuntisID IN ('" . $ressourcename . "'))";
		}
		elseif ($type === "subject")
		{
			$query .= "(#__thm_organizer_subjects.id IN ('" . $ressourcename . "'))" .
					" OR (#__thm_organizer_subjects.gpuntisID IN ('" . $ressourcename . "'))";
		}

		$query .= ")";

		$hits  = $this->JDA->query($query);
		return $hits;
	}
}
