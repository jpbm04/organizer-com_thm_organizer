<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizersViewMajors
 * @description THM_OrganizersViewMajors component admin view
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Class THM_OrganizersViewMajors for component com_thm_organizer
 *
 * Class provides methods to display the view majors
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizersViewMajors extends JView
{
	/**
	 * Method to get display
	 *
	 * @param   Object  $tpl  template  (default: null)
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		$this->items = $items;
		$this->pagination = $pagination;
		$this->state = $this->get('State');
		$this->addToolBar();

		parent::display($tpl);
	}

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return  void
	 */
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('com_thm_organizer_SUBMENU_MAJORS_TITLE'), 'generic.png');

		JToolBarHelper::addNew('major.add', 'JTOOLBAR_NEW');
		JToolBarHelper::editList('major.edit', 'JTOOLBAR_EDIT');
		JToolBarHelper::custom(
				'major.importRedirect', 'export', JPATH_COMPONENT . DS . 'img' . DS . 'moderate.png',
				'com_thm_organizer_SUBMENU_ASSETS_IMPORT', true, true
		);
		JToolBarHelper::deleteList('', 'major.delete', 'JTOOLBAR_DELETE');
		JToolBarHelper::preferences('com_thm_organizer', '500');
	}
}
