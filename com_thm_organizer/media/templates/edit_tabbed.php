<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

/**
 * Class provides a standardized display of tabbed item edit forms.
 */
class THM_OrganizerTemplateEdit_Tabbed
{
    /**
     * Method to create a list output
     *
     * @param object &$view the view context calling the function
     *
     * @return void
     */
    public static function render(&$view)
    {
        ?>

        <form action="index.php?option=com_thm_organizer"
              enctype="multipart/form-data"
              method="post"
              name="adminForm"
              id="item-form"
              class="form-horizontal form-validate">
            <div class="form-horizontal">
                <?php
                echo JHtml::_('bootstrap.startTabSet', 'myTab', ['active' => 'details']);
                $sets = $view->form->getFieldSets();
                foreach ($sets as $set) {
                    $isInitialized  = (bool)$view->form->getValue('id');
                    $displayInitial = isset($set->displayinitial) ? $set->displayinitial : true;
                    if ($displayInitial or $isInitialized) {
                        echo JHtml::_('bootstrap.addTab', 'myTab', $set->name, JText::_($set->label, true));
                        echo $view->form->renderFieldset($set->name);
                        echo JHtml::_('bootstrap.endTab');
                    }
                }
                echo JHtml::_('bootstrap.endTabSet');
                ?>
            </div>
            <?php echo $view->form->getInput('id'); ?>
            <?php echo JHtml::_('form.token'); ?>
            <input type="hidden" name="task" value=""/>
        </form>
        <?php
    }

}
