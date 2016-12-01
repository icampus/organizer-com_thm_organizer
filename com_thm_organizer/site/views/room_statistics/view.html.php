<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        thm_organizerViewRoom_Statistics
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

define('K_PATH_IMAGES', JPATH_ROOT . '/media/com_thm_organizer/images/');
jimport('tcpdf.tcpdf');

/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/schedule.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * View class for the display of schedules
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewRoom_Statistics extends JViewLegacy
{
	public $fields = array();

	public $date;

	public $timePeriods;

	public $planningPeriods;

	public $departments;

	public $programs;

	public $roomIDs;


	/**
	 * Method to get extra
	 *
	 * @param string $tpl template
	 *
	 * @return  mixed  false on error, otherwise void
	 */
	public function display($tpl = null)
	{
		$this->modifyDocument();

		$this->lang = THM_OrganizerHelperLanguage::getLanguage();

		$this->model = $this->getModel();

		$this->setBaseFields();
		$this->setFilterFields();

		parent::display($tpl);

	}

	/**
	 * Modifies document variables and adds links to external files
	 *
	 * @return  void
	 */
	private function modifyDocument()
	{
		JHtml::_('bootstrap.framework');
		JHtml::_('bootstrap.tooltip');
		JHtml::_('jquery.ui');
		JHtml::_('behavior.calendar');
		JHtml::_('formbehavior.chosen', 'select');

		$document = JFactory::getDocument();
		$document->addScript(JUri::root() . '/media/com_thm_organizer/js/room_statistics.js');
		$document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/room_statistics.css');
	}

	private function setBaseFields()
	{
		$attribs                      = array();
		$this->fields['baseSettings'] = array();

		$dateRestrictions       = array();
		$dateRestrictionAttribs = array('onChange' => 'handleDateRestriction();');
		$dateRestrictions[]     = array('text' => JText::_('COM_THM_ORGANIZER_WEEK'), 'value' => 'week');
		$dateRestrictions[]     = array('text' => JText::_('COM_THM_ORGANIZER_MONTH'), 'value' => 'month');
		$dateRestrictions[]     = array('text' => JText::_('COM_THM_ORGANIZER_SEMESTER'), 'value' => 'semester');

		$dateRestrictionSelect                           =
			JHtml::_('select.genericlist', $dateRestrictions, 'dateRestriction', $dateRestrictionAttribs, 'value', 'text', 'semester');
		$this->fields['baseSettings']['dateRestriction'] = array(
			'label'       => JText::_('COM_THM_ORGANIZER_DATE_RESTRICTION'),
			'description' => JText::_('COM_THM_ORGANIZER_DATE_RESTRICTION_DESC'),
			'input'       => $dateRestrictionSelect
		);

		// The Joomla calendar form field demands the % character before the real date format instruction values.
		$rawDateFormat = JFactory::getApplication()->getParams()->get('dateFormat');
		$dateFormat    = preg_replace("/([a-zA-Z])/", "%$1", $rawDateFormat);

		$dateSelect                           = JHtml::_('calendar', date('Y-m-d'), 'date', 'date', $dateFormat, $attribs);
		$this->fields['baseSettings']['date'] = array(
			'label'       => JText::_('JDATE'),
			'description' => JText::_('COM_THM_ORGANIZER_DATE_DESC'),
			'input'       => $dateSelect
		);

		$ppAttribs                                         = $attribs;
		$ppOptions                                         = $this->model->getPlanningPeriodOptions();
		$ppDefault                                         = $this->model->getPlanningPeriodDefault();
		$ppSelect                                          = JHtml::_('select.genericlist', $ppOptions, 'planningPeriodIDs[]', $ppAttribs, 'value', 'text', $ppDefault);
		$this->fields['baseSettings']['planningPeriodIDs'] = array(
			'label'       => JText::_('COM_THM_ORGANIZER_PLANNING_PERIOD'),
			'description' => JText::_('COM_THM_ORGANIZER_ROOMS_EXPORT_DESC'),
			'input'       => $ppSelect
		);
	}

	/**
	 * Creates resource selection fields for the form
	 *
	 * @return void sets indexes in $this->fields['resouceSettings'] with html content
	 */
	private function setFilterFields()
	{
		$this->fields['filterFields'] = array();
		$attribs                      = array('multiple' => 'multiple');

		$roomAttribs                             = $attribs;
		$roomAttribs['data-placeholder']         = JText::_('COM_THM_ORGANIZER_ROOM_SELECT_PLACEHOLDER');
		$planRoomOptions                         = $this->model->getRoomOptions();
		$roomSelect                              = JHtml::_('select.genericlist', $planRoomOptions, 'roomIDs[]', $roomAttribs, 'value', 'text');
		$this->fields['filterFields']['roomIDs'] = array(
			'label'       => JText::_('COM_THM_ORGANIZER_ROOMS'),
			'description' => JText::_('COM_THM_ORGANIZER_ROOMS_EXPORT_DESC'),
			'input'       => $roomSelect
		);

		$roomTypeAttribs                         = $attribs;
		$roomTypeAttribs['onChange']             = 'repopulateRooms();';
		$roomTypeAttribs['data-placeholder']     = JText::_('COM_THM_ORGANIZER_ROOM_TYPE_SELECT_PLACEHOLDER');
		$typeOptions                             = $this->model->getRoomTypeOptions();
		$roomTypeSelect                          = JHtml::_('select.genericlist', $typeOptions, 'typeIDs[]', $roomTypeAttribs, 'value', 'text');
		$this->fields['filterFields']['typeIDs'] = array(
			'label'       => JText::_('COM_THM_ORGANIZER_ROOM_TYPES'),
			'description' => JText::_('COM_THM_ORGANIZER_ROOMS_EXPORT_DESC'),
			'input'       => $roomTypeSelect
		);

		// Departments
		$deptAttribs                                  = $attribs;
		$deptAttribs['onChange']                      = 'repopulatePlanningPeriods();repopulatePrograms();repopulateRooms();';
		$deptAttribs['data-placeholder']              = JText::_('COM_THM_ORGANIZER_DEPARTMENT_SELECT_PLACEHOLDER');
		$planDepartmentOptions                        = $this->model->getDepartmentOptions();
		$departmentSelect                             = JHtml::_('select.genericlist', $planDepartmentOptions, 'departmentIDs[]', $deptAttribs, 'value', 'text');
		$this->fields['filterFields']['departmetIDs'] = array(
			'label'       => JText::_('COM_THM_ORGANIZER_DEPARTMENTS'),
			'description' => JText::_('COM_THM_ORGANIZER_DEPARTMENTS_EXPORT_DESC'),
			'input'       => $departmentSelect
		);

		// Programs
		$programAttribs                             = $attribs;
		$programAttribs['onChange']                 = 'repopulatePlanningPeriods();repopulateRooms();';
		$programAttribs['data-placeholder']         = JText::_('COM_THM_ORGANIZER_PROGRAM_SELECT_PLACEHOLDER');
		$planProgramOptions                         = $this->model->getProgramOptions();
		$programSelect                              = JHtml::_('select.genericlist', $planProgramOptions, 'programIDs[]', $programAttribs, 'value', 'text');
		$this->fields['filterFields']['programIDs'] = array(
			'label'       => JText::_('COM_THM_ORGANIZER_PROGRAMS'),
			'description' => JText::_('COM_THM_ORGANIZER_PROGRAMS_EXPORT_DESC'),
			'input'       => $programSelect
		);
	}
}