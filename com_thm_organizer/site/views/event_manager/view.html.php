<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewEvent_manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
jimport('jquery.jquery');

/**
 * Build event list
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewEvent_Manager extends JViewLegacy
{
    /**
     * Loads model data into context and sets variables used for html output
     *
     * @param   string  $tpl  the template to be used
     *
     * @return void
     */
    public function display($tpl = null)
    {
        JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/event_manager.js'));

        $model = $this->getModel();

        $this->form = $this->get('Form');

        $events = $model->events;
        $this->assign('events', $events);
        $display_type = $model->display_type;
        $this->assign('display_type', $display_type);
 
        $categories = $model->categories;
        $this->assignRef('categories', $categories);
        $categoryID = ($model->getState('categoryID'))? $model->getState('categoryID') : - 1;
        $this->assignRef('categoryID', $categoryID);
        $this->makeCategorySelect($categories, $categoryID);

        $canWrite = $model->canWrite;
        $this->assignRef('canWrite', $canWrite);
        $canEdit = $model->canEdit;
        $this->assignRef('canEdit', $canEdit);
        $this->assign('itemID', JRequest::getInt('Itemid'));

        $total = $model->total;
        $this->assign('total', $total);
 
        // Create the pagination object
        $pageNav = $model->pagination;
        $this->assign('pageNav', $pageNav);

        // Form state variables
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

    /**
     * Build HTML elements from saved data
     *
     * @return void
     */
    private function buildHTMLElements()
    {
        $newImage = JHtml::image('components/com_thm_organizer/assets/images/add.png', JText::_('COM_THM_ORGANIZER_NEW_TITLE'), null, null, null);
        $this->assignRef('newImage', $newImage);

        $editImage = JHtml::image('components/com_thm_organizer/assets/images/edit.png', JText::_('COM_THM_ORGANIZER_EDIT_TITLE'), null, null, null);
        
        $this->assignRef('editImage', $editImage);
        $deleteImage = JHtml::image('components/com_thm_organizer/assets/images/delete.png', JText::_('COM_THM_ORGANIZER_DELETE_TITLE'), null, null, null);
        $this->assignRef('deleteImage', $deleteImage);

        $attribs = array();
        $attribs['class'] = "thm_organizer_el_sortLink hasTip";
        $spanOpen = "<span class='thm_organizer_el_th'>";
        $spanClose = "</span>";
        $ascImage = JHtml::image('media/system/images/sort_asc.png', JText::_('COM_THM_ORGANIZER_EL_ASC_DESCRIPTION'), null, null, null);
        $descImage = JHtml::image('media/system/images/sort_desc.png', JText::_('COM_THM_ORGANIZER_EL_DESC_DESCRIPTION'), null, null, null);

        $titleText = JText::_('COM_THM_ORGANIZER_EL_TITLE');
        $titleAttribs = array();
        $titleAttribs['title'] = JText::_('COM_THM_ORGANIZER_EL_SORT');
        $titleLink = "javascript:reSort(";
        if ($this->orderby == 'title' and $this->orderbydir == 'ASC')
        {
            $titleText .= $ascImage;
            $titleAttribs['title'] .= "::" . JText::_('COM_THM_ORGANIZER_EL_DESC_DESCRIPTION');
            $titleLink .= "'title', 'DESC')";
        }
        else
        {
            $titleText .= ($this->orderby == 'title')? $descImage : "";
            $titleAttribs['title'] .= "::" . JText::_('COM_THM_ORGANIZER_EL_ASC_DESCRIPTION');
            $titleLink .= "'title', 'ASC')";
        }
        $titleAttribs = array_merge($titleAttribs, $attribs);
        $titleHead = $spanOpen . JHtml::_('link', $titleLink, $titleText, $titleAttribs) . $spanClose;
        $this->assignRef('titleHead', $titleHead);


        $authorText = JText::_('COM_THM_ORGANIZER_EL_AUTHOR');
        $authorAttribs = array();
        $authorAttribs['title'] = JText::_('COM_THM_ORGANIZER_EL_SORT');
        $authorLink = "javascript:reSort(";
        if ($this->orderby == 'author' and $this->orderbydir == 'ASC')
        {
            $authorText .= $ascImage;
            $authorAttribs['title'] .= "::" . JText::_('COM_THM_ORGANIZER_EL_DESC_DESCRIPTION');
            $authorLink .= "'author', 'DESC')";
        }
        else
        {
            $authorText .= ($this->orderby == 'author')? $descImage : "";
            $authorAttribs['title'] .= "::" . JText::_('COM_THM_ORGANIZER_EL_ASC_DESCRIPTION');
            $authorLink .= "'author', 'ASC')";
        }
        $authorAttribs = array_merge($authorAttribs, $attribs);
        $authorHead = $spanOpen . JHtml::_('link', $authorLink, $authorText, $authorAttribs) . $spanClose;
        $this->assignRef('authorHead', $authorHead);

        $resourceHead = $spanOpen . JText::_('COM_THM_ORGANIZER_EL_RESOURCE') . $spanClose;
        $this->assignRef('resourceHead', $resourceHead);

        $categoryText = JText::_('COM_THM_ORGANIZER_EL_CATEGORY');
        $categoryAttribs = array();
        $categoryAttribs['title'] = JText::_('COM_THM_ORGANIZER_EL_SORT');
        $categoryLink = "javascript:reSort(";
        if ($this->orderby == 'eventCategory' and $this->orderbydir == 'ASC')
        {
            $categoryText .= $ascImage;
            $categoryAttribs['title'] .= "::" . JText::_('COM_THM_ORGANIZER_EL_DESC_DESCRIPTION');
            $categoryLink .= "'eventCategory', 'DESC')";
        }
        else
        {
            $categoryText .= ($this->orderby == 'eventCategory')? $descImage : "";
            $categoryAttribs['title'] .= "::" . JText::_('COM_THM_ORGANIZER_EL_ASC_DESCRIPTION');
            $categoryLink .= "'eventCategory', 'ASC')";
        }
        $categoryAttribs = array_merge($categoryAttribs, $attribs);
        $categoryHead = $spanOpen . JHtml::_('link', $categoryLink, $categoryText, $categoryAttribs) . $spanClose;
        $this->assignRef('categoryHead', $categoryHead);

        $dateText = JText::_('COM_THM_ORGANIZER_EL_DATE');
        $dateAttribs = array();
        $dateAttribs['title'] = JText::_('COM_THM_ORGANIZER_EL_SORT');
        $dateLink = "javascript:reSort(";
        if ($this->orderby == 'date' and $this->orderbydir == 'ASC')
        {
            $dateText .= $ascImage;
            $dateAttribs['title'] .= "::" . JText::_('COM_THM_ORGANIZER_EL_DESC_DESCRIPTION');
            $dateLink .= "'date', 'DESC')";
        }
        else
        {
            $dateText .= ($this->orderby == 'date')? $descImage : "";
            $dateAttribs['title'] .= "::" . JText::_('COM_THM_ORGANIZER_EL_ASC_DESCRIPTION');
            $dateLink .= "'date', 'ASC')";
        }
        $dateAttribs = array_merge($dateAttribs, $attribs);
        $dateHead = $spanOpen . JHtml::_('link', $dateLink, $dateText, $dateAttribs) . $spanClose;
        $this->assignRef('dateHead', $dateHead);
    }

    /**
     * Method to build the category selection
     *
     * @param   object  $categories  the categories to be used
     * @param   object  $selected    the selected category
     *
     * @return void
     */
    private function makeCategorySelect($categories, $selected)
    {
        $nocategories = array(1 => array('id' => '-1', 'title' => JText::_('COM_THM_ORGANIZER_EL_ALL_CATEGORIES')));
        $categories = array_merge($nocategories, $categories);
        $categorySelect = JHtml::_('select.genericlist', $categories, 'categoryID',
                 'id="categoryID" class="inputbox" size="1"', 'id', 'title', $selected
                );
        $this->assignRef('categorySelect', $categorySelect);
    }
}
