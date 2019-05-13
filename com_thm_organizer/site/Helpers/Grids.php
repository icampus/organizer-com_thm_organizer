<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use stdClass;

defined('_JEXEC') or die;

/**
 * Class provides general functions for retrieving building data.
 */
class Grids implements XMLValidator
{
    /**
     * Retrieves the table id if existent.
     *
     * @param string $untisID the grid name in untis
     *
     * @return mixed int id on success, otherwise null
     */
    public static function getID($untisID)
    {
        $table  = OrganizerHelper::getTable('Grids');
        $data   = ['gpuntisID' => $untisID];
        $exists = $table->load($data);

        return empty ($exists) ? null : $table->id;
    }

    /**
     * Retrieves the resource id using the Untis ID. Creates the resource id if unavailable.
     *
     * @param object &$scheduleModel the validating schedule model
     * @param string  $untisID       the id of the resource in Untis
     *
     * @return void modifies the scheduleModel, setting the id property of the resource
     */
    public static function setID(&$scheduleModel, $untisID)
    {
        if (empty($scheduleModel->schedule->periods->$untisID)) {
            return;
        }

        $grid = $scheduleModel->schedule->periods->$untisID;
        $grid->grid = json_encode($grid->grid);

        $table        = OrganizerHelper::getTable('Grids');
        $loadCriteria = ['gpuntisID' => $untisID];
        $exists       = $table->load($loadCriteria);
        if ($exists) {
            $altered = false;
            foreach ($grid as $key => $value) {
                if (property_exists($table, $key) and empty($table->$key) and !empty($value)) {
                    $table->set($key, $value);
                    $altered = true;
                }
            }

            if ($altered) {
                $table->store();
            }

            $scheduleModel->schedule->periods->$untisID->id = $table->id;

            return;
        }

        $table->save($grid);
        $scheduleModel->schedule->periods->$untisID->id = $table->id;

        return;
    }

    /**
     * Checks whether nodes have the expected structure and required information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$xmlObject     the object being validated
     *
     * @return void modifies &$scheduleModel
     */
    public static function validateCollection(&$scheduleModel, &$xmlObject)
    {
        if (empty($xmlObject->timeperiods)) {
            $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_ERROR_PERIODS_MISSING');

            return;
        }

        $scheduleModel->schedule->periods = new stdClass;

        foreach ($xmlObject->timeperiods->children() as $timePeriodNode) {
            self::validateIndividual($scheduleModel, $timePeriodNode);
        }

        foreach (array_keys((array)$scheduleModel->schedule->periods) as $gridName) {
            self::validateIndividual($scheduleModel, $timePeriodNode);
        }
    }

    /**
     * Checks whether pool nodes have the expected structure and required
     * information
     *
     * @param object &$scheduleModel  the validating schedule model
     * @param object &$timePeriodNode the time period node to be validated
     *
     * @return void
     */
    public static function validateIndividual(&$scheduleModel, &$timePeriodNode)
    {
        // Not actually referenced but evinces data inconsistencies in Untis
        $exportKey = trim((string)$timePeriodNode[0]['id']);
        $gridName  = (string)$timePeriodNode->timegrid;
        $day       = (int)$timePeriodNode->day;
        $periodNo  = (int)$timePeriodNode->period;
        $startTime = trim((string)$timePeriodNode->starttime);
        $endTime   = trim((string)$timePeriodNode->endtime);

        $invalidKeys   = (empty($exportKey) or empty($gridName) or empty($periodNo));
        $invalidTimes  = (empty($day) or empty($startTime) or empty($endTime));
        $invalidPeriod = ($invalidKeys or $invalidTimes);

        if ($invalidPeriod) {
            if (!in_array(Languages::_('THM_ORGANIZER_ERROR_PERIODS_INCONSISTENT'), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_ERROR_PERIODS_INCONSISTENT');
            }

            return;
        }

        // Set the grid if not already existent
        if (empty($scheduleModel->schedule->periods->$gridName)) {
            $scheduleModel->schedule->periods->$gridName = new stdClass;
        }

        $scheduleModel->schedule->periods->$gridName->$periodNo            = new stdClass;
        $scheduleModel->schedule->periods->$gridName->$periodNo->startTime = $startTime;
        $scheduleModel->schedule->periods->$gridName->$periodNo->endTime   = $endTime;

        $label = (string)$timePeriodNode->label;
        if (!empty($label)) {
            $textual = preg_match("/[a-zA-ZäÄöÖüÜß]+/", $label);

            if ($textual) {
                $scheduleModel->schedule->periods->$gridName->$periodNo->label_de = $label;
                $scheduleModel->schedule->periods->$gridName->$periodNo->label_en = $label;

                // This is an assumption, which can later be rectified as necessary.
                $scheduleModel->schedule->periods->$gridName->$periodNo->type = 'break';
            }
        }
    }
}