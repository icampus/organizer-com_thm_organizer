<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\ICS;

use Joomla\CMS\Factory;
use Organizer\Helpers\Languages;
use Organizer\Views\BaseView;

require_once JPATH_ROOT . '/components/com_thm_organizer/icalcreator/iCalcreator.php';

/**
 * Class creates a ICS file for the display of the filtered schedule information.
 */
class ScheduleExport extends BaseView
{
	private $parameters;

	private $lessons;

	private $calendar;

	/**
	 * Sets context variables and renders the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function display($tpl = null)
	{
		$model                      = $this->getModel();
		$this->parameters           = $model->parameters;
		$this->parameters['mailto'] = empty($this->parameters['userID']) ?
			'' : Factory::getUser($this->parameters['userID'])->email;

		$this->createCalendar();

		$this->lessons = $model->lessons;
		$noLessons     = (array_key_exists('pastDate', $this->lessons)
			or array_key_exists('futureDate', $this->lessons));

		if (!$noLessons)
		{
			$this->addData();
		}

		$this->calendar->returnCalendar();
		ob_flush();
	}

	/**
	 * Method to create a ics schedule
	 *
	 * @return void sets the object variable calendar
	 */
	public function createCalendar()
	{
		$vCalendar = new \vcalendar;
		$vCalendar->setConfig('unique_id', $this->parameters['docTitle']);
		$vCalendar->setConfig('lang', Languages::getTag());
		$vCalendar->setProperty('x-wr-calname', $this->parameters['pageTitle']);
		$vCalendar->setProperty('X-WR-TIMEZONE', 'Europe/Berlin');
		$vCalendar->setProperty('METHOD', 'PUBLISH');

		$vTimeZone1 = new \vtimezone;
		$vTimeZone1->setProperty('TZID', 'Europe/Berlin');

		$vTimeZone2 = new \vtimezone('standard');
		$vTimeZone2->setProperty('DTSTART', 1601, 1, 1, 0, 0, 0);
		$vTimeZone2->setProperty('TZNAME', 'Standard Time');
		$vTimeZone2->setProperty('TZOFFSETFROM', '+0100');
		$vTimeZone2->setProperty('TZOFFSETTO', '+0200');

		$vTimeZone1->setComponent($vTimeZone2);
		$vCalendar->setComponent($vTimeZone1);

		$this->calendar = $vCalendar;
	}

	/**
	 * Adds events to the calendar
	 *
	 * @return void calls the function to add individual events to the calendar
	 */
	private function addData()
	{
		foreach ($this->lessons as $date => $timesIndexes)
		{
			foreach ($timesIndexes as $lessonInstances)
			{
				foreach ($lessonInstances as $lessonInstance)
				{
					$this->setEvent($date, $lessonInstance);
				}
			}
		}
	}

	/**
	 * Method to add an event to the calendar
	 *
	 * @param   string  $date            the lesson instance date
	 * @param   array   $lessonInstance  the lesson instance
	 *
	 * @return void sets object variables
	 */
	private function setEvent($date, $lessonInstance)
	{
		$vEvent = new \vevent;
		$vEvent->setProperty('TRANSP', 'OPAQUE');
		$vEvent->setProperty('SEQUENCE', '0');
		$vEvent->setProperty('PRIORITY', '5');

		$datePieces      = explode('-', $date);
		$startTimePieces = explode(':', $lessonInstance['startTime']);
		$endTimePieces   = explode(':', $lessonInstance['endTime']);

		$dtStart = [
			'year'  => $datePieces[0],
			'month' => $datePieces[1],
			'day'   => $datePieces[2],
			'hour'  => $startTimePieces[0],
			'min'   => $startTimePieces[1],
			'sec'   => $startTimePieces[2]
		];
		$vEvent->setProperty('DTSTART', $dtStart);

		$dtEnd = [
			'year'  => $datePieces[0],
			'month' => $datePieces[1],
			'day'   => $datePieces[2],
			'hour'  => $endTimePieces[0],
			'min'   => $endTimePieces[1],
			'sec'   => $endTimePieces[2]
		];
		$vEvent->setProperty('DTEND', $dtEnd);

		$subjectNames = array_keys($lessonInstance['subjects']);
		$subjectNos   = [];
		$persons      = [];
		$rooms        = [];
		foreach ($lessonInstance['subjects'] as $subjectConfiguration)
		{
			if (!empty($subjectConfiguration['subjectNo']))
			{
				$subjectNos[$subjectConfiguration['subjectNo']] = $subjectConfiguration['subjectNo'];
			}

			$persons = $persons + $subjectConfiguration['persons'];
			$rooms   = $rooms + $subjectConfiguration['rooms'];
		}

		$comment = empty($lessonInstance['comment']) ? '' : $lessonInstance['comment'];
		$vEvent->setProperty('DESCRIPTION', $comment);

		$title = implode('/', $subjectNames);
		$title .= empty($lessonInstance['method']) ? '' : " - {$lessonInstance['method']}";
		$title .= empty($subjectNos) ? '' : ' (' . implode('/', $subjectNos) . ')';

		$personsText = implode('/', $persons);
		$roomsText   = implode('/', $rooms);

		$summary = sprintf(Languages::_('ORGANIZER_ICS_SUMMARY'), $title, $personsText);

		$organizer = empty($this->parameters['mailto']) ? $personsText : $this->parameters['mailto'];
		$vEvent->setProperty('ORGANIZER', $organizer);
		$vEvent->setProperty('LOCATION', $roomsText);
		$vEvent->setProperty('SUMMARY', $summary);
		$this->calendar->setComponent($vEvent);
	}
}
