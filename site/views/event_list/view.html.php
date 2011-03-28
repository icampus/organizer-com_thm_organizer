<?php

/**
* Notelist View Class for the Giessen Times Component
*
* @package    Giessen Scheduler
*/


// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');
JHtml::core();

/**
* HTML View class for the Giessen Scheduler Component
*
* @package    Giessen Scheduler
*/

class thm_organizerViewevent_list extends JView
{
    public function display($tpl = null)
    {
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/event_list.js'));

        $model = $this->getModel();
        $events = $model->events;
        //echo "<pre>".print_r($events, true)."</pre>";
        $this->assign('events', $events);
        $display_type = $model->display_type;
        $this->assign('display_type', $display_type);
        
        $categories = $model->categories;
        $this->assignRef('categories', $categories);
        $category = ($model->getState('category'))? $model->getState('category') : -1;
        $this->assignRef('category', $category);
        $this->makeCategorySelect($categories, $category);

        $canWrite = $model->canWrite;
        $this->assignRef('canWrite', $canWrite);
        $canEdit = $model->canEdit;
        $this->assignRef('canEdit', $canEdit);
        $this->assign('itemID' , JRequest::getInt('Itemid'));

        $total = $model->total;
        $this->assign('total', $total);
        
        // Create the pagination object
        $pageNav = & $this->get('Pagination');
        $this->assign('pageNav', $pageNav);

        //form state variables
        $search = $model->getState('search');
        $search = (empty($search))? "" : $search;
        $this->assignRef('search', $search);
        $orderby = $model->getState('orderby');
        $orderby = (empty($orderby))? "startdate" : $orderby;
        $this->assign('orderby', $orderby);
        $orderbydir = $model->getState('orderbydir');
        $orderbydir = (empty($orderbydir))? "ASC" : $orderbydir;
        $this->assign('orderbydir', $orderbydir);
        
        $this->buildHTMLElements();

        parent::display($tpl);
    }


    private function buildHTMLElements()
    {
        $model = $this->getModel();

        $newImage = JHTML::_('image.site', 'add.png', 'components/com_thm_organizer/assets/images/', NULL, NULL, JText::_( 'Termin Erzeugen' ));
        $this->assignRef('newImage', $newImage);
        $editImage = JHTML::_('image.site', 'edit.png', 'components/com_thm_organizer/assets/images/', NULL, NULL, JText::_( 'Termin EBearbeiten' ));
        $this->assignRef('editImage', $editImage);
        $deleteimage= JHTML::_('image.site', 'delete.png', 'components/com_thm_organizer/assets/images/', NULL, NULL, JText::_( 'Termin Löschen' ));
        $this->assignRef('deleteImage', $deleteImage);

        $fromdate = $model->getState('fromdate');
        $fromdate = (empty($fromdate))? "" : $fromdate;
        $fromdate =  JHTML::_('calendar', $fromdate, 'fromdate', 'fromdate', '%d.%m.%Y', array('size'=>'7',  'maxlength'=>'10'));
        $this->assignRef('fromdate', $fromdate);
        $todate = $model->getState('todate');
        $todate = (empty($todate))? "" : $todate;
        $todate =  JHTML::_('calendar', $todate, 'fromdate', 'fromdate', '%d.%m.%Y', array('size'=>'7',  'maxlength'=>'10'));
        $this->assignRef('todate', $todate);

        $attribs = array();
        $attribs['class'] = "thm_organizer_el_sortLink hasTip";
        $spanOpen = "<span class='thm_organizer_el_th'>";
        $spanClose = "</span>";
        $ascImage= JHTML::_('image.site', 'sort_asc.png', 'media/system/images/', NULL, NULL, JText::_( 'Aufsteigend Sortieren' ));
        $descImage= JHTML::_('image.site', 'sort_desc.png', 'media/system/images/', NULL, NULL, JText::_( 'Absteigend Sortieren' ));

        $titleText = JText::_('COM_THM_ORGANIZER_EL_TITLE');
        $titleAttribs = array();
        $titleAttribs['title'] = JText::_('COM_THM_ORGANIZER_EL_SORT');
        $titleLink = "javascript:reSort(";
        if($this->orderby == 'title' and $this->orderbydir == 'ASC')
        {
            $titleText .= $ascImage;
            $titleAttribs['title'] .= "::".JText::_('COM_THM_ORGANIZER_EL_DESC_DESCRIPTION');
            $titleLink .= '"title", "DESC")';
        }
        else
        {
            $titleText .= ($this->orderby == 'title')? $descImage : "";
            $titleAttribs['title'] .= "::".JText::_('COM_THM_ORGANIZER_EL_ASC_DESCRIPTION');
            $titleLink .= '"title", "ASC")';
        }
        $titleAttribs = array_merge($titleAttribs, $attribs);
        $titleHead = $spanOpen.JHTML::_('link', $titleLink, $titleText, $titleAttribs).$spanClose;
        $this->assignRef('titleHead', $titleHead);


        $authorText = JText::_('COM_THM_ORGANIZER_EL_AUTHOR');
        $authorAttribs = array();
        $authorAttribs['title'] = JText::_('COM_THM_ORGANIZER_EL_SORT');
        $authorLink = "javascript:reSort(";
        if($this->orderby == 'author' and $this->orderbydir == 'ASC')
        {
            $authorText .= $ascImage;
            $authorAttribs['title'] .= "::".JText::_('COM_THM_ORGANIZER_EL_DESC_DESCRIPTION');
            $authorLink .= '"author", "DESC")';
        }
        else
        {
            $authorText .= ($this->orderby == 'author')? $descImage : "";
            $authorAttribs['title'] .= "::".JText::_('COM_THM_ORGANIZER_EL_ASC_DESCRIPTION');
            $authorLink .= '"author", "ASC")';
        }
        $authorAttribs = array_merge($authorAttribs, $attribs);
        $authorHead = $spanOpen.JHTML::_('link', $authorLink, $authorText, $authorAttribs).$spanClose;
        $this->assignRef('authorHead', $authorHead);

        $resourceHead = $spanOpen.JText::_('COM_THM_ORGANIZER_EL_RESOURCE').$spanClose;
        $this->assignRef('resourceHead', $resourceHead);

        $categoryText = JText::_('COM_THM_ORGANIZER_EL_CATEGORY');
        $categoryAttribs = array();
        $categoryAttribs['title'] = JText::_('COM_THM_ORGANIZER_EL_SORT');
        $categoryLink = "javascript:reSort(";
        if($this->orderby == 'category' and $this->orderbydir == 'ASC')
        {
            $categoryText .= $ascImage;
            $categoryAttribs['title'] .= "::".JText::_('COM_THM_ORGANIZER_EL_DESC_DESCRIPTION');
            $categoryLink .= '"category", "DESC")';
        }
        else
        {
            $categoryText .= ($this->orderby == 'category')? $descImage : "";
            $categoryAttribs['title'] .= "::".JText::_('COM_THM_ORGANIZER_EL_ASC_DESCRIPTION');
            $categoryLink .= '"category", "ASC")';
        }
        $categoryAttribs = array_merge($categoryAttribs, $attribs);
        $categoryHead = $spanOpen.JHTML::_('link', $categoryLink, $categoryText, $categoryAttribs).$spanClose;
        $this->assignRef('categoryHead', $categoryHead);

        $dateText = JText::_('COM_THM_ORGANIZER_EL_DATE');
        $dateAttribs = array();
        $dateAttribs['title'] = JText::_('COM_THM_ORGANIZER_EL_SORT');
        $dateLink = "javascript:reSort(";
        if($this->orderby == 'date' and $this->orderbydir == 'ASC')
        {
            $dateText .= $ascImage;
            $dateAttribs['title'] .= "::".JText::_('COM_THM_ORGANIZER_EL_DESC_DESCRIPTION');
            $dateLink .= '"date", "DESC")';
        }
        else
        {
            $dateText .= ($this->orderby == 'date')? $descImage : "";
            $dateAttribs['title'] .= "::".JText::_('COM_THM_ORGANIZER_EL_ASC_DESCRIPTION');
            $dateLink .= '"date", "ASC")';
        }
        $dateAttribs = array_merge($dateAttribs, $attribs);
        $dateHead = $spanOpen.JHTML::_('link', $dateLink, $dateText, $dateAttribs).$spanClose;
        $this->assignRef('dateHead', $dateHead);
    }

    private function makeCategorySelect($categories, $selected)
    {
        //echo "<pre>".print_r($categories, true)."</pre>";
        $nocategories = array(1=>array('id'=>'-1','title'=>JText::_('Alle Kategorien')));
        $categories = array_merge($nocategories, $categories);
        $categorySelect = JHTML::_('select.genericlist', $categories, 'category[]','id="category" class="inputbox" size="1"', 'id', 'title', $selected );
        $this->assignRef('categorySelect', $categorySelect);
    }
    /*
                <button onclick="document.getElementById('thm_organizer_el_form').submit();">
                    <?php echo JText::_( 'Los' ); ?>
                </button>
                <input type="submit"
                       onclick="document.getElementById('filter').value='';
                                 document.getElementById('date').value=''
                                 document.getElementById('thm_organizer_el_form').submit();"
                       value="<?php echo JText::_( 'Reset' ); ?>" >*/
}