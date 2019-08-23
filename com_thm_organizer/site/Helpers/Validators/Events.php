<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers\Validators;

use Organizer\Helpers\Languages;
use Organizer\Helpers\ResourceHelper;
use stdClass;

/**
 * Provides general functions for course access checks, data retrieval and display.
 */
class Events extends ResourceHelper implements UntisXMLValidator
{
    /**
     * Retrieves the resource id using the Untis ID. Creates the resource id if unavailable.
     *
     * @param object &$model   the validating schedule model
     * @param string  $untisID the id of the resource in Untis
     *
     * @return void modifies the model, setting the id property of the resource
     */
    public static function setID(&$model, $untisID)
    {
        $event = $model->schedule->events->$untisID;
        $table = self::getTable();

        if ($table->load(['departmentID' => $event->departmentID, 'untisID' => $untisID])) {
            $altered = false;
            foreach ($event as $key => $value) {

                // Context based changes need no protection.
                if (property_exists($table, $key)) {
                    $table->set($key, $value);
                    $altered = true;
                }
            }

            if ($altered) {
                $table->store();
            }
        } else {
            $table->save($event);
        }

        $event->id = $table->id;

        return;
    }

    /**
     * Checks whether nodes have the expected structure and required information
     *
     * @param object &$model     the validating schedule model
     * @param object &$xmlObject the object being validated
     *
     * @return void modifies &$model
     */
    public static function validateCollection(&$model, &$xmlObject)
    {
        $model->schedule->events = new stdClass;

        foreach ($xmlObject->subjects->children() as $node) {
            self::validateIndividual($model, $node);
        }

        if (!empty($model->warnings['SUNO'])) {
            $warningCount = $model->warnings['SUNO'];
            unset($model->warnings['SUNO']);
            $model->warnings[] = sprintf(Languages::_('THM_ORGANIZER_EVENT_SUBJECTNOS_MISSING'), $warningCount);
        }
    }

    /**
     * Checks whether XML node has the expected structure and required
     * information
     *
     * @param object &$model the validating schedule model
     * @param object &$node  the node to be validated
     *
     * @return void
     */
    public static function validateIndividual(&$model, &$node)
    {
        $untisID = trim((string)$node[0]['id']);
        if (empty($untisID)) {
            if (!in_array(Languages::_('THM_ORGANIZER_EVENT_IDS_MISSING'), $model->errors)) {
                $model->errors[] = Languages::_('THM_ORGANIZER_EVENT_IDS_MISSING');
            }

            return;
        }

        $untisID = str_replace('SU_', '', $untisID);
        $name    = trim((string)$node->longname);

        if (empty($name)) {
            $model->errors[] = sprintf(Languages::_('THM_ORGANIZER_EVENT_NAME_MISSING'), $untisID);

            return;
        }

        $subjectNo = trim((string)$node->text);

        if (empty($subjectNo)) {
            $model->warnings['SUNO'] = empty($model->warnings['SUNO']) ? 1 : $model->warnings['SUNO']++;

            $subjectNo = '';
        }

        $fieldID      = str_replace('DS_', '', trim($node->subject_description[0]['id']));
        $fields       = $model->schedule->fields;
        $invalidField = (empty($fieldID) or empty($fields->$fieldID));
        $fieldID      = $invalidField ? null : $fields->$fieldID;

        $event               = new stdClass;
        $event->departmentID = $model->schedule->departmentID;
        $event->fieldID      = $fieldID;
        $event->untisID      = $untisID;
        $event->name_de      = $name;
        $event->name_en      = $name;
        $event->subjectNo    = $subjectNo;

        $model->schedule->events->$untisID = $event;
        self::setID($model, $untisID);
    }
}
