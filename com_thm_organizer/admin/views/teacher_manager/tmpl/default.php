<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view lectures default
 * @description THM_Curriculum component admin view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
?>
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=teacher_manager'); ?>"
      method="post" name="adminForm" id="adminForm">
    <fieldset id="filter-bar" class='filter-bar'>
        <div class="filter-search fltlft">
            <label class="filter-search-lbl" for="filter_search">
                <?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>
            </label>
            <input type="text" name="filter_search" id="filter_search"
                value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                title="<?php echo JText::_('COM_THM_ORGANIZER_SEARCH_TITLE'); ?>" />
            <button type="submit">
                <?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
            </button>
            <button type="button"
                onclick="document.id('filter_search').value='';this.form.submit();">
                <?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
            </button>
        </div>
    </fieldset>
    <table class="adminlist">
        <thead>
        <tr>
            <th width="5%">
                <input type="checkbox" name="toggle" value=""
                       onclick="checkAll(<?php echo count($this->items); ?>);" />
            </th>
            <th width="12%">
                <?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_TRM_SURNAME_TITLE'), 'surname', $listDirn, $listOrder); ?>
            </th>
            <th width="15%">
                <?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_TRM_FORENAME_TITLE'), 'forename', $listDirn, $listOrder); ?>
            </th>
            <th width="10%">
                <?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_TRM_TITLE_TITLE'), 'forename', $listDirn, $listOrder); ?>
            </th>
            <th width="10%">
                <?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_TRM_USERNAME_TITLE'), 'username', $listDirn, $listOrder); ?>
            </th>
            <th width="5%">
                <?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_GPUNTISID'), 'id', $listDirn, $listOrder); ?>
            </th>
            <th width="15%">
                <?php echo JHTML::_('grid.sort', JText::_('COM_THM_ORGANIZER_TRM_FIELD_TITLE'), 'id', $listDirn, $listOrder); ?>
            </th>
        </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="7">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
            <?php echo JHtml::_('form.token');?>
        </tfoot>
        <tbody>
<?php
foreach ($this->items as $i => $item)
{
?>
            <tr class="row<?php echo $i % 2; ?>">

                <td>
                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                </td>
                <td>
                    <a href="index.php?option=com_thm_organizer&view=teacher_edit&id=<?php echo $item->id; ?>">
                        <?php echo $item->surname; ?>
                    </a>
                </td>
                <td>
                    <a href="index.php?option=com_thm_organizer&view=teacher_edit&id=<?php echo $item->id; ?>">
                        <?php echo $item->forename; ?>
                    </a>
                </td>
                <td>
                    <a href="index.php?option=com_thm_organizer&view=teacher_edit&id=<?php echo $item->id; ?>">
                        <?php echo $item->title; ?>
                    </a>
                </td>
                <td>
                    <a href="index.php?option=com_thm_organizer&view=teacher_edit&id=<?php echo $item->id; ?>">
                        <?php echo $item->username; ?>
                    </a>
                </td>
                <td>
                    <a href="index.php?option=com_thm_organizer&view=teacher_edit&id=<?php echo $item->id; ?>">
                        <?php echo $item->gpuntisID; ?>
                    </a>
                </td>
                <td>
                    <a href="index.php?option=com_thm_organizer&view=teacher_edit&id=<?php echo $item->id; ?>">
                        <?php echo $item->field; ?>
                    </a>
                </td>
            </tr>
<?php
}
?>
        </tbody>
    </table>
</form>
