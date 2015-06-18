<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        curriculum view item panel layout
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_COMPONENT_SITE . '/helpers/pool.php';

class THM_OrganizerTemplateCurriculumItemPanel
{
    /**
     * Generates the HTML output for a main panel element
     *
     * @param   object  &$element  the element to be rendered
     *
     * @return  void  generates HTML output
     */
    public static function render(&$element)
    {
        $headStyle = '';
        $moduleNumber = empty($element->externalID)? '' : $element->externalID;
        if ($element->type == 'subject')
        {
            $linkAttribs = array('target' => '_blank');
            $moduleNumberHTML = JHtml::link($element->link, $moduleNumber, $linkAttribs);
            $crpHTML = JHtml::link($element->link, $element->CrP, $linkAttribs);
            $nameHTML = JHtml::link($element->link, $element->name, $linkAttribs);
        }
        else
        {
            $moduleNumberHTML = $moduleNumber;
            $crpHTML = THM_OrganizerHelperPool::getCrPText($element);
            $nameHTML = $element->name;
        }
        if (!empty($element->bgColor))
        {
            $textColor = THM_OrganizerHelperComponent::getTextColor($element->bgColor);
            $headStyle .= ' style="background-color: ' . $element->bgColor . '; color: ' . $textColor . '"';
        }
        echo '<div class="item">';
        echo '<div class="item-head"' . $headStyle. '>';
        echo '<span class="item-code">' . $moduleNumberHTML . '</span>';
        echo '<span class="item-crp">' .  $crpHTML . '</span>';
        echo '</div>';
        echo '<div class="item-name">' . $nameHTML . '</div>';
        echo '<div class="item-tools">';
        if (!empty($element->teacherName))
        {
            echo '<a class="btn hasTooltip" href="#" title="' . $element->teacherName . '"><icon class="icon-user"></icon></a>';
        }
        if (!empty($element->children))
        {
            $script = 'onclick="toggleGroupDisplay(\'#panel-' . $element->mapping . '\')"';
            echo '<a class="btn hasTooltip" ' . $script . ' title="' . JText::_('COM_THM_ORGANIZER_ACTION_OPEN_POOL') . '"><icon class="icon-grid-view-2"></icon></a>';
            THM_OrganizerTemplateCurriculumPanel::render($element);
        }
        echo '</div>';
        echo '</div>';
    }
}