<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        JFormFieldProgramID
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
JFormHelper::loadFieldClass('list');

/**
 * Class creates a form field for subject-degree program association
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class JFormFieldProgramID extends JFormFieldList
{
	/**
	 * @var  string
	 */
	protected $type = 'programID';

	/**
	 * Returns a select box where stored degree programs can be chosen
	 *
	 * @return  array  the available degree programs
	 */
	public function getOptions()
	{
		$shortTag = THM_OrganizerHelperLanguage::getShortTag();
		$dbo      = JFactory::getDbo();
		$query    = $dbo->getQuery(true);

		$nameParts  = array("dp.name_$shortTag", 'd.abbreviation', 'dp.version');
		$nameSelect = $query->concatenate($nameParts, ', ') . " AS text";

		$query->select("dp.id AS value, $nameSelect");
		$query->from('#__thm_organizer_programs AS dp');
		$query->innerJoin('#__thm_organizer_degrees AS d ON dp.degreeID = d.id');
		$query->innerJoin('#__thm_organizer_mappings AS m ON dp.id = m.programID');
		$query->order('text ASC');
		$dbo->setQuery((string) $query);

		try
		{
			$programs = $dbo->loadAssocList();
			$options  = array();
			foreach ($programs as $program)
			{
				$options[] = JHtml::_('select.option', $program['value'], $program['text']);
			}

			return array_merge(parent::getOptions(), $options);
		}
		catch (Exception $exc)
		{
			return parent::getOptions();
		}
	}
}
