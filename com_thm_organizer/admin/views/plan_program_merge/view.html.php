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

require_once JPATH_COMPONENT . '/views/merge.php';

/**
 * Class loads the plan (degree) program / organizational grouping merge form into display context.
 */
class THM_OrganizerViewPlan_Program_Merge extends THM_OrganizerViewMerge
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        \JToolbarHelper::title(\JText::_('THM_ORGANIZER_PLAN_PROGRAM_MERGE_VIEW_TITLE'));
        \JToolbarHelper::custom('plan_program.merge', 'attachment', 'attachment', 'THM_ORGANIZER_ACTION_MERGE',
            false);
        \JToolbarHelper::cancel('plan_program.cancel', 'JTOOLBAR_CANCEL');
    }
}
