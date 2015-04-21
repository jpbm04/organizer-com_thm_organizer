<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldProgramID
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.helpers.corehelper');
JFormHelper::loadFieldClass('list');

/**
 * Class creates a form field for subject-degree program association
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldMergeByID extends JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'mergeByID';

    /**
     * Returns a selectionbox where stored coursepool can be chosen as a parent node
     *
     * @return Select box
     */
    public function getOptions()
    {
        $input = JFactory::getApplication()->input;
        $selectedIDs = $input->get('cid', array(), 'array');
        $valueColumn = $this->getAttribute('name');
        $tables = explode(',', $this->getAttribute('tables'));
        $tableAlias = '';

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $textColumn = $this->resolveText($query);
        $query->select("DISTINCT $valueColumn AS value, $textColumn AS text");
        $query->from("#__{$tables[0]}");
        $count = count($tables);
        if ($count > 1)
        {
            $baseParts = explode(' AS ', $tables[0]);
            $tableAlias .= $baseParts[1] . '.';
            for ($index = 1; $index < $count; $index++)
            {
                $query->leftjoin("#__{$tables[$index]}");
            }
        }

        $query->where("{$tableAlias}id IN ( '" . implode("', '", $selectedIDs) . "' )");
        $query->order('text ASC');
        $dbo->setQuery((string) $query);

        try
        {
            $values = $dbo->loadAssocList();
            $options = array();
            foreach ($values as $value)
            {
                if (!empty($value['value']))
                {
                    $options[] = JHtml::_('select.option', $value['value'], $value['text']);
                }
            }
            return count($options)? $options : parent::getOptions();
        }
        catch (Exception $exc)
        {
            return parent::getOptions();
        }
    }

    /**
     * Resolves the textColumns for concatenated values
     *
     * @param   object  &$query  the query object
     *
     * @return  string  the string to use for text selection
     */
    private function resolveText(&$query)
    {
        $textColumn = $this->getAttribute('textColumn');
        $glue = $this->getAttribute('glue');

        $textColumns = explode(',', $textColumn);
        if (count($textColumns) === 1 OR empty($glue))
        {
            return $textColumn;
        }

        return '( ' . $query->concatenate($textColumns, $glue) . ' )';
    }
}