<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

defined('_JEXEC') or die;

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\Access;
use Organizer\Helpers\Campuses;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads persistent information a filtered set of events into the display context.
 */
class Events extends ListView
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_EVENTS_TITLE'), 'contract-2');
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'new', 'THM_ORGANIZER_ADD', 'event.add', false);
        $toolbar->appendButton('Standard', 'edit', 'THM_ORGANIZER_EDIT', 'event.edit', true);
        $toolbar->appendButton(
            'Confirm',
            Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
            'delete',
            Languages::_('THM_ORGANIZER_DELETE'),
            'event.delete',
            true
        );
        if (Access::isAdmin()) {
            HTML::setPreferencesButton();
        }
    }

    /**
     * Function determines whether the user may access the view.
     *
     * @return bool true if the use may access the view, otherwise false
     */
    protected function allowAccess()
    {
        return Access::isAdmin();
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $ordering  = $this->state->get('list.ordering');
        $direction = $this->state->get('list.direction');
        $headers   = [];

        $headers['checkbox']        = '';
        $headers['name']            = HTML::sort('NAME', 'name', $direction, $ordering);
        $headers['department']      = HTML::sort('DEPARTMENT', 'name', $direction, $ordering);
        $headers['campus']          = Languages::_('THM_ORGANIZER_CAMPUS');
        $headers['maxParticipants'] = Languages::_('THM_ORGANIZER_MAX_PARTICIPANTS');

        return $headers;
    }

    /**
     * Processes the items in a manner specific to the view, so that a generalized  output in the layout can occur.
     *
     * @return void processes the class items property
     */
    protected function preProcessItems()
    {
        if (empty($this->items)) {
            return;
        }

        $index          = 0;
        $link           = 'index.php?option=com_thm_organizer&view=event_edit&id=';
        $processedItems = [];

        foreach ($this->items as $item) {

            $campus = Campuses::getName($item->campusID);
            $maxParticipants = empty($item->maxParticipants) ? 1000  : $item->maxParticipants;

            $thisLink                                  = $link . $item->id;
            $processedItems[$index]                    = [];
            $processedItems[$index]['checkbox']        = HTML::_('grid.id', $index, $item->id);
            $processedItems[$index]['name']            = HTML::_('link', $thisLink, $item->name);
            $processedItems[$index]['department']      = HTML::_('link', $thisLink, $item->department);
            $processedItems[$index]['campus']          = HTML::_('link', $thisLink, $campus);
            $processedItems[$index]['maxParticipants'] = HTML::_('link', $thisLink, $maxParticipants);

            $index++;
        }

        $this->items = $processedItems;
    }
}