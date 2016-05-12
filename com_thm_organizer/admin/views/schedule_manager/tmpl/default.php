<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @description default view template file for schedule lists
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined("_JEXEC") or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/templates/list.php';
THM_OrganizerTemplateList::render($this);