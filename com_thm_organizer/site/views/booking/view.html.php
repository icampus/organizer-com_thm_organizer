<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewBooking
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen 
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * Outputs a string explaining possible conflicts which would emerge if an event were saved
 * 
 * @category	Joomla.Component.Site
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.1.0
 */
class THM_OrganizerViewBooking extends JView
{
    /**
     * Initiates model checks for conflicts and 'displays' them
     * 
     * @param   string  $tpl  the name of the template to be use on the output 
     * 
     * @return  void
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();
        $conflicts = $model->getConflicts();
        if (count($conflicts))
        {
            $message = JText::_('COM_THM_ORGANIZER_B_CONFLICTS_FOUND') . ":\r\n";
            foreach ($conflicts as $conflict)
            {
                $message .= "\r\n" . $conflict['text'] . "\r\n";
            }
            echo $message;
        }
    }
}
