<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerControllerRoom_Type
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class performs access checks, redirects and model function calls for data persistence
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerControllerRoom_Type extends JControllerLegacy
{
    /**
     * Performs access checks and redirects to the color edit view
     *
     * @return void
     */
    public function add()
    {
        $this->setRedirect("index.php?option=com_thm_organizer&view=room_type_edit");
    }

    /**
     * Performs access checks and redirects to the color edit view
     *
     * @return  void
     */
    public function edit()
    {
        $cid = $this->input->post->get('cid', array(), 'array');

        // Only edit the first id in the list
        if (count($cid) > 0)
        {
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=room_type_edit&id=$cid[0]", false));
        }
        else
        {
            $this->setRedirect("index.php?option=com_thm_organizer&view=room_type_edit");
        }
    }

    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the room_type manager view
     *
     * @return  void
     */
    public function apply()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return;
        }

        $typeID = $this->getModel('room_type')->save();
        if (!empty($typeID))
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=room_type_edit&id=$typeID", false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_type_edit&id=0', false), $msg, 'error');
        }
    }


    /**
     * Performs access checks, makes call to the models's save function, and
     * redirects to the room_type manager view
     *
     * @return  void
     */
    public function save()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return;
        }

        $success = $this->getModel('room_type')->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_type_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_type_manager', false), $msg, 'error');
        }
    }

    /**
     * Performs access checks, makes call to the models's delete function, and
     * redirects to the room_type manager view
     *
     * @return  void
     */
    public function delete()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return;
        }

        $success = $this->getModel('room_type')->delete();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_type_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_DELETE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_type_manager', false), $msg, 'error');
        }
    }

    /**
     * Method to cancel an edit.
     *
     * @return  void
     */
    public function cancel()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return;
        }

        $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_type_manager', false));
    }

    /**
     * Redirects to the room type merge view.
     *
     * @return  void
     */
    public function mergeView()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return;
        }

        $input = JFactory::getApplication()->input;
        $selectedRoomTypes = $input->get('cid', array(), 'array');
        if (count($selectedRoomTypes) == 1)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_ERROR_TOOFEW');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_type_manager', false), $msg, 'warning');
        }
        else
        {
            $input->set('view', 'room_type_merge');
            parent::display();
        }
    }

    /**
     * Performs access checks, makes call to the models's merge function, and
     * redirects to the room manager view
     *
     * @return  void
     */
    public function merge()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return;
        }

        $success = $this->getModel('room_type')->merge();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_MERGE_SUCCESS');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_type_manager', false), $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_MERGE_FAIL');
            $this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=room_type_manager', false), $msg, 'error');
        }
    }
}
