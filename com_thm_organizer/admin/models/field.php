<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelField
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/models/merge.php';

/**
 * Class provides methods for field entry database abstraction
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelField extends THM_OrganizerModelMerge
{
    /**
     * Updates key references to the entry being merged.
     *
     * @param int   $newDBID  the id onto which the room entries merge
     * @param array $oldDBIDs an array containing the ids to be replaced
     *
     * @return  boolean  true on success, otherwise false
     */
    protected function updateAssociations($newDBID, $oldDBIDs)
    {
        $ppUpdated = $this->updateAssociation('field', $newDBID, $oldDBIDs, 'plan_pools');
        if (!$ppUpdated) {
            return false;
        }

        $psUpdated = $this->updateAssociation('field', $newDBID, $oldDBIDs, 'plan_subjects');
        if (!$psUpdated) {
            return false;
        }

        $poolsUpdated = $this->updateAssociation('field', $newDBID, $oldDBIDs, 'pools');
        if (!$poolsUpdated) {
            return false;
        }

        $programsUpdated = $this->updateAssociation('field', $newDBID, $oldDBIDs, 'programs');
        if (!$programsUpdated) {
            return false;
        }

        $subjectsUpdated = $this->updateAssociation('field', $newDBID, $oldDBIDs, 'subjects');
        if (!$subjectsUpdated) {
            return false;
        }

        return $this->updateAssociation('field', $newDBID, $oldDBIDs, 'teachers');
    }

    /**
     * The field associations are a part of the resource data in the new structure.
     *
     * @param object &$schedule     the schedule being processed
     * @param array  &$data         the data for the schedule db entry
     * @param int    $newDBID       the new id to use for the merged resource in the database (and schedules)
     * @param string $newGPUntisID  the new gpuntis ID to use for the merged resource in the schedule
     * @param array  $allGPUntisIDs all gpuntis IDs for the resources to be merged
     * @param array  $allDBIDs      all db IDs for the resources to be merged
     *
     * @return  void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function updateSchedule(&$schedule, &$data, $newDBID, $newGPUntisID, $allGPUntisIDs, $allDBIDs)
    {
        return;
    }
}
