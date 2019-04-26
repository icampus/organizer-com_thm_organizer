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

require_once JPATH_COMPONENT . '/views/edit.php';

/**
 * Class loads the grid form into display context.
 */
class THM_OrganizerViewGrid_Edit extends THM_OrganizerViewEdit
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        \JToolbarHelper::save('grid.save');
        if (empty($this->item->id)) {
            \JToolbarHelper::title(Languages::_('THM_ORGANIZER_GRID_EDIT_NEW_TITLE'), 'organizer_grids');
            \JToolbarHelper::cancel('grid.cancel', 'JTOOLBAR_CANCEL');
        } else {
            \JToolbarHelper::title(Languages::_('THM_ORGANIZER_GRID_EDIT_EDIT_TITLE'), 'organizer_grids');
            \JToolbarHelper::cancel('grid.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
