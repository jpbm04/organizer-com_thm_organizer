<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelSchedule_Ajax
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/pools.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/programs.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/rooms.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/schedule.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/teachers.php';

define('SEMESTER_MODE', 1);
define('PERIOD_MODE', 2);
define('INSTANCE_MODE', 3);

/**
 * Class provides methods for retrieving program data
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSchedule_Ajax extends JModelLegacy
{
	/**
	 * Getter method for programs
	 *
	 * @return string  a json coded array of available program objects
	 */
	public function getPrograms()
	{
		$programs = THM_OrganizerHelperPrograms::getPlanPrograms();

		$results = array();
		foreach ($programs as $program)
		{
			$name           = empty($program['name']) ? $program['ppName'] : $program['name'];
			$results[$name] = $program['id'];
		}

		return empty($results) ? '[]' : json_encode($results);
	}

	/**
	 * Getter method for pools
	 *
	 * @return string  all pools in JSON format
	 */
	public function getPools()
	{
		$selectedPrograms = JFactory::getApplication()->input->getString('programIDs');
		$programIDs       = explode(",", $selectedPrograms);
		$result           = THM_OrganizerHelperPools::getPlanPools(count($programIDs) == 1);

		return empty($result) ? '[]' : json_encode($result);
	}

	/**
	 * Getter method for room types
	 *
	 * @throws RuntimeException
	 * @return string  all room types in JSON format
	 */
	public function getRoomTypes()
	{
		$languageTag = THM_OrganizerHelperLanguage::getShortTag();

		/** @noinspection PhpIncludeInspection */
		require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/rooms.php';
		$rooms = THM_OrganizerHelperRooms::getPlanRooms();

		$relevantIDs = array();
		foreach ($rooms as $room)
		{
			if (!empty($room['typeID']))
			{
				$relevantIDs[$room['typeID']] = $room['typeID'];
			}
		}

		$query = $this->_db->getQuery(true);
		$query->select("id, name_$languageTag AS name")
			->from('#__thm_organizer_room_types AS type');

		if (!empty($relevantIDs))
		{
			$query->where("id IN ('" . implode("','", $relevantIDs) . "')");
		}

		$query->order('name');
		$this->_db->setQuery($query);

		try
		{
			// Like teachers, pools etc. roomTypes are returned as an ["name" => "id"] array instead of an object
			$result = $this->_db->loadAssocList('name', 'id');
		}
		catch (RuntimeException $exc)
		{
			return '[]';
		}

		return empty($result) ? '[]' : json_encode($result);
	}

	/**
	 * Getter method for rooms in database
	 *
	 * @throws RuntimeException
	 * @return string  all rooms in JSON format
	 */
	public function getRooms()
	{
		$departmentID = JFactory::getApplication()->input->getInt('departmentIDs');
		$typeID       = JFactory::getApplication()->input->getInt('typeID');

		$query = $this->_db->getQuery(true);
		$query->select("roo.id, roo.longname AS name")
			->from('#__thm_organizer_rooms AS roo');

		if ($departmentID != 0)
		{
			$query->leftJoin('#__thm_organizer_department_resources AS dr ON roo.id = dr.roomID');
			$query->where("dr.departmentID = $departmentID");
		}

		$query->where("roo.typeID = $typeID");
		$query->order('name');
		$this->_db->setQuery($query);

		try
		{
			// Like teachers, pools etc. rooms are returned as an ["name" => "id"] array instead of an object
			$result = $this->_db->loadAssocList('name', 'id');
		}
		catch (RuntimeException $exc)
		{
			return '[]';
		}

		return empty($result) ? '[]' : json_encode($result);
	}

	/**
	 * Getter method for teachers in database
	 *
	 * @return string  all teachers in JSON format
	 */
	public function getTeachers()
	{
		$result = THM_OrganizerHelperTeachers::getPlanTeachers();

		return empty($result) ? '[]' : json_encode($result);
	}

	/**
	 * get lessons by chosen resource
	 *
	 * @return string JSON coded lessons
	 */
	public function getLessons()
	{
		$input       = JFactory::getApplication()->input;
		$inputParams = $input->getArray();
		$inputKeys   = array_keys($inputParams);
		$parameters  = array();
		foreach ($inputKeys as $key)
		{
			if (preg_match('/\w+IDs/', $key))
			{
				$parameters[$key] = explode(',', $inputParams[$key]);
			}
		}

		$parameters['userID']          = JFactory::getUser()->id;
		$parameters['mySchedule']      = $input->getBool('mySchedule', false);
		$oneDay                        = $input->getBool('oneDay', false);
		$parameters['dateRestriction'] = $oneDay ? 'day' : 'week';
		$parameters['date']            = $input->getString('date');
		$parameters['format']          = '';
		$deltaDays                     = $input->getString('deltaDays', '14');
		$parameters['delta']           = empty($deltaDays) ? '' : date('Y-m-d', strtotime("-" . $deltaDays . " days"));

		$lessons = THM_OrganizerHelperSchedule::getLessons($parameters);

		return empty($lessons) ? '[]' : json_encode($lessons);
	}

	/**
	 * saves lessons in the personal schedule of the logged in user
	 *
	 * @return string JSON coded and saved ccmIDs
	 */
	public function saveLesson()
	{
		$input       = JFactory::getApplication()->input;
		$mode        = $input->getInt('mode', PERIOD_MODE);
		$ccmID       = $input->getString('ccmID');
		$userID      = JFactory::getUser()->id;
		$savedCcmIDs = array();

		if (JFactory::getUser()->guest OR empty($ccmID))
		{
			return '[]';
		}

		$mappings = $this->getMatchingLessons($mode, $ccmID);
		foreach ($mappings as $lessonID => $ccmIDs)
		{
			try
			{
				$userLessonTable = JTable::getInstance('user_lessons', 'thm_organizerTable');
				$hasUserLesson   = $userLessonTable->load(array('userID' => $userID, 'lessonID' => $lessonID));
			}
			catch (Exception $e)
			{
				return '[]';
			}

			$conditions = array(
				'userID'    => $userID,
				'lessonID'  => $lessonID,
				'user_date' => date('Y-m-d H:i:s')
			);

			if ($hasUserLesson)
			{
				$conditions['id'] = $userLessonTable->id;
				$oldCcmIds        = json_decode($userLessonTable->configuration);
				$ccmIDs           = array_merge($ccmIDs, array_diff($oldCcmIds, $ccmIDs));
			}

			$conditions['configuration'] = $ccmIDs;

			if ($userLessonTable->bind($conditions) AND $userLessonTable->store())
			{
				$savedCcmIDs = array_merge($savedCcmIDs, $ccmIDs);
			}
		}

		return json_encode($savedCcmIDs);
	}

	/**
	 * Get startTime, endTime, schedule_date, day of week and subjectID from calendar_configuration_map table
	 *
	 * @param int $ccmID primary key of ccm
	 *
	 * @return object|boolean
	 */
	private function getCalendarData($ccmID)
	{
		$query = $this->_db->getQuery(true);
		$query->select('cal.lessonID, startTime, endTime, schedule_date, DAYOFWEEK(schedule_date) AS weekday, subjectID')
			->from('#__thm_organizer_calendar_configuration_map AS map')
			->innerJoin('#__thm_organizer_calendar AS cal ON cal.id = map.calendarID')
			->innerJoin('#__thm_organizer_lessons AS l ON l.id = cal.lessonID')
			->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.lessonID = l.id')
			->where("map.id = '$ccmID'")
			->where("cal.delta != 'removed'");

		$query->order('map.id');
		$this->_db->setQuery($query);

		try
		{
			$calReference = $this->_db->loadObject();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		return empty($calReference) ? false : $calReference;
	}

	/**
	 * Get an array with matching ccmIDs, sorted by lessonIDs
	 *
	 * @param int $mode  global param like SEMESTER_MODE
	 * @param int $ccmID primary key of ccm
	 *
	 * @return array (lessonID => array(ccmIDs))
	 */
	private function getMatchingLessons($mode, $ccmID)
	{
		$calReference = $this->getCalendarData($ccmID);
		if (!$calReference)
		{
			return array();
		}

		// Only lessonID for one ccmID
		if ($mode == INSTANCE_MODE)
		{
			return array($calReference->lessonID => array($ccmID));
		}

		// Get lessonIDs
		$query = $this->_db->getQuery(true);
		$query->select('ls.lessonID')
			->from('#__thm_organizer_lesson_subjects AS ls')
			->innerJoin('#__thm_organizer_lessons AS l ON l.id = ls.lessonID')
			->innerJoin('#__thm_organizer_planning_periods AS p ON p.id = l.planningPeriodID')
			->where("p.startDate <= '$calReference->schedule_date'")
			->where("p.endDate >= '$calReference->schedule_date'")
			->where("subjectID = '$calReference->subjectID'")
			->order('lessonID');

		$this->_db->setQuery($query);

		try
		{
			$lessonIDs = $this->_db->loadColumn();
		}
		catch (RuntimeException $e)
		{
			return array();
		}

		$result = array();
		foreach ($lessonIDs as $lessonID)
		{
			$query = $this->_db->getQuery(true);
			$query->select('map.id')
				->from('#__thm_organizer_calendar_configuration_map AS map')
				->innerJoin('#__thm_organizer_calendar AS cal ON cal.id = map.calendarID')
				->where("cal.lessonID = '$lessonID'")
				->where("delta != 'removed'");

			// Lessons for same day of the week and same time
			if ($mode == PERIOD_MODE)
			{
				$query->where("cal.startTime = '$calReference->startTime'");
				$query->where("cal.endTime = '$calReference->endTime'");
				$query->where("DAYOFWEEK(cal.schedule_date) = '$calReference->weekday'");
			}

			$query->order('map.id');
			$this->_db->setQuery($query);
			try
			{
				$ccmIDs = $this->_db->loadColumn(0);
				if (!empty($ccmIDs))
				{
					$result[$lessonID] = $ccmIDs;
				}
			}
			catch (RuntimeException $e)
			{
				return array();
			}
		}

		return $result;
	}

	/**
	 * deletes lessons in the personal schedule of a logged in user
	 *
	 * @return string JSON coded and deleted ccmIDs
	 */
	public function deleteLesson()
	{
		$input  = JFactory::getApplication()->input;
		$mode   = $input->getInt('mode', PERIOD_MODE);
		$ccmID  = $input->getString('ccmID');
		$userID = JFactory::getUser()->id;

		if (JFactory::getUser()->guest OR empty($ccmID))
		{
			return '[]';
		}

		$mappings      = $this->getMatchingLessons($mode, $ccmID);
		$deletedCcmIDs = array();
		foreach ($mappings as $lessonID => $ccmIDs)
		{
			try
			{
				$userLessonTable = JTable::getInstance('user_lessons', 'thm_organizerTable');
				if (!$userLessonTable->load(array('userID' => $userID, 'lessonID' => $lessonID)))
				{
					continue;
				}
			}
			catch (Exception $e)
			{
				return '[]';
			}

			$deletedCcmIDs = array_merge($deletedCcmIDs, $ccmIDs);

			// Delete a lesson completely? delete whole row in database
			if ($mode == SEMESTER_MODE)
			{
				$userLessonTable->delete($userLessonTable->id);
			}
			else
			{
				$configurations = array_flip(json_decode($userLessonTable->configuration));
				foreach ($ccmIDs as $ccmID)
				{
					unset($configurations[$ccmID]);
				}

				$configurations = array_flip($configurations);
				if (empty($configurations))
				{
					$userLessonTable->delete($userLessonTable->id);
				}
				else
				{
					$conditions = array(
						'id'            => $userLessonTable->id,
						'userID'        => $userID,
						'lessonID'      => $userLessonTable->lessonID,
						'configuration' => array_values($configurations),
						'user_date'     => date('Y-m-d H:i:s')
					);
					$userLessonTable->bind($conditions);
				}

				$userLessonTable->store();
			}
		}

		return json_encode($deletedCcmIDs);
	}
}
