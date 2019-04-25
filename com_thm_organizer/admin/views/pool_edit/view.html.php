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

require_once JPATH_COMPONENT . '/views/edit.php';

/**
 * Class loads the (subject) pool form into display context.
 */
class THM_OrganizerViewPool_Edit extends THM_OrganizerViewEdit
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        if (empty($this->item->id)) {
            $title = \JText::_('THM_ORGANIZER_POOL_EDIT_NEW_TITLE');
            \JToolbarHelper::apply('pool.apply', 'THM_ORGANIZER_CREATE');
            \JToolbarHelper::save('pool.save');
            \JToolbarHelper::save2new('pool.save2new');
            \JToolbarHelper::cancel('pool.cancel', 'JTOOLBAR_CANCEL');
        } else {
            $title = \JText::_('THM_ORGANIZER_POOL_EDIT_EDIT_TITLE');
            \JToolbarHelper::apply('pool.apply', 'THM_ORGANIZER_APPLY');
            \JToolbarHelper::save('pool.save');
            \JToolbarHelper::save2new('pool.save2new');
            \JToolbarHelper::save2copy('pool.save2copy');
            \JToolbarHelper::cancel('pool.cancel', 'JTOOLBAR_CLOSE');

            $toolbar = \JToolbar::getInstance('toolbar');
            $baseURL = "index.php?option=com_thm_organizer&tmpl=component&type=pool&id={$this->item->id}&view=";

            $poolLink = $baseURL . 'pool_selection';
            $toolbar->appendButton('Popup', 'list', \JText::_('THM_ORGANIZER_ADD_POOL'), $poolLink);

            $subjectLink = $baseURL . 'subject_selection';
            $toolbar->appendButton('Popup', 'book', \JText::_('THM_ORGANIZER_ADD_SUBJECT'), $subjectLink);
        }
        \JToolbarHelper::title($title, 'organizer_pools');
    }
}
