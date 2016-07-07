<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewSubject_List
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class loads a list of subjects sorted according to different criteria into
 * the view context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewSubject_List extends JViewLegacy
{
	public $languageSwitches = array();

	public $lang;

	public $groupBy = 'list';

	public $disclaimer;

	public $disclaimerData;

	/**
	 * Method to get display
	 *
	 * @param   Object $tpl template  (default: null)
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->modifyDocument();
		$this->lang = THM_OrganizerHelperLanguage::getLanguage();

		$this->state      = $this->get('State');
		$this->items      = $this->get('items');
		$this->pagination = $this->get('Pagination');

		$switchParams           = array('view' => 'subject_list', 'form' => true);
		$this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($switchParams);

		$model             = $this->getModel();
		$this->programName = $model->programName;

		$groupByArray  = array(0 => 'list', 1 => 'pool', 2 => 'teacher', 3 => 'field');
		$groupByIndex  = JFactory::getApplication()->getParams()->get('groupBy', 0);
		$this->groupBy = $groupByArray[$groupByIndex];

		$this->disclaimer = new JLayoutFile('disclaimer', $basePath = JPATH_ROOT .'/media/com_thm_organizer/layouts');
		$this->disclaimerData = array('language' => $this->lang);

		parent::display($tpl);
	}

	/**
	 * Modifies document variables and adds links to external files
	 *
	 * @return  void
	 */
	private function modifyDocument()
	{
		JHtml::_('bootstrap.framework');
		JHtml::_('bootstrap.tooltip');
		JHtml::_('jquery.ui');

		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/subject_list.css');
	}
}
