<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        template for the editing of schedule commentary
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined("_JEXEC") or die;?>
<script language="javascript" type="text/javascript">
Joomla.submitbutton = function(task)
{
    if (task == '') { return false; }
    else
    {
        var isValid=true; var action = task.split('.');
        if (action[1] != 'cancel' && action[1] != 'close')
        {
            var forms = $$('form.form-validate');
            for (var i=0;i<forms.length;i++)
            {
                if (!document.formvalidator.isValid(forms[i])) { isValid = false; break; }
            }
        }
        if (isValid) { Joomla.submitform(task, document.eventForm); }
        else
        {
            alert('<?php echo addslashes(JText::_('COM_THM_ORGANIZER_INVALID_FORM')); ?>');
            return false;
        }
    }
}
</script>
<form action="index.php?option=com_thm_organizer"
      enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
    <div id="thm_organizer_se" class="width-60 fltlft">
        <fieldset class="adminform">
            <legend><?php echo JText::_('COM_THM_ORGANIZER_EDIT') . ' ' . $this->form->getValue('plantypeID'); ?></legend>
            <ul class="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('departmentname'); ?>
                    <?php echo $this->form->getInput('departmentname'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('semestername'); ?>
                    <?php echo $this->form->getInput('semestername'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('startdate'); ?>
                    <?php echo $this->form->getInput('startdate'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('enddate'); ?>
                    <?php echo $this->form->getInput('enddate'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('creationdate'); ?>
                    <?php echo $this->form->getInput('creationdate'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('description'); ?>
                    <?php echo $this->form->getInput('description'); ?>
                </li>
            </ul>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="scheduleID" value="<?php echo $this->form->getValue('id'); ?>" />
</form>