<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Tables;

/**
 * Class instantiates a Table Object associated with the subject_events table.
 */
class EventCoordinators extends BaseTable
{
	/**
	 * The id of the event entry referenced.
	 * INT(11) UNSIGNED NOT NULL
	 *
	 * @var int
	 */
	public $eventID;

	/**
	 * The id of the person entry referenced.
	 * INT(11) DEFAULT NULL
	 *
	 * @var int
	 */
	public $personID;

	/**
	 * Declares the associated table
	 *
	 * @param   \JDatabaseDriver &$dbo  A database connector object
	 */
	public function __construct(&$dbo = null)
	{
		parent::__construct('#__thm_organizer_event_coordinators', 'id', $dbo);
	}
}
