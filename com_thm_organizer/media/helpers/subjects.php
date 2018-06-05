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

require_once 'departments.php';


/**
 * Provides general functions for subject access checks, data retrieval and display.
 */
class THM_OrganizerHelperSubjects
{
    /**
     * Check if user is registered as a subject's teacher, optionally for a specific subject
     *
     * @param int $subjectID id of the course resource
     *
     * @return boolean true if the user is a registered teacher, otherwise false
     * @throws Exception
     */
    public static function allowEdit($subjectID)
    {
        $user = JFactory::getUser();

        if (empty($user->id)) {
            return false;
        }

        if ($user->authorise('core.admin', "com_thm_organizer")) {
            return true;
        }

        // Belongs to an explicitly authorized user group
        require_once 'component.php';

        if (empty($subjectID) or !THM_OrganizerHelperComponent::checkAssetInitialization('subject', $subjectID)) {
            return THM_OrganizerHelperComponent::allowDeptResourceCreate('subject');
        }

        if (THM_OrganizerHelperComponent::allowResourceManage('subject', $subjectID, 'manage')) {
            return true;
        }

        // Teacher coordinator responsibility association from the documentation system
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select("COUNT(*)")
            ->from('#__thm_organizer_subject_teachers AS st')
            ->innerJoin('#__thm_organizer_teachers AS t ON t.id = st.teacherID')
            ->where("t.username = '{$user->username}'")
            ->where("st.subjectID = '$subjectID'")
            ->where("teacherResp = '1'");

        $dbo->setQuery($query);

        try {
            $assocCount = $dbo->loadResult();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

            return false;
        }

        return !empty($assocCount);
    }

    /**
     * Retrieves the table id if existent.
     *
     * @param string $subjectIndex the subject index (dept. abbreviation + gpuntis id)
     *
     * @return mixed int id on success, otherwise null
     */
    public static function getID($subjectIndex)
    {
        $table  = JTable::getInstance('plan_subjects', 'thm_organizerTable');
        $data   = ['subjectIndex' => $subjectIndex];
        $exists = $table->load($data);
        if ($exists) {
            return $exists ? $table->id : null;
        }

        return null;
    }

    /**
     * Retrieves the (plan) subject name
     *
     * @param int     $subjectID the table id for the subject
     * @param string  $type      the type of the id (real or plan)
     * @param boolean $withNumber
     *
     * @return string the (plan) subject name
     * @throws Exception
     */
    public static function getName($subjectID, $type, $withNumber = false)
    {
        $dbo         = JFactory::getDbo();
        $languageTag = THM_OrganizerHelperLanguage::getShortTag();

        $query = $dbo->getQuery(true);
        $query->select("ps.name as psName, s.name_$languageTag as name");
        $query->select("s.short_name_$languageTag as shortName, s.abbreviation_$languageTag as abbreviation");
        $query->select("ps.subjectNo as psSubjectNo, s.externalID as subjectNo");

        if ($type == 'real') {
            $query->from('#__thm_organizer_subjects AS s');
            $query->leftJoin('#__thm_organizer_subject_mappings AS sm ON s.id = sm.subjectID');
            $query->leftJoin('#__thm_organizer_plan_subjects AS ps ON sm.plan_subjectID = ps.id');
            $query->where("s.id = '$subjectID'");
        } else {
            $query->from('#__thm_organizer_plan_subjects AS ps');
            $query->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.plan_subjectID = ps.id');
            $query->leftJoin('#__thm_organizer_subjects AS s ON s.id = sm.subjectID');
            $query->where("ps.id = '$subjectID'");
        }

        $dbo->setQuery($query);

        try {
            $names = $dbo->loadAssoc();
        } catch (RuntimeException $exc) {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');

            return '';
        }

        if (empty($names)) {
            return '';
        }

        $suffix = '';

        if ($withNumber) {
            if (!empty($names['subjectNo'])) {
                $suffix .= " ({$names['subjectNo']})";
            } elseif (!empty($names['psSubjectNo'])) {
                $suffix .= " ({$names['psSubjectNo']})";
            }
        }

        if (!empty($names['name'])) {
            return $names['name'] . $suffix;
        }

        if (!empty($names['shortName'])) {
            return $names['shortName'] . $suffix;
        }

        return empty($names['psName']) ? $names['abbreviation'] . $suffix : $names['psName'] . $suffix;
    }

    /**
     * Attempts to get the plan subject's id, creating it if non-existent.
     *
     * @param string $subjectIndex
     * @param object $subject the subject object
     *
     * @return mixed int on success, otherwise null
     */
    public static function getPlanResourceID($subjectIndex, $subject)
    {
        $subjectID = self::getID($subjectIndex);

        $table = JTable::getInstance('plan_subjects', 'thm_organizerTable');

        if (!empty($subjectID)) {
            $table->load($subjectID);
        }

        $data                 = [];
        $data['subjectIndex'] = $subjectIndex;
        $data['gpuntisID']    = $subject->gpuntisID;

        if (!empty($subject->fieldID)) {
            $data['fieldID'] = $subject->fieldID;
        }

        $data['subjectNo'] = $subject->subjectNo;
        $data['name']      = $subject->longname;

        $success = $table->save($data);

        return $success ? $table->id : null;
    }

    /**
     * Looks up the names of the (plan) programs associated with the subject
     *
     * @param int    $subjectID the id of the (plan) subject
     * @param string $type      the type of the reference subject (plan|real)
     *
     * @return array the associated program names
     * @throws Exception
     */
    public static function getPrograms($subjectID, $type)
    {
        $names       = [];
        $dbo         = JFactory::getDbo();
        $languageTag = THM_OrganizerHelperLanguage::getShortTag();

        $query     = $dbo->getQuery(true);
        $nameParts = ["p.name_$languageTag", "' ('", "d.abbreviation", "' '", "p.version", "')'"];
        $query->select('ppr.name AS ppName, ' . $query->concatenate($nameParts, "") . ' AS name');

        if ($type == 'real') {
            $query->select('p.id');
            $query->from('#__thm_organizer_programs AS p');
            $query->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
            $query->innerJoin('#__thm_organizer_mappings AS m1 ON m1.programID = p.id');
            $query->innerJoin('#__thm_organizer_mappings AS m2 ON m1.lft < m2.lft AND m1.rgt > m2.rgt');
            $query->leftJoin('#__thm_organizer_plan_programs AS ppr ON ppr.programID = p.id');
            $query->where("m2.subjectID = '$subjectID'");
        } else {
            $query->select('ppr.id');
            $query->from('#__thm_organizer_plan_programs AS ppr');
            $query->innerJoin('#__thm_organizer_plan_pools AS ppl ON ppl.programID = ppr.id');
            $query->innerJoin('#__thm_organizer_lesson_pools AS lp ON lp.poolID = ppl.id');
            $query->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.id = lp.subjectID');
            $query->leftJoin('#__thm_organizer_programs AS p ON ppr.programID = p.id');
            $query->leftJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
            $query->where("ls.subjectID = '$subjectID'");
        }

        $dbo->setQuery($query);

        try {
            $results = $dbo->loadAssocList();
        } catch (RuntimeException $exception) {
            JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

            return $names;
        }


        foreach ($results as $result) {
            $names[$result['id']] = empty($result['name']) ? $result['ppName'] : $result['name'];
        }

        return $names;
    }
}
