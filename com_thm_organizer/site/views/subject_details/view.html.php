<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Wolf Rost,  <Wolf.Rost@mni.thm.de>
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use \THM_OrganizerHelperHTML as HTML;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class loads the subject into the display context.
 */
class THM_OrganizerViewSubject_Details extends \Joomla\CMS\MVC\View\HtmlView
{
    const PENDING = 0;
    const REGISTERED = 1;

    public $color = 'blue';

    public $courses = [];

    public $disclaimer;

    public $disclaimerParams;

    public $languageLinks;

    public $languageParams;

    public $lang;

    public $langTag = 'de';

    public $menu;

    public $showRegistration = false;

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->modifyDocument();
        $this->item = $this->get('Item');

        if (!empty($this->item['subjectID'])) {
            $this->lang = $this->get('Language');

            $courses = THM_OrganizerHelperCourses::getLatestCourses($this->item['subjectID']);

            if (!empty($courses)) {
                $this->showRegistration = true;
                $isCoordinator          = THM_OrganizerHelperSubjects::allowEdit($this->item['subjectID']);

                foreach ($courses as &$course) {
                    $courseID                     = $course['id'];
                    $course['dateText']           = THM_OrganizerHelperCourses::getDateDisplay($courseID);
                    $course['expired']            = !THM_OrganizerHelperCourses::isRegistrationOpen($courseID);
                    $course['registrationButton'] = THM_OrganizerHelperCourses::getActionButton('subject', $courseID);
                    $regState                     = THM_OrganizerHelperCourses::getParticipantState($courseID);
                    $course['status']             = empty($regState) ? null : (int)$regState['status'];
                    $course['statusDisplay']      = THM_OrganizerHelperCourses::getStatusDisplay($courseID);

                    // Course administrators are green
                    $isTeacher = THM_OrganizerHelperCourses::authorized($courseID);
                    if ($isCoordinator or $isTeacher) {
                        $this->color = 'green';
                        continue;
                    }

                    // No change if: course has no status information, the status color has already been set to green
                    if ($course['status'] === null or $this->color === 'green') {
                        continue;
                    }

                    $this->color = $course['status'] === self::REGISTERED ? 'green' : 'yellow';
                }

                $this->courses = $courses;
            }

            THM_OrganizerHelperComponent::addMenuParameters($this);

            $this->languageLinks    = new \JLayoutFile('language_links', JPATH_COMPONENT . '/layouts');
            $this->languageParams   = ['id' => $this->item['subjectID'], 'view' => 'subject_details'];
            $this->disclaimer       = new \JLayoutFile('disclaimer', JPATH_COMPONENT . '/layouts');
            $this->disclaimerParams = ['language' => $this->lang];
        }

        parent::display($tpl);
    }

    /**
     * Modifies document variables and adds links to external files
     *
     * @return void
     */
    private function modifyDocument()
    {
        HTML::_('bootstrap.tooltip');
        HTML::_('behavior.framework', true);

        $document = \JFactory::getDocument();
        $document->addStyleSheet(\JUri::root() . '/media/com_thm_organizer/css/subject_details.css');
    }

    /**
     * Creates a basic output for processed values
     *
     * @param string $attribute the attribute name
     * @param mixed  $data      the data to be displayed array|string
     *
     * @return void outputs HTML
     */
    public function renderAttribute($attribute, $data)
    {
        if (empty($data['label']) or empty($data['value'])) {
            return;
        }

        $starAttributes = ['expertise', 'methodCompetence', 'selfCompetence', 'socialCompetence'];
        echo '<div class="subject-item">';
        echo '<div class="subject-label">' . $data['label'] . '</div>';
        echo '<div class="subject-content attribute-' . $attribute . '">';
        if (is_array($data['value'])) {
            $this->renderListValue($attribute, $data['value']);
        } elseif (in_array($attribute, $starAttributes)) {
            $this->renderStarValue($data['value']);
        } elseif ($attribute == 'campus') {
            if (!empty($data['location'])) {
                $pin = THM_OrganizerHelperCampuses::getPin($data['location']);
                echo "$pin {$data['value']}";
            } else {
                echo $data['value'];
            }
        } else {
            echo $data['value'];
        }
        echo '</div></div>';
    }

    /**
     * Displays a link to the collaboration platform course for the module.
     *
     * @return void
     */
    public function renderCollab()
    {
        if (empty($this->item->externalID)) {
            return;
        }

        $params         = THM_OrganizerHelperComponent::getParams();
        $displayeCollab = $params->get('displayeCollabLink', false);
        $ecollabLink    = $params->get('eCollabLink', '');

        if (empty($displayeCollab) or empty($ecollabLink)) {
            return;
        }
        ?>
        <div class="subject-item">
            <div class="subject-label">eCollaboration Link</div>
            <div class="subject-content">
                <a href="<?php echo $ecollabLink . $this->item->externalID; ?>" target="_blank">
                    <span class='icon-moodle' title='Moodle'></span>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Renders array values as lists
     *
     * @param array $value the array value to render
     *
     * @return void outputs html directly
     */
    private function renderListValue($attribute, $value)
    {
        $linkAttribs = ['target' => '_blank'];
        $subjectHref = "index.php?view=subject_details&languageTag={$this->langTag}&id=";
        echo '<ul>';
        foreach ($value as $id => $data) {
            echo '<li>';
            if (is_array($data)) {
                echo $id;
                $this->renderListValue($attribute, $data);
            } else {
                if ($attribute == 'preRequisiteModules' or $attribute == 'postRequisiteModules') {
                    echo HTML::link(\JRoute::_($subjectHref . $id), $data, $linkAttribs);
                } else {
                    echo $data;
                }
            }
            echo '</li>';
        }
        echo '</ul>';
    }

    /**
     * Renders a number of stars appropriate to the value
     *
     * @param string $value the value of the star attribute
     *
     * @return void outputs HTML
     */
    public function renderStarValue($value)
    {
        $invalid = (is_null($value) or $value > 3);
        if ($invalid) {
            return;
        }

        $option = 'COM_THM_ORGANIZER_';
        switch ($value) {
            case 3:
                $stars = '<span class="icon-featured"></span>';
                $stars .= '<span class="icon-featured"></span>';
                $stars .= '<span class="icon-featured"></span>';
                $aria  = $this->lang->_($option . 'THREE_STARS');
                break;
            case 2:
                $stars = '<span class="icon-featured"></span>';
                $stars .= '<span class="icon-featured"></span>';
                $stars .= '<span class="icon-unfeatured"></span>';
                $aria  = $this->lang->_($option . 'TWO_STARS');
                break;
            case 1:
                $stars = '<span class="icon-featured"></span>';
                $stars .= '<span class="icon-unfeatured"></span>';
                $stars .= '<span class="icon-unfeatured"></span>';
                $aria  = $this->lang->_($option . 'ONE_STAR');
                break;
            case 0:
            default:
                $stars = '<span class="icon-unfeatured"></span>';
                $stars .= '<span class="icon-unfeatured"></span>';
                $stars .= '<span class="icon-unfeatured"></span>';
                $aria  = $this->lang->_($option . 'NO_STARS');
                break;
        }

        echo '<span aria-label="' . $aria . '">' . $stars . '</span>';
    }
}
