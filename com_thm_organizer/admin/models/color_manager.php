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
require_once JPATH_ROOT . '/media/com_thm_organizer/models/list.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class retrieves information for a filtered set of colors.
 */
class THM_OrganizerModelColor_Manager extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'asc';

    /**
     * Method to get all colors from the database
     *
     * @return JDatabaseQuery
     */
    protected function getListQuery()
    {
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();
        $query    = $this->_db->getQuery(true);

        $select = "id, name_$shortTag AS name, color, ";
        $parts  = ["'index.php?option=com_thm_organizer&view=color_edit&id='", "id"];
        $select .= $query->concatenate($parts, "") . " AS link";
        $query->select($select);
        $query->from('#__thm_organizer_colors');

        $this->setSearchFilter($query, ['name_de', 'name_en', 'color']);
        $this->setValueFilters($query, ['color']);
        $this->setIDFilter($query, 'id', ['filter.name']);

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Function to feed the data in the table body correctly to the list view
     *
     * @return array consisting of items in the body
     */
    public function getItems()
    {
        $items  = parent::getItems();
        $return = [];

        if (empty($items)) {
            return $return;
        }

        $index = 0;

        foreach ($items as $item) {
            $return[$index]             = [];
            $return[$index]['checkbox'] = JHtml::_('grid.id', $index, $item->id);
            $return[$index]['name']     = JHtml::_('link', $item->link, $item->name);
            $return[$index]['color']    = THM_OrganizerHelperComponent::getColorField($item->color, $item->color);
            $index++;
        }

        return $return;
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $ordering            = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction           = $this->state->get('list.direction', $this->defaultDirection);
        $headers             = [];
        $headers['checkbox'] = '';
        $headers['name']     = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'name', $direction, $ordering);
        $headers['color']    = JText::_('COM_THM_ORGANIZER_COLOR');

        return $headers;
    }
}
