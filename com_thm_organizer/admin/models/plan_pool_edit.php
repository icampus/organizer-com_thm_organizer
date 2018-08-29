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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/plan_pools.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/models/edit.php';

/**
 * Class loads a form for editing plan (subject) pool data.
 */
class THM_OrganizerModelPlan_Pool_Edit extends THM_OrganizerModelEdit
{
    /**
     * Checks access for edit views
     *
     * @param int $pPoolID the id of the resource to be edited (empty for new entries)
     *
     * @return bool  true if the user can access the edit view, otherwise false
     * @throws Exception
     */
    public function allowEdit($pPoolID = null)
    {
        if (empty($pPoolID)) {
            return false;
        }
        $pPoolIDs = [$pPoolID];

        return THM_OrganizerHelperPlan_Pools::allowEdit($pPoolIDs);
    }
}
