<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use JDatabaseQuery;
use Organizer\Helpers\Languages;

/**
 * Class retrieves information for a filtered set of runs.
 */
class Runs extends ListModel
{
	protected $defaultOrdering = 'name';

	protected $filter_fields = ['termID'];

	/**
	 * Method to get a list of resources from the database.
	 *
	 * @return JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$tag       = Languages::getTag();
		$linkParts = ["'index.php?option=com_thm_organizer&view=run_edit&id='", 'r.id'];
		$query     = $this->_db->getQuery(true);
		$query->select("r.id, r.name_$tag as name, r.run, r.termID, t.name_$tag as term")
			->select($query->concatenate($linkParts, '') . ' AS link')
			->leftJoin('#__thm_organizer_terms AS t ON t.id = r.termID')
			->from('#__thm_organizer_runs AS r');

		$this->setSearchFilter($query, ['name_de', 'name_en']);
		$this->setValueFilters($query, ['termID']);

		$this->setOrdering($query);

		return $query;
	}
}