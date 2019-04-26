<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use THM_OrganizerHelperHTML as HTML;

/**
 * Class loads the schedule form into the display context.
 */
class THM_OrganizerViewSchedule extends \Joomla\CMS\MVC\View\HtmlView
{
    /**
     * format for displaying dates
     *
     * @var string
     */
    protected $dateFormat;

    /**
     * default time grid, loaded first
     *
     * @var object
     */
    protected $defaultGrid;

    /**
     * the department for this schedule, chosen in menu options
     *
     * @var string
     */
    protected $departmentID;

    /**
     * The time period in days in which removed lessons should get displayed.
     *
     * @var string
     */
    protected $deltaDays;

    /**
     * Filter to indicate intern emails
     *
     * @var string
     */
    protected $emailFilter;

    /**
     * mobile device or not
     *
     * @var boolean
     */
    protected $isMobile = false;

    /**
     * Contains the current languageTag
     *
     * @var string
     */
    protected $languageTag = 'de-DE';

    /**
     * Model to this view
     *
     * @var THM_OrganizerModelSchedule
     */
    protected $model;

    /**
     * Method to display the template
     *
     * @param null $tpl template
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->isMobile    = OrganizerHelper::isSmartphone();
        $this->languageTag = Languages::getShortTag();
        $this->model       = $this->getModel();
        $this->defaultGrid = $this->model->getDefaultGrid();
        $compParams        = OrganizerHelper::getParams();
        $this->dateFormat  = $compParams->get('dateFormat', 'd.m.Y');
        $this->emailFilter = $compParams->get('emailFilter', '');
        $this->modifyDocument();
        parent::display($tpl);
    }

    /**
     * Adds resource files to the document
     *
     * @return void
     */
    private function modifyDocument()
    {
        $doc = \JFactory::getDocument();

        HTML::_('formbehavior.chosen', 'select');
        $this->addScriptOptions();
        $doc->addScript(Uri::root() . 'components/com_thm_organizer/js/schedule.js');
        $doc->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/schedule.css');
        $doc->addStyleSheet(Uri::root() . 'media/jui/css/icomoon.css');
    }

    /**
     * Generates required params for Javascript and adds them to the document
     *
     * @return void
     */
    private function addScriptOptions()
    {
        $user = \JFactory::getUser();
        $root = Uri::root();

        $variables = [
            'SEMESTER_MODE'     => 1,
            'PERIOD_MODE'       => 2,
            'INSTANCE_MODE'     => 3,
            'ajaxBase'          => $root . 'index.php?option=com_thm_organizer&view=schedule_ajax&format=raw',
            'auth'              => !empty($user->id) ?
                urlencode(password_hash($user->email . $user->registerDate, PASSWORD_BCRYPT)) : '',
            'dateFormat'        => $this->dateFormat,
            'defaultGrid'       => $this->defaultGrid->grid,
            'exportBase'        => $root . 'index.php?option=com_thm_organizer&view=schedule_export',
            'isMobile'          => $this->isMobile,
            'menuID'            => OrganizerHelper::getInput()->get('Itemid', 0),
            'registered'        => !empty($user->id),
            'subjectDetailBase' => $root . 'index.php?option=com_thm_organizer&view=subject_details&id=1',
            'username'          => !empty($user->id) ? $user->username : ''
        ];

        $grids = [];
        foreach ($this->model->grids as $grid) {
            $grids[$grid->id] = [
                'id'   => $grid->id,
                'grid' => $grid->grid
            ];
        }
        $variables['grids'] = $grids;

        if (empty($user->email)) {
            $variables['internalUser'] = false;
        } else {
            if (empty($this->emailFilter)) {
                $variables['internalUser'] = true;
            } else {
                $atSignPos                 = strpos($user->email, '@');
                $variables['internalUser'] = strpos($user->email, $this->emailFilter, $atSignPos) !== false;
            }
        }

        $doc = \JFactory::getDocument();
        $doc->addScriptOptions('variables', array_merge($variables, $this->model->params));

        Languages::script('APRIL');
        Languages::script('AUGUST');
        Languages::script('THM_ORGANIZER_ACTION_GENERATE_LINK');
        Languages::script('DECEMBER');
        Languages::script('FEBRUARY');
        Languages::script('FRI');
        Languages::script('JANUARY');
        Languages::script('JULY');
        Languages::script('JUNE');
        Languages::script('THM_ORGANIZER_LUNCHTIME');
        Languages::script('MARCH');
        Languages::script('MAY');
        Languages::script('MON');
        Languages::script('THM_ORGANIZER_MY_SCHEDULE');
        Languages::script('NOVEMBER');
        Languages::script('OCTOBER');
        Languages::script('THM_ORGANIZER_POOL_SELECT_PLACEHOLDER');
        Languages::script('THM_ORGANIZER_PROGRAM_SELECT_PLACEHOLDER');
        Languages::script('THM_ORGANIZER_ROOM_SELECT_PLACEHOLDER');
        Languages::script('THM_ORGANIZER_ROOM_TYPE_SELECT_PLACEHOLDER');
        Languages::script('SAT');
        Languages::script('SEPTEMBER');
        Languages::script('SUN');
        Languages::script('THM_ORGANIZER_TEACHER_SELECT_PLACEHOLDER');
        Languages::script('THU');
        Languages::script('THM_ORGANIZER_TIME');
        Languages::script('TUE');
        Languages::script('WED');
    }
}
