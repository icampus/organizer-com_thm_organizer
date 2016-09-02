<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelXML_Schedule
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class enapsulating data abstraction and business logic for json schedules.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelJSONSchedule extends JModelLegacy
{
	/**
	 * Object containing information from the actual schedule
	 *
	 * @var object
	 */
	private $schedule = null;

	/**
	 * Object containing information from a comparison schedule
	 *
	 * @var object
	 */
	private $compSchedule = null;

	/**
	 * Removes all existing lessons for the given department/planning period. This implicitly removes associated lesson
	 * subjects, lesson teachers, lesson pools, configurations and calendar entries.
	 *
	 * @return void removes entries from the database
	 */
	private function deleteLessons()
	{
		$query = $this->_db->getQuery(true);
		$query->delete('#__thm_organizer_lessons')
			->where("departmentID = '{$this->schedule->departmentID}'")
			->where("planningPeriodID = '{$this->schedule->planningPeriodID}'");
		$this->_db->setQuery($query);
		$this->_db->execute();
	}

	/**
	 * Retrieves the configurations associated with the lesson instance
	 *
	 * @param int $lessonID the id of the lesson in the database
	 * @param int $lessonGPUntisID the id of the lesson in the json schedule
	 * @param array $calendarEntry the the calendar entry being currently iterated
	 * @param array $lessonSubjects an array containing the plan subject id (subjectID) and lesson subject id (id), indexed by the plan subject id
	 *
	 * @return array
	 */
	private function getInstanceConfigurations($lessonID, $calendarEntry, $lessonSubjects)
	{
		$date = $calendarEntry['schedule_date'];
		$startTime = date('Hi', strtotime($calendarEntry['start_time']));
		$endTime = date('Hi', strtotime($calendarEntry['end_time']));
		$timeKey = $startTime . '-' . $endTime;
		$configIndexes = $this->schedule->calendar->$date->$timeKey->$lessonID->configurations;
		$configurations = array();

		foreach ($configIndexes as $configIndex)
		{
			/**
			 * lessonID => the untis lesson id
			 * subjectID => the db / plan subject id
			 * teachers & rooms => the teachers and rooms for this configuration
			 */
			$rawConfig = $this->schedule->configurations[$configIndex];
			$configuration = json_decode($rawConfig);

			$configData    = array('lessonID' => $lessonSubjects[$configuration->subjectID]['id'], 'configuration' => $rawConfig);
			$configsTable = JTable::getInstance('lesson_configurations', 'thm_organizerTable');
			$exists = $configsTable->load($configData);

			if ($exists)
			{
				$configurations[] = $configsTable->id;
			}
		}

		return $configurations;
	}

	/**
	 * Maps configurations to calendar entries
	 *
	 * @void creates database entries
	 */
	private function mapConfigurations()
	{
		foreach ($this->schedule->lessons as $lessonGPUntisID => $lesson)
		{
			$lessonsData                     = array();
			$lessonsData['gpuntisID']        = $lessonGPUntisID;
			$lessonsData['departmentID']     = $this->schedule->departmentID;
			$lessonsData['planningPeriodID'] = $this->schedule->planningPeriodID;

			$lessonsTable = JTable::getInstance('lessons', 'thm_organizerTable');
			$lessonExists = $lessonsTable->load($lessonsData);

			// Should not occur
			if (!$lessonExists)
			{
				continue;
			}

			$lessonID = $lessonsTable->id;

			// Get the calendar entries which reference the lesson
			$calendarQuery = $this->_db->getQuery(true);
			$calendarQuery->select('id, schedule_date, start_time, end_time')
				->from('#__thm_organizer_calendar')
				->where("lessonID = '$lessonID'");
			$this->_db->setQuery($calendarQuery);
			$calendarEntries = $this->_db->loadAssocList('id');

			// Should not occur
			if (empty($calendarEntries))
			{
				continue;
			}

			$lessonSubjectsQuery = $this->_db->getQuery(true);
			$lessonSubjectsQuery->select('id, subjectID')->from('#__thm_organizer_lesson_subjects')->where("lessonID = '$lessonID'");
			$this->_db->setQuery($lessonSubjectsQuery);
			$lessonSubjects = $this->_db->loadAssocList('subjectID');

			// Should not occur
			if (empty($lessonSubjects))
			{
				continue;
			}

			foreach($calendarEntries as $calendarID => $calendarEntry)
			{
				$instanceConfigs = $this->getInstanceConfigurations($lessonGPUntisID, $calendarEntry, $lessonSubjects);
				foreach ($instanceConfigs as $configID)
				{
					$mapData = array('calendarID' => $calendarID, 'configurationID' => $configID);
					$mapTable = JTable::getInstance('calendar_configurations_map', 'thm_organizerTable');
					try{
						$mapTable->load($mapData);
						$mapTable->save($mapData);
					}
					catch (Exception $exc)
					{
						JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
					}
				}
			}
		}
	}

	/**
	 * Resolves the subject ids to their module numbers, if available
	 *
	 * @param object $subjects the lesson subjects
	 *
	 * @return array the id => delta mapping of the deprecated format lesson, empty if resolution failed
	 */
	private function mapSubjectNos($subjects)
	{
		$return = array();
		if (empty($subjects))
		{
			return $return;
		}

		foreach ($subjects as $gpuntisID => $value)
		{
			$subjectID = $this->compSchedule->subjects->$gpuntisID->id;
			if (empty($subjectID))
			{
				continue;
			}

			$return[$subjectID] = empty($this->compSchedule->subjects->$gpuntisID->subjectNo) ?
				'' : $this->compSchedule->subjects->$gpuntisID->subjectNo;
		}

		return $return;
	}

	/**
	 * Migrates old format json schedules to new format json schedules
	 *
	 * @param int $scheduleID the id of the schedule to be migrated
	 *
	 * @return bool
	 */
	public function migrate($scheduleID)
	{
		$scheduleRow = JTable::getInstance('schedules', 'thm_organizerTable');
		$loaded      = $scheduleRow->load($scheduleID);

		if (!$loaded)
		{
			return false;
		}

		$this->compSchedule = json_decode($scheduleRow->schedule);
		$this->schedule     = new stdClass;

		// Common information
		$this->schedule->departmentID     = $scheduleRow->departmentID;
		$this->schedule->planningPeriodID = $scheduleRow->planningPeriodID;
		$this->schedule->creationDate     = $this->compSchedule->creationdate;
		$this->schedule->creationTime     = $this->compSchedule->creationtime;

		$this->schedule->programs = array();
		$this->migratePrograms();

		$this->schedule->pools = array();
		$this->migratePools();

		$this->schedule->rooms = array();
		$this->migrateRooms();

		$this->schedule->subjects = array();
		$this->migrateSubjects();

		$this->schedule->teachers = array();
		$this->migrateTeachers();


		// Make this attribute accessible during the lesson/calendar migration process
		$this->schedule->active = $scheduleRow->active;

		$this->schedule->calendar       = new stdClass;
		$this->schedule->configurations = array();
		$this->schedule->lessons        = array();
		$this->migrateLessons();
		$this->migrateCalendar();

		// Don't save this in the schedule
		unset($this->schedule->active);

		foreach ($this->schedule->calendar as $date => $times)
		{
			$empty = true;
			foreach ($times AS $time => $lessons)
			{
				$empty = false;
			}

			if ($empty)
			{
				unset($this->schedule->calendar->$date);
			}
		}

		$scheduleRow->newSchedule = json_encode($this->schedule);


		if ($scheduleRow->active)
		{
			$this->save();
		}

		$scheduleRow->store();
	}

	/**
	 * Migrates the deprecated format calendar/lessons nodes to the new format calendar/configurations/lessons nodes
	 *
	 * @return void alters the calendar/configurations/lessons nodes of the object's schedule
	 */
	private function migrateCalendar()
	{
		foreach ($this->compSchedule->calendar as $date => $blocks)
		{
			if (empty($this->schedule->calendar->$date))
			{
				$this->schedule->calendar->$date = new stdClass;
			}

			foreach ($blocks as $blockNo => $blockLessons)
			{
				foreach ($blockLessons as $lessonCode => $instanceRooms)
				{
					$gridName = $this->compSchedule->lessons->$lessonCode->grid;
					$times    = $this->compSchedule->periods->$gridName->$blockNo;
					$time     = $times->starttime . '-' . $times->endtime;

					if (empty($this->schedule->calendar->$date->$time))
					{
						$this->schedule->calendar->$date->$time = new stdClass;
					}

					$lessonID                                          = $this->compSchedule->lessons->$lessonCode->gpuntisID;
					$this->schedule->calendar->$date->$time->$lessonID = new stdClass;


					$this->schedule->calendar->$date->$time->$lessonID->delta
						                                                               = empty($instanceRooms->delta) ? '' : $this->resolveDelta($instanceRooms->delta);
					$configurations                                                    = $this->migrateConfigurations($lessonCode, $instanceRooms);
					$this->schedule->calendar->$date->$time->$lessonID->configurations = $configurations;
				}
			}
		}
	}

	/**
	 * Creates complete instance configurations with lessonID, subjectID, teacher and room IDs => deltas
	 *
	 * @param string $lessonCode    the reference string used in the deprecated schedules
	 * @param object $instanceRooms the room gpuntis ids with their corresponding deltas
	 *
	 * @return array the ids of the configurations
	 */
	private function migrateConfigurations($lessonCode, $instanceRooms)
	{
		$rooms = array();
		foreach ($instanceRooms as $gpuntisID => $delta)
		{
			if ($gpuntisID == 'delta')
			{
				continue;
			}
			$rooms[$this->compSchedule->rooms->$gpuntisID->id] = $this->resolveDelta($delta);
		}

		$configurations = array();
		$rawBaseConfigs = $this->compSchedule->lessons->$lessonCode->configurations;
		foreach ($rawBaseConfigs as $rawBaseConfig)
		{
			// lesson, subject & teachers
			$config        = json_decode($rawBaseConfig);
			$config->rooms = $rooms;
			$jsonConfig    = json_encode($config);

			$configExists = in_array($jsonConfig, $this->schedule->configurations);
			if (!$configExists)
			{
				$this->schedule->configurations[] = $jsonConfig;
			}

			$configIndex = array_search($jsonConfig, $this->schedule->configurations);

			$referenceExists = in_array($configIndex, $configurations);
			if (!$referenceExists)
			{
				$configurations[] = $configIndex;
			}
		}

		return $configurations;
	}

	/**
	 * Migrates the deprecated format calendar/lessons nodes to the new format calendar/configurations/lessons nodes
	 *
	 * @return void alters the calendar/configurations/lessons nodes of the object's schedule
	 */
	private function migrateLessons()
	{
		foreach ($this->compSchedule->lessons as $lessonCode => $lesson)
		{
			$lessonID                                  = $lesson->gpuntisID;
			$this->schedule->lessons[$lessonID]        = new stdClass;
			$this->schedule->lessons[$lessonID]->delta = $this->resolveDelta($lesson);

			if (!empty($lesson->methodID))
			{
				$this->schedule->lessons[$lessonID]->methodID = $lesson->methodID;
			}

			$this->schedule->lessons[$lessonID]->comment = $lesson->comment;

			$pools                                        = $this->resolveCollection($lesson->pools, 'pools');
			$subjectDeltas                                = $this->resolveCollection($lesson->subjects, 'subjects');
			$subjectNos                                   = $this->mapSubjectNos($lesson->subjects);
			$this->schedule->lessons[$lessonID]->subjects = array();
			foreach ($subjectDeltas as $subjectID => $delta)
			{
				$this->schedule->lessons[$lessonID]->subjects[$subjectID]            = new stdClass;
				$this->schedule->lessons[$lessonID]->subjects[$subjectID]->delta     = $delta;
				$this->schedule->lessons[$lessonID]->subjects[$subjectID]->subjectNo = $subjectNos[$subjectID];
				$this->schedule->lessons[$lessonID]->subjects[$subjectID]->pools     = $pools;

				$teachers                                                           = $this->resolveCollection($lesson->teachers, 'teachers');
				$this->schedule->lessons[$lessonID]->subjects[$subjectID]->teachers = $teachers;

				// Save this to the comp schedule for easier cross referencing in migrateCalendar
				if (empty($this->compSchedule->lessons->$lessonCode->configurations))
				{
					$this->compSchedule->lessons->$lessonCode->configurations = array();
				}

				$baseConfig            = new stdClass;
				$baseConfig->lessonID  = $lessonID;
				$baseConfig->subjectID = $subjectID;
				$baseConfig->teachers  = $teachers;
				$jsonConfig            = json_encode($baseConfig);

				if (!in_array($jsonConfig, $this->compSchedule->lessons->$lessonCode->configurations))
				{
					$this->compSchedule->lessons->$lessonCode->configurations[] = $jsonConfig;
				}
			}
		}
	}

	/**
	 * Migrates the deprecated format pools node to the new format pools node
	 *
	 * @return void alters the pools node of the object's schedule
	 */
	private function migratePools()
	{
		require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/pools.php';

		foreach ($this->compSchedule->pools as $gpuntisID => $pool)
		{
			// Newer format schedule has associated plan programs
			if (!empty($pool->id) AND !in_array($pool->id, $this->schedule->pools))
			{
				$this->schedule->pools[] = $pool->id;
				continue;
			}

			$poolID = THM_OrganizerHelperPools::getPlanResourceID($gpuntisID, $pool);
			if (!empty($poolID))
			{
				$this->compSchedule->pools->$gpuntisID->id = $poolID;
				$this->schedule->pools[]                   = $poolID;
				THM_OrganizerHelperDepartment_Resources::setDepartmentResource($poolID, 'poolID', $this->schedule->departmentID);
			}
		}
	}

	/**
	 * Migrates the deprecated format degrees node to the new format programs node
	 *
	 * @return void alters the programs node of the object's schedule
	 */
	private function migratePrograms()
	{
		require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/programs.php';

		foreach ($this->compSchedule->degrees as $gpuntisID => $program)
		{
			// Newer format schedule has associated plan programs
			if (!empty($program->id) AND !in_array($program->id, $this->schedule->programs))
			{
				$this->schedule->programs[] = $program->id;
				continue;
			}

			$programID = THM_OrganizerHelperPrograms::getPlanResourceID($program);
			if (!empty($programID))
			{
				$this->compSchedule->degrees->$gpuntisID->id = $programID;
				$this->schedule->programs[]                  = $programID;
				THM_OrganizerHelperDepartment_Resources::setDepartmentResource($programID, 'programID', $this->schedule->departmentID);
			}
		}
	}

	/**
	 * Migrates the deprecated format rooms node to the new format rooms node
	 *
	 * @return void alters the rooms node of the object's schedule
	 */
	private function migrateRooms()
	{
		require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/rooms.php';

		foreach ($this->compSchedule->rooms as $gpuntisID => $room)
		{
			// Newer format schedule has the room id available in the object
			if (!empty($room->id) AND !in_array($room->id, $this->schedule->rooms))
			{
				$this->schedule->rooms[] = $room->id;
				continue;
			}

			$roomID = THM_OrganizerHelperRooms::getID($gpuntisID, $room);
			if (!empty($roomID))
			{
				$this->compSchedule->rooms->$gpuntisID->id = $roomID;
				$this->schedule->rooms[]                   = $roomID;
				THM_OrganizerHelperDepartment_Resources::setDepartmentResource($roomID, 'roomID', $this->schedule->departmentID);
			}
		}
	}

	/**
	 * Migrates the deprecated format subjects node to the new format subjects node
	 *
	 * @return void alters the subjects node of the object's schedule
	 */
	private function migrateSubjects()
	{
		require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/subjects.php';

		foreach ($this->compSchedule->subjects as $gpuntisID => $subject)
		{
			// Newer format schedule has associated plan programs
			if (!empty($subject->id) AND !in_array($subject->id, $this->schedule->subjects))
			{
				$this->schedule->subjects[] = $subject->id;
				continue;
			};

			$subjectID    = THM_OrganizerHelperSubjects::getPlanResourceID($gpuntisID, $subject);
			if (!empty($subjectID))
			{
				$this->compSchedule->subjects->$gpuntisID->id = $subjectID;
				$this->schedule->subjects[]                   = $subjectID;
				THM_OrganizerHelperDepartment_Resources::setDepartmentResource($subjectID, 'subjectID', $this->schedule->departmentID);
			}
		}
	}

	/**
	 * Migrates the deprecated format teachers node to the new format teachers node
	 *
	 * @return void alters the teachers node of the object's schedule
	 */
	private function migrateTeachers()
	{
		require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/teachers.php';

		foreach ($this->compSchedule->teachers as $gpuntisID => $teacher)
		{
			// Newer format schedule has the room id available in the object
			if (!empty($teacher->id) AND !in_array($teacher->id, $this->schedule->teachers))
			{
				$this->schedule->teachers[] = $teacher->id;
				continue;
			}

			$teacherID = THM_OrganizerHelperTeachers::getID($gpuntisID, $teacher);
			if (!empty($teacherID))
			{
				$this->compSchedule->teachers->$gpuntisID->id = $teacherID;
				$this->schedule->teachers[]                   = $teacherID;
				THM_OrganizerHelperDepartment_Resources::setDepartmentResource($teacherID, 'teacherID', $this->schedule->departmentID);
			}
		}
	}

	/**
	 * Resolves the resource delta
	 *
	 * @param mixed $resource the resource being checked (object) or the value of a dynamic field typically string
	 *
	 * @return string empty if the schedule is inactive or the resource had no changes, otherwise new/removed
	 */
	private function resolveDelta($resource)
	{
		if (is_object($resource))
		{
			$value = empty($resource->delta) ? '' : $resource->delta;
		}
		else
		{
			$value = $resource;
		}

		return (empty($this->schedule->active) OR empty($value)) ? '' : $value;
	}

	/**
	 * Resolves the collection id strings to the numerical values from the database
	 *
	 * @param object $collection the collection being processed
	 * @param string $collection the name of the collection being resolved
	 *
	 * @return array the id => delta mapping of the deprecated format lesson, empty if resolution failed
	 */
	private function resolveCollection($collection, $collectionName)
	{
		$return = array();
		if (empty($collection))
		{
			return $return;
		}

		foreach ($collection as $gpuntisID => $value)
		{
			$resourceID = $this->compSchedule->$collectionName->$gpuntisID->id;
			if (empty($resourceID))
			{
				continue;
			}

			$return[$resourceID] = $this->resolveDelta($value);
		}

		return $return;
	}

	/**
	 * Saves dynamic schedule information to the database.
	 *
	 * @param object &$schedule the schedule being processed
	 *
	 * @return void saves lessons to the database
	 */
	public function save(&$schedule = null)
	{
		if (!empty($schedule))
		{
			$this->schedule = $schedule;
		}

		if (empty($this->schedule))
		{
			return;
		}

		$this->_db->transactionStart();

		// This deletes all existing lessons for the department/planning period explicitly and all associated entries implicitly.
		try
		{
			$this->deleteLessons();
			$this->saveLessons();
			$this->saveConfigurations();
			$this->saveCalendar();
			$this->mapConfigurations();
		}
		catch (Exception $exc)
		{
			//JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_DATABASE_ERROR'), 'error');
			JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
			$this->_db->transactionRollback();
		}

		$this->_db->transactionCommit();
	}

	/**
	 * Creates calendar entries in the database
	 *
	 * @void creates database entries
	 */
	private function saveCalendar()
	{
		foreach ($this->schedule->calendar as $date => $times)
		{
			$calData                  = array();
			$calData['schedule_date'] = $date;

			foreach ($times as $startEnd => $lessons)
			{
				list($startTime, $endTime) = explode('-', $startEnd);
				$calData['start_time'] = $startTime . '00';
				$calData['end_time']   = $endTime . '00';

				foreach ($lessons as $lessonID => $instanceData)
				{
					$lessonsData                     = array();
					$lessonsData['gpuntisID']        = $lessonID;
					$lessonsData['departmentID']     = $this->schedule->departmentID;
					$lessonsData['planningPeriodID'] = $this->schedule->planningPeriodID;

					$lessonsTable = JTable::getInstance('lessons', 'thm_organizerTable');
					$lessonsTable->load($lessonsData);

					if (empty($lessonsTable->id))
					{
						continue;
					}

					$calData['lessonID'] = $lessonsTable->id;

					$calendarTable = JTable::getInstance('calendar', 'thm_organizerTable');
					$calendarTable->load($calData);

					$calData['delta'] = $instanceData->delta;
					$calendarTable->save($calData);
				}
			}
		}
	}

	/**
	 * Creates lesson configuration entries in the database
	 *
	 * @void creates database entries
	 */
	private function saveConfigurations()
	{
		foreach ($this->schedule->configurations as $json)
		{
			$config = json_decode($json);

			$lessonsData                     = array();
			$lessonsData['gpuntisID']        = $config->lessonID;
			$lessonsData['departmentID']     = $this->schedule->departmentID;
			$lessonsData['planningPeriodID'] = $this->schedule->planningPeriodID;

			$lessonsTable = JTable::getInstance('lessons', 'thm_organizerTable');
			$lessonsTable->load($lessonsData);

			if (empty($lessonsTable->id))
			{
				continue;
			}

			$lSubjectsData              = array();
			$lSubjectsData['lessonID']  = $lessonsTable->id;
			$lSubjectsData['subjectID'] = $config->subjectID;

			$lSubjectsTable = JTable::getInstance('lesson_subjects', 'thm_organizerTable');
			$lSubjectsTable->load($lSubjectsData);

			if (empty($lSubjectsTable->id))
			{
				continue;
			}

			// Information would be redundant in the db
			unset($config->lessonID, $config->subjectID);

			$configData    = array('lessonID' => $lSubjectsTable->id, 'configuration' => json_encode($config));
			$lConfigsTable = JTable::getInstance('lesson_configurations', 'thm_organizerTable');
			$lConfigsTable->load($configData);
			$lConfigsTable->save($configData);
		}
	}

	/**
	 * Saves the lessons from the schedule object to the database and triggers functions for saving lesson associations.
	 *
	 * @return void saves lessons to the database
	 */
	private function saveLessons()
	{
		foreach ($this->schedule->lessons as $gpuntisID => $lesson)
		{
			// If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
			$table = JTable::getInstance('lessons', 'thm_organizerTable');

			$data                     = array();
			$data['gpuntisID']        = $gpuntisID;
			$data['departmentID']     = $this->schedule->departmentID;
			$data['planningPeriodID'] = $this->schedule->planningPeriodID;

			$table->load($data);

			if (!empty($lesson->methodID))
			{
				$data['methodID'] = $lesson->methodID;
			}

			$data['delta'] = $lesson->delta;

			$success = $table->save($data);
			if (!$success)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');
				continue;
			}

			$this->saveLessonSubjects($table->id, $lesson->subjects);
		}
	}

	/**
	 * Saves the lesson pools from the schedule object to the database and triggers functions for saving lesson associations.
	 *
	 * @param string $lessonSubjectID the db id of the lesson subject association
	 * @param object $pools           the pools associated with the subject
	 * @param string $subjectNo       the subject's id in documentation
	 *
	 * @return void saves lessons to the database
	 */
	private function saveLessonPools($lessonSubjectID, $pools, $subjectID, $subjectNo)
	{
		foreach ($pools as $poolID => $delta)
		{
			// If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
			$table = JTable::getInstance('lesson_pools', 'thm_organizerTable');

			$data              = array();
			$data['subjectID'] = $lessonSubjectID;
			$data['poolID']    = $poolID;
			$table->load($data);

			// Delta will be 'calculated' later but explicitly overwritten now irregardless
			$data['delta'] = '';

			$success = $table->save($data);
			if (!$success)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');
				continue;
			}

			if (!empty($subjectNo))
			{
				$this->savePlanSubjectMapping($subjectID, $poolID, $subjectNo);
			}
		}
	}

	/**
	 * Saves the lesson subjects from the schedule object to the database and triggers functions for saving lesson
	 * associations.
	 *
	 * @param string $lessonID the db id of the lesson subject association
	 * @param object $subjects the subjects associated with the lesson
	 *
	 * @return void saves lessons to the database
	 */
	private function saveLessonSubjects($lessonID, $subjects)
	{
		foreach ($subjects as $subjectID => $subjectData)
		{
			// If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
			$table = JTable::getInstance('lesson_subjects', 'thm_organizerTable');

			$data              = array();
			$data['lessonID']  = $lessonID;
			$data['subjectID'] = $subjectID;
			$table->load($data);

			// Delta will be 'calculated' later but explicitly overwritten now irregardless
			$data['delta'] = $subjectData->delta;

			$success = $table->save($data);
			if (!$success)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');
				continue;
			}

			$subjectNo = empty($subjectData->subjectNo) ? null : $subjectData->subjectNo;
			$this->saveLessonPools($table->id, $subjectData->pools, $subjectID, $subjectNo);
			$this->saveLessonTeachers($table->id, $subjectData->teachers);
		}
	}

	/**
	 * Saves the lesson pools from the schedule object to the database and triggers functions for saving lesson associations.
	 *
	 * @param string $subjectID the db id of the lesson subject association
	 * @param object $teachers  the teacherss associated with the subject
	 *
	 * @return void saves lessons to the database
	 */
	private function saveLessonTeachers($subjectID, $teachers)
	{
		foreach ($teachers as $teacherID => $delta)
		{
			// If this isn't in the foreach it uses the same entry repeatedly irregardless of the data used for the load
			$table = JTable::getInstance('lesson_teachers', 'thm_organizerTable');

			$data              = array();
			$data['subjectID'] = $subjectID;
			$data['teacherID'] = $teacherID;
			$table->load($data);

			// Delta will be 'calculated' later but explicitly overwritten now irregardless
			$data['delta'] = '';

			$success = $table->save($data);
			if (!$success)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');
				continue;
			}
		}
	}

	/**
	 * Attempts to associate subjects used in scheduling with their documentation
	 *
	 * @param string $planSubjectID the id of the subject in the plan_subjects table
	 * @param string $poolID        the id of the pool in the plan_pools table
	 * @param string $subjectNo     the subject id used in documentation
	 *
	 * @return void saves/updates a database entry
	 */
	private function savePlanSubjectMapping($planSubjectID, $poolID, $subjectNo)
	{
		// Get the mapping boundaries for the program
		$boundariesQuery = $this->_db->getQuery(true);
		$boundariesQuery->select('lft, rgt')
			->from('#__thm_organizer_mappings as m')
			->innerJoin('#__thm_organizer_programs as prg on m.programID = prg.id')
			->innerJoin('#__thm_organizer_plan_programs as p_prg on prg.id = p_prg.programID')
			->innerJoin('#__thm_organizer_plan_pools as p_pool on p_prg.id = p_pool.programID')
			->where("p_pool.id = '$poolID'");
		$this->_db->setQuery((string) $boundariesQuery);

		try
		{
			$boundaries = $this->_db->loadAssoc();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return;
		}

		if (empty($boundaries))
		{
			return;
		}

		// Get the id for the subject documentation
		$subjectQuery = $this->_db->getQuery(true);
		$subjectQuery->select('subjectID')
			->from('#__thm_organizer_mappings as m')
			->innerJoin('#__thm_organizer_subjects as s on m.subjectID = s.id')
			->where("m.lft > '{$boundaries['lft']}'")
			->where("m.rgt < '{$boundaries['rgt']}'")
			->where("s.externalID = '$subjectNo'");
		$this->_db->setQuery((string) $subjectQuery);

		try
		{
			$subjectID = $this->_db->loadResult();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return;
		}

		if (empty($subjectID))
		{
			return;
		}

		$data  = array('subjectID' => $subjectID, 'plan_subjectID' => $planSubjectID);
		$table = JTable::getInstance('subject_mappings', 'thm_organizerTable');
		$table->load($data);
		$table->save($data);
	}
}