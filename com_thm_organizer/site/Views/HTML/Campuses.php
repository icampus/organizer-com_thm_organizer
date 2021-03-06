<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\Can;
use Organizer\Helpers\Campuses as Helper;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads a filtered set of campuses into the display context.
 */
class Campuses extends ListView
{
	protected $rowStructure = [
		'checkbox' => '',
		'name'     => 'link',
		'address'  => 'link',
		'location' => 'value',
		'gridID'   => 'link'
	];

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('ORGANIZER_CAMPUSES'), 'location');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', Languages::_('ORGANIZER_ADD'), 'campuses.add', false);
		$toolbar->appendButton('Standard', 'edit', Languages::_('ORGANIZER_EDIT'), 'campuses.edit', true);
		$toolbar->appendButton(
			'Confirm',
			Languages::_('ORGANIZER_DELETE_CONFIRM'),
			'delete',
			Languages::_('ORGANIZER_DELETE'),
			'campuses.delete',
			true
		);
	}

	/**
	 * Function determines whether the user may access the view.
	 *
	 * @return bool true if the use may access the view, otherwise false
	 */
	protected function allowAccess()
	{
		return Can::manage('facilities');
	}

	/**
	 * Function to set the object's headers property
	 *
	 * @return void sets the object headers property
	 */
	public function setHeaders()
	{
		$headers = [
			'checkbox' => '',
			'name'     => Languages::_('ORGANIZER_NAME'),
			'address'  => Languages::_('ORGANIZER_ADDRESS'),
			'location' => Languages::_('ORGANIZER_LOCATION'),
			'gridID'   => Languages::_('ORGANIZER_GRID')
		];

		$this->headers = $headers;
	}

	/**
	 * Processes the items in a manner specific to the view, so that a generalized  output in the layout can occur.
	 *
	 * @return void processes the class items property
	 */
	protected function structureItems()
	{
		$structuredItems = [];

		foreach ($this->items as $item)
		{
			if (empty($item->parentID))
			{
				$index = $item->name;
			}
			else
			{
				$index      = "{$item->parentName}-{$item->name}";
				$item->name = "|&nbsp;&nbsp;-&nbsp;{$item->name}";
			}

			$address    = '';
			$ownAddress = (!empty($item->address) or !empty($item->city) or !empty($item->zipCode));

			if ($ownAddress)
			{
				$addressParts   = [];
				$addressParts[] = empty($item->address) ? empty($item->parentAddress) ?
					'' : $item->parentAddress : $item->address;
				$addressParts[] = empty($item->city) ? empty($item->parentCity) ? '' : $item->parentCity : $item->city;
				$addressParts[] = empty($item->zipCode) ? empty($item->parentZIPCode) ?
					'' : $item->parentZIPCode : $item->zipCode;
				$address        = implode(' ', $addressParts);
			}

			$item->address  = $address;
			$item->location = Helper::getPin($item->location);

			if (!empty($item->gridName))
			{
				$gridName = $item->gridName;
			}
			elseif (!empty($item->parentGridName))
			{
				$gridName = $item->parentGridName;
			}
			else
			{
				$gridName = Languages::_('JNONE');
			}
			$item->gridID = $gridName;

			$structuredItems[$index] = $this->structureItem($index, $item, $item->link);
		}

		asort($structuredItems);

		$this->items = $structuredItems;
	}
}
