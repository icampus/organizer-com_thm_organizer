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

use \THM_OrganizerHelperHTML as HTML;

JFormHelper::loadFieldClass('list');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/campuses.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/component.php';

/**
 * Class creates a form field for building selection.
 */
class JFormFieldBuildingID extends JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'buildingID';

    /**
     * Returns a select box where stored buildings can be chosen
     *
     * @return array  the available buildings
     */
    protected function getOptions()
    {
        $defaultOptions = HTML::getTranslatedOptions($this, $this->element);
        $input          = THM_OrganizerHelperComponent::getInput();
        $formData       = $input->get('jform', [], 'array');
        $campusID       = (empty($formData) or empty($formData['campusID'])) ? $input->getInt('campusID') : (int)$formData['campusID'];

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('DISTINCT b.id, b.name, c.id AS campusID, c.parentID');
        $query->from('#__thm_organizer_buildings AS b')
            ->leftJoin('#__thm_organizer_campuses AS c ON c.id = b.campusID');

        if (!empty($campusID)) {
            $query->where("(c.id = '$campusID' OR c.parentID = '$campusID')");
        }

        $query->order('name');
        $dbo->setQuery($query);

        $buildings = THM_OrganizerHelperComponent::executeQuery('loadAssocList');
        if (empty($buildings)) {
            return $defaultOptions;
        }

        $options = [];
        for ($index = 0; $index < count($buildings); $index++) {
            $thisBuilding = $buildings[$index];

            // Nothing to compare, or the comparison reveals non-identical names
            $standardHandling = (empty($buildings[$index + 1]) or $thisBuilding['name'] != $buildings[$index + 1]['name']);
            if ($standardHandling) {
                // Integrate the campus name as appropriate
                $buildingName = empty($thisBuilding['campusName']) ?
                    $thisBuilding['name'] : "{$thisBuilding['name']} ({$thisBuilding['campusName']})";
                $options[]    = HTML::_('select.option', $thisBuilding['id'], $buildingName);
                continue;
            }

            if (empty($thisBuilding['campusName'])) {
                $thisCampusID               = empty($thisBuilding['parentID']) ? $thisBuilding['campusID'] : $thisBuilding['parentID'];
                $thisBuilding['campusName'] = THM_OrganizerHelperCampuses::getName($thisCampusID);
            }

            $nextBuilding               = $buildings[$index + 1];
            $nextCampusID               = empty($nextBuilding['parentID']) ? $nextBuilding['campusID'] : $nextBuilding['parentID'];
            $nextBuilding['campusName'] = THM_OrganizerHelperCampuses::getName($nextCampusID);

            // The campus name of the building being iterated comes alphabetically before the campus name of the next building
            if ($thisBuilding['campusName'] < $nextBuilding['campusName']) {
                $options[] = HTML::_(
                    'select.option',
                    $thisBuilding['id'],
                    "{$thisBuilding['name']} ({$thisBuilding['campusName']})"
                );

                $buildings[$index + 1] = $nextBuilding;
                continue;
            }

            // Set the options with the information from the next building and move this one to the next index
            $options[] = HTML::_(
                'select.option',
                $nextBuilding['id'],
                "{$nextBuilding['name']} ({$nextBuilding['campusName']})"
            );

            $buildings[$index + 1] = $thisBuilding;

        }

        return array_merge($defaultOptions, $options);
    }
}
