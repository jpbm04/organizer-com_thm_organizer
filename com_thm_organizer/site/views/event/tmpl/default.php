<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        default template for the event view
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined('_JEXEC') or die;
$event = $this->event;
$showListLink = (isset($this->listLink) and $this->listLink != "")? true : false;
?>
<div id="thm_organizer_e">
    <div id="thm_organizer_e_header">
        <span id="thm_organizer_e_title"><?php echo $event['title']; ?></span>
        <div id="thm_organizer_e_headerlinks">
<?php
if ($showListLink)
{
?>
            <a  class="hasTip thm_organizer_action_link"
                title="<?php echo JText::_('COM_THM_ORGANIZER_LIST_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_LIST_DESCRIPTION');?>"
                href="<?php echo $this->listLink ?>">
                <span id="thm_organizer_list_span" class="thm_organizer_action_span"></span>
                <?php echo JText::_('COM_THM_ORGANIZER_LIST'); ?>
            </a>
<?php
}
if ($showListLink and ($event['access'] or $this->canWrite))
{
?>
            <span class="thm_organizer_divider_span"></span>
<?php
}
if ($this->canWrite)
{
?>
            <a  class="hasTip thm_organizer_action_link"
                title="<?php echo JText::_('COM_THM_ORGANIZER_NEW_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_NEW_DESCRIPTION');?>"
                href="<?php echo $this->baseurl; ?>/index.php?&option=com_thm_organizer&view=event_edit&Itemid=<?php echo JRequest::getInt('Itemid'); ?>"  >
                <span id="thm_organizer_new_span" class="thm_organizer_action_span"></span>
                <?php echo JText::_('COM_THM_ORGANIZER_NEW'); ?>
            </a>
<?php
}
if ($event['access'])
{
?>
            <a  class="hasTip thm_organizer_action_link"
                title="<?php echo JText::_('COM_THM_ORGANIZER_EDIT_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_EDIT_DESCRIPTION');?>"
                href="<?php echo JRoute::_("index.php?option=com_thm_organizer&task=events.edit&eventID={$this->event['id']}&Itemid=$this->itemID"); ?>">
                <span id="thm_organizer_edit_span" class="thm_organizer_action_span"></span>
                <?php echo JText::_('COM_THM_ORGANIZER_EDIT'); ?>
            </a>
            <a  class="hasTip thm_organizer_action_link"
                title="<?php echo JText::_('COM_THM_ORGANIZER_DELETE_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_DELETE_DESCRIPTION');?>"
                href="<?php echo JRoute::_("index.php?option=com_thm_organizer&task=events.delete&eventID={$this->event['id']}&Itemid=$this->itemID"); ?>">
                <span id="thm_organizer_delete_span" class="thm_organizer_action_span"></span>
                <?php echo JText::_('COM_THM_ORGANIZER_DELETE'); ?>
            </a>
<?php
}
?>
        </div>
    </div>
    <div class="thm_organizer_e_block_div" >
        <div id='thm_organizer_e_author'>
            <p><?php echo JText::_('COM_THM_ORGANIZER_E_WRITTEN_BY') . $event['author']; ?></p>
        </div>
<?php
if (!empty($event['description']))
{
?>
        <div id='thm_organizer_e_description'>
            <?php echo $event['description']; ?>
        </div>
<?php
}
?>
        <div id="thm_organizer_e_time">
            <p><?php echo $this->dateTimeText; ?></p>
        </div>
<?php
if ($this->teachers or $this->rooms or $this->groups)
{
?>
        <div id="thm_organizer_e_resources" >
            <h3><?php echo JText::_('COM_THM_ORGANIZER_E_RESOURCE_HEAD'); ?></h3>
<?php
    if ($this->teachers)
    {
?>
            <p>
                <?php echo $this->teachersLabel; ?>
                <?php echo $this->teachers; ?>
            </p>
<?php
    }
    if ($this->rooms)
    {
?>
            <p>
                <?php echo $this->roomsLabel; ?>
                <?php echo $this->rooms; ?>
            </p>
<?php
    }
    if ($this->groups)
    {
?>
            <p>
                <?php echo $this->groupsLabel; ?>
                <?php echo $this->groups; ?>
            </p>
<?php
    }
?>
        </div>
<?php
}
?>
    </div>
</div>
	