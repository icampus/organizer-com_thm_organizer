<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

defined('_JEXEC') or die;

use Organizer\Helpers\Access;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class which manages stored room data.
 */
class Room extends MergeModel
{
    protected $fkColumn = 'roomID';

    protected $tableName = 'rooms';

    /**
     * Provides user access checks to rooms
     *
     * @return boolean  true if the user may edit the given resource, otherwise false
     */
    protected function allowEdit()
    {
        return Access::allowFMAccess();
    }

    /**
     * Updates key references to the entry being merged.
     *
     * @return boolean  true on success, otherwise false
     */
    protected function updateAssociations()
    {
        $monitorsUpdated = $this->updateAssociation('monitors');
        if (!$monitorsUpdated) {
            return false;
        }

        $configsUpdated = $this->updateStoredConfigurations();
        if (!$configsUpdated) {
            return false;
        }

        return true;
    }

    /**
     * Processes the data for an individual schedule
     *
     * @param object &$schedule the schedule being processed
     *
     * @return void
     */
    protected function updateSchedule(&$schedule)
    {
        foreach ($schedule->configurations as $index => $configuration) {
            $inConfig      = false;
            $configuration = json_decode($configuration);

            foreach ($configuration->rooms as $roomID => $delta) {
                if (in_array($roomID, $this->data['otherIDs'])) {
                    $inConfig = true;

                    // Whether old or new high probability of having to overwrite an attribute this enables standard handling.
                    unset($configuration->rooms->$roomID);
                    $configuration->rooms->{$this->data['id']} = $delta;
                }
            }

            if ($inConfig) {
                $schedule->configurations[$index] = json_encode($configuration);
            }
        }
    }

    /**
     * Updates the lesson configurations table with the room id changes.
     *
     * @return bool
     */
    private function updateStoredConfigurations()
    {
        $table       = '#__thm_organizer_lesson_configurations';
        $selectQuery = $this->_db->getQuery(true);
        $selectQuery->select('id, configuration')
            ->from($table);

        $updateQuery = $this->_db->getQuery(true);
        $updateQuery->update($table);

        foreach ($this->data['otherIDs'] as $oldID) {
            $selectQuery->clear('where');
            $regexp = '"rooms":\\{("[0-9]+":"[\w]*",)*"' . $oldID . '"';
            $selectQuery->where("configuration REGEXP '$regexp'");
            $this->_db->setQuery($selectQuery);

            $storedConfigurations = OrganizerHelper::executeQuery('loadAssocList');
            if (empty($storedConfigurations)) {
                continue;
            }

            foreach ($storedConfigurations as $storedConfiguration) {
                $configuration = json_decode($storedConfiguration['configuration'], true);

                $oldDelta = $configuration['rooms'][$oldID];
                unset($configuration['rooms'][$oldID]);

                // The new id is not yet an index, or it is, but has no delta value and the old id did
                if (!isset($configuration['rooms'][$this->data['id']])
                    or (empty($configuration['rooms'][$this->data['id']]) and !empty($oldDelta))) {
                    $configuration['rooms'][$this->data['id']] = $oldDelta;
                }

                $configuration = json_encode($configuration);
                $updateQuery->clear('set');
                $updateQuery->set("configuration = '$configuration'");
                $updateQuery->clear('where');
                $updateQuery->where("id = '{$storedConfiguration['id']}'");
                $this->_db->setQuery($updateQuery);
                $success = (bool)OrganizerHelper::executeQuery('execute');
                if (!$success) {
                    return false;
                }
            }
        }

        return true;
    }
}