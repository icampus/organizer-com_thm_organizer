<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Joomla\CMS\Table\Table;
use Organizer\Helpers\Access;
use Organizer\Tables\Subjects as SubjectsTable;

/**
 * Class loads a form for editing data.
 */
class SubjectEdit extends EditModel
{
	protected $deptResource;

	public $item = null;

	/**
	 * Checks for user authorization to access the view
	 *
	 * @param   int  $subjectID  the id of the subject for which authorization is to be checked
	 *
	 * @return bool  true if the user can access the view, otherwise false
	 */
	protected function allowEdit($subjectID = null)
	{
		return Access::allowSubjectAccess($subjectID);
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
		return new SubjectsTable;
	}
}
