<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        monitor manager default template
 * @description standard template for the display of registered monitors
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die;
$orderby = $this->escape($this->state->get('list.ordering'));
$direction = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>"
      method="post" name="adminForm" id="adminForm">
    <fieldset id="filter-bar">
        <div class="filter-select fltrt">
            <select name="filter_display" class="inputbox" onchange="this.form.submit()">
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_MON_SEARCH_BEHAVIOURS'); ?></option>
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_MON_ALL_BEHAVIOURS'); ?></option>
                    <?php echo JHtml::_('select.options', $this->behaviours, 'id', 'behaviour', $this->state->get('filter.display'));?>
            </select>
        </div>
        <div class="filter-select fltrt">
            <select name="filter_room" class="inputbox" onchange="this.form.submit()">
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_MON_SEARCH_ROOMS'); ?></option>
                    <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_MON_ALL_ROOMS'); ?></option>
                    <?php echo JHtml::_('select.options', $this->rooms, 'id', 'name', $this->state->get('filter.room'));?>
            </select>
        </div>
    </fieldset>
    <div class="clr"> </div>
<?php if(!empty($this->monitors)) { $k = 0;?>
    <div>
        <table class="adminlist" id="thm_organizer_mon_table">
            <colgroup>
                <col id="thm_organizer_mon_col_checkbox" />
                <col id="thm_organizer_mon_col_room" />
                <col id="thm_organizer_mon_col_ip" />
                <col id="thm_organizer_mon_col_display" />
                <col id="thm_organizer_mon_col_interval" />
                <col id="thm_organizer_mon_col_content" />
            </colgroup>
            <thead>
                <tr>
                    <th />
                    <th class="thm_organizer_th hasTip"
                        title="<?php echo JText::_('COM_THM_ORGANIZER_MON_ROOM')."::".JText::_('COM_THM_ORGANIZER_MON_ROOM_DESC'); ?>">
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MON_ROOM', 'r.name', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_th hasTip"
                        title="<?php echo JText::_('COM_THM_ORGANIZER_MON_IP')."::".JText::_('COM_THM_ORGANIZER_MON_IP_DESC'); ?>">
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MON_IP', 'm.ip', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_th hasTip"
                        title="<?php echo JText::_('COM_THM_ORGANIZER_MON_DISPLAY')."::".JText::_('COM_THM_ORGANIZER_MON_DISPLAY_DESC'); ?>">
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MON_DISPLAY', 'd.behaviour', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_th hasTip"
                        title="<?php echo JText::_('COM_THM_ORGANIZER_MON_INTERVAL')."::".JText::_('COM_THM_ORGANIZER_MON_INTERVAL_DESC'); ?>">
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MON_INTERVAL', 'm.interval', $direction, $orderby); ?>
                    </th>
                    <th class="thm_organizer_th hasTip"
                        title="<?php echo JText::_('COM_THM_ORGANIZER_MON_CONTENT')."::".JText::_('COM_THM_ORGANIZER_MON_CONTENT_DESC'); ?>">
                        <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_MON_CONTENT', 'm.content', $direction, $orderby); ?>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="9">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
            <?php foreach($this->monitors as $k => $monitor){ ?>
                <tr class="row<?php echo $k % 2;?>">
                    <td><?php echo JHtml::_('grid.id', $k, $monitor->monitorID); ?></td>
                    <td><a href='<?php echo $monitor->link; ?>' ><?php echo $monitor->room; ?></a></td>
                    <td><a href='<?php echo $monitor->link; ?>' > <?php echo $monitor->ip; ?></a></td>
                    <td><a href='<?php echo $monitor->link; ?>' ><?php echo $monitor->behaviour; ?></a></td>
                    <td><a href='<?php echo $monitor->link; ?>' ><?php echo $monitor->interval; ?></a></td>
                    <td><a href='<?php echo $monitor->link; ?>' ><?php echo $monitor->content; ?></a></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
<?php }?>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $orderby; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $direction; ?>" />
    <input type="hidden" name="view" value="monitor_manager" />
    <?php echo JHtml::_('form.token'); ?>
</form>
