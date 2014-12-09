<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerTableteachers
 * @description teachers table class
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.table');
/**
 * Class representing the teachers table.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerTableteachers extends JTable
{
    /**
     * Constructor function for the class representing the teachers table
     *
     * @param   JDatabaseDriver  &$dbo  A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_teachers', 'id', $dbo);
    }

    /**
     * Method to store a row in the database from the JTable instance properties.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     */
    public function store($updateNulls = true)
    {
        return parent::store(true);
    }

    /**
     * Set the table column names which are allowed to be null
     *
     * @return  boolean  true
     */
    public function check()
    {
        $nullColumns = array('fieldID');
        foreach ($nullColumns as $nullColumn)
        {
            if (!strlen($this->$nullColumn))
            {
                $this->$nullColumn = NULL;
            }
        }
        return true;
    }
}
