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

require_once 'grids.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/departments.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/grids.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/pools.php';
require_once 'programs.php';

/**
 * Provides functions for XML (subject) pool validation and modeling.
 */
class THM_OrganizerHelperXMLPools
{
    /**
     * Validates the pools (classes) node
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$xmlObject     the xml object being validated
     *
     * @return void
     */
    public static function validate(&$scheduleModel, &$xmlObject)
    {
        if (empty($xmlObject->classes)) {
            $scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_POOLS_MISSING");

            return;
        }

        $scheduleModel->newSchedule->pools = new stdClass;

        foreach ($xmlObject->classes->children() as $poolNode) {
            self::validateIndividual($scheduleModel, $poolNode);
        }
    }

    /**
     * Checks whether pool nodes have the expected structure and required information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$poolNode      the pool node to be validated
     *
     * @return void
     */
    private static function validateIndividual(&$scheduleModel, &$poolNode)
    {

        $gpuntisID = self::validateUntisID($scheduleModel, $poolNode);
        if (empty($gpuntisID)) {
            return;
        }

        $poolID                                                   = str_replace('CL_', '', $gpuntisID);
        $scheduleModel->newSchedule->pools->$poolID               = new stdClass;
        $scheduleModel->newSchedule->pools->$poolID->gpuntisID    = $poolID;
        $scheduleModel->newSchedule->pools->$poolID->name         = $poolID;
        $scheduleModel->newSchedule->pools->$poolID->localUntisID = str_replace('CL_', '',
            trim((string)$poolNode[0]['id']));

        $longName = trim((string)$poolNode->longname);

        if (empty($longName)) {
            $scheduleModel->scheduleErrors[] = sprintf(JText::_('COM_THM_ORGANIZER_ERROR_POOL_LONGNAME_MISSING'),
                $poolID);
            unset($scheduleModel->newSchedule->pools->$poolID);

            return;
        }

        $restriction = trim((string)$poolNode->classlevel);

        if (empty($restriction)) {
            $scheduleModel->scheduleErrors[] = sprintf(JText::_('COM_THM_ORGANIZER_ERROR_NODE_NAME'), $longName,
                $poolID);

            return;
        }

        $degreeID = str_replace('DP_', '', trim((string)$poolNode->class_department[0]['id']));

        if (empty($degreeID)) {
            $scheduleModel->scheduleErrors[] = sprintf(JText::_('COM_THM_ORGANIZER_ERROR_POOL_DEGREE_MISSING'),
                $longName, $poolID);

            return;
        } elseif (empty($scheduleModel->newSchedule->degrees->$degreeID)) {
            $scheduleModel->scheduleErrors[] = sprintf(JText::_('COM_THM_ORGANIZER_ERROR_POOL_DEGREE_LACKING'),
                $longName, $poolID, $degreeID);

            return;
        }

        $scheduleModel->newSchedule->pools->$poolID->longname    = $longName;
        $scheduleModel->newSchedule->pools->$poolID->restriction = $restriction;
        $scheduleModel->newSchedule->pools->$poolID->degree      = $degreeID;

        $grid = (string)$poolNode->timegrid;

        if (empty($grid)) {
            $scheduleModel->scheduleErrors[] = sprintf(JText::_('COM_THM_ORGANIZER_ERROR_POOL_GRID_MISSING'),
                $longName, $poolID);

            return;
        } elseif (empty($scheduleModel->newSchedule->periods->$grid)) {
            $scheduleModel->scheduleErrors[] = sprintf(JText::_('COM_THM_ORGANIZER_ERROR_POOL_GRID_LACKING'),
                $longName, $poolID, $grid);

            return;
        }

        $scheduleModel->newSchedule->pools->$poolID->grid   = $grid;
        $scheduleModel->newSchedule->pools->$poolID->gridID = THM_OrganizerHelperGrids::getID($grid);

        $planResourceID = THM_OrganizerHelperPools::getPlanResourceID($poolID,
            $scheduleModel->newSchedule->pools->$poolID);

        if (!empty($planResourceID)) {
            $scheduleModel->newSchedule->pools->$poolID->id = $planResourceID;
            THM_OrganizerHelperDepartments::setDepartmentResource($planResourceID, 'poolID');
        }
    }

    /**
     * Validates the pools's gp untis id
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$poolNode      the pool node object
     *
     * @return mixed  string id if valid, otherwise false
     */
    private static function validateUntisID(&$scheduleModel, &$poolNode)
    {
        $externalName = trim((string)$poolNode->external_name);
        $internalName = trim((string)$poolNode[0]['id']);
        $gpuntisID    = empty($externalName) ? $internalName : $externalName;
        if (empty($gpuntisID)) {
            if (!in_array(JText::_("COM_THM_ORGANIZER_ERROR_POOL_ID_MISSING"), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_POOL_ID_MISSING");
            }

            return false;
        }

        return $gpuntisID;
    }
}
