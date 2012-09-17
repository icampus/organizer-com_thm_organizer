<?php
/**
 * @version     v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin.controller
 * @name        THM_OrganizersControllervirtual_schedule
 * @description perform tasks that affects virtual schedules
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.controller');

/**
 * Class THM_OrganizersControllervirtual_schedule for component com_thm_organizer
 *
 * Class provides methods to handle tasks that affects virtual schedules
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin.controller
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class THM_OrganizersControllerVirtual_Schedule extends JController
{
	/**
	 * Constructor that register tasks and call the parent constructor
	 *
	 * @since   v0.0.1
	 *
	 */
	public function __construct()
	{
		//parent::__construct();
		$this->registerTask('add', 'edit');
		$this->registerTask('deleteList', '');
	}

	/**
	 * Method to display the dit form
	 *
	 * @return void
	 */
	public function edit()
	{
		JRequest::setVar('view', 'virtual_schedule_edit');
		parent::display();
	}

	/**
	 * Method to remove a virtual schedule
	 *
	 * @return void
	 */
	public function remove()
	{
		$dbo = & JFactory::getDBO();
		$cid = JRequest::getVar('cid',   array(), 'post', 'array');
		$cids = implode(',', $cid);
		$cids_temp = $cids;
		$cids_temp = str_replace(',', ', ', $cids_temp);
		$cids = str_replace(',', '","', $cids);
		$cids = '"' . $cids . '"';

		$query = 'DELETE FROM #__thm_organizer_virtual_schedules'
		. ' WHERE vid IN (' . $cids . ');';

		$dbo->setQuery($query);
		$dbo->query();

		if ($dbo->getErrorNum())
		{
			$msg = JText::_('Fehler beim Löschen.');
		}
		else
		{
			$query = 'DELETE FROM #__thm_organizer_virtual_schedules_elements'
			. ' WHERE vid IN ( ' . $cids . ' );';

			$dbo->setQuery($query);
			$dbo->query();
		}

		if (count($cid) > 1)
		{
			$msg = JText::_('Virtuelle Stundenpläne ' . $cids_temp . ' gelöscht.');
		}
		else
		{
			$msg = JText::_('Virtuellen Stundenplan ' . $cids_temp . ' gelöscht.');
		}

		$this->setRedirect('index.php?option=com_thm_organizer&view=virtual_schedule_manager', $msg);

	}

	/**
	 * Method to save a virtual schedule
	 *
	 * @return void
	 */
	public function save()
	{
		$model = $this->getModel('virtual_schedule_edit');
		 
		$data = JRequest::getVar('jform', null, null, null);

		$vscheduler_id = $data["id"];
		$vscheduler_vid = $data["vid"];
		$vscheduler_name = $data["name"];
		$vscheduler_types = $data["type"];

		if ($vscheduler_name == null)
		{
			$this->setRedirect('index.php?option=com_thm_organizer&view=virtual_schedule_edit', JText::_('Der Name darf nicht leer sein.'), 'error');
			$session =& JFactory::getSession();
			$session->set('oldPost', $_POST);
			return;
		}
		$vscheduler_semid = $data["semester"];
		$vscheduler_resps = $data["responsible"];
		$vscheduler_classesDepartments = $data["ClassDepartment"];
		$vscheduler_teacherDepartments = $data["TeacherDepartment"];
		$vscheduler_roomDepartments = $data["RoomDepartment"];
		 
		$vscheduler_classes = null;
		$vscheduler_rooms = null;
		$vscheduler_teachers = null;
		 
		if ($vscheduler_types == "room")
		{
			$vscheduler_rooms = $data["Rooms"];
		}
		if ($vscheduler_types == "class")
		{
			$vscheduler_classes = $data["Classes"];
		}
		if ($vscheduler_types == "teacher")
		{
			$vscheduler_teachers = $data["Teachers"];
		}

		if (!isset($vscheduler_name)
		 || !isset($vscheduler_types)
		 || !isset($vscheduler_semid)
		 || !isset($vscheduler_resps)
		 || !isset($vscheduler_classesDepartments)
		 || !isset($vscheduler_teacherDepartments)
		 || !isset($vscheduler_roomDepartments)
		 || (!isset($vscheduler_classes) && !isset($vscheduler_rooms) && !isset($vscheduler_teachers)))
		{
			$msg = "Folgende Felder haben ungültige Werte:<br/>";
			if (!isset($vscheduler_name))
			{
				$msg .= "vscheduler_name<br/>";
			}
			if (!isset($vscheduler_types))
			{
				$msg .= "vscheduler_types<br/>";
			}
			if (!isset($vscheduler_semid))
			{
				$msg .= "vscheduler_semid<br/>";
			}
			if (!isset($vscheduler_resps))
			{
				$msg .= "vscheduler_resps<br/>";
			}
			if (!isset($vscheduler_classesDepartments))
			{
				$msg .= "vscheduler_classesDepartments<br/>";
			}
			if (!isset($vscheduler_teacherDepartments))
			{
				$msg .= "vscheduler_teacherDepartments<br/>";
			}
			if (!isset($vscheduler_roomDepartments))
			{
				$msg .= "vscheduler_roomDepartments<br/>";
			}
			if (!isset($vscheduler_classes) && $vscheduler_types == "class")
			{
				$msg .= "vscheduler_classes<br/>";
			}
			if (!isset($vscheduler_rooms) && $vscheduler_types == "room")
			{
				$msg .= "vscheduler_rooms<br/>";
			}
			if (!isset($vscheduler_teachers) && $vscheduler_types == "teacher")
			{
				$msg .= "vscheduler_teachers<br/>";
			}

			$this->setRedirect('index.php?option=com_thm_organizer&view=virtual_schedule_edit', JText::_($msg), 'error');
			$session =& JFactory::getSession();
			$session->set('oldPost', $_POST);
			return;
		}
		else
		{
			// Alles Felder haben gültige Werte
			$torf = false;

			if ($vscheduler_types == "room")
			{
				$vscheduler_Departments = $vscheduler_roomDepartments;
				$vscheduler_elements = $vscheduler_rooms;
			}
			if ($vscheduler_types == "class")
			{
				$vscheduler_Departments = $vscheduler_classesDepartments;
				$vscheduler_elements = $vscheduler_classes;
			}
			if ($vscheduler_types == "teacher")
			{
				$vscheduler_Departments = $vscheduler_teacherDepartments;
				$vscheduler_elements = $vscheduler_teachers;
			}

			$torf = $model->save(
					$vscheduler_id,
					$vscheduler_vid,
					$vscheduler_name,
					$vscheduler_types,
					$vscheduler_semid,
					$vscheduler_resps,
					$vscheduler_Departments,
					$vscheduler_elements
					);

			if ($torf === true)
			{
				if ($vscheduler_id == null)
				{
					$this->setRedirect('index.php?option=com_thm_organizer&view=virtual_schedule_manager',
							JText::_('Virtuellen Stundenplan ' . $vscheduler_name . ' erfolgreich angelegt.')
					);
				}
				else
				{
					$this->setRedirect('index.php?option=com_thm_organizer&view=virtual_schedule_manager',
							JText::_('Virtuellen Stundenplan ' . $vscheduler_id . ' erfolgreich bearbeitet.')
					);
				}
				return;
			}
			else
			{
				$this->setRedirect('index.php?option=com_thm_organizer&view=virtual_schedule_edit', JText::_("Error: " . $torf), 'error');
				$session =& JFactory::getSession();
				$session->set('oldPost', $_POST);
				return;
			}
		}
	}

	/**
	 * Method to cancel the current action
	 *
	 * @return void
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_thm_organizer&view=virtual_schedule_manager');
	}
}
