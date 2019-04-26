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

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/teachers.php';

use OrganizerHelper as OrganizerHelper;

/**
 * Class builds a model of a set of curriculum resources in JSON format.
 */
class THM_OrganizerModelCurriculum_Ajax extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{
    private $_scheduleID;

    private $_scheduleLink;

    private $_schedule;

    /**
     * Method to select the Tree of the current major
     *
     * @return string  the json encoded string modeling the curriculum
     */
    public function getCurriculum()
    {
        $programID = OrganizerHelper::getInput()->getInt('programID');

        if (empty($programID)) {
            return '';
        }

        $languageTag = OrganizerHelper::getInput()->getString('languageTag', 'de');

        // Get the major in order to build the complete label of a given major/curriculum
        $program = $this->getProgramData($programID);
        $this->setScheduleData($program->name);
        $program->children = $this->getChildren($program->lft, $program->rgt, $languageTag);
        $program->fields   = $this->getFieldColors($program->lft, $program->rgt);

        if (empty($program->children)) {
            return '';
        } else {
            return json_encode($program);
        }
    }

    /**
     * Retrieves a list of the fields associated with program subjects their colors
     *
     * @param int $left  the left value for the program
     * @param int $right the right value for the program
     *
     * @return mixed  array on success, otherwise false
     */
    private function getFieldColors($left, $right)
    {
        $query = $this->_db->getQuery(true);
        $query->select('DISTINCT field, color');

        $query->from('#__thm_organizer_fields AS f');
        $query->innerJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
        $query->innerJoin('#__thm_organizer_subjects AS s ON s.fieldID = f.id');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.subjectID = s.id');
        $query->where("m.lft >= '$left'");
        $query->where("m.rgt <= '$right'");
        $query->order('field');

        return OrganizerHelper::executeQuery('loadAssocList');
    }

    /**
     * Retrieves pool specific information.
     *
     * @param int    $poolID  the id of the pool being sought
     * @param string $langTag the current display language
     *
     * @return mixed  The return value or null if the query failed.
     * @throws  exception
     */
    private function getPoolData($poolID, $langTag)
    {
        $dbo    = \JFactory::getDbo();
        $query  = $dbo->getQuery(true);
        $select = "p.id, lsfID, hisID, externalID, name_$langTag AS name, minCrP, maxCrP, color";
        $query->select($select);
        $query->from('#__thm_organizer_pools AS p');
        $query->leftJoin('#__thm_organizer_fields AS f ON p.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
        $query->where("p.id = '$poolID'");
        $dbo->setQuery($query);

        $poolData = OrganizerHelper::executeQuery('loadObject');
        if (empty($poolData)) {
            $poolData = new \stdClass;
        }

        if (empty($poolData->color)) {
            $poolData->color = OrganizerHelper::getParams()->get('backgroundColor', '#ffffff');
        }

        $poolData->children = [];

        return $poolData;
    }

    /**
     * Method to get program information
     *
     * @param int $programID the id of the program being modelled
     *
     * @return array
     *
     * @throws  exception
     */
    private function getProgramData($programID)
    {
        $languageTag = OrganizerHelper::getInput()->getString('languageTag', 'de');
        $dbo         = \JFactory::getDbo();
        $query       = $dbo->getQuery(true);
        $parts       = ["p.name_{$languageTag}", "' ('", 'd.abbreviation', "' '", 'p.version', "')'"];
        $select      = $query->concatenate($parts, '') . ' AS name, ';
        $select      .= "m.id AS mappingID, m.lft, m.rgt, p.description_{$languageTag} AS description";
        $query->select($select);
        $query->from('#__thm_organizer_programs AS p');
        $query->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
        $query->innerJoin('#__thm_organizer_mappings AS m ON p.id = m.programID');
        $query->where("p.id = '$programID'");
        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadObject');
    }

    /**
     * Retrieves subject specific information
     *
     * @param int    $subjectID the id of the subject being sought
     * @param string $langTag   the current display language
     *
     * @return mixed  The return value or null if the query failed.
     *
     * @throws  exception
     */
    private function getSubjectData($subjectID, $langTag)
    {
        $itemID        = OrganizerHelper::getInput()->get('Itemid');
        $dbo           = \JFactory::getDbo();
        $query         = $dbo->getQuery(true);
        $select        = "s.id, lsfID, hisID, externalID, name_$langTag AS name, creditpoints AS maxCrP, color, ";
        $concateSelect = [
            "'index.php?option=com_thm_organizer&view=subject_details&languageTag='",
            "'$langTag'",
            "'&id='",
            's.id',
            "'&Itemid='",
            "'$itemID'"
        ];
        $select        .= $query->concatenate($concateSelect, "");
        $select        .= ' AS link';
        $query->select($select);
        $query->from('#__thm_organizer_subjects AS s');
        $query->leftJoin('#__thm_organizer_fields AS f ON s.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
        $query->where("s.id = '$subjectID'");
        $dbo->setQuery($query);

        $subjectData = OrganizerHelper::executeQuery('loadObject');
        if (empty($subjectData)) {
            return null;
        }

        if (empty($subjectData->color)) {
            $subjectData->color = OrganizerHelper::getParams()->get('backgroundColor', '#ffffff');
        }

        $subjectData->link = \JRoute::_($subjectData->link);
        if (!empty($subjectData->externalID) and !empty($this->_schedule)) {
            foreach ($this->_schedule->subjects as $subjectID => $subject) {
                if ($subject->subjectNo == $subjectData->externalID) {
                    $subjectData->scheduleLink = $this->_scheduleLink . "&subjectID=$subjectID";
                    break;
                }
            }
        }

        $this->setTeacherProperties($subjectData);

        return $subjectData;
    }

    /**
     * Retrieves program children recursively
     *
     * @param int    $lft     the left boundary of the program in the nested table
     * @param int    $rgt     the right boundary of the program in the nested table
     * @param string $langTag the current display language
     *
     * @return array  empty if no child data exists
     *
     * @throws  exception
     */
    public function getChildren($lft, $rgt, $langTag = 'de')
    {
        $dbo      = \JFactory::getDbo();
        $children = [];

        $mappingsQuery = $dbo->getQuery(true);
        $mappingsQuery->select('*')->from('#__thm_organizer_mappings');
        $mappingsQuery->where("lft > '$lft'");
        $mappingsQuery->where("rgt < '$rgt'");
        $mappingsQuery->order('lft');
        $dbo->setQuery($mappingsQuery);

        $mappings = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($mappings)) {
            return $children;
        }

        $nodes = [];
        foreach ($mappings as $mapping) {
            $parent = $children;
            if ($mapping['level'] > 1) {
                for ($i = 1; $i < $mapping['level']; $i++) {
                    $parent = $parent[$nodes[$i]]->children;
                }
            }

            if (isset($mapping['poolID'])) {
                $nodes[(int)$mapping['level']]     = (int)$mapping['ordering'];
                $poolData                          = $this->getPoolData($mapping['poolID'], $langTag);
                $poolData->mappingID               = $mapping['id'];
                $poolData->lastChildOrder          = $this->lastChildOrder($poolData->mappingID);
                $parent[(int)$mapping['ordering']] = $poolData;
            } elseif (isset($mapping['subjectID'])) {
                $subjectData                       = $this->getSubjectData($mapping['subjectID'], $langTag);
                $subjectData->mappingID            = $mapping['id'];
                $parent[(int)$mapping['ordering']] = $subjectData;
            }
        }

        return $children;
    }

    /**
     * Retrieves the ordering of the last direct child element
     *
     * @param int $mappingID the id of the mapped element
     *
     * @return int  the last child element's ordering value
     *
     * @throws  exception
     */
    private function lastChildOrder($mappingID)
    {
        $dbo   = \JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('MAX(ordering)')->from('#__thm_organizer_mappings')->where("parentID = '$mappingID'");
        $dbo->setQuery($query);

        return (int)OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Checks for and sets schedule data if an applicable schedule is found
     *
     * @param string $programName the name of the program being modelled
     *
     * @return void
     *
     * @throws  exception
     */
    private function setScheduleData($programName)
    {
        $date  = date('Y-m-d');
        $dbo   = \JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id, schedule')->from('#__thm_organizer_schedules');
        $query->where("startDate <= '$date'")->where("endDate >= '$date'")->where("active = '1'");
        $dbo->setQuery($query);

        $currentSchedules = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($currentSchedules)) {
            return;
        }

        foreach ($currentSchedules as $currentSchedule) {
            $schedule = json_decode($currentSchedule['schedule']);
            foreach ((array)$schedule->degrees as $program) {
                if ($program->name == $programName) {
                    $this->_scheduleID   = $currentSchedule['id'];
                    $this->_scheduleLink = 'index.php?option=com_thm_organizer&view=scheduler';
                    $this->_scheduleLink .= "&scheduleID={$currentSchedule['id']}";
                    $this->_schedule     = $schedule;

                    return;
                }
            }
        }
    }

    /**
     * Sets subject properties relating to the responsible teacher
     *
     * @param object &$subjectData an object containing subject data
     *
     * @return void
     */
    private function setTeacherProperties(&$subjectData)
    {
        $teacherData = THM_OrganizerHelperTeachers::getDataBySubject($subjectData->id, 1);

        if (empty($teacherData)) {
            return;
        }

        $defaultName = THM_OrganizerHelperTeachers::getDefaultName($teacherData['id']);

        if (!empty($teacherData['userID'])) {
            $subjectData->teacherID   = $teacherData['userID'];
            $subjectData->teacherName = $defaultName;

            // TODO: Retrieve this information from a Joomla! instance with THm Groups.

            return;
        } else {
            $subjectData->teacherName = $defaultName;
        }

        if (!empty($teacherData['gpuntisID']) and !empty($this->_scheduleLink)) {
            $subjectData->teacherScheduleLink
                = $this->_scheduleLink . "&teacherID={$teacherData['gpuntisID']}";
        }
    }
}
