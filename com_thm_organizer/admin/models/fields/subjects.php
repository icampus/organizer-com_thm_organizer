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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class creates a select box for subjects.
 */
class JFormFieldSubjects extends JFormField
{
    protected $type = 'subjects';

    /**
     * Returns a selectionbox where stored coursepool can be chosen as a parent node
     *
     * @return string the HTML for the select box
     */
    public function getInput()
    {
        $subjectID = THM_OrganizerHelperComponent::getInput()->getInt('id', 0);
        $direction = $this->getAttribute('direction', 'pre');

        if ($direction == 'post') {
            $select = 'subjectID';
            $column = 'prerequisite';
        } else {
            $select = 'prerequisite';
            $column = 'subjectID';
        }

        $dbo           = JFactory::getDbo();
        $selectedQuery = $dbo->getQuery(true);
        $selectedQuery->select($select);
        $selectedQuery->from('#__thm_organizer_prerequisites');
        $selectedQuery->where("$column = '$subjectID'");
        $dbo->setQuery($selectedQuery);
        $selected = THM_OrganizerHelperComponent::query('loadColumn', []);

        $langTag       = THM_OrganizerHelperLanguage::getShortTag();
        $subjectsQuery = $dbo->getQuery(true);
        $subjectsQuery->select("DISTINCT id AS value, name_$langTag AS name, externalID");
        $subjectsQuery->from('#__thm_organizer_subjects');
        $subjectsQuery->order('name');
        $dbo->setQuery($subjectsQuery);

        $subjects = THM_OrganizerHelperComponent::query('loadAssocList');
        if (empty($subjects)) {
            return $this->getDefault();
        }

        foreach ($subjects as $key => $subject) {
            if (empty($subject['name'])) {
                unset($subjects[$key]);
                continue;
            }

            $subjects[$key]['text'] = empty($subject['externalID']) ? $subject['name'] : "{$subject['name']} ({$subject['externalID']})";
        }

        $fieldName        = $this->getAttribute('name');
        $attributes       = ['multiple' => 'multiple', 'class' => 'inputbox', 'size' => '10'];
        $selectedSubjects = empty($selected) ? [] : $selected;

        return JHtml::_(
            'select.genericlist',
            $subjects,
            "jform[$fieldName][]",
            $attributes,
            'value',
            'text',
            $selectedSubjects
        );
    }

    /**
     * Creates a default input in the event of an exception
     *
     * @return string  a default teacher selection field without any teachers
     */
    private function getDefault()
    {
        $subjects   = [];
        $subjects[] = ['value' => '-1', 'name' => JText::_('JNONE')];
        $fieldName  = $this->getAttribute('name');
        $attributes = ['multiple' => 'multiple', 'class' => 'inputbox', 'size' => '1'];

        return JHtml::_('select.genericlist', $subjects, "jform[$fieldName][]", $attributes, 'value', 'text');
    }
}
