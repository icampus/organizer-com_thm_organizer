<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view degree program edit view edit layout
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
JHtml::_('behavior.tooltip');
if (!empty($this->children))
{
    $maxOrdering = max(array_keys($this->children));
}
$rawPoolURL = 'index.php?option=com_thm_organizer&view=pool_manager';
$poolURL = JRoute::_($rawPoolURL, false);
$rawSubjectURL = 'index.php?option=com_thm_organizer&view=subject_manager';
$subjectURL = JRoute::_($rawSubjectURL, false);
?>
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=program_edit&id=' . $this->form->getValue('id')); ?>"
      method="post" name="adminForm" id="modulmapping-form">
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_PRM_PROPERTIES'); ?></legend>
        <ul class="adminformlist">
            <li>
                <?php echo $this->form->getLabel('lsfFieldID'); ?>
                <?php echo $this->form->getInput('lsfFieldID'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('degreeID'); ?>
                <?php echo $this->form->getInput('degreeID'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('version'); ?>
                <?php echo $this->form->getInput('version'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('fieldID'); ?>
                <?php echo $this->form->getInput('fieldID'); ?>
            </li>
        </ul>
    </fieldset>
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_PROPERTIES_DE'); ?></legend>
        <ul class="adminformlist">
            <li>
                <?php echo $this->form->getLabel('subject_de'); ?>
                <?php echo $this->form->getInput('subject_de'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('description_de'); ?>
                <?php echo $this->form->getInput('description_de'); ?>
            </li>
        </ul>
    </fieldset>
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_PROPERTIES_EN'); ?></legend>
        <ul class="adminformlist">
            <li>
                <?php echo $this->form->getLabel('subject_en'); ?>
                <?php echo $this->form->getInput('subject_en'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('description_en'); ?>
                <?php echo $this->form->getInput('description_en'); ?>
            </li>
        </ul>
    </fieldset>
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_CHILDREN'); ?></legend>
        <div class="thm_organizer_children">
<?php
if (!empty($this->children))
{
?>
            <table id="childList" class="adminlist">
                <thead>
                    <tr>
                        <th>
                            <?php echo JText::_('COM_THM_ORGANIZER_NAME'); ?>
                        </th>
                        <th class="thm_organizer_pools_ordering">
                            <?php echo JText::_('COM_THM_ORGANIZER_CHILD_ORDER'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
<?php
    for ($index = 1; $index <= $maxOrdering; $index++)
    {
        if (isset($this->children[$index]))
        {
            $name = $this->children[$index]['name'];
            $id = $this->children[$index]['id'];
            if (!empty($this->children[$index]['poolID']))
            {
                $rawEditURL = 'index.php?option=com_thm_organizer&view=pool_edit&id=' . $this->children[$index]['poolID'];
            }
            else
            {
                $rawEditURL = 'index.php?option=com_thm_organizer&view=subject_edit&id=' . $this->children[$index]['subjectID'];
            }
            $editURL = JRoute::_($rawEditURL, false);
        }
        else
        {
            $editURL = $name = $id = '';
        }
?>
                    <tr id="childRow<?php echo $index; ?>"
                        class="row<?php echo $index % 2; ?>">
                        <td>
                            <a id="child<?php echo $index; ?>link"
                               href="<?php echo $editURL; ?>">
                                <span id="child<?php echo $index; ?>name">
                                    <?php echo $name ?>
                                </span>
                            </a>
                            <input type="hidden"
                                   name="child<?php echo $index; ?>"
                                   id="child<?php echo $index; ?>"
                                   value="<?php echo $id;?>" />
                        </td>
                        <td class="order">
                            <span>
                                <a class="jgrid" href="javascript:void(0);"
                                   onclick="moveUp('<?php echo $index; ?>');" title="Move Up">
                                    <span class="state uparrow">
                                        <span class="text"><?php echo JText::_('JLIB_HTML_MOVE_UP'); ?></span>
                                    </span>
                                </a>
                            </span>
                            <span>
                                <a class="jgrid"
                                   href="javascript:void(0);"
                                   onclick="moveDown('<?php echo $index; ?>');"
                                   title="Move Down">
                                    <span class="state downarrow">
                                        <span class="text"><?php echo JText::_('JLIB_HTML_MOVE_DOWN'); ?></span>
                                    </span>
                                </a>
                            </span>
                            <span>
                                <a class="jgrid"
                                   href="javascript:void(0);"
                                   onclick="setEmptyElement('<?php echo $index; ?>');"
                                   title="Add Empty Element">
                                    <span class="icon-16-newlevel">
                                        <span class="text"><?php echo JText::_('JLIB_HTML_MOVE_DOWN'); ?></span>
                                    </span>
                                </a>
                            </span>
                            <span>
                                <a class="jgrid"
                                   href="javascript:void(0);"
                                   onclick="setElementOnLastPosition('<?php echo $index; ?>');"
                                   title="Set On Last Position">
                                    <span class="icon-16-clear">
                                        <span class="text"><?php echo JText::_('JLIB_HTML_MOVE_UP'); ?></span>
                                    </span>
                                </a>
                            </span>
                            <input type="text"
                                   title="Ordering"
                                   name="child<?php echo $index; ?>order"
                                   id="child<?php echo $index; ?>order"
                                   size="2" value="<?php echo $index;?>"
                                   class="text-area-order"
                                   onChange="orderWithNumber(<?php echo $index; ?>);"/>
                            <a class="thm_organizer_delete_child"
                               href="javascript:void(0);"
                               title="<?php echo JText::_('COM_THM_ORGANIZER_MAPPING_DELETE'); ?>"
                               onClick="removeRow(<?php echo $index; ?>);">
                            </a>
                        </td>
                    </tr>
<?php
    }
?>
                </tbody>
            </table>
<?php
}
else
{
    echo "<span class='thm_organizer_no_children'>" . JText::_('COM_THM_ORGANIZER_NO_CHILDREN') . "</span>";
}
?>
        </div>
        <div class="thm_organizer_pools">
            <a href="<?php echo $poolURL; ?>">
                <?php echo JText::_('COM_THM_ORGANIZER_ADD_POOLS'); ?>
            </a>
        </div>
        <div class="thm_organizer_subjects">
            <a href="<?php echo $subjectURL; ?>">
                <?php echo JText::_('COM_THM_ORGANIZER_ADD_SUBJECTS'); ?>
            </a>
        </div>
    </fieldset>
    <div>
        <?php echo $this->form->getInput('id'); ?>
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
