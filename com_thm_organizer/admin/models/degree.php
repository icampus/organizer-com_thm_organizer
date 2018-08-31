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

/**
 * Class which manages stored degree data.
 */
class THM_OrganizerModelDegree extends JModelLegacy
{
    /**
     * Saves degree information to the database
     *
     * @return boolean true on success, otherwise false
     * @throws Exception
     */
    public function save()
    {
        if (!THM_OrganizerHelperComponent::isAdmin()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $data  = JFactory::getApplication()->input->get('jform', [], 'array');
        $table = JTable::getInstance('degrees', 'thm_organizerTable');

        return $table->save($data);
    }

    /**
     * Deletes the chosen degrees from the database
     *
     * @return boolean true on success, otherwise false
     */
    public function delete()
    {
        if (!THM_OrganizerHelperComponent::isAdmin()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        return THM_OrganizerHelperComponent::delete('degrees');
    }
}
