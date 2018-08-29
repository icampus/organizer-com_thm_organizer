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
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/models/list.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class retrieves information for a filtered set of teachers.
 */
class THM_OrganizerModelTeacher_Manager extends THM_OrganizerModelList
{
    protected $defaultOrdering = 't.surname';

    protected $defaultDirection = 'asc';

    /**
     * Constructor to set the config array and call the parent constructor
     *
     * @param array $config Configuration  (default: array)
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['t.surname', 't.forename', 't.username', 't.untisID', 'f.field'];
        }

        parent::__construct($config);
    }

    /**
     * Method to get all teachers from the database
     *
     * @return JDatabaseQuery
     */
    protected function getListQuery()
    {
        // Create the query
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();
        $query    = $this->_db->getQuery(true);
        $select   = "t.id, t.surname, t.forename, t.username, t.gpuntisID, f.field_$shortTag AS field, c.color, ";
        $parts    = ["'index.php?option=com_thm_organizer&view=teacher_edit&id='", "t.id"];
        $select   .= $query->concatenate($parts, "") . " AS link ";
        $query->select($select);
        $query->from('#__thm_organizer_teachers AS t');
        $query->leftJoin('#__thm_organizer_fields AS f ON t.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');

        $this->setSearchFilter($query, ['surname', 'forename', 'username', 't.gpuntisID', 'field_de', 'field_en']);
        $this->setValueFilters($query, ['surname', 'forename', 'username', 't.gpuntisID']);
        $this->setLocalizedFilters($query, ['field']);

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Method to overwrite the getItems method in order to create iterate table data
     *
     * @return array  an array of arrays with preformatted teacher data
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
            $itemForename  = empty($item->forename) ? '' : $item->forename;
            $itemUsername  = empty($item->username) ? '' : $item->username;
            $itemGPUntisID = empty($item->gpuntisID) ? '' : $item->gpuntisID;

            $return[$index]                = [];
            $return[$index]['checkbox']    = JHtml::_('grid.id', $index, $item->id);
            $return[$index]['surname']     = JHtml::_('link', $item->link, $item->surname);
            $return[$index]['forename']    = JHtml::_('link', $item->link, $itemForename);
            $return[$index]['username']    = JHtml::_('link', $item->link, $itemUsername);
            $return[$index]['t.gpuntisID'] = JHtml::_('link', $item->link, $itemGPUntisID);

            if (!empty($item->field)) {
                $bgColor                 = empty($item->color) ? 'ffffff' : $item->color;
                $return[$index]['field'] = THM_OrganizerHelperComponent::getColorField($item->field, $bgColor);
            } else {
                $return[$index]['field'] = '';
            }

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
        $ordering               = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction              = $this->state->get('list.direction', $this->defaultDirection);
        $headers                = [];
        $headers['checkbox']    = '';
        $headers['surname']     = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_SURNAME', 't.surname', $direction,
            $ordering);
        $headers['forename']    = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_FORENAME', 't.forename', $direction,
            $ordering);
        $headers['username']    = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_USERNAME', 't.username', $direction,
            $ordering);
        $headers['t.gpuntisID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_GPUNTISID', 't.gpuntisID', $direction,
            $ordering);
        $headers['field']       = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_FIELD', 'field', $direction,
            $ordering);

        return $headers;
    }
}
