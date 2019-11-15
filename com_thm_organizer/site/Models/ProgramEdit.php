<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Joomla\CMS\Table\Table;
use Organizer\Helpers\Access;
use Organizer\Tables\Programs as ProgramsTable;

/**
 * Class loads a form for editing (degree) program data.
 */
class ProgramEdit extends EditModel
{
	public $children = null;

	/**
	 * Checks for user authorization to access the view.
	 *
	 * @return bool  true if the user can access the edit view, otherwise false
	 */
	public function allowEdit()
	{
		$programID = (isset($this->item->id) and !empty($this->item->id)) ? $this->item->id : 0;
		if (empty($programID) or !Access::checkAssetInitialization('program', $programID))
		{
			return Access::allowDocumentAccess();
		}

		return Access::allowDocumentAccess('program', $programID);
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return Table A Table object
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function getTable($name = '', $prefix = '', $options = [])
	{
		return new ProgramsTable;
	}
}
