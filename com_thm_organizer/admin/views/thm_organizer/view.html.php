<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.administrator
 * @name        THM_OrganizerViewthm_organizer
 * @description view output class for the component splash page
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Class defining view output
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.administrator
 */
class THM_OrganizerViewTHM_Organizer extends JViewLegacy
{
    /**
     * Loads model data into view context
     *
     * @param   string  $tpl  the template type to be used
     *
     * @return  void or JError on unauthorized access
     */
    public function display($tpl = null)
    {
        JHtml::_('behavior.tooltip');
        $document = Jfactory::getDocument();
        $document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/backend.css');
        $document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/fonts/iconfont.css');

        THM_OrganizerHelperComponent::addSubmenu($this);

        $this->addToolBar();

        parent::display($tpl);
    }

    /**
     * creates a joomla administratoristrative tool bar
     *
     * @return void
     */
    private function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_MAIN_VIEW_TITLE'), 'organizer');
        JToolbarHelper::preferences('com_thm_organizer');
    }
}
