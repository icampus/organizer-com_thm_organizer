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

require_once JPATH_COMPONENT . '/views/list.php';

/**
 * Class loads persistent information a filtered set of (scheduled degree) programs (or organizational groupings) into the display context.
 */
class THM_OrganizerViewPlan_Program_Manager extends THM_OrganizerViewList
{
    public $items;

    public $pagination;

    public $state;

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     * @throws Exception => unauthorized access
     */
    public function display($tpl = null)
    {
        if (!THM_OrganizerHelperAccess::allowSchedulingAccess()) {
            throw new \Exception(\JText::_('THM_ORGANIZER_401'), 401);
        }

        parent::display($tpl);
    }

    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        \JToolbarHelper::title(\JText::_('THM_ORGANIZER_PLAN_PROGRAM_MANAGER_VIEW_TITLE'), 'organizer_programs');
        \JToolbarHelper::editList('plan_program.edit');
        if (THM_OrganizerHelperAccess::isAdmin()) {
            \JToolbarHelper::custom('plan_program.mergeView', 'attachment', 'attachment',
                'THM_ORGANIZER_ACTION_MERGE', true);
            \JToolbarHelper::preferences('com_thm_organizer');
        }
    }
}
