<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        provides functions useful to multiple component files
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

/**
 * Class providing functions usefull to multiple component files
 *
 * @category  Joomla.Component.Admin
 * @package   thm_organizer
 */
class THM_OrganizerHelperComponent
{
    /**
     * Configure the Linkbar.
     *
     * @param   object  &$view  the view context calling the function
     *
     * @return void
     */
    public static function addSubmenu(&$view)
    {
        $viewName = $view->get('name');

        // No submenu creation while editing a resource
        if (!strpos($viewName, 'manager') AND $viewName == 'thm_organizer')
        {
            return;
        }

        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_MAIN_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=thm_organizer',
            $viewName == 'thm_organizer'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_DEGREE_MANAGER_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=degree_manager',
            $viewName == 'degree_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_USER_MANAGER_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=user_manager',
            $viewName == 'user_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_MONITOR_MANAGER_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=monitor_manager',
            $viewName == 'monitor_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_TEACHER_MANAGER_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=teacher_manager',
            $viewName == 'teacher_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_SUBJECT_MANAGER_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=subject_manager',
            $viewName == 'subject_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_FIELD_MANAGER_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=field_manager',
            $viewName == 'field_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_COLOR_MANAGER_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=color_manager',
            $viewName == 'color_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_POOL_MANAGER_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=pool_manager',
            $viewName == 'pool_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_ROOM_MANAGER_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=room_manager',
            $viewName == 'room_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_PROGRAM_MANAGER_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=program_manager',
            $viewName == 'program_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_SCHEDULE_MANAGER_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=schedule_manager',
            $viewName == 'schedule_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_CATEGORY_MANAGER_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=category_manager',
            $viewName == 'category_manager'
        );
        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER_VIRTUAL_SCHEDULE_MANAGER_TITLE'),
            'index.php?option=com_thm_organizer&amp;view=virtual_schedule_manager',
            $viewName == 'virtual_schedule_manager'
        );

        $view->sidebar = JHtmlSidebar::render();
    }

    /**
     * Gets a div with a given background color and text with a dynamically calculated text color
     *
     * @param   string  $text     the text to be displayed
     * @param   string  $bgColor  hexadecimal color code
     *
     * @return  string  the html output string
     */
    public static function getColorField($text, $bgColor)
    {
        $textColor = self::getTextColor($bgColor);
        $style = 'color:#' . $textColor . '; background-color:#' . $bgColor . '; text-align:center';
        return '<div class="color-preview" style="' . $style . '">' . $text . '</div>';
    }

    /**
     * Gets an appropriate value for text color
     *
     * @param   string  $bgColor  the background color associated with the field
     *
     * @return  string  the hexadecimal value for an appropriate text color
     */
    public static function getTextColor($bgColor)
    {
        $red = hexdec(substr($bgColor, 0, 2));
        $green = hexdec(substr($bgColor, 2, 2));
        $blue = hexdec(substr($bgColor, 4, 2));
        $relativeBrightness = ($red * 299) + ($green * 587) + ($blue * 114);
        $brightness = $relativeBrightness / 255000;
        if ($brightness >= 0.6)
        {
            return "4a5c66";
        }
        else
        {
            return "eeeeee";
        }
    }

    /**
     * Formats the date stored in the database according to the format in the component parameters
     *
     * @param   string  $date  the date to be formatted
     *
     * @return  string|bool  a formatted date string otherwise false
     */
    public static function formatDate($date)
    {
        $params =JComponentHelper::getParams('com_thm_organizer');
        $dateFormat = $params->get('dateFormat', 'd.m.Y');
        return date($dateFormat, strtotime($date));
        $timeFormat = $params->get('timeFormat', 'H:i');
    }

    /**
     * Formats the date stored in the database according to the format in the component parameters
     *
     * @param   string  $date  the date to be formatted
     *
     * @return  string|bool  a formatted date string otherwise false
     */
    public static function formatTime($time)
    {
        $params =JComponentHelper::getParams('com_thm_organizer');
        $timeFormat = $params->get('timeFormat', 'H:i');
        return date($timeFormat, strtotime($time));
    }

    /**
     * Converts a date string into the format used by the database
     *
     * @param   string  $date  the date string
     *
     * @return  string  date sting in format Y-m-d
     */
    public static function standardizeDate($date)
    {
        if (empty($date))
        {
            return '';
        }
        return date_format(date_create($date), 'Y-m-d');
    }
}