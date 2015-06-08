<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        Grid
 * @description Grid file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;

/**
 * Class Grid for component com_thm_organizer
 *
 * Class provides methods for the schedule grid
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 */
class THMGrid
{
    /**
     * Semester id
     *
     * @var    Integer
     */
    private $_semID = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_semID = JFactory::getApplication()->input->getString('semesterID');
    }

    /**
     * Method to load the grid data
     *
     * @return Array An array which includes the grid data
     */
    public function load()
    {
        if (isset( $this->_semID))
        {
            // Get a db connection.
            $dbo = JFactory::getDbo();
 
            // Create a new query object.
            $query = $dbo->getQuery(true);
 
            // Select all records from the user profile table where key begins with "custom.".
            // Order it by the ordering field.
            $query->select("gpuntisID AS tpid, day, period, starttime, endtime");
            $query->from('#__thm_organizer_periods');
            $query->order('CAST(SUBSTRING(tpid, 4) AS SIGNED INTEGER)');
 
            // Reset the query using our newly populated query object.
            $dbo->setQuery((string) $query);
 
            try 
            {
                // Load the results as a list of stdClass objects.
                $ret = $dbo->loadObjectList();
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
            }

            return array(
                         "success" => false,
                         "data" => ($ret !== false)?
                             $ret : JText::_('COM_THM_ORGANIZER_SCHEDULER_GRID_ERROR_LOADING')
                        );
        }
        else
        {
            return array(
                         "success" => false,
                         "data" => JText::_('COM_THM_ORGANIZER_SCHEDULER_GRID_ERROR_LOADING')
                        );
        }
    }
}
