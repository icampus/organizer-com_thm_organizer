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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use stdClass;

/**
 * Provides general functions for teacher access checks, data retrieval and display.
 */
class Teachers implements DepartmentAssociated, XMLValidator
{
    const COORDINATES = 1;

    const TEACHER = 2;

    /**
     * Checks for multiple teacher entries (responsibilities) for a subject and removes the lesser
     *
     * @param array &$list the list of teachers responsilbe for a subject
     *
     * @return void  removes duplicate list entries dependent on responsibility
     */
    private static function ensureUnique(&$list)
    {
        $keysToIds = [];
        foreach ($list as $key => $item) {
            $keysToIds[$key] = $item['id'];
        }

        $valueCount = array_count_values($keysToIds);
        foreach ($list as $key => $item) {
            $unset = ($valueCount[$item['id']] > 1 and $item['teacherResp'] > 1);
            if ($unset) {
                unset($list[$key]);
            }
        }
    }

    /**
     * Retrieves the teacher responsible for the subject's development
     *
     * @param int  $subjectID      the subject's id
     * @param int  $responsibility represents the teacher's level of responsibility for the subject
     * @param bool $multiple       whether or not multiple results are desired
     * @param bool $unique         whether or not unique results are desired
     *
     * @return array  an array of teacher data
     */
    public static function getDataBySubject($subjectID, $responsibility = null, $multiple = false, $unique = true)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('t.id, t.surname, t.forename, t.title, t.username, u.id AS userID, teacherResp, untisID');
        $query->from('#__thm_organizer_teachers AS t');
        $query->innerJoin('#__thm_organizer_subject_teachers AS st ON t.id = st.teacherID ');
        $query->leftJoin('#__users AS u ON t.username = u.username');
        $query->where("st.subjectID = '$subjectID' ");

        if (!empty($responsibility)) {
            $query->where("st.teacherResp = '$responsibility'");
        }

        $query->order('surname ASC');
        $dbo->setQuery($query);

        if ($multiple) {

            $teacherList = OrganizerHelper::executeQuery('loadAssocList');
            if (empty($teacherList)) {
                return [];
            }

            if ($unique) {
                self::ensureUnique($teacherList);
            }

            return $teacherList;
        }

        return OrganizerHelper::executeQuery('loadAssoc', []);
    }

    /**
     * Generates a default teacher text based upon organizer's internal data
     *
     * @param int $teacherID the teacher's id
     *
     * @return string  the default name of the teacher
     */
    public static function getDefaultName($teacherID)
    {
        $teacher = OrganizerHelper::getTable('Teachers');
        $teacher->load($teacherID);

        $return = '';
        if (!empty($teacher->id)) {
            $title    = empty($teacher->title) ? '' : "{$teacher->title} ";
            $forename = empty($teacher->forename) ? '' : "{$teacher->forename} ";
            $surname  = $teacher->surname;
            $return   .= $title . $forename . $surname;
        }

        return $return;
    }

    /**
     * Retrieves the ids of departments associated with the resource
     *
     * @param int $resourceID the id of the resource for which the associated departments are requested
     *
     * @return array the ids of departments associated with the resource
     */
    public static function getDepartmentIDs($resourceID)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('departmentID')
            ->from('#__thm_organizer_department_resources')
            ->where("teacherID = $resourceID");
        $dbo->setQuery($query);
        $departmentIDs = OrganizerHelper::executeQuery('loadColumn', []);

        return empty($departmentIDs) ? [] : $departmentIDs;
    }

    /**
     * Gets the departments with which the teacher is associated
     *
     * @param int $teacherID the teacher's id
     *
     * @return array the departments with which the teacher is associated id => name
     */
    public static function getDepartmentNames($teacherID)
    {
        $shortTag = Languages::getShortTag();

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("d.short_name_$shortTag AS name")
            ->from('#__thm_organizer_departments AS d')
            ->innerJoin('#__thm_organizer_department_resources AS dr ON dr.departmentID = d.id')
            ->where("teacherID = $teacherID");
        $dbo->setQuery($query);
        $departments = OrganizerHelper::executeQuery('loadColumn', []);

        return empty($departments) ? [] : $departments;
    }

    /**
     * Generates a preformatted teacher text based upon organizer's internal data
     *
     * @param int  $teacherID the teacher's id
     * @param bool $short     Whether or not the teacher's forename should be abbrevieated
     *
     * @return string  the default name of the teacher
     */
    public static function getLNFName($teacherID, $short = false)
    {
        $teacher = OrganizerHelper::getTable('Teachers');
        $teacher->load($teacherID);

        $return = '';
        if (!empty($teacher->id)) {
            if (!empty($teacher->forename)) {
                // Getting the first letter by other means can cause encoding problems with 'interesting' first names.
                $forename = $short ? mb_substr($teacher->forename, 0, 1) . '.' : $teacher->forename;
            }
            $return = $teacher->surname;
            $return .= empty($forename) ? '' : ", $forename";
        }

        return $return;
    }

    /**
     * Checks whether the user is a registered teacher returning their internal teacher id if existent.
     *
     * @param int $userID the user id if empty the current user is used
     *
     * @return int the teacher id if the user is a teacher, otherwise 0
     */
    public static function getIDByUserID($userID = null)
    {
        $user = Factory::getUser($userID);
        if (empty($user->id)) {
            return false;
        }

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id')
            ->from('#__thm_organizer_teachers')
            ->where("username = '{$user->username}'");
        $dbo->setQuery($query);

        return (int)OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Retrieves a list of resources in the form of name => id.
     *
     * @return array the resources, or empty
     */
    public static function getOptions()
    {
        return self::getPlanTeachers();
    }

    /**
     * Getter method for teachers in database. Only retrieving the IDs here allows for formatting the names according to
     * the needs of the calling views.
     *
     * @return array  the scheduled teachers which the user has access to
     */
    public static function getPlanTeachers()
    {
        $user = Factory::getUser();
        if (empty($user->id)) {
            return [];
        }

        $input         = OrganizerHelper::getInput();
        $departmentIDs = explode(',', $input->getString('departmentIDs'));
        $isTeacher     = self::getIDByUserID();
        if (empty($departmentIDs) and empty($isTeacher)) {
            return [];
        }

        $departmentIDs = ArrayHelper::toInteger($departmentIDs);

        foreach ($departmentIDs as $key => $departmentID) {
            $departmentAccess = Access::allowViewAccess($departmentID);
            if (!$departmentAccess) {
                unset($departmentIDs[$key]);
            }
        }

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('DISTINCT lt.teacherID')
            ->from('#__thm_organizer_lesson_teachers AS lt')
            ->innerJoin('#__thm_organizer_teachers AS t ON t.id = lt.teacherID');

        $wherray = [];
        if ($isTeacher) {
            $wherray[] = "t.username = '{$user->username}'";
        }

        if (!empty($departmentIDs)) {
            $query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.teacherID = lt.teacherID');

            $where = 'dr.departmentID IN (' . implode(',', $departmentIDs) . ')';

            $selectedPrograms = $input->getString('programIDs');

            if (!empty($selectedPrograms)) {
                $programIDs = "'" . str_replace(',', "', '", $selectedPrograms) . "'";
                $query->innerJoin('#__thm_organizer_lesson_courses AS lcrs ON lt.lessonCourseID = lcrs.id');
                $query->innerJoin('#__thm_organizer_lesson_groups AS lg ON lg.lessonCourseID = lcrs.id');
                $query->innerJoin('#__thm_organizer_groups AS gr ON gr.id = lg.groupID');

                $where .= " AND gr.programID in ($programIDs)";
                $where = "($where)";
            }

            $wherray[] = $where;
        }

        $query->where(implode(' OR ', $wherray));
        $dbo->setQuery($query);
        $teacherIDs = OrganizerHelper::executeQuery('loadColumn', []);

        if (empty($teacherIDs)) {
            return [];
        }

        $teachers = [];
        foreach ($teacherIDs as $teacherID) {
            $teachers[self::getLNFName($teacherID)] = $teacherID;
        }

        ksort($teachers);

        return $teachers;
    }

    /**
     * Function to sort teachers by their surnames and forenames.
     *
     * @param array &$teachers the teachers array to sort.
     */
    public static function nameSort(&$teachers)
    {
        uasort($teachers, function ($teacherOne, $teacherTwo) {
            $oneResp = isset($teacherOne['teacherResp'][self::COORDINATES]);
            $twoResp = isset($teacherTwo['teacherResp'][self::COORDINATES]);
            if ($oneResp or !$twoResp) {
                return 1;
            }

            return -1;
        });
    }

    /**
     * Function to sort teachers by their surnames and forenames.
     *
     * @param array &$teachers the teachers array to sort.
     */
    public static function respSort(&$teachers)
    {
        uasort($teachers, function ($teacherOne, $teacherTwo) {
            if ($teacherOne['surname'] == $teacherTwo['surname']) {
                return $teacherOne['forename'] > $teacherTwo['forename'];
            }

            return $teacherOne['surname'] > $teacherTwo['surname'];
        });
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
        $teacher      = $scheduleModel->schedule->teachers->$untisID;
        $table        = OrganizerHelper::getTable('Teachers');
        $loadCriteria = [];

        if (!empty($teacher->username)) {
            $loadCriteria[] = ['username' => $teacher->username];
        }
        if (!empty($teacher->forename)) {
            $loadCriteria[] = ['surname' => $teacher->surname, 'forename' => $teacher->forename];
        }
        $loadCriteria[] = ['untisID' => $teacher->untisID];

        $extPattern = "/^[v]?[A-ZÀ-ÖØ-Þ][a-zß-ÿ]{1,3}([A-ZÀ-ÖØ-Þ][A-ZÀ-ÖØ-Þa-zß-ÿ]*)$/";
        foreach ($loadCriteria as $criteria) {
            $success = $table->load($criteria);

            if ($success) {
                $altered = false;
                foreach ($teacher as $key => $value) {
                    if (property_exists($table, $key) and empty($table->$key) and !empty($value)) {
                        $table->set($key, $value);
                        $altered = true;
                    }
                }

                $existingInvalid = empty(preg_match($extPattern, $table->untisID));
                $newValid        = preg_match($extPattern, $untisID);
                $overwriteUntis  = ($table->untisID != $untisID and $existingInvalid and $newValid);
                if ($overwriteUntis) {
                    $table->untisID = $untisID;
                    $altered        = true;
                }
                if ($altered) {
                    $table->store();
                }

                $scheduleModel->schedule->teachers->$untisID->id = $table->id;

                return;
            }
        }

        // Entry not found
        $table->save($teacher);
        $scheduleModel->schedule->teachers->$untisID->id = $table->id;

        return;
    }

    /**
     * Checks whether the teacher is associated with lessons
     *
     * @param string $table     the dynamic part of the table name
     * @param int    $teacherID the id of the teacher being checked
     *
     * @return bool true if the teacher is assigned to a lesson
     */
    public static function teaches($table, $teacherID)
    {
        if (empty($table)) {
            return false;
        }

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('COUNT(*)')->from("#__thm_organizer_{$table}_teachers")->where("teacherID = '$teacherID'");
        $dbo->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('loadResult');
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
        if (empty($xmlObject->teachers)) {
            $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_ERROR_TEACHERS_MISSING');

            return;
        }

        $scheduleModel->schedule->teachers = new stdClass;

        foreach ($xmlObject->teachers->children() as $teacherNode) {
            self::validateIndividual($scheduleModel, $teacherNode);
        }

        if (!empty($scheduleModel->scheduleWarnings['TEACHER-EXTERNALID'])) {
            $warningCount = $scheduleModel->scheduleWarnings['TEACHER-EXTERNALID'];
            unset($scheduleModel->scheduleWarnings['TEACHER-EXTERNALID']);
            $scheduleModel->scheduleWarnings[]
                = sprintf(Languages::_('THM_ORGANIZER_WARNING_TEACHER_EXTID_MISSING'), $warningCount);
        }

        if (!empty($scheduleModel->scheduleWarnings['TEACHER-FORENAME'])) {
            $warningCount = $scheduleModel->scheduleWarnings['TEACHER-FORENAME'];
            unset($scheduleModel->scheduleWarnings['TEACHER-FORENAME']);
            $scheduleModel->scheduleWarnings[]
                = sprintf(Languages::_('THM_ORGANIZER_WARNING_FORENAME_MISSING'), $warningCount);
        }
    }

    /**
     * Checks whether teacher nodes have the expected structure and required
     * information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$teacherNode   the teacher node to be validated
     *
     * @return void
     */
    public static function validateIndividual(&$scheduleModel, &$teacherNode)
    {
        $internalID = trim((string)$teacherNode[0]['id']);
        if (empty($internalID)) {
            if (!in_array(Languages::_('THM_ORGANIZER_ERROR_TEACHER_ID_MISSING'), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = Languages::_('THM_ORGANIZER_ERROR_TEACHER_ID_MISSING');
            }

            return;
        }

        $internalID = str_replace('TR_', '', $internalID);
        $externalID = trim((string)$teacherNode->external_name);

        if (empty($externalID)) {
            $scheduleModel->scheduleWarnings['TEACHER-EXTERNALID']
                = empty($scheduleModel->scheduleWarnings['TEACHER-EXTERNALID']) ?
                1 : $scheduleModel->scheduleWarnings['TEACHER-EXTERNALID']++;
        } else {
            $externalID = str_replace('TR_', '', $externalID);
        }

        $untisID = empty($externalID) ? $internalID : $externalID;

        $surname = trim((string)$teacherNode->surname);
        if (empty($surname)) {
            $scheduleModel->scheduleErrors[]
                = sprintf(Languages::_('THM_ORGANIZER_ERROR_TEACHER_SURNAME_MISSING'), $internalID);

            return;
        }

        $forename = trim((string)$teacherNode->forename);
        if (empty($forename)) {
            $scheduleModel->scheduleWarnings['TEACHER-FORENAME']
                = empty($scheduleModel->scheduleWarnings['TEACHER-FORENAME']) ?
                1 : $scheduleModel->scheduleWarnings['TEACHER-FORENAME']++;
        }

        $fieldID        = str_replace('DS_', '', trim($teacherNode->teacher_description[0]['id']));
        $invalidFieldID = (empty($fieldID) or empty($scheduleModel->schedule->fields->$fieldID));
        $fieldID        = $invalidFieldID ? null : $scheduleModel->schedule->fields->$fieldID->id;
        $title          = trim((string)$teacherNode->title);
        $userName       = trim((string)$teacherNode->payrollnumber);

        $teacher           = new stdClass;
        $teacher->fieldID  = $fieldID;
        $teacher->forename = $forename;
        $teacher->untisID  = $untisID;
        $teacher->surname  = $surname;
        $teacher->title    = $title;
        $teacher->username = $userName;

        $scheduleModel->schedule->teachers->$internalID = $teacher;

        self::setID($scheduleModel, $internalID);
        Departments::setDepartmentResource($teacher->id, 'teacherID');
    }
}
