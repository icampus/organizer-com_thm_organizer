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
jimport('joomla.application.component.model');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/planning_periods.php';

/**
 * Class provides planning period options for a given department/program. Called from the room statistics view.
 */
class THM_OrganizerModelPlanning_Period_Ajax extends JModelLegacy
{
    /**
     * Gets the pool options as a string
     *
     * @return string the concatenated plan pool options
     * @throws Exception
     */
    public function getOptions()
    {
        $planningPeriods = THM_OrganizerHelperPlanning_Periods::getPlanningPeriods();
        $options         = [];

        foreach ($planningPeriods as $planningPeriodID => $planningPeriod) {
            $shortSD = THM_OrganizerHelperComponent::formatDate($planningPeriod['startDate']);
            $shortED = THM_OrganizerHelperComponent::formatDate($planningPeriod['endDate']);

            $option['value'] = $planningPeriod['id'];
            $option['text']  = "{$planningPeriod['name']} ($shortSD - $shortED)";
            $options[]       = $option;
        }

        return json_encode($options);
    }
}
