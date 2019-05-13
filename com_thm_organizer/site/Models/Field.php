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

/**
 * Class which manages stored field (of expertise) data.
 */
class Field extends MergeModel
{
    protected $fkColumn = 'fieldID';

    protected $tableName = 'fields';

    /**
     * Updates key references to the entry being merged.
     *
     * @return boolean  true on success, otherwise false
     */
    protected function updateAssociations()
    {
        $ppUpdated = $this->updateAssociation('plan_pools');
        if (!$ppUpdated) {
            return false;
        }

        $psUpdated = $this->updateAssociation('plan_subjects');
        if (!$psUpdated) {
            return false;
        }

        $poolsUpdated = $this->updateAssociation('pools');
        if (!$poolsUpdated) {
            return false;
        }

        $programsUpdated = $this->updateAssociation('programs');
        if (!$programsUpdated) {
            return false;
        }

        $subjectsUpdated = $this->updateAssociation('subjects');
        if (!$subjectsUpdated) {
            return false;
        }

        return $this->updateAssociation('teachers');
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
        return;
    }
}