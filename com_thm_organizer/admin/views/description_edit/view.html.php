<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        description editor view
 * @description provides a form for editing description information
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
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

class thm_organizersViewdescription_edit extends JView
{
    public function display($tpl = null)
    {
        if(!JFactory::getUser()->authorise('core.admin'))
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        
        JHtml::_('behavior.framework', true);
        JHTML::_('behavior.formvalidation');
        JHTML::_('behavior.tooltip');
        
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/schedule_edit.js'));

        $model = $this->getModel();
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        
        // get task for template (gpuntisID might be readable only)
        $task = JRequest::getVar('task', null, 'post','STRING');
        
        // set values if new button is clicked while an item is checked
        if ($task == 'description.add') {
        	$this->item->id = 0;
        	$formElements = $this->form->getFieldset();
        	// set values in form
        	foreach ($formElements as $value) {
        		$this->form->setValue($value->fieldname, null, '');
        	}
        }
        
        $title = JText::_("COM_THM_ORGANIZER_SCH_TITLE").": ";

        $this->setLayout('edit');

        JToolBarHelper::title($title);
        $this->addToolBar();

        // set old data on error redirect
        $session =& JFactory::getSession();
        $oldPost = $session->get('oldPost');
        
        // check werether to prefill field values
        if ($oldPost != null) {  // do prefill
        	 
        	// set values in form
        	foreach ($oldPost['jform'] as $key => $value) {
        		$this->form->setValue($key, null, $value);
        	}
        	$session->clear('oldPost');
        }
        
        parent::display($tpl);
    }
    
    private function addToolBar()
    {
        JRequest::setVar('hidemainmenu', true);
		$isNew = ($this->item->id == 0);
		$title = JText::_('COM_THM_ORGANIZER').': ';
		$title .= ($isNew ? JText::_('JTOOLBAR_NEW') : JText::_('JTOOLBAR_EDIT')).' ';
		$title .= JText::_('COM_THM_ORGANIZER_DS');
		JToolBarHelper::title($title, 'mni');
		JToolBarHelper::save('description.save');
		JToolBarHelper::cancel('description.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}?>
	