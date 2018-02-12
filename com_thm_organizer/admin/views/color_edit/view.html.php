<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/edit.php';

/**
 * Class loads the color form into display context.
 */
class THM_OrganizerViewColor_Edit extends THM_OrganizerViewEdit
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        $isNew = ($this->item->id == 0);
        $title = $isNew ? JText::_('COM_THM_ORGANIZER_COLOR_EDIT_NEW_VIEW_TITLE') : JText::_('COM_THM_ORGANIZER_COLOR_EDIT_EDIT_VIEW_TITLE');
        JToolbarHelper::title($title, 'organizer_colors');
        JToolbarHelper::save('color.save');
        JToolbarHelper::cancel('color.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
