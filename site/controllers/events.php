<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        create/edit appointment/event controller
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');

class thm_organizerControllerevents extends JController
{
    /**
     * edit
     * 
     * reroutes to the event_edit view if access is allowed
     * 
     * @access public
     */
    public function edit()
    {
        $eventID = JRequest::getInt('eventID', 0);
        $itemID = JRequest::getInt('Itemid');
        $dbo = JFactory::getDbo();
        $user = JFactory::getUser();
        $access = false;

        if($eventID)
        {
            $query = $dbo->getQuery(true);
            $query->select("created_by AS author");
            $query->from("#__content");
            $query->where("id = '$eventID'");
            $dbo->setQuery((string)$query);
            $author = $dbo->loadResult();
            $isAuthor = ($user->id == $author)? true : false;

            $assetname = "com_content.article.$eventID";
            if($isAuthor) $canEditOwn = $user->authorise('edit.own', $assetname);
            $canEdit = $user->authorise('edit', $assetname);
            $access = $canEdit or $canEditOwn;
        }
        else
        {
            $query = $dbo->getQuery(true);
            $query->select("ccat.id");
            $query->from('#__thm_organizer_categories AS ecat');
            $query->innerJoin('#__categories AS ccat');
            $dbo->setQuery((string)$query);
            $associatedCategories = $dbo->loadResultArray();
            if(isset($associatedCategoryIDs) and count($associatedCategoryIDs))
            {
                foreach($associatedCategoryIDs as $associatedCategoryID)
                {
                    $assetname = "com_content.category.$associatedCategoryID";
                    $canWrite = $user->authorise('core.create', $assetname);
                    if($canWrite)
                    {
                        $access = true;
                        break;
                    }
                }
            }
        }

        if($access)
        {
            $url = "index.php?option=com_thm_organizer&view=event_edit";
            $url .= ($eventID)? "&eventID=$eventID" : "";
            $url .= (isset($itemID))? "&Itemid=$itemID" : "";
            $link = JRoute::_($url, false);
            $this->setRedirect($link);
        }
        else
        {
            JError::raiseError( 777, JText::_('COM_THM_ORGANIZER_ERROR_NOAUTH') );
            return;
        }
    }

    /**
    * Saves the event
    *
    */
    function save()
    {
        $dbo = JFactory::getDBO();
        $eventID = JRequest::getInt('eventID', 0);
        $categoryID = JRequest::getInt('category');
        $user = JFactory::getUser();
        $userID = $user->id;

        $query = $dbo->getQuery(true);
        if($eventID == 0)
        {
            $query->select('contentCatID');
            $query->from('#__thm_organizer_categories');
            $query->where("id = '$categoryID'");
            $dbo->setQuery((string)$query);
            $contentCatID = $dbo->loadResult();
        }
        else
        {
            $query->select('created_by, catid');
            $query->from('#__content');
            $query->where("id = '$eventID'");
            $dbo->setQuery((string)$query);
            $contentInfo = $dbo->loadAssoc();
            $contentCatID = $contentInfo['catid'];
            $isAuthor = ($contentInfo['created_by'] == $userID)? true : false;
        }

        $actionPermissions = array();
        $assetName = "com_content.category.$contentCatID";
        $actions = array( 'core.create', 'core.edit', 'core.edit.own' );
        foreach($actions as $action) $actionPermissions[$action] = $user->authorise($action, $assetName);

        $canSave = false;
        if($eventID == 0) $canSave = $actionPermissions['core.create'];
        else if(($isAuthor and $actionPermissions['core.edit.own']) or $actionPermissions['core.edit'])
            $canSave = true;
            
        $menuID = JRequest::getVar('Itemid');
        if($canSave)
        {
            $schedulerCall = JRequest::getVar('schedulerCall');
            $model = $this->getModel('event_edit');
            $eventID = $model->save();
            if($eventID)
            {
                $msg = JText::_( 'COM_THM_ORGANIZER_EE_SAVED' );
                if($schedulerCall)
                    $link = JRoute::_('index.php?option=com_thm_organizer&view=event_edit&eventID=0&tmpl=component', false);
                else $link = JRoute::_("index.php?option=com_thm_organizer&view=event&eventID=$eventID&Itemid=$menuID", false);
                $this->setRedirect($link, $msg);
            }
            else
            {
                $msg = JText::_( 'COM_THM_ORGANIZER_EE_SAVE_FAILED' );
                if($schedulerCall) $link = JRoute::_('index.php?option=com_thm_organizer&view=event_edit&eventID=0&tmpl=component', false);
                else $link = JRoute::_("index.php?option=com_thm_organizer&view=event_edit&Itemid=$menuID", false);
                $this->setRedirect($link, $msg, 'error');
            }
        }
        else
        {
            JError::raiseError( 777, JText::_('COM_THM_ORGANIZER_ERROR_NOAUTH') );
            return;
        }

    }

    /**
     * Deletes the event everything created with it
     *
     */
    function delete()
    {
        $itemID = JRequest::getInt('Itemid');
        // Check for request forgeries
        //how does this work?
        //JRequest::checkToken() or jexit( 'Invalid Token' );

        // Initialize variables
        /*$dbo	=& JFactory::getDBO();
        $user =& JFactory::getUser();
        $userid = $user->get('id');
        $gid = $user->get('gid');
        $eventid = JRequest::getVar('eventid');
        $itemid = JRequest::getVar('Itemid');
		
		//get author
    	$query = "SELECT created_by FROM #__thm_organizer_events WHERE eid = $eventid";
        $dbo->setQuery($query);
        $author = $dbo->loadResult();

        // Make sure user has the necessary access rights
        if ($gid < 19 || ($gid < 21 && $author != $userid))
        {
            JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
            return;
        }

        //get data from the request
        $model = $this->getModel('event_edit');
        if ($model->delete($eventid)) $msg = JText::_( 'Ereignis gel&ouml;scht' );
        else $msg = JText::_( 'Ein Fehler ist beim L&ouml;schen des Ereignisses aufgetretten.' );*/
        $msg = "the delete function isnt implemented yet";
        $eventlist = JRoute::_("index.php?option=com_thm_organizer&view=eventlist&Itemid=$itemid", false);
    	$this->setRedirect($eventlist, $msg);	
    }
}