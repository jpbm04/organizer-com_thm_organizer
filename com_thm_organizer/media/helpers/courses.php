<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/participants.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/subjects.php';

/**
 * Provides general functions for course access checks, data retrieval and display.
 */
class THM_OrganizerHelperCourses
{
    const MANUAL_ACCEPTANCE = 1;
    const PERIOD_MODE = 2;
    const INSTANCE_MODE = 3;

    /**
     * Check if user is registered as a teacher, optionally for a specific course
     *
     * @param int $courseID id of the course resource
     *
     * @return boolean if user is authorized
     * @throws Exception
     */
    public static function authorized($courseID = 0)
    {
        $user = JFactory::getUser();

        if (empty($user->id)) {
            return false;
        }

        if ($user->authorise('core.admin', "com_thm_organizer")) {
            return true;
        }

        $subjectID = self::getSubjectID($courseID);

        if (THM_OrganizerHelperSubjects::authorized($subjectID)) {
            return true;
        }

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select("COUNT(*)")
            ->from('#__thm_organizer_lesson_subjects AS ls')
            ->innerJoin('#__thm_organizer_lesson_teachers AS lt ON lt.subjectID = ls.id')
            ->innerJoin('#__thm_organizer_teachers AS t ON t.id = lt.teacherID')
            ->where("t.username = '{$user->username}'");

        if (!empty($courseID)) {
            $query->where("ls.lessonID = '$courseID'");
        }

        $dbo->setQuery($query);

        try {
            $assocCount = $dbo->loadResult();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

            return false;
        }

        return !empty($assocCount);
    }

    /**
     * Check if course with specific id is full
     *
     * @param int $courseID identifier of course
     *
     * @return bool true when course can accept more participants, false otherwise
     * @throws Exception
     */
    public static function canAcceptParticipant($courseID)
    {
        $course = self::getCourse($courseID);

        // Should not occur.
        if (empty($course)) {
            return false;
        }

        $manualAcceptance = (!empty($course['registration_type']) and $course['registration_type'] === self::MANUAL_ACCEPTANCE);

        if ($manualAcceptance) {
            return false;
        }

        $acceptedParticipants = count(self::getParticipants($courseID, 1));
        $maxParticipants      = empty($course["lessonP"]) ? $course["subjectP"] : $course["lessonP"];

        if (empty($maxParticipants)) {
            return true;
        }

        return ($acceptedParticipants < $maxParticipants);
    }

    /**
     * Creates a button for user interaction with the course. (De-/registration, Administration)
     *
     * @param string $view     the view to be redirected to after registration action
     * @param int    $courseID the id of the course
     *
     * @return string the HTML for the action button as appropriate for the user
     * @throws Exception
     */
    public static function getActionButton($view, $courseID)
    {
        $expired    = !self::isRegistrationOpen($courseID);
        $authorized = self::authorized($courseID);

        $lang            = THM_OrganizerHelperLanguage::getLanguage();
        $shortTag        = THM_OrganizerHelperLanguage::getShortTag();
        $menuID          = JFactory::getApplication()->input->getInt('Itemid');
        $pathPrefix      = "index.php?option=com_thm_organizer";
        $registrationURL = "$pathPrefix&task=$view.register&languageTag=$shortTag";
        $registrationURL .= $view == 'subject' ? '&id=' . JFactory::getApplication()->input->getInt('id', 0) : "";
        $manage          = '<span class="icon-cogs"></span>' . $lang->_("COM_THM_ORGANIZER_MANAGE");
        $managerURL      = "{$pathPrefix}&view=course_manager&languageTag=$shortTag";

        if (!empty($menuID)) {
            $managerURL      .= "&Itemid=$menuID";
            $registrationURL .= "&Itemid=$menuID";
        }

        if (!empty(JFactory::getUser()->id)) {
            $lessonURL = "&lessonID=$courseID";

            if ($authorized) {
                $managerRoute = JRoute::_($managerURL . $lessonURL);
                $register     = "<a class='btn' href='$managerRoute'>$manage</a>";
            } else {
                $regState = self::getParticipantState($courseID);

                if ($expired) {
                    $register = '';
                } else {
                    $registerRoute = JRoute::_($registrationURL . $lessonURL);
                    $disabled      = self::isRegistrationOpen($courseID) ? '' : 'disabled';

                    if (!empty($regState)) {
                        $registerText = '<span class="icon-out-2"></span>' . $lang->_('COM_THM_ORGANIZER_COURSE_DEREGISTER');
                    } else {
                        $registerText = '<span class="icon-apply"></span>' . $lang->_('COM_THM_ORGANIZER_COURSE_REGISTER');
                    }

                    $register = "<a class='btn $disabled' href='$registerRoute' type='button'>$registerText</a>";
                }
            }
        } else {
            $register = '';
        }

        return $register;
    }

    /**
     * Sets campus information (id and name) for a given course
     *
     * @param mixed $course    the course information (array|int|object)
     * @param bool  $redundant whether redundant names should be set
     *
     * @return array an array with the actionable campus id and name
     * @throws Exception
     */
    public static function getCampus($course, $redundant = false)
    {
        require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/campuses.php';

        if (is_object($course)) {
            $course = (array)$course;
        } elseif (is_int($course)) {
            $course = self::getCourse($course);
        }

        if (empty($course['abstractCampusID']) and empty($course['campusID'])) {
            $campus = ['id' => '', 'name' => THM_OrganizerHelperCampuses::getName()];
        } elseif (empty($course['campusID']) OR $course['abstractCampusID'] == $course['campusID']) {
            $campus         = ['id' => $course['abstractCampusID']];
            $campus['name'] = $redundant ? THM_OrganizerHelperCampuses::getName($course['abstractCampusID']) : null;
        } else {
            $campus = [
                'id'   => $course['campusID'],
                'name' => THM_OrganizerHelperCampuses::getName($course['campusID'])
            ];
        }

        return $campus;
    }

    /**
     * Loads course information from the database
     *
     * @param int $courseID int id of requested lesson
     *
     * @return array  with course data on success, otherwise empty
     * @throws Exception
     */
    public static function getCourse($courseID = 0)
    {
        $courseID = JFactory::getApplication()->input->getInt('lessonID', $courseID);

        if (empty($courseID)) {
            return [];
        }

        $shortTag = THM_OrganizerHelperLanguage::getShortTag();

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('pp.name as planningPeriodName, pp.id as planningPeriodID')
            ->select('l.id, l.max_participants as lessonP, l.campusID AS campusID, l.registration_type')
            ->select("s.id as subjectID, s.name_$shortTag as name, s.instructionLanguage, s.max_participants as subjectP")
            ->select('s.campusID AS abstractCampusID');

        $query->from('#__thm_organizer_lessons AS l');
        $query->leftJoin('#__thm_organizer_lesson_subjects AS ls ON ls.lessonID = l.id');
        $query->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.plan_subjectID = ls.subjectID');
        $query->leftJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id');
        $query->leftJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id');
        $query->leftJoin('#__thm_organizer_planning_periods AS pp ON l.planningPeriodID = pp.id');
        $query->where("l.id = '$courseID'");

        $dbo->setQuery($query);

        try {
            $courseData = $dbo->loadAssoc();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

            return [];
        }

        return empty($courseData) ? [] : $courseData;
    }

    /**
     * Creates a display of formatted dates for a course
     *
     * @param int $courseID the id of the course to be loaded
     *
     * @return string the dates to display
     * @throws Exception
     */
    public static function getDateDisplay($courseID = 0)
    {
        $courseID = JFactory::getApplication()->input->getInt('lessonID', $courseID);

        $dates = self::getDates($courseID);

        if (!empty($dates)) {
            $dateFormat = JComponentHelper::getParams('com_thm_organizer')->get('dateFormat', 'd.m.Y');
            $start      = JHtml::_('date', $dates[0]["schedule_date"], $dateFormat);
            $end        = JHtml::_('date', end($dates)["schedule_date"], $dateFormat);

            return "$start - $end";
        }

        return '';
    }

    /**
     * Loads all calendar information for specific course  from the database
     *
     * @param int $courseID id of course to be loaded
     *
     * @return array  array with calendar registration data on success, otherwise empty
     * @throws Exception
     */
    public static function getDates($courseID = 0)
    {
        $courseID = JFactory::getApplication()->input->getInt('lessonID', $courseID);

        if (empty($courseID)) {
            return [];
        }

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('*');
        $query->from('#__thm_organizer_lessons AS l');
        $query->leftJoin('#__thm_organizer_calendar AS c ON c.lessonID = l.id');
        $query->where("l.id = '$courseID'");
        $query->order('c.schedule_date');

        $dbo->setQuery($query);

        try {
            $dates = $dbo->loadAssocList();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

            return [];
        }

        return empty($dates) ? [] : $dates;
    }

    /**
     * Loads all all participants for specific course from database
     *
     * @param int     $courseID id of course to be loaded
     * @param boolean $includeWaitList
     *
     * @return array  with course registration data on success, otherwise empty
     * @throws Exception
     */
    public static function getFullParticipantData($courseID = 0, $includeWaitList = false)
    {
        if (empty($courseID)) {
            return [];
        }

        $shortTag = THM_OrganizerHelperLanguage::getShortTag();

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $select = 'CONCAT(pt.surname, ", ", pt.forename) AS name , pt.address, pt.zip_code, pt.city';
        $select .= ",pr.name_$shortTag as programName, d.short_name_$shortTag as departmentName";
        $select .= ',u.id, u.email';

        $query->select($select);
        $query->from('#__thm_organizer_user_lessons AS ul');
        $query->leftJoin('#__users AS u ON u.id = ul.userID');
        $query->leftJoin('#__thm_organizer_participants AS pt ON u.id = pt.id');
        $query->leftJoin('#__thm_organizer_programs AS pr ON pr.id = pt.programID');
        $query->leftJoin('#__thm_organizer_departments AS d ON pr.departmentID = d.id');
        $query->where("ul.lessonID = '$courseID'");

        if (!$includeWaitList) {
            $query->where("ul.status = '1'");
        }

        $query->order('name');

        $dbo->setQuery($query);

        try {
            $participantData = $dbo->loadAssocList();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

            return [];
        }

        return empty($participantData) ? [] : $participantData;
    }

    /**
     * Retrieves the course instances
     *
     * @param int    $courseID     the id of the course
     * @param int    $mode         the retrieval mode (empty => all, 2 => same block, 3 => single instance
     * @param object $calReference a reference calendar entry modeled on an object
     *
     * @return array the instance ids on success, otherwise empty
     */
    public static function getInstances($courseID, $mode = null, $calReference = null)
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('map.id')
            ->from('#__thm_organizer_calendar_configuration_map AS map')
            ->innerJoin('#__thm_organizer_calendar AS cal ON cal.id = map.calendarID')
            ->where("cal.lessonID = '$courseID'")
            ->where("delta != 'removed'");

        // Restrictions
        if ($mode == self::PERIOD_MODE OR $mode == self::INSTANCE_MODE) {
            $query->where("cal.startTime = '$calReference->startTime'");
            $query->where("cal.endTime = '$calReference->endTime'");

            if ($mode == self::INSTANCE_MODE) {
                $query->where("cal.schedule_date = '$calReference->schedule_date'");
            } else {
                $query->where("DAYOFWEEK(cal.schedule_date) = '$calReference->weekday'");
            }
        }

        $query->order('map.id');
        $dbo->setQuery($query);

        try {
            $ccmIDs = $dbo->loadColumn();
        } catch (RuntimeException $e) {
            return [];
        }

        return empty($ccmIDs) ? [] : $ccmIDs;
    }

    /**
     * Loads course information from the database
     *
     * @param int $subjectID id of subject with which courses must be associated
     * @param int $campusID  id of the course campus
     *
     * @return array  with course data on success, otherwise empty
     * @throws Exception
     */
    public static function getLatestCourses($subjectID, $campusID = null)
    {
        if (empty($subjectID)) {
            return [];
        }

        $shortTag = THM_OrganizerHelperLanguage::getShortTag();

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('DISTINCT l.id, l.max_participants as lessonP')
            ->select("s.id as subjectID, s.name_$shortTag as name, s.instructionLanguage, s.max_participants as subjectP")
            ->select('pp.name as planningPeriodName')
            ->select("l.campusID AS campusID, s.campusID AS abstractCampusID");

        $query->from('#__thm_organizer_lessons AS l')
            ->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.lessonID = l.id')
            ->innerJoin('#__thm_organizer_subject_mappings AS sm ON sm.plan_subjectID = ls.subjectID')
            ->innerJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id')
            ->innerJoin('#__thm_organizer_calendar AS ca ON ca.lessonID = l.id')
            ->innerJoin('#__thm_organizer_planning_periods AS pp ON l.planningPeriodID = pp.id')
            ->leftJoin('#__thm_organizer_campuses as cp on s.campusID = cp.id')
            ->where("s.id = '$subjectID'")
            ->where("(s.is_prep_course = '1' OR s.registration_type IS NOT NULL OR l.registration_type IS NOT NULL)");

        $query->order('schedule_date DESC');

        if (!empty($campusID)) {
            $campusConditions = "(l.campusID = '{$campusID}' OR (l.campusID IS NULL AND ";
            $campusConditions .= "(c.id = '{$campusID}' OR c.parentID = '{$campusID}' OR s.campusID IS NULL)))";
            $query->where($campusConditions);
        }

        $dbo->setQuery($query);

        try {
            $courses = $dbo->loadAssocList();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

            return [];
        }

        if (empty($courses)) {
            return [];
        }

        $campuses = [];

        foreach ($courses as $index => &$course) {
            $campus   = self::getCampus($course);
            $campusID = empty($campus['id']) ? 0 : $campus['id'];

            if (isset($campuses[$campusID])) {
                unset($courses[$index]);
                continue;
            }

            $course['campus']    = $campus;
            $campuses[$campusID] = $campusID;
        }

        return $courses;
    }

    /**
     * Get list of registered students in specific course
     *
     * @param int $courseID identifier of course
     * @param int $status   status of participants (1 registered, 0 waiting)
     *
     * @return mixed list of students in course with $id, false on error
     * @throws Exception
     */
    public static function getParticipants($courseID, $status = null)
    {
        if (empty($courseID)) {
            return [];
        }

        $dbo      = JFactory::getDbo();
        $query    = $dbo->getQuery(true);
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();

        $select = 'CONCAT(pt.surname, ", ", pt.forename) as name, ul.*, pt.*';
        $select .= ',u.email, u.username, u.id as cid';
        $select .= ",p.name_$shortTag as program";

        $query->select($select)
            ->from('#__thm_organizer_user_lessons as ul')
            ->innerJoin('#__users as u on u.id = ul.userID')
            ->leftJoin('#__thm_organizer_participants as pt on pt.id = ul.userID')
            ->leftJoin('#__thm_organizer_programs as p on p.id = pt.programID')
            ->where("ul.lessonID = '$courseID'")
            ->order('name ASC');

        if ($status === 1) {
            $query->where("ul.status = '1'");
        } elseif ($status === 0) {
            $query->where("ul.status = '0'");
        }

        $dbo->setQuery($query);

        try {
            $participants = $dbo->loadAssocList();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

            return [];
        }

        return empty($participants) ? [] : $participants;
    }

    /**
     * Figure out if student is signed into course
     *
     * @param int $courseID of lesson
     *
     * @return array containing the user specific information or empty on error
     * @throws Exception
     */
    public static function getParticipantState($courseID = 0)
    {
        $userID   = JFactory::getUser()->id;
        $courseID = JFactory::getApplication()->input->getInt('lessonID', $courseID);

        if (empty($courseID) || empty($userID)) {
            return [];
        }

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select("*");
        $query->from("#__thm_organizer_user_lessons");
        $query->where("userID = '$userID' AND lessonID = '$courseID'");

        $dbo->setQuery($query);

        try {
            $regState = $dbo->loadAssoc();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

            return [];
        }

        return empty($regState) ? [] : $regState;
    }

    /**
     * Creates a status display for the user's relation to the respective course.
     *
     * @param int $courseID the id of the course
     *
     * @return string the HTML for the status display
     * @throws Exception
     */
    public static function getStatusDisplay($courseID)
    {
        $lang       = THM_OrganizerHelperLanguage::getLanguage();
        $expired    = !self::isRegistrationOpen($courseID);
        $authorized = self::authorized($courseID);

        // Personal Status
        $none        = $expired ?
            $lang->_('COM_THM_ORGANIZER_EXPIRED') : $lang->_('COM_THM_ORGANIZER_COURSE_NOT_REGISTERED');
        $notLoggedIn = '<span class="icon-warning"></span>' . $lang->_('COM_THM_ORGANIZER_NOT_LOGGED_IN');
        $waitList    = '<span class="icon-checkbox-partial"></span>' . $lang->_('COM_THM_ORGANIZER_WAIT_LIST');
        $registered  = '<span class="icon-checkbox-checked"></span>' . $lang->_('COM_THM_ORGANIZER_COURSE_REGISTERED');

        if (!empty(JFactory::getUser()->id)) {
            if ($authorized) {
                $userStatus = $lang->_('COM_THM_ORGANIZER_COURSE_ADMINISTRATOR');
            } else {
                $regState = self::getParticipantState($courseID);

                if (empty($regState)) {
                    $text = $none;
                } else {
                    $text = empty($regState["status"]) ? $waitList : $registered;
                }

                $disabled   = '<span class="disabled">%s</span>';
                $userStatus = $expired ? sprintf($disabled, $text) : $text;
            }
        } else {
            $userStatus = $expired ? $none : '<span class="disabled">' . $notLoggedIn . '</span>';
        }

        return $userStatus;
    }

    /**
     * Gets the subject id which corresponds to a given course id
     *
     * @param int $courseID the id of the course
     *
     * @return int the id of the subject or 0 if the course could not be resolved to a subject
     * @throws Exception
     */
    public static function getSubjectID($courseID)
    {
        if (empty($courseID)) {
            return 0;
        }

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('sm.subjectID AS id')
            ->from('#__thm_organizer_subject_mappings AS sm')
            ->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.subjectID = sm.plan_subjectID')
            ->where("ls.lessonID = '$courseID'");

        $dbo->setQuery($query);

        try {
            $subjectID = $dbo->loadResult();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

            return 0;
        }

        return empty($subjectID) ? 0 : $subjectID;
    }

    /**
     * Check if the course is open for registration
     *
     * @param int $courseID id of lesson
     *
     * @return bool true if registration deadline not yet in the past, false otherwise
     * @throws Exception
     */
    public static function isRegistrationOpen($courseID = 0)
    {
        $dates    = self::getDates($courseID);
        $now      = new DateTime;
        $deadline = JComponentHelper::getParams('com_thm_organizer')->get('deadline', '5');
        $now->add(new DateInterval("P{$deadline}D"));

        return sizeof($dates) > 0 && new DateTime($dates[0]["schedule_date"]) > $now;
    }

    /**
     * Might move users from state waiting to registered
     *
     * @param int $courseID lesson id of lesson where participants have to be moved up
     *
     * @return void
     * @throws Exception
     */
    public static function refreshWaitList($courseID)
    {
        $canAccept = self::canAcceptParticipant($courseID);
        $lang      = THM_OrganizerHelperLanguage::getLanguage();

        if ($canAccept) {
            $dbo   = JFactory::getDbo();
            $query = $dbo->getQuery(true);

            $query->select('userID');
            $query->from('#__thm_organizer_user_lessons');
            $query->where("lessonID = '$courseID' and status = '0'");
            $query->order('status_date, user_date');

            $dbo->setQuery($query);

            try {
                $nextParticipantID = $dbo->loadResult();
            } catch (Exception $exc) {
                JFactory::getApplication()->enqueueMessage($lang->_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"),
                    'error');

                return;
            }

            if (!empty($nextParticipantID)) {
                THM_OrganizerHelperParticipants::changeState($nextParticipantID, $courseID, 1);
            }
        }
    }

    /**
     * Get formatted array with all prep courses in format id => name
     *
     * @return array  assoc array with all prep courses with id => name
     * @throws Exception
     */
    public static function prepCourseList()
    {
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select("id, name_$shortTag AS name");
        $query->from("#__thm_organizer_subjects");
        $query->where("is_prep_course = '1'");

        $dbo->setQuery($query);

        try {
            $courses = $dbo->loadAssocList();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

            return [];
        }

        if (empty($courses)) {
            return [];
        }

        $return = [];
        foreach ($courses as $course) {
            $return[$course["id"]] = $course["name"];
        }

        return $return;
    }

    /**
     * Get name of course/lesson
     *
     * @param int $courseID
     *
     * @return string
     * @throws Exception
     */
    public static function getName($courseID = 0)
    {
        $courseID = JFactory::getApplication()->input->getInt('lessonID', $courseID);

        if (empty($courseID)) {
            return '';
        }

        $lang  = THM_OrganizerHelperLanguage::getShortTag();
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("name_$lang")
            ->from('#__thm_organizer_lesson_subjects AS ls')
            ->innerJoin('#__thm_organizer_subject_mappings AS map ON map.plan_subjectID = ls.subjectID')
            ->innerJoin('#__thm_organizer_subjects AS s ON s.id = map.subjectID')
            ->where("ls.lessonID = '{$courseID}'");
        $dbo->setQuery($query);

        try {
            $name = $dbo->loadResult();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

            return '';
        }

        return $name;
    }
}
