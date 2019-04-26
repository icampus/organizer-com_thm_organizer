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

use Joomla\CMS\Uri\Uri;

/**
 * Class loads the plan (subject) pool form into display context.
 */
class THM_OrganizerViewPlan_Pool_Edit extends THM_OrganizerViewEdit
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        \JToolbarHelper::title(Languages::_('THM_ORGANIZER_PLAN_POOL_EDIT_TITLE'), 'organizer_pools');
        \JToolbarHelper::save('plan_pool.save');
        $cancelText = empty($this->item->id) ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE';
        \JToolbarHelper::cancel('plan_pool.cancel', $cancelText);
    }

    /**
     * Adds styles and scripts to the document
     *
     * @return void  modifies the document
     */
    protected function modifyDocument()
    {
        parent::modifyDocument();

        $document = \JFactory::getDocument();
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/plan_pool_edit.css');
    }
}
