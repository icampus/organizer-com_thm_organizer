<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester editor view
 * @description provides a form for editing semester information
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */

defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersViewsemester_edit extends JView
{
    public function display($tpl = null)
    {
        JHTML::_('behavior.tooltip');
        JHtml::_('behavior.modal', 'a.modal');
        
        $model = $this->getModel();
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $title = ($model->semesterID)?
                JText::_('COM_THM_ORGANIZER_SM_SEMESTER_EDIT') : JText::_('COM_THM_ORGANIZER_SM_SEMESTER_NEW');
        $this->assignRef( 'title', $title );
        $this->assignRef( 'semesterID', $model->semesterID );
        $this->assignRef( 'semesterDesc', $model->semesterDesc );
        $this->assignRef( 'organization', $model->organization );

        parent::display($tpl);
    }
}?>
	