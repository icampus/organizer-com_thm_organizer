<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Subjects;

/**
 * Class loads persistent information about a subject into the display context.
 */
class Subject_Edit extends EditView
{
    public $form;

    public $item;

    protected $_layout = 'tabs';

    public $languageLinks;

    public $languageParams;

    public $lessonID;

    public $menu;

    public $subjectID;
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        $new = empty($this->item->id);
        $title = $new ? Languages::_('THM_ORGANIZER_SUBJECT_NEW') : Languages::_('THM_ORGANIZER_SUBJECT_EDIT');
        HTML::setTitle($title, 'book');
        $toolbar = Toolbar::getInstance();
        $applyText = $new ? Languages::_('THM_ORGANIZER_CREATE') : Languages::_('THM_ORGANIZER_APPLY');
        $toolbar->appendButton('Standard', 'apply', $applyText, 'subject.apply', false);
        $toolbar->appendButton('Standard', 'save', Languages::_('THM_ORGANIZER_SAVE'), 'subject.save', false);
        $toolbar->appendButton(
            'Standard', 'save-new', Languages::_('THM_ORGANIZER_SAVE2NEW'), 'subject.save2new', false
        );
        $cancelText = $new ? Languages::_('THM_ORGANIZER_CANCEL') : Languages::_('THM_ORGANIZER_CLOSE');
        $toolbar->appendButton('Standard', 'cancel', $cancelText, 'subject.cancel', false);
    }

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     * @throws Exception => invalid request / unauthorized access
     */
    public function display($tpl = null)
    {
        $input           = OrganizerHelper::getInput();
        $this->subjectID = $input->getInt('id', 0);

        if (empty($this->subjectID)) {
            throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
        }

        if (!Subjects::allowEdit($this->subjectID)) {
            throw new Exception(Languages::_('THM_ORGANIZER_401'), 401);
        }

        $this->item = $this->get('Item');
        $this->form = $this->get('Form');

        $this->lessonID    = $input->getInt('lessonID', 0);
        $this->languageTag = Languages::getShortTag();

        OrganizerHelper::addMenuParameters($this);

        $this->languageLinks  = new LayoutFile('language_links', JPATH_ROOT . '/components/com_thm_organizer/Layouts');
        $this->languageParams = ['id' => $this->subjectID, 'lessonID' => $this->lessonID, 'view' => 'subject_edit'];

        $this->modifyDocument();

        parent::display($tpl);
    }

    /**
     * Adds resource files to the document
     *
     * @return void
     */
    protected function modifyDocument()
    {
        HTML::_('bootstrap.tooltip');
        HTML::_('behavior.framework', true);

        Factory::getDocument()->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/subject_edit.css');
        Factory::getDocument()->addScript(Uri::root() . 'components/com_thm_organizer/js/subject_prep_course.js');
    }
}