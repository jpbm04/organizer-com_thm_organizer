<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizersViewColors
 * @description THM_OrganizersViewColors component admin view
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Class THM_OrganizersViewColors for component com_thm_organizer
 *
 * Class provides methods to display the view colors
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizersViewColors extends JView
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
		JToolBarHelper::title(JText::_('com_thm_organizer_SUBMENU_COLORS_TITLE'), 'generic.png');

		JToolBarHelper::addNew('color.add', 'JTOOLBAR_NEW');
		JToolBarHelper::editList('color.edit', 'JTOOLBAR_EDIT');
		JToolBarHelper::deleteList('', 'color.delete', 'JTOOLBAR_DELETE');
	}
}
