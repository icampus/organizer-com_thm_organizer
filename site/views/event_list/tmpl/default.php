<?php
defined('_JEXEC') or die('Restricted access');
$rowcount = 0;
?>
<div id="thm_organizer_el">
    <form id='thm_organizer_el_form' name='thm_organizer_el_form' enctype='multipart/form-data' method='post'
          action='<?php echo JRoute::_("index.php?option=com_thm_organizer&view=event_list"); ?>' >
        <div id="thm_organizer_el_top_div" >
            <?php if($this->categoryID != -1) { ?>
            <div id="thm_organizer_el_category_desc_div">
            <?php foreach($this->categories as $category){ if($category['id'] == $this->categoryID){ ?>
                <h2><?php echo $category['title']; ?></h2>
                <?php if(isset($category['description'])){ echo $category['description']; } ?>
            <?php break; }} ?>
            </div>
            <?php } ?>
            <div class="thm_organizer_el_action_div">
                <?php if($this->canWrite): ?>
                <a  class="hasTip thm_organizer_action_link"
                    title="<?php echo JText::_('COM_THM_ORGANIZER_NEW_TITLE')."::".JText::_('COM_THM_ORGANIZER_NEW_DESCRIPTION');?>"
                    onClick="submitForm('events.new');" >
                    <span id="thm_organizer_new_span" class="thm_organizer_action_span"></span>
                    <?php echo JText::_('COM_THM_ORGANIZER_NEW'); ?>
                </a>
                <?php endif; if($this->canEdit): ?>
                <a  class="hasTip thm_organizer_action_link"
                    title="<?php echo JText::_('COM_THM_ORGANIZER_EDIT_TITLE')."::".JText::_('COM_THM_ORGANIZER_EDIT_DESCRIPTION');?>"
                    onClick="submitForm('events.edit');">
                    <span id="thm_organizer_edit_span" class="thm_organizer_action_span"></span>
                    <?php echo JText::_('COM_THM_ORGANIZER_EDIT'); ?>
                </a>
                <a  class="hasTip thm_organizer_action_link"
                    title="<?php echo JText::_('COM_THM_ORGANIZER_DELETE_TITLE')."::".JText::_('COM_THM_ORGANIZER_DELETE_DESCRIPTION');?>"
                    onClick="submitForm('events.delete');">
                    <span id="thm_organizer_delete_span" class="thm_organizer_action_span"></span>
                    <?php echo JText::_('COM_THM_ORGANIZER_DELETE'); ?>
                </a>
                <?php endif; if($this->canWrite or $this->canEdit): ?>
                <span id="thm_organizer_el_divider_span" class="thm_organizer_divider_span"></span>
                <?php endif; ?>
                <button type="submit" class="hasTip thm_organizer_action_button"
                    title="<?php echo JText::_('COM_THM_ORGANIZER_SUBMIT_TITLE')."::".JText::_('COM_THM_ORGANIZER_SUBMIT_DESCRIPTION');?>"
                    onClick="document.getElementById('thm_organizer_el_form').submit();">
                    <span id="thm_organizer_submit_span" class="thm_organizer_action_span"></span>
                    <?php echo JText::_('COM_THM_ORGANIZER_SUBMIT'); ?>
                </button>
                <button type="button"  class="hasTip thm_organizer_action_button"
                    title="<?php echo JText::_('COM_THM_ORGANIZER_RESET_TITLE')."::".JText::_('COM_THM_ORGANIZER_RESET_DESCRIPTION');?>"
                    onclick="resetForm()">
                    <span id="thm_organizer_back_span" class="thm_organizer_action_span"></span>
                    <?php echo JText::_('COM_THM_ORGANIZER_RESET'); ?>
                </button>
            </div>
        </div>
        <div id="thm_organizer_el_form_div" >
            <div id='thm_organizer_el_search_div'>
                <span class="thm_organizer_el_label_span" >
                    <?php echo $this->form->getLabel('thm_organizer_el_search_text'); ?>
                </span>
                <?php echo $this->form->getInput('thm_organizer_el_search_text'); ?>
                <?php if($this->display_type != 1 and $this->display_type != 5): ?>
                <span class="thm_organizer_el_label_span" >
                    <?php echo $this->form->getLabel('categoryDummy'); ?>
                    <?php echo $this->categorySelect; ?>
                </span>
                <?php endif; ?>
                <span class="thm_organizer_el_label_span" >
                    <?php echo $this->form->getLabel('fromdate'); ?>
                </span>
                <?php echo $this->form->getInput('fromdate'); ?>
                <span class="thm_organizer_el_label_span" >
                    <?php echo $this->form->getLabel('todate'); ?>
                </span>
                <?php echo $this->form->getInput('todate'); ?>
                <span class="thm_organizer_el_label_span" >
                    <label for="limit"
                           title="<?php echo JText::_('COM_THM_ORGANIZER_EL_COUNT_TITLE')."::".JText::_('COM_THM_ORGANIZER_EL_COUNT_DESCRIPTION');?>">
                           <?php echo JText::_('COM_THM_ORGANIZER_EL_COUNT'); ?>
                    </label>
                </span>
                <?php echo $this->pageNav->getLimitBox(); ?>
                <span id="thm_organizer_el_count2">
                    <?php echo JText::_('COM_THM_ORGANIZER_EL_COUNT_COUNT2'); ?>
                </span>
            </div>
    <?php if(count($this->events) > 0){ ?>
            <div id="thm_organizer_el_events_div" >
                <table id="thm_organizer_el_eventtable">
                    <colgroup>
                        <col id="thm_organizer_el_col_check" />
                        <col id="thm_organizer_el_col_title" />
                        <?php if($this->display_type != 3 and $this->display_type != 7): ?>
                        <col id="thm_organizer_el_col_author" />
                        <?php endif; if($this->display_type != 2 and $this->display_type != 6): ?>
                        <col id="thm_organizer_el_col_room" />
                        <?php endif; if($this->display_type != 1 and $this->display_type != 5): ?>
                        <col id="thm_organizer_el_col_category" />
                        <?php endif; ?>
                        <col id="thm_organizer_el_col_date" />
                    </colgroup>
                    <thead>
                        <tr>
                            <?php if($this->canEdit): ?>
                            <th align="left">
                                <input type="checkbox" name="eventIDs[]" value="0" onclick="checkAll()" />
                            </th>
                            <?php else: ?>
                            <th />
                            <?php endif;?>
                            <th id="thm_organizer_el_eventtitlehead"><?php echo $this->titleHead; ?></th>
                            <?php if($this->display_type != 3 and $this->display_type != 7): ?>
                            <th id="thm_organizer_el_eventauthorhead"><?php echo $this->authorHead; ?></th>
                            <?php endif; if($this->display_type != 2 and $this->display_type != 6): ?>
                            <th id="thm_organizer_el_eventroomhead"><?php echo $this->resourceHead; ?></th>
                            <?php endif; if($this->display_type != 1 and $this->display_type != 5): ?>
                            <th id="thm_organizer_el_eventcathead"><?php echo $this->categoryHead; ?></th>
                            <?php endif; ?>
                            <th id="thm_organizer_el_eventdthead"><?php echo $this->dateHead; ?></th>
                        </tr>
                    </thead>
                    <?php foreach($this->events as $event){
                        $rowclass = ($rowcount % 2 === 0)? "thm_organizer_el_row_even" : "thm_organizer_el_row_odd"; ?>
                    <tr class="<?php echo $rowclass; ?>">
                        <?php if($event['userCanEdit']): ?>
                        <td class="thm_organizer_ce_checkbox">
                            <input type="checkbox" name="eventIDs[]" value="<?php echo $event['id']; ?>">
                        </td>
                        <?php else: ?>
                        <td />
                        <?php endif; ?>
                        <td>
                            <span class="thm_organizer_el_eventtitle hasTip"
                                  title="<?php echo JText::_('COM_THM_ORGANIZER_EL_EVENT_TITLE')."::".JText::_('COM_THM_ORGANIZER_EL_EVENT_DESCRIPTION');?>">
                                <a href="<?php echo $event['detailsLink'].$this->itemID; ?>">
                                    <?php echo $event['title']; ?>
                                </a>
                            </span>
                        </td>
                        <?php if($this->display_type != 3 and $this->display_type != 7): ?>
                        <td>
                            <span><?php echo $event['author']; ?></span>
                        </td>
                        <?php endif; if($this->display_type != 2 and $this->display_type != 6): ?>
                        <td>
                            <span class="thm_organizer_el_eventroom hasTip"
                                  title="Termin Ressourcen::Ressourcen, die von diesem Termin betroffen sind.">
                                    <?php echo $event['resources']; ?>
                            </span>
                        </td>
                        <?php endif; if($this->display_type != 1 and $this->display_type != 5): ?>
                        <td>
                            <span class="thm_organizer_el_eventcat hasTip"
                                  title="Kategorie Ansicht::Events dieser Kategorie betrachten.">
                                <a href="<?php echo $event['categoryLink'].$this->itemID; ?>">
                                    <?php echo $event['eventCategory']; ?>
                                </a>
                            </span>
                        </td>
                        <?php endif; ?>
                        <td>
                            <span class="thm_organizer_el_eventdt">
                                    <?php echo $event['displayDates']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php $rowcount++; } ?>
                </table>
            </div>
            <?php }else{ ?>
            <br />
            <h4><?php echo JText::_("Keine Events erf&uuml;llen die Suchkriterien"); ?></h4>
            <?php } ?>
        </div>
        <div class="pageslinks"><?php echo $this->pageNav->getPagesLinks(); ?></div>
        <input type="hidden" id="orderby" name="orderby" value="<?php echo $this->orderby; ?>" />
        <input type="hidden" id="orderbydir" name="orderbydir" value="<?php echo $this->orderbydir; ?>" />
        <input type="hidden" id="itemID" name="Itemid" value="<?php echo $this->itemID; ?>" />
        <input type="hidden" id="task" name="task" value="" />
    </form>
</div>
