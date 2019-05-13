<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * Class loads curriculum information into the display context.
 */
class Curriculum extends BaseHTMLView
{
    public $disclaimer;

    public $ecollabLink;

    public $item;

    public $languageLinks;

    public $languageParams;

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

        $menu = OrganizerHelper::getApplication()->getMenu()->getActive();

        if (!is_object($menu)) {
            $this->ecollabLink = '';
        } else {
            $this->ecollabLink = $menu->params->get('eCollabLink', '');
        }

        $this->item = $this->get('Item');

        $this->languageLinks  = new LayoutFile('language_links', JPATH_ROOT . '/components/com_thm_organizer/Layouts');
        $this->languageParams = ['id' => $this->item->id, 'view' => 'curriculum'];

        $this->disclaimer = new LayoutFile('disclaimer', JPATH_ROOT . '/components/com_thm_organizer/Layouts');

        parent::display($tpl);
    }

    /**
     * Sets document scripts and styles
     *
     * @return void
     */
    private function modifyDocument()
    {
        HTML::_('bootstrap.tooltip');
        HTML::_('bootstrap.framework');

        $document = Factory::getDocument();
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/curriculum.css');
        $document->addScript(Uri::root() . 'components/com_thm_organizer/js/curriculum.js');
        $document->addScript(Uri::root() . 'components/com_thm_organizer/js/container.js');
    }
}