<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerModelLessons
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class encapsulating data abstraction and business logic for lessons.
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperXMLLessons
{
	/**
	 * The schedule model
	 *
	 * @var object
	 */
	private $_scheduleModel = null;

	/**
	 * Whether or not rooms should produce blocking errors.
	 *
	 * @var bool
	 */
	private $_roomsRequired = true;

	/**
	 * The name of the lesson. (subject & type)
	 *
	 * @var string
	 */
	private $_lessonName = '';

	/**
	 * The lesson's id.
	 *
	 * @var string
	 */
	private $_lessonID = '';

	/**
	 * A unique identifier for the lesson across schedules. (dpt., sem., id)
	 *
	 * @var string
	 */
	private $_lessonIndex = '';

	/**
	 * Creates the lesson model
	 *
	 * @param   object &$scheduleModel the model for the schedule
	 * @param   object &$xmlObject     the xml object being validated
	 */
	public function __construct(&$scheduleModel, &$xmlObject)
	{
		$this->_scheduleModel = $scheduleModel;
		$this->_xmlObject     = $xmlObject;
		$formData             = JFactory::getApplication()->input->get('jform', array(), 'array');
		$this->_roomsRequired = !empty($formData['rooms_required']);
	}

	/**
	 * Checks whether subject nodes have the expected structure and required information
	 *
	 * @return void
	 */
	public function validate()
	{
		if (empty($this->_xmlObject->lessons))
		{
			$this->_scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_LESSONS_MISSING");

			return;
		}

		$this->_scheduleModel->schedule->lessons = new stdClass;

		foreach ($this->_xmlObject->lessons->children() as $lessonNode)
		{
			$this->validateIndividual($lessonNode);
		}

		if (!empty($this->scheduleWarnings['LESSON-METHOD']))
		{
			$warningCount = $this->scheduleWarnings['LESSON-METHOD'];
			unset($this->scheduleWarnings['LESSON-METHOD']);
			$this->scheduleWarnings[] = JText::sprintf('COM_THM_ORGANIZER_WARNING_METHODID', $warningCount);
		}

		return;
	}

	/**
	 * Checks whether lesson nodes have the expected structure and required
	 * information
	 *
	 * @param   object &$lessonNode a SimpleXML object modeling the lesson node to be validated
	 *
	 * @return void
	 */
	private function validateIndividual(&$lessonNode)
	{
		$this->_lessonIndex = '';
		$this->_lessonName  = '';

		$this->_lessonID = $this->validateUntisID(trim((string) $lessonNode[0]['id']));
		if (empty($this->_lessonID))
		{
			return;
		}

		$department         = $this->_scheduleModel->schedule->departmentname;
		$semester           = $this->_scheduleModel->schedule->semestername;
		$this->_lessonIndex = $department . $semester . "_" . $this->_lessonID;

		if (!isset($this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}))
		{
			$this->_scheduleModel->schedule->lessons->{$this->_lessonIndex} = new stdClass;
		}

		$this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->gpuntisID = $this->_lessonID;

		$subjectID  = str_replace('SU_', '', trim((string) $lessonNode->lesson_subject[0]['id']));
		$lessonName = $this->validateSubject($subjectID, $department);
		if (!$lessonName)
		{
			return;
		}

		// Set before completion so that the error message is built correctly
		$this->_lessonName = $lessonName;

		$methodID = $this->validateMethod($lessonNode);
		if (!empty($methodID))
		{
			$lessonName .= " - $methodID";
		}

		$this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->name = $lessonName;

		$teacherID    = str_replace('TR_', '', trim((string) $lessonNode->lesson_teacher[0]['id']));
		$teacherValid = $this->validateTeacher($teacherID);
		if (!$teacherValid)
		{
			return;
		}

		$possibleGrid                                                         = (string) $lessonNode->timegrid;
		$grid                                                                 = empty($possibleGrid) ? 'Haupt-Zeitraster' : $possibleGrid;
		$this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->grid = $grid;

		$poolIDs    = (string) $lessonNode->lesson_classes[0]['id'];
		$poolsValid = $this->validatePools($poolIDs, $grid);
		if (!$poolsValid)
		{
			return;
		}

		$startDT    = strtotime(trim((string) $lessonNode->effectivebegindate));
		$endDT      = strtotime(trim((string) $lessonNode->effectiveenddate));
		$datesValid = $this->validateDates($startDT, $endDT);
		if (!$datesValid)
		{
			return;
		}

		$rawOccurrences = trim((string) $lessonNode->occurence);
		$occurrences    = $this->validateRawOccurrences($rawOccurrences, $startDT, $endDT);

		$comment = trim((string) $lessonNode->text);

		// Ensures that the comment is set and empty. '.' is used in Untis to ensure that a comment is correctly associated.
		if (empty($comment) OR $comment == '.')
		{
			$comment = '';
		}

		$this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->comment = $comment;

		$times = $lessonNode->xpath("times/time");

		// Cannot produce blocking errors
		$this->validateOccurrences($occurrences, $startDT, $times, $grid);
	}

	/**
	 * Checks if the untis id is valid
	 *
	 * @param   string $rawUntisID the untis lesson id
	 *
	 * @return  mixed  string if valid, otherwise false
	 */
	private function validateUntisID($rawUntisID)
	{
		$withoutPrefix = str_replace("LS_", '', $rawUntisID);
		$untisID       = substr($withoutPrefix, 0, strlen($withoutPrefix) - 2);
		if (empty($untisID))
		{
			if (!in_array(JText::_("COM_THM_ORGANIZER_ERROR_LESSON_ID_MISSING"), $this->_scheduleModel->scheduleErrors))
			{
				$this->_scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_LESSON_ID_MISSING");
			}

			return false;
		}

		return $untisID;
	}

	/**
	 * Validates the subjectID and builds dependant structural elements
	 *
	 * @param   string $subjectID  the id of the subject
	 * @param   string $department the name of the department
	 *
	 * @return  mixed  string the name of the lesson (subjects) on success,
	 *                 otherwise boolean false
	 */
	private function validateSubject($subjectID, $department)
	{
		$subjectIndex = $department . "_" . $subjectID;
		if (!isset($this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->subjects))
		{
			if (empty($subjectID))
			{
				$this->_scheduleModel->scheduleErrors[]
					= JText::sprintf("COM_THM_ORGANIZER_ERROR_LESSON_SUBJECT_MISSING", $this->_lessonID);

				return false;
			}

			if (empty($this->_scheduleModel->schedule->subjects->$subjectIndex))
			{
				$this->_scheduleModel->scheduleErrors[]
					= JText::sprintf("COM_THM_ORGANIZER_ERROR_LESSON_SUBJECT_LACKING", $this->_lessonID, $subjectID);

				return false;
			}

			$this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->subjects = new stdClass;
		}

		if (!empty($subjectID)
			AND !array_key_exists($subjectIndex, $this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->subjects)
		)
		{
			$this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->subjects->$subjectIndex = '';
		}

		$subjectIndexes = array_keys((array) $this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->subjects);
		$lessonName     = implode(' / ', $subjectIndexes);

		return str_replace($department . '_', '', $lessonName);
	}

	/**
	 * Validates the description
	 *
	 * @param   object &$lessonNode the lesson node
	 *
	 * @return  boolean  string the methodID on success, otherwise false
	 */
	private function validateMethod(&$lessonNode)
	{
		$methodID = str_replace('DS_', '', trim((string) $lessonNode->lesson_description));

		$invalidMethod = (empty($methodID) OR empty($this->_scheduleModel->schedule->methods->$methodID));
		if ($invalidMethod)
		{
			$this->_scheduleModel->scheduleWarnings['LESSON-METHOD']
				= empty($this->_scheduleModel->scheduleWarnings['LESSON-METHOD']) ?
				1 : $this->_scheduleModel->scheduleWarnings['LESSON-METHOD'] + 1;

			return '';
		}

		$this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->description = $methodID;
		$this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->methodID
		                                                                             = $this->_scheduleModel->schedule->methods->$methodID->id;

		return $methodID;
	}

	/**
	 * Validates the teacher attribute and sets corresponding schedule elements
	 *
	 * @param   string $teacherID the teacher id
	 *
	 * @return  boolean  true if valid, otherwise false
	 */
	private function validateTeacher($teacherID)
	{
		$teacherFound = false;
		if (empty($teacherID))
		{
			$this->_scheduleModel->scheduleErrors[]
				= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_TEACHER_MISSING', $this->_lessonName, $this->_lessonID);

			return false;
		}
		else
		{
			foreach ($this->_scheduleModel->schedule->teachers as $teacherKey => $teacher)
			{
				if ($teacher->localUntisID == $teacherID)
				{
					$teacherFound = true;
					$teacherID    = $teacherKey;
					break;
				}
			}

			if (!$teacherFound)
			{
				$this->_scheduleModel->scheduleErrors[]
					= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_TEACHER_LACKING', $this->_lessonName, $this->_lessonID, $teacherID);

				return false;
			}
		}

		if (!isset($this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->teachers))
		{
			$this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->teachers = new stdClass;
		}

		if (!array_key_exists($teacherID, $this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->teachers))
		{
			$this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->teachers->$teacherID = '';
		}

		return true;
	}

	/**
	 * Validates the pools attribute and sets corresponding schedule elements
	 *
	 * @param   string $poolIDs the ids of the associated pools as string
	 * @param   string $grid    the name of the grid in which this lesson should be displayed
	 *
	 * @return  boolean  true if valid, otherwise false
	 */
	private function validatePools($poolIDs, $grid)
	{
		if (empty($poolIDs) AND !isset($this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->pools))
		{
			$this->_scheduleModel->scheduleErrors[]
				= JText::sprintf("COM_THM_ORGANIZER_ERROR_LESSON_POOL_MISSING", $this->_lessonName, $this->_lessonID);

			return false;
		}
		elseif (!empty($poolIDs))
		{
			if (!isset($this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->pools))
			{
				$this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->pools = new stdClass;
			}

			$poolIDs   = explode(" ", $poolIDs);
			$gridFound = false;
			foreach ($poolIDs as $poolID)
			{
				$poolID    = str_replace('CL_', '', $poolID);
				$poolFound = false;
				foreach ($this->_scheduleModel->schedule->pools as $poolKey => $pool)
				{
					if ($pool->localUntisID == $poolID)
					{
						$poolFound = true;
						$poolID    = $poolKey;
						if ($grid == $pool->grid)
						{
							$gridFound = true;
						}

						break;
					}
				}

				if (!$poolFound)
				{
					$this->_scheduleModel->scheduleErrors[]
						= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_POOL_LACKING', $this->_lessonName, $this->_lessonID, $poolID);

					return false;
				}

				$this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->pools->$poolID = '';
			}

			if (!$gridFound)
			{
				$this->_scheduleModel->scheduleErrors[]
					= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_POOL_GRID_INCONSISTENT', $this->_lessonName, $this->_lessonID, $grid);

				return false;
			}
		}

		return true;
	}

	/**
	 * Checks for the validity and consistency of date values
	 *
	 * @param   int $startDT the startdate as integer
	 * @param   int $endDT   the enddate as integer
	 *
	 * @return  boolean  true if dates are valid, otherwise false
	 */
	private function validateDates($startDT, $endDT)
	{

		if (empty($startDT))
		{
			$this->_scheduleModel->scheduleErrors[]
				= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_START_DATE_MISSING', $this->_lessonName, $this->_lessonID);

			return false;
		}

		$syStartTime     = strtotime($this->_scheduleModel->schedule->startdate);
		$syEndTime       = strtotime($this->_scheduleModel->schedule->enddate);
		$lessonStartDate = date('Y-m-d', $startDT);

		$validStartDate = ($startDT >= $syStartTime AND $startDT <= $syEndTime);
		if (!$validStartDate)
		{
			$this->_scheduleModel->scheduleErrors[]
				= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_START_DATE_INVALID', $this->_lessonName, $this->_lessonID, $lessonStartDate);

			return false;
		}

		if (empty($endDT))
		{
			$this->_scheduleModel->scheduleErrors[]
				= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_END_DATE_MISSING', $this->_lessonName, $this->_lessonID);

			return false;
		}

		$lessonEndDate = date('Y-m-d', $endDT);

		$validEndDate = ($endDT >= $syStartTime AND $endDT <= $syEndTime);
		if (!$validEndDate)
		{
			$this->_scheduleModel->scheduleErrors[]
				= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_END_DATE_INVALID', $this->_lessonName, $this->_lessonID, $lessonEndDate);

			return false;
		}

		// Checks if startdate is before enddate
		if ($endDT < $startDT)
		{
			$this->_scheduleModel->scheduleErrors[] =
				JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_DATES_INCONSISTENT',
					$this->_lessonName,
					$this->_lessonID,
					$lessonStartDate,
					$lessonEndDate
				);

			return false;
		}

		return true;
	}

	/**
	 * Validates the occurrences attribute
	 *
	 * @param   string $raw   the string containing the occurrences
	 * @param   int    $start the timestamp of the lesson's begin
	 * @param   int    $end   the timestamp of the lesson's end
	 *
	 * @return  mixed   array if valid, otherwise false
	 */
	private function validateRawOccurrences($raw, $start, $end)
	{
		if (empty($raw))
		{
			$this->_scheduleModel->scheduleErrors[]
				= JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_OCC_MISSING', $this->_lessonName, $this->_lessonID);

			return false;
		}

		// Increases the end value one day (Untis uses inclusive dates)
		$end = strtotime('+1 day', $end);

		// 86400 is the number of seconds in a day 24 * 60 * 60
		$offset = floor(($start - strtotime($this->_scheduleModel->schedule->startdate)) / 86400);
		$length = floor(($end - $start) / 86400);

		// Change occurrences from a string to an array of the appropriate length for iteration
		return str_split(substr($raw, $offset, $length));
	}

	/**
	 * Iterates over possible occurrences and validates them
	 *
	 * @param   array  $occurrences an array of 'occurrences'
	 * @param   int    $currentDT   the starting timestamp
	 * @param   array  &$instances  the object containing the instances
	 * @param   string $grid        the grid used by the lesson
	 *
	 * @return  void
	 */
	private function validateOccurrences($occurrences, $currentDT, &$instances, $grid)
	{
		if (count($instances) == 0)
		{
			return;
		}

		foreach ($occurrences as $occurrence)
		{
			$currentDate      = date('Y-m-d', $currentDT);
			$outOfBounds      = empty($this->_scheduleModel->schedule->calendar->$currentDate);
			$notPlannedOnDate = ($occurrence == '0' OR $occurrence == 'F');
			if ($outOfBounds OR $notPlannedOnDate)
			{
				$currentDT = strtotime('+1 day', $currentDT);
				continue;
			}

			foreach ($instances as $instance)
			{
				$valid = $this->validateInstance($instance, $currentDT, $grid);
				if (!$valid)
				{
					return;
				}
			}
			$currentDT = strtotime('+1 day', $currentDT);
		}

		return;
	}

	/**
	 * Validates a lesson instance
	 *
	 * @param   object &$instance the lesson instance
	 * @param   int    $currentDT the current date time in the iteration
	 * @param   string $grid      the grid used by the lesson
	 *
	 * @return  boolean  true if valid, otherwise false
	 */
	private function validateInstance(&$instance, $currentDT, $grid)
	{
		$currentDate = date('Y-m-d', $currentDT);

		// Sporadic lessons occur on specific dates
		$assigned_date = strtotime(trim((string) $instance->assigned_date));

		// The lesson is sporadic and does not occur on the date being currently iterated
		if (!empty($assigned_date) AND $assigned_date != $currentDT)
		{
			return true;
		}

		$day      = trim((string) $instance->assigned_day);
		$validDay = $this->validateInstanceDay($day, $currentDT);

		// The lesson does not occur on the day (true) or the day is invalid (false)
		if ($validDay === true OR $validDay === false)
		{
			return $validDay;
		}

		$period = $this->validatePeriod(trim((string) $instance->assigned_period), $currentDate, $grid);
		if (!$period)
		{
			return false;
		}

		$roomAttribute = trim((string) $instance->assigned_room[0]['id']);
		if (empty($roomAttribute))
		{
			$throwError = $this->handleMissingRooms($currentDT, $period);
			if ($throwError)
			{
				return false;
			}
		}
		else
		{
			$roomsValid = $this->validateRooms($roomAttribute, $currentDT, $period);
			if (!$roomsValid)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Validates whether the instance day attribute
	 *
	 * @param   string $day       the numeric day of the week
	 * @param   int    $currentDT the current date time in the iteration
	 *
	 * @return  mixed  boolean false if the day is missing, true if the lesson
	 *                 does not occur on the given day, otherwise the integer dow
	 */
	private function validateInstanceDay($day, $currentDT)
	{
		if (empty($day))
		{
			$error = JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_PERIOD_DAY_MISSING', $this->_lessonName, $this->_lessonID);
			if (!in_array($error, $this->_scheduleModel->scheduleErrors))
			{
				$this->_scheduleModel->scheduleErrors[] = $error;

				return false;
			}
		}

		if ($day != date('w', $currentDT))
		{
			// Does not occur on this date, no error
			return true;
		}

		return $day;
	}

	/**
	 * Validates the period attribute of an instance
	 *
	 * @param   string $period      the period attribute
	 * @param   string $currentDate the date in the current iteration
	 * @param   string $grid        the grid used by the lesson
	 *
	 * @return  boolean  true on success,
	 */
	private function validatePeriod($period, $currentDate, $grid)
	{
		if (empty($period))
		{
			$error = JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_PERIOD_NUMBER_MISSING', $this->_lessonName, $this->_lessonID);
			if (!in_array($error, $this->_scheduleModel->scheduleErrors))
			{
				$this->_scheduleModel->scheduleErrors[] = $error;

				return false;
			}
		}

		if (!isset($this->_scheduleModel->schedule->periods->$grid))
		{
			$error = JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_GRID_INCONSISTENT', $this->_lessonName, $this->_lessonID, $grid);
			if (!in_array($error, $this->_scheduleModel->scheduleErrors))
			{
				$this->_scheduleModel->scheduleErrors[] = $error;

				return false;
			}
		}

		if (!isset($this->_scheduleModel->schedule->periods->$grid->$period))
		{
			$error = JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_GRID_PERIOD_INCONSISTENT', $this->_lessonName, $this->_lessonID, $period, $grid);
			if (!in_array($error, $this->_scheduleModel->scheduleErrors))
			{
				$this->_scheduleModel->scheduleErrors[] = $error;

				return false;
			}
		}

		// Should not occur, but creates the period anyway
		if (!isset($this->_scheduleModel->schedule->calendar->$currentDate->$period))
		{
			$this->_scheduleModel->schedule->calendar->$currentDate->$period = new stdClass;
		}

		return $period;
	}

	/**
	 * Determines how the missing room attribute will be handled
	 *
	 * @param   string $currentDT the timestamp of the date being iterated
	 * @param   string $period    the value of the period attribute
	 *
	 * @return  boolean  true if blocking and not set elsewhere, otherwise false
	 */
	private function handleMissingRooms($currentDT, $period)
	{
		$currentDate = date('Y-m-d', $currentDT);

		// Attribute has also not been set by any other lesson
		if (!isset($this->_scheduleModel->schedule->calendar->$currentDate->$period->{$this->_lessonIndex}))
		{
			$pools        = implode(', ', array_keys(get_object_vars($this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->pools)));
			$dow          = strtoupper(date('l', $currentDT));
			$localizedDoW = JText::_($dow);
			$error        = JText::sprintf('COM_THM_ORGANIZER_ERROR_LESSON_ROOM_MISSING',
				$this->_lessonName,
				$this->_lessonID,
				$pools,
				$localizedDoW,
				$period
			);
			if (!in_array($error, $this->_scheduleModel->scheduleErrors) AND !in_array($error, $this->_scheduleModel->scheduleWarnings))
			{
				if ($this->_roomsRequired)
				{
					$this->_scheduleModel->scheduleErrors[] = $error;

					return true;
				}
				else
				{
					$this->_scheduleModel->scheduleWarnings[] = $error;

					return false;
				}
			}
		}

		// Attribute has been set by another lesson
		return false;
	}

	/**
	 * Validates the room attribute
	 *
	 * @param   string $roomAttribute the room attribute
	 * @param   int    $currentDT     the timestamp of the date being iterated
	 * @param   string $period        the period attribute
	 *
	 * @return  boolean  true on success, otherwise false
	 */
	private function validateRooms($roomAttribute, $currentDT, $period)
	{
		$currentDate = date('Y-m-d', $currentDT);

		$roomIDs = explode(' ', str_replace('RM_', '', $roomAttribute));
		foreach ($roomIDs as $roomID)
		{
			$roomFound = false;
			foreach ($this->_scheduleModel->schedule->rooms as $roomKey => $room)
			{
				if ($room->localUntisID == $roomID)
				{
					$roomFound = true;
					$roomID    = $roomKey;
					break;
				}
			}

			if (!$roomFound)
			{
				$pools        = implode(', ', array_keys($this->_scheduleModel->schedule->lessons->{$this->_lessonIndex}->pools));
				$dow          = strtoupper(date('l', $currentDT));
				$localizedDoW = JText::_($dow);
				$error        = JText::sprintf(
					'COM_THM_ORGANIZER_ERROR_LESSON_ROOM_LACKING',
					$this->_lessonName, $this->_lessonID, $pools,
					$localizedDoW, $period, $roomID
				);
				if (!in_array($error, $this->_scheduleModel->scheduleErrors))
				{
					$this->_scheduleModel->scheduleErrors[] = $error;
				}

				return false;
			}
			else
			{
				if (!isset($this->_scheduleModel->schedule->calendar->$currentDate->$period->{$this->_lessonIndex}))
				{
					$this->_scheduleModel->schedule->calendar->$currentDate->$period->{$this->_lessonIndex} = new stdClass;
				}

				$lessonIndexes = get_object_vars($this->_scheduleModel->schedule->calendar->$currentDate->$period->{$this->_lessonIndex});
				if (!empty($roomID) AND !in_array($roomID, $lessonIndexes))
				{
					$this->_scheduleModel->schedule->calendar->$currentDate->$period->{$this->_lessonIndex}->$roomID = '';
				}
			}
		}

		return true;
	}
}