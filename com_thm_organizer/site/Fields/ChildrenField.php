<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class creates a box for managing subordinated curriculum elements. Change order, remove, add empty element.
 */
class ChildrenField extends FormField
{
	use Translated;

	/**
	 * Type
	 *
	 * @var    String
	 */
	protected $type = 'Children';

	/**
	 * Generates a text for the management of child elements
	 *
	 * @return string  the HTML for the input
	 */
	public function getInput()
	{
		$children = $this->getChildren();

		$document = Factory::getDocument();
		$document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/children.css');
		$document->addScript(Uri::root() . 'components/com_thm_organizer/js/children.js');

		return $this->getHTML($children);
	}

	/**
	 * Retrieves child mappings for the resource being edited
	 *
	 * @return array  empty if no child data exists
	 */
	private function getChildren()
	{
		$resourceID   = $this->form->getValue('id');
		$contextParts = explode('.', $this->form->getName());
		$resource     = OrganizerHelper::getResource($contextParts[1]);

		$dbo     = Factory::getDbo();
		$idQuery = $dbo->getQuery(true);
		$idQuery->select('id')->from('#__thm_organizer_mappings');
		$idQuery->where("{$resource}ID = '$resourceID'");

		/**
		 * Subordinate structures are the same for every parent mapping,
		 * therefore only the first mapping needs to be found
		 */
		$dbo->setQuery($idQuery, 0, 1);
		$parentID = OrganizerHelper::executeQuery('loadResult');

		if (empty($parentID))
		{
			return [];
		}

		$childMappingQuery = $dbo->getQuery(true);
		$childMappingQuery->select('poolID, subjectID, ordering');
		$childMappingQuery->from('#__thm_organizer_mappings');
		$childMappingQuery->where("parentID = '$parentID'");
		$childMappingQuery->order('lft ASC');
		$dbo->setQuery($childMappingQuery);

		$children = OrganizerHelper::executeQuery('loadAssocList', [], 'ordering');
		if (empty($children))
		{
			return [];
		}

		$this->setTypeData($children);

		return $children;
	}

	/**
	 * Sets mapping data dependent upon the resource type
	 *
	 * @param   array &$children  the subordinate resource data
	 *
	 * @return void  adds data to the &$children array
	 */
	private function setTypeData(&$children)
	{
		$poolEditLink    = 'index.php?option=com_thm_organizer&view=pool_edit&id=';
		$subjectEditLink = 'index.php?option=com_thm_organizer&view=subject_edit&id=';
		foreach ($children as $ordering => $mapping)
		{
			if (!empty($mapping['poolID']))
			{
				$children[$ordering]['id']   = $mapping['poolID'] . 'p';
				$children[$ordering]['name'] = $this->getResourceName($mapping['poolID'], 'pool');
				$children[$ordering]['link'] = $poolEditLink . $mapping['poolID'];
			}
			else
			{
				$children[$ordering]['id']   = $mapping['subjectID'] . 's';
				$children[$ordering]['name'] = $this->getResourceName($mapping['subjectID'], 'subject');
				$children[$ordering]['link'] = $subjectEditLink . $mapping['subjectID'];
			}
		}
	}

	/**
	 * Retrieves the child's name from the database
	 *
	 * @param   string  $resourceID    the id used for the child element
	 * @param   string  $resourceType  the child element's type
	 *
	 * @return string  the name of the child element
	 */
	private function getResourceName($resourceID, $resourceType)
	{
		$dbo   = Factory::getDbo();
		$tag   = Languages::getTag();
		$query = $dbo->getQuery(true);

		$query->select("name_$tag")->from("#__thm_organizer_{$resourceType}s")->where("id = '$resourceID'");
		$dbo->setQuery($query);

		return (string) OrganizerHelper::executeQuery('loadResult');
	}

	/**
	 * Generates the HTML Output for the children field
	 *
	 * @param   array &$children  the children of the resource being edited
	 *
	 * @return string  the HTML string for the children field
	 */
	private function getHTML(&$children)
	{
		$html = '<table id="childList" class="table table-striped">';
		$html .= '<thead><tr>';
		$html .= '<th>' . Languages::_('ORGANIZER_NAME') . '</th>';
		$html .= '<th class="thm_organizer_pools_ordering">' . Languages::_('ORGANIZER_ORDER') . '</th>';
		$html .= '</tr></thead>';
		$html .= '<tbody>';

		$addSpace = Languages::_('ORGANIZER_ADD_EMPTY');
		Languages::script('ORGANIZER_ADD_EMPTY');
		$makeFirst = Languages::_('ORGANIZER_MAKE_FIRST');
		Languages::script('ORGANIZER_MAKE_FIRST');
		$makeLast = Languages::_('ORGANIZER_MAKE_LAST');
		Languages::script('ORGANIZER_MAKE_LAST');
		$moveChildUp = Languages::_('ORGANIZER_MOVE_UP');
		Languages::script('ORGANIZER_MOVE_UP');
		$moveChildDown = Languages::_('ORGANIZER_MOVE_DOWN');
		Languages::script('ORGANIZER_MOVE_DOWN');
		Languages::script('ORGANIZER_DELETE');

		$rowClass = 'row0';
		if (!empty($children))
		{
			$maxOrdering = max(array_keys($children));
			for ($ordering = 1; $ordering <= $maxOrdering; $ordering++)
			{
				if (isset($children[$ordering]))
				{
					$childID = $children[$ordering]['id'];
					$name    = $children[$ordering]['name'];
					$link    = Route::_($children[$ordering]['link'], false);
				}
				else
				{
					$link = $name = $childID = '';
				}

				$icon = '';
				if (!empty($children[$ordering]))
				{
					$icon = empty($children[$ordering]['subjectID']) ? 'icon-list' : 'icon-book';
				}

				$html .= '<tr id="childRow' . $ordering . '" class="' . $rowClass . '">';

				$visualDetails = '<td class="child-name">';
				$visualDetails .= '<a id="child' . $ordering . 'Link" href="' . $link . '" target="_blank">';
				$visualDetails .= '<span id="child' . $ordering . 'Icon" class="' . $icon . '"></span>';
				$visualDetails .= '<span id="child' . $ordering . 'Name">' . $name . '</span></a>';
				$visualDetails .= '<input type="hidden" name="child' . $ordering . '" id="child' . $ordering . '" ';
				$visualDetails .= 'value="' . $childID . '" /></td>';

				$orderingToolbar = '<td class="child-order">';

				$first = '<button class="btn btn-small" onclick="setFirst(\'' . $ordering . '\');" ';
				$first .= 'title="' . $makeFirst . '"><span class="icon-first"></span></button>';

				$previous = '<button class="btn btn-small" onclick="moveChildUp(\'' . $ordering . '\');" ';
				$previous .= 'title="' . $moveChildUp . '"><span class="icon-previous"></span></button>';

				$order = '<input type="text" title="Ordering" name="child' . $ordering . 'Order" ';
				$order .= 'id="child' . $ordering . 'Order" size="2" value="' . $ordering . '" ';
				$order .= 'class="text-area-order" onChange="moveChildToIndex(' . $ordering . ');"/>';

				$blank = '<button class="btn btn-small" onclick="addBlankChild(\'' . $ordering . '\');" ';
				$blank .= 'title="' . $addSpace . '"><span class="icon-download"></span></button>';

				$trash = '<button class="btn btn-small" onClick="trash(' . $ordering . ');" ';
				$trash .= 'title="' . Languages::_('ORGANIZER_DELETE') . '" ><span class="icon-trash"></span>';
				$trash .= '</button>';

				$next = '<button class="btn btn-small" onclick="moveChildDown(\'' . $ordering . '\');" ';
				$next .= 'title="' . $moveChildDown . '"><span class="icon-next"></span></button>';

				$last = '<button class="btn btn-small" onclick="setLast(\'' . $ordering . '\');" ';
				$last .= 'title="' . $makeLast . '"><span class="icon-last"></span></button>';

				$orderingToolbar .= $first . $previous . $order . $blank . $trash . $next . $last . '</td>';

				$html     .= $visualDetails . $orderingToolbar . '</tr>';
				$rowClass = $rowClass == 'row0' ? 'row1' : 'row0';
			}
		}
		$html .= '</tbody>';
		$html .= '</table>';
		$html .= '<div class="btn-toolbar" id="children-toolbar"></div>';

		return $html;
	}
}
