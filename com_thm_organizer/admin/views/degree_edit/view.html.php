<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewDegree_Edit
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.edit.view');

/**
 * Class loads persistent degree information into edit display context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewDegree_Edit extends JViewLegacy
{
    /**
     * Method to get display
     *
     * @param   Object  $tpl  template  (default: null)
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        THM_CoreEditView::setUp($this);
        parent::display($tpl);
    }

    /**
     * Method to generate buttons for user interaction
     *
     * @return  void
     */
    protected function addToolBar()
    {
        if ($this->form->getValue('id') == 0)
        {
            $title = JText::_('COM_THM_ORGANIZER_DEGREE_EDIT_NEW_VIEW_TITLE');
            $cancelText = JText::_('COM_THM_ORGANIZER_ACTION_CANCEL');
        }
        else
        {
            $title = JText::_('COM_THM_ORGANIZER_DEGREE_EDIT_EDIT_VIEW_TITLE');
            $cancelText = JText::_('COM_THM_ORGANIZER_ACTION_CLOSE');
        }
        JToolbarHelper::title($title, 'organizer_degrees');
        JToolbarHelper::save('degree.save');
        JToolbarHelper::cancel('degree.cancel', $cancelText);
    }
}
