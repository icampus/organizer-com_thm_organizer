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
 * Class instantiates a Table Object associated with the lesson_groups table.
 */
class InstanceGroups extends Nullable
{
	/**
	 * The id of the instance persons entry referenced.
	 * INT(20) UNSIGNED NOT NULL
	 *
	 * @var int
	 */
	public $assocID;

	/**
	 * The textual description of the associations last change. Values: changed, <empty>, new, removed.
	 * VARCHAR(10) NOT NULL DEFAULT ''
	 *
	 * @var string
	 */
	public $delta;

	/**
	 * The id of the group entry referenced.
	 * INT(11) UNSIGNED NOT NULL
	 *
	 * @var int
	 */
	public $groupID;

	/**
	 * The primary key.
	 * INT(20) UNSIGNED NOT NULL AUTO_INCREMENT
	 *
	 * @var int
	 */
	public $id;

	/**
	 * The timestamp of the time at which the last change to the entry occurred.
	 * TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
	 *
	 * @var int
	 */
	public $modified;

	/**
	 * Declares the associated table
	 *
	 * @param   \JDatabaseDriver &$dbo  A database connector object
	 */
	public function __construct(&$dbo = null)
	{
		parent::__construct('#__thm_organizer_instance_groups', 'id', $dbo);
	}

	/**
	 * Set the table column names which are allowed to be null
	 *
	 * @return boolean  true
	 */
	public function check()
	{
		$this->modified = null;

		return true;
	}
}
