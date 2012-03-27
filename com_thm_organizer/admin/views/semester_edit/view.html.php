<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester editor view
 * @description provides a form for editing semester information
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @author      Markus Bader markusDOTbaderATmniDOTthmDOTde
 * @author      Daniel Kirsten danielDOTkirstenATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2012
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     2.5.0
 */
defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersViewsemester_edit extends JView
{
    public function display($tpl = null)
    {
        if(!JFactory::getUser()->authorise('core.admin'))
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $model = $this->getModel();
        $this->assignRef( 'title', $title );
        $this->assignRef( 'semesterID', $model->semesterID );
        $this->assignRef( 'semesterDesc', $model->semesterDesc );
        $this->assignRef( 'organization', $model->organization );

        $title = JText::_('COM_THM_ORGANIZER').': ';
        $title .= ($model->semesterID)? JText::_('JTOOLBAR_EDIT') : JText::_('JTOOLBAR_NEW');
        $title .= " ".JText::_('COM_THM_ORGANIZER_SCH_SEMESTER_TITLE');        
        JToolBarHelper::title( $title, 'mni' );
        $this->addToolBar();

        parent::display($tpl);
    }
    
    private function addToolBar()
    {
        JToolBarHelper::apply('semester.apply');
        JToolBarHelper::save('semester.save');
        JToolBarHelper::save2new('semester.save2new');
        JToolBarHelper::cancel('semester.cancel');
    }
}?>
	