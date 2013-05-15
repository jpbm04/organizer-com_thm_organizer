<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view soapquery edit
 * @description THM_Curriculum component admin view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=soapquery&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="helloworld-form"
	class="form-validate">

	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend>

				<?php echo JText::_('COM_THM_ORGANIZER_CONFIGURATION'); ?>
			</legend>
			<ul class="adminformlist">


				<?php foreach ($this->form->getFieldset('details') as $field)
				{
					?>
				<li><?php echo $field->label;
				echo $field->input; ?>
				</li>
				<?php
}
				?>
			</ul>

	</div>
	<div>
		<input type="hidden" name="task" value="configuration.edit" />

		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
