<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelEvent
 * @description create/edit appointment/event model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_COMPONENT . "/assets/classes/eventAccess.php";

/**
 * Retrieves stored event data
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelEvent_Details extends JModelLegacy
{
    /**
     * @var int the id of the event in the database
     */
    public $eventID = 0;

    /**
     * @var array of event properties
     */
    public $event = null;

    /**
     * @var string containing the url of the event list menu item from which
     * the user came to this view (if the user came from the event list view)
     */
    public $listLink = "";

    /**
     * @var boolean true if the user is allowed to create events, otherwise false
     */
    public $canWrite = false;

    /**
     * construct
     *
     * calls class functions to load object variables with data
     */
    public function __construct()
    {
        parent::__construct();
        $this->loadEvent();
        if ($this->event['id'] != 0)
        {
            $this->loadEventResources();
            $this->setMenuLinks();
        }
        $this->canWrite = THMEventAccess::canCreate();
    }

    /**
     * loadEvent
     *
     * creates an event as an array of properties and sets this as an object
     * variable
     *
     * @return void
     */
    public function loadEvent()
    {
        $app = JFactory::getApplication();
        $eventID = $app->input->getInt('eventID', 0);
        if (empty($eventID))
        {
            return;
        }

        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select($this->getSelect());        
        $query->from("#__thm_organizer_events AS e");
        $query->innerJoin("#__content AS c ON e.id = c.id");
        $query->innerJoin("#__users AS u ON c.created_by = u.id");
        $query->innerJoin("#__thm_organizer_categories AS ecat ON e.categoryID = ecat.id");
        $query->innerJoin("#__categories AS ccat ON ecat.contentCatID = ccat.id");
        $query->where("e.id = '$eventID'");
        $dbo->setQuery((string) $query);
        
        try
        {
            $event = $dbo->loadAssoc();
        }        
        catch (Exception $exc)
        {
            $app->enqueueMessage($exc->getMessage(), 'error');
            return;
        }

        if (empty($event))
        {
            return;
        }

        THM_OrganizerHelperEvent::localizeEvent($event);
        $event['publish_up'] = date_format(date_create($event['publish_up']), 'd.m.Y');

        $this->eventID = $event['id'];
        if ($event['id'] != 0)
        {
            $event['access'] = THMEventAccess::canEdit($this->event['id']);
        }
        $this->event = $event;
    }

    /**
     * getSelect
     *
     * creates the select clause for the event properties
     *
     * @return string select clause
     */
    private function getSelect()
    {
        $select = "e.id AS id, ";
        $select .= "e.categoryID AS eventCategoryID, ";
        $select .= "e.startdate AS startdate, ";
        $select .= "e.enddate AS enddate, ";
        $select .= "e.starttime AS starttime, ";
        $select .= "e.endtime AS endtime, ";
        $select .= "e.recurrence_type AS rec_type, ";
        $select .= "ecat.title AS eventCategory, ";
        $select .= "ecat.description AS eventCategoryDesc, ";
        $select .= "ecat.global, ";
        $select .= "ecat.reserves, ";
        $select .= "c.title AS title, ";
        $select .= "c.fulltext AS description, ";
        $select .= "c.publish_up AS publish_up, ";
        $select .= "c.publish_down AS publish_down, ";
        $select .= "c.access AS contentAccess, ";
        $select .= "ccat.id AS categoryID, ";
        $select .= "ccat.title AS contentCategory, ";
        $select .= "ccat.description AS contentCategoryDesc, ";
        $select .= "ccat.access AS contentCategoryAccess, ";
        $select .= "u.name AS author, ";
        $select .= "u.id AS authorID ";
        return $select;
    }

    /**
     * loadEventResources
     *
     * calls functions for loading differing sorts of event resources
     *
     * @return void
     */
    private function loadEventResources()
    {
        $this->loadEventRooms();
        $this->loadEventTeachers();
        $this->loadEventGroups();
    }

    /**
     * loadEventRooms
     *
     * loads room data into the event
     *
     * @return void
     */
    private function loadEventRooms()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("id");
        $query->from("#__thm_organizer_event_rooms AS er");
        $query->innerJoin("#__thm_organizer_rooms AS r ON er.roomID = r.id");
        $query->where("er.eventID = '$this->eventID'");
        $dbo->setQuery((string) $query);
        
        try
        {
            $this->event['rooms'] = $dbo->loadColumn();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
    }

    /**
     * loadEventTeachers
     *
     * loads teacher data into the event
     *
     * @return void
     */
    private function loadEventTeachers()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("id");
        $query->from("#__thm_organizer_event_teachers AS et");
        $query->innerJoin("#__thm_organizer_teachers AS t ON et.teacherID = t.id");
        $query->where("et.eventID = '$this->eventID'");
        $dbo->setQuery((string) $query);
        
        try
        {
            $this->event['teachers'] = $dbo->loadColumn();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
    }

    /**
     * loadEventGroups
     *
     * loads group data into the event
     *
     * @return void
     */
    private function loadEventGroups()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("id");
        $query->from("#__thm_organizer_event_groups AS eg");
        $query->innerJoin("#__usergroups AS ug ON eg.groupID = ug.id");
        $query->where("eg.eventID = '$this->eventID'");
        $dbo->setQuery((string) $query);
        
        try 
        {
            $this->event['groups'] = $dbo->loadColumn();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
    }

    /**
     * funtion setMenuLink
     *
     * retrieves the url of the event list menu item and sets the object
     * variable listLink with it
     *
     * @return void
     */
    private function setMenuLinks()
    {
        $menuID = JFactory::getApplication()->input->getInt('Itemid');
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("link");
        $query->from("#__menu AS eg");
        $query->where("id = $menuID");
        $query->where("link LIKE '%event_manager%'");
        $dbo->setQuery((string) $query);
        
        try
        {
            $link = $dbo->loadResult();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        if (isset($link) and $link != "")
        {
            $this->listLink = JRoute::_($link);
        }
    }
}
