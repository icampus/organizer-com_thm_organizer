<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers;
use Organizer\Helpers\Courses as Helper;
use Organizer\Helpers\Languages as Languages;

/**
 * Class which loads data into the view output context
 */
class Courses extends ListView
{
	private $allowNew = false;

	private $params = null;

	/**
	 * Constructor
	 *
	 * @param   array  $config  A named configuration array for object construction.
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->params = Helpers\Input::getParams();

		if ($this->clientContext === self::BACKEND)
		{
			$this->rowStructure = [
				'checkbox' => '',
				'id'       => 'link',
				'name'     => 'link',
				'dates'    => 'link',
				'persons'  => 'link',
				'groups'   => 'link'
			];
		}
		else
		{

			$structure = ['name' => 'link', 'dates' => 'value', 'courseStatus' => 'value'];

			if (Factory::getUser()->id)
			{
				$structure ['participantStatus'] = 'value';
				$structure ['toolbar']           = 'value';
			}

			$this->rowStructure = $structure;
		}
	}

	/**
	 * Adds supplemental information to the display output.
	 *
	 * @return void modifies the object property supplement
	 */
	protected function addSupplement()
	{
		if (empty(Factory::getUser()->id))
		{
			$this->supplement =
				'<div class="tbox-yellow">' . Languages::_('THM_ORGANIZER_COURSE_LOGIN_WARNING') . '</div>';
		}
	}

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		$frontend     = $this->clientContext !== self::BACKEND;
		$resourceName = '';
		if ($frontend)
		{
			if (Helpers\Input::getBool('onlyPrepCourses', false))
			{
				$resourceName .= Languages::_('THM_ORGANIZER_PREP_COURSES');
				if ($campusID = $this->state->get('filter.campusID', 0))
				{
					$resourceName .= ' ' . Helpers\Campuses::getName($campusID);
				}
			}
		}

		Helpers\HTML::setMenuTitle('THM_ORGANIZER_COURSES', $resourceName, 'contract-2');

		if (Factory::getUser()->id)
		{
			$toolbar = Toolbar::getInstance();
			if ($frontend)
			{
				$toolbar->appendButton(
					'Standard',
					'edit',
					Languages::_('THM_ORGANIZER_EDIT_PROFILE'),
					'participants.edit',
					false
				);
			}

			if (Helper::coordinates())
			{
				$toolbar->appendButton('Standard', 'new', Languages::_('THM_ORGANIZER_ADD'), 'courses.add', false);
			}

			if (!$frontend)
			{
				$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT'), 'courses.edit', true);
				$toolbar->appendButton(
					'Confirm',
					Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
					'delete',
					Languages::_('THM_ORGANIZER_DELETE'),
					'courses.delete',
					true
				);
			}

		}
	}

	/**
	 * Function determines whether the user may access the view.
	 *
	 * @return bool true if the user may access the view, otherwise false
	 */
	protected function allowAccess()
	{
		if ($this->clientContext == self::FRONTEND)
		{
			return true;
		}

		return (Helpers\Can::administrate() or Helper::coordinates());
	}

	/**
	 * Function to set the object's headers property
	 *
	 * @return void sets the object headers property
	 */
	public function setHeaders()
	{
		$backend = $this->clientContext === self::BACKEND;

		if ($backend)
		{
			$headers = [
				'checkbox' => '',
				'id'       => '#',
				'name'     => Languages::_('THM_ORGANIZER_NAME'),
				'dates'    => Languages::_('THM_ORGANIZER_DATES'),
				'persons'  => Languages::_('THM_ORGANIZER_PERSONS'),
				'groups'   => Languages::_('THM_ORGANIZER_GROUPS')
			];
		}
		else
		{
			$headers = [
				'name'         => Languages::_('THM_ORGANIZER_NAME'),
				'dates'        => Languages::_('THM_ORGANIZER_DATES'),
				'courseStatus' => Languages::_('THM_ORGANIZER_COURSE_STATUS')
			];

			if (Factory::getUser()->id)
			{
				$headers ['participantStatus'] = '';
				$headers ['toolbar']           = '';
			}
		}

		$this->headers = $headers;
	}

	/**
	 * Processes the items in a manner specific to the view, so that a generalized  output in the layout can occur.
	 *
	 * @return void processes the class items property
	 */
	protected function structureItems()
	{
		$backend       = $this->clientContext === self::BACKEND;
		$URL           = Uri::base() . '?option=com_thm_organizer';
		$URL           .= $backend ? '&view=course_edit&id=' : '&view=course_item&id=';
		$participantID = Factory::getUser()->id;

		$this->allowNew  = Helper::coordinates();
		$structuredItems = [];

		foreach ($this->items as $course)
		{
			$courseID      = $course->id;
			$course->dates = Helper::getDateDisplay($courseID);
			$groups        = empty($course->groups) ? '' : ": {$course->groups}";
			$course->name  = Helper::getName($courseID) . $groups;
			$course->name  .= $backend ? '' : " ($courseID)";
			$index         = "{$course->name}{$course->dates}{$courseID}";

			if ($backend)
			{
				$course->persons = implode(', ', Helper::getPersons($courseID));
				$course->groups  = implode(', ', Helper::getGroups($courseID));
			}
			else
			{
				$course->courseStatus = Helper::getStatusText($courseID);

				if ($participantID)
				{
					$course->participantStatus = Helpers\CourseParticipants::getStatusText($courseID, $participantID);
					$course->toolbar           = Helpers\CourseParticipants::getToolbar($courseID, $participantID);
				}
			}

			$structuredItems[$index] = $this->structureItem($index, $course, $URL . $courseID);
		}

		ksort($structuredItems);

		$this->items = $structuredItems;
	}
}
