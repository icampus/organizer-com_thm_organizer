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
require_once JPATH_ROOT . '/media/com_thm_organizer/templates/edit_tabbed.php';
?>
    <script type='text/javascript' charset='utf-8'>
        var defaultDepartment = <?php echo JComponentHelper::getParams('com_thm_organizer')->get('department'); ?>;
    </script>
<?php
THM_OrganizerTemplateEdit_Tabbed::render($this);
