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

require_once 'assets.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class instantiates a \JTable Object associated with the departments table.
 */
class THM_OrganizerTableDepartments extends THM_OrganizerTableAssets
{
    /**
     * Declares the associated table
     *
     * @param \JDatabaseDriver &$dbo A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_departments', 'id', $dbo);
    }

    /**
     * Method to return the title to use for the asset table.  In tracking the assets a title is kept for each asset so
     * that there is some context available in a unified access manager.
     *
     * @return string  The string to use as the title in the asset table.
     */
    protected function _getAssetTitle()
    {
        $shortNameColumn = 'short_name_' . \Languages::getShortTag();

        return $this->$shortNameColumn;
    }

    /**
     * Sets the department asset name
     *
     * @return string
     */
    protected function _getAssetName()
    {
        $key = $this->_tbl_key;

        return 'com_thm_organizer.department.' . (int)$this->$key;
    }

    /**
     * Sets the parent as the component root
     *
     * @param \JTable $table the \JTable object
     * @param int     $id    the resource id
     *
     * @return int  the asset id of the component root
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getAssetParentId(\JTable $table = null, $id = null)
    {
        $asset = \JTable::getInstance('Asset');
        $asset->loadByName('com_thm_organizer');

        return $asset->id;
    }
}
