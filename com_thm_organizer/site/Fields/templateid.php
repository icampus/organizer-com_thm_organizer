<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

use \THM_OrganizerHelperHTML as HTML;

\JFormHelper::loadFieldClass('list');

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/component.php';

/**
 * Class creates a form field for template selection.
 * @todo rename this and make it generally accessible should this usage occur again.
 */
class JFormFieldTemplateID extends \JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'templateID';

    /**
     * Returns a select box where stored degree programs can be chosen
     *
     * @return array  the available degree programs
     */
    protected function getOptions()
    {
        return HTML::getTranslatedOptions($this, $this->element);
    }
}
