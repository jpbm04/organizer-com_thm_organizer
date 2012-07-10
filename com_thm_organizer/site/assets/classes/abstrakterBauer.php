<?php
/**
 *@category    Joomla component
 *
 *@package     THM_Organizer
 *
 *@subpackage  com_thm_organizer.site
 *@name		   abstrakterBauer
 *@description abstrakterBauer file from com_thm_organizer
 *@author	   Wolf Rost, wolf.rost@mni.thm.de
 *
 *@copyright   2012 TH Mittelhessen
 *
 *@license     GNU GPL v.2
 *@link		   www.mni.thm.de
 *@version	   1.0
 */

defined(_'JEXEC') or die;

/**
 * Abstract class AbstrakterBauer for component com_thm_organizer
 *
 * Class provides abstract methods for the builder pattern
 *
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       1.5
 */
abstract class AbstrakterBauer
{
	/**
	 * Saves the file to $dest with $filename in picturtype $type
	 *
	 * @param   Array   $arr       Array with the schedule data
	 * @param   String  $username  The username of the logged in user
	 * @param   String  $title     The title of the schedule
	 *
	 * @return void
	 */
	abstract protected function erstelleStundenplan( $arr, $username, $title );
}
