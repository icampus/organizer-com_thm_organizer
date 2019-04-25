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

\JFormHelper::loadFieldClass('list');
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/html.php';

use THM_OrganizerHelperHTML as HTML;

/**
 * Class creates a generalized select box for selection of a single column value among those already selected.
 */
class JFormFieldMergeByValue extends \JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'mergeByValue';

    /**
     * Returns a select box where resource attributes can be selected
     *
     * @return array the options for the select box
     */
    protected function getOptions()
    {
        $input       = THM_OrganizerHelperComponent::getInput();
        $selectedIDs = $input->get('cid', [], 'array');
        $column      = $this->getAttribute('name');
        $resource    = str_replace('_merge', '', $input->get('view')) . 's';

        $dbo   = \JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT $column AS value, $column AS text")->from("#__thm_organizer_$resource");
        $query->where("id IN ( '" . implode("', '", $selectedIDs) . "' )");
        $query->order('text ASC');
        $dbo->setQuery($query);

        $defaultOptions = parent::getOptions();
        $values         = THM_OrganizerHelperComponent::executeQuery('loadAssocList');
        if (empty($values)) {
            return $defaultOptions;
        }

        $options = [];
        foreach ($values as $value) {
            if (!empty($value['value'])) {
                $options[] = HTML::_('select.option', $value['value'], $value['text']);
            }
        }

        return empty($options) ? $defaultOptions : $options;
    }
}