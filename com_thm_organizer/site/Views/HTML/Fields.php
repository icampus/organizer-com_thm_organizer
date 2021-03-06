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
use Organizer\Helpers\Colors;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads persistent information a filtered set of fields (of expertise) into the display context.
 */
class Fields extends ListView
{
	protected $rowStructure = ['checkbox' => '', 'field' => 'link', 'untisID' => 'link', 'colorID' => 'value'];

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('ORGANIZER_FIELDS'), 'lamp');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', Languages::_('ORGANIZER_ADD'), 'fields.add', false);
		$toolbar->appendButton('Standard', 'edit', Languages::_('ORGANIZER_EDIT'), 'fields.edit', true);
		$toolbar->appendButton('Standard', 'attachment', Languages::_('ORGANIZER_MERGE'), 'fields.mergeView', true);
		$toolbar->appendButton(
			'Confirm',
			Languages::_('ORGANIZER_DELETE_CONFIRM'),
			'delete',
			Languages::_('ORGANIZER_DELETE'),
			'fields.delete',
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
		return Can::administrate();
	}

	/**
	 * Function to set the object's headers property
	 *
	 * @return void sets the object headers property
	 */
	public function setHeaders()
	{
		$ordering  = $this->state->get('list.ordering');
		$direction = $this->state->get('list.direction');
		$headers   = [
			'checkbox' => '',
			'field'    => HTML::sort('NAME', 'field', $direction, $ordering),
			'untisID'  => HTML::sort('UNTIS_ID', 'untisID', $direction, $ordering),
			'colorID'  => HTML::sort('COLOR', 'c.name', $direction, $ordering)
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
		$index           = 0;
		$link            = 'index.php?option=com_thm_organizer&view=field_edit&id=';
		$structuredItems = [];

		foreach ($this->items as $item)
		{
			$item->colorID           = Colors::getListDisplay($item->color, $item->colorID);
			$structuredItems[$index] = $this->structureItem($index, $item, $link . $item->id);
			$index++;
		}

		$this->items = $structuredItems;
	}
}
