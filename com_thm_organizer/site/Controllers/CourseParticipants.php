<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Controllers;


use Exception;
use Joomla\CMS\Router\Route;
use Organizer\Helpers;
use Organizer\Models\CourseParticipant;

trait CourseParticipants
{
	/**
	 * Accepts the selected participants into the course.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function accept()
	{
		$model = new CourseParticipant;

		if ($model->accept())
		{
			Helpers\OrganizerHelper::message('ORGANIZER_STATUS_CHANGE_SUCCESS', 'success');
		}
		else
		{
			Helpers\OrganizerHelper::message('ORGANIZER_STATUS_CHANGE_FAIL', 'error');
		}

		$this->setRedirect(Helpers\Input::getInput()->server->getString('HTTP_REFERER'));
	}

	/**
	 * Sends an circular email to all course participants
	 *
	 * @return void
	 * @throws Exception
	 */
	public function circular()
	{
		if (empty($this->getModel('course')->circular()))
		{
			Helpers\OrganizerHelper::message('ORGANIZER_SEND_FAIL', 'error');
		}
		else
		{
			Helpers\OrganizerHelper::message('ORGANIZER_SEND_SUCCESS', 'error');
		}

		$lessonID = $this->input->get('lessonID');
		$redirect = Helpers\Routing::getRedirectBase() . "view=courses&lessonID=$lessonID";
		$this->setRedirect(Route::_($redirect, false));
	}

	/**
	 * Changes the participant's course state.
	 *
	 * @return void
	 */
	public function changeState()
	{
		$model = new CourseParticipant;

		if ($model->changeState())
		{
			Helpers\OrganizerHelper::message('ORGANIZER_STATUS_CHANGE_SUCCESS');
		}
		else
		{
			Helpers\OrganizerHelper::message('ORGANIZER_STATUS_CHANGE_FAIL', 'error');
		}

		$this->setRedirect(Helpers\Input::getInput()->server->getString('HTTP_REFERER'));
	}

	/**
	 * Accepts the selected participants into the course.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function confirmAttendance()
	{
		$model = new CourseParticipant;

		if ($model->confirmAttendance())
		{
			Helpers\OrganizerHelper::message('ORGANIZER_STATUS_CHANGE_SUCCESS', 'success');
		}
		else
		{
			Helpers\OrganizerHelper::message('ORGANIZER_STATUS_CHANGE_FAIL', 'error');
		}

		$this->setRedirect(Helpers\Input::getInput()->server->getString('HTTP_REFERER'));
	}

	/**
	 * Accepts the selected participants into the course.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function confirmPayment()
	{
		$model = new CourseParticipant;

		if ($model->confirmPayment())
		{
			Helpers\OrganizerHelper::message('ORGANIZER_STATUS_CHANGE_SUCCESS', 'success');
		}
		else
		{
			Helpers\OrganizerHelper::message('ORGANIZER_STATUS_CHANGE_FAIL', 'error');
		}

		$this->setRedirect(Helpers\Input::getInput()->server->getString('HTTP_REFERER'));
	}

	/**
	 * Prints badges for the selected participants.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function printBadges()
	{
		// Reliance on POST requires a different method of redirection
		$this->input->set('format', "pdf");
		$this->input->set('view', "badges");
		parent::display();
	}

	/**
	 * De-/registers a participant from/to a course.
	 *
	 * @return void
	 */
	public function register()
	{
		$participantID = Helpers\Input::getInt('participantID');

		if (!Helpers\Participants::canRegister($participantID))
		{
			Helpers\OrganizerHelper::message('ORGANIZER_PARTICIPANT_REGISTRATION_INCOMPLETE', 'error');
			$this->setRedirect(Helpers\Input::getInput()->server->getString('HTTP_REFERER'));
		}

		$courseID      = Helpers\Input::getInt('participantID');
		$eventID       = Helpers\Input::getInt('eventID');
		$model         = new CourseParticipant;
		$previousState = Helpers\CourseParticipants::getState($courseID, $participantID, $eventID);

		if ($model->register())
		{
			if ($previousState !== self::UNREGISTERED)
			{
				Helpers\OrganizerHelper::message('ORGANIZER_DEREGISTER_SUCCESS', 'success');
			}
			else
			{
				$currentState = Helpers\CourseParticipants::getState($courseID, $participantID, $eventID);

				$msg = $currentState ? 'ORGANIZER_REGISTRATION_ACCEPTED' : 'ORGANIZER_REGISTRATION_WAIT';
				Helpers\OrganizerHelper::message($msg);
			}
		}
		else
		{
			Helpers\OrganizerHelper::message('ORGANIZER_STATUS_CHANGE_FAIL', 'error');
		}

		$this->setRedirect(Helpers\Input::getInput()->server->getString('HTTP_REFERER'));
	}

	/**
	 * Accepts the selected participants into the course.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function remove()
	{
		$model = new CourseParticipant;

		if ($model->remove())
		{
			Helpers\OrganizerHelper::message('ORGANIZER_REMOVE_SUCCESS', 'success');
		}
		else
		{
			Helpers\OrganizerHelper::message('ORGANIZER_REMOVE_FAIL', 'error');
		}

		$this->setRedirect(Helpers\Input::getInput()->server->getString('HTTP_REFERER'));
	}

	/**
	 * Toggles binary resource properties from a list view.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function toggle()
	{
		$model = new CourseParticipant;

		if ($model->toggle())
		{
			Helpers\OrganizerHelper::message('ORGANIZER_TOGGLE_SUCCESS', 'success');
		}
		else
		{
			Helpers\OrganizerHelper::message('ORGANIZER_TOGGLE_FAIL', 'error');
		}

		$this->setRedirect(Helpers\Input::getInput()->server->getString('HTTP_REFERER'));
	}
}