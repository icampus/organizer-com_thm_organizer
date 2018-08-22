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
$mobile      = $this->isMobile ? 'mobile' : '';
$displayName = empty($this->model->displayName) ?
    'THM Organizer  - ' . JText::_('COM_THM_ORGANIZER_SCHEDULES')
    : JText::_('COM_THM_ORGANIZER_SCHEDULE') . ' - ' . $this->model->displayName;
?>

<div class="organizer <?php echo $mobile; ?>">

    <div class="page-header">
        <h2><?php echo $displayName; ?></h2>
    </div>

    <div class="menu-bar">
        <ul class="tabs" role="tablist">
            <li class="tabs-tab active" role="presentation">
                <a href="#schedule-form" class="tabs-toggle" id="tab-schedule-form"
                   data-toggle="tab" data-id="schedule-form" role="tab" aria-controls="schedule-form"
                   aria-selected="true">
                    <span class="icon-schedules"></span>
                    <span class="tab-text"><?php echo JText::_("COM_THM_ORGANIZER_SCHEDULES"); ?></span>
                </a>
            </li>
            <li class="tabs-tab" role="presentation">
                <a href="#selected-schedules" class="tabs-toggle" id="tab-selected-schedules"
                   data-toggle="tab" data-id="selected-schedules" role="tab" aria-controls="selected-schedules"
                   aria-selected="true">
                    <span class="icon-checkbox-checked"></span>
                    <span class="tab-text"><?php echo JText::_("COM_THM_ORGANIZER_SELECTED"); ?></span>
                </a>
            </li>
            <li class="tabs-tab" role="presentation">
                <a href="#time-selection" class="tabs-toggle" id="tab-time-selection"
                   data-toggle="tab" data-id="time-selection" role="tab" aria-controls="time-selection"
                   aria-selected="true">
                    <span class="icon-grid-2"></span>
                    <span class="tab-text"><?php echo JText::_("COM_THM_ORGANIZER_GRID_MANAGER_TITLE"); ?></span>
                </a>
            </li>
            <li class="date-input-list-item">
                <div class="date-input">
                    <button id="previous-month" class="controls" type="button"
                            onclick="scheduleApp.getCalendar().changeSelectedDate(false, 'month');">
                        <span class="icon-arrow-left-22"></span>
                    </button>
                    <button id="previous-week" class="controls" type="button"
                            onclick="scheduleApp.getCalendar().changeSelectedDate(false, 'week');">
                        <span class="icon-arrow-left"></span>
                    </button>
                    <input id="date" type="text" required onchange="scheduleApp.updateSchedule('date');"/>
                    <button id="calendar-icon" type="button" class="controls"
                            onclick="scheduleApp.getCalendar().showCalendar();">
                        <span class="icon-calendar"></span>
                    </button>
                    <div id="calendar">
                        <table id="calendar-table">
                            <thead>
                            <tr>
                                <td colspan="1">
                                    <button id="calendar-previous-month" type="button"
                                            onclick="scheduleApp.getCalendar().changeCalendarMonth(false);">
                                        <span class="icon-arrow-left"></span>
                                    </button>
                                </td>
                                <td colspan="5">
                                    <span id="display-month"></span> <span id="display-year"></span>
                                </td>
                                <td colspan="1">
                                    <button id="calendar-next-month" type="button"
                                            onclick="scheduleApp.getCalendar().changeCalendarMonth(true);">
                                        <span class="icon-arrow-right"></span>
                                    </button>
                                </td>
                            </tr>
                            </thead>
                            <thead>
                            <tr>
                                <td><?php echo JText::_("MON"); ?></td>
                                <td><?php echo JText::_("TUE"); ?></td>
                                <td><?php echo JText::_("WED"); ?></td>
                                <td><?php echo JText::_("THU"); ?></td>
                                <td><?php echo JText::_("FRI"); ?></td>
                                <td><?php echo JText::_("SAT"); ?></td>
                                <td><?php echo JText::_("SUN"); ?></td>
                            </tr>
                            </thead>
                            <tbody>
                            <!-- generated code with JavaScript -->
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="7">
                                    <button id="today" type="button" class="today"
                                            onclick="scheduleApp.getCalendar().changeSelectedDate(true, 'week');">
                                        <?php echo JText::_("COM_THM_ORGANIZER_TODAY"); ?>
                                    </button>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                    <button id="next-week" class="controls" type="button"
                            onclick="scheduleApp.getCalendar().changeSelectedDate(true, 'week');">
                        <span class="icon-arrow-right"></span>
                    </button>
                    <button id="next-month" class="controls" type="button"
                            onclick="scheduleApp.getCalendar().changeSelectedDate(true, 'month');">
                        <span class="icon-arrow-right-22"></span>
                    </button>
                </div>
            </li>
            <li class="tabs-tab" role="presentation">
                <a href="#exports" class="tabs-toggle" id="tab-exports" data-toggle="tab"
                   data-id="exports" role="tab" aria-controls="exports" aria-selected="true">
                    <span class="icon-download"></span>
                    <span class="tab-text"><?php echo JText::_("COM_THM_ORGANIZER_FILE_FORMAT"); ?></span>
                </a>
            </li>
        </ul>

        <!-- Menu -->
        <div class="tab-content">

            <div class="tab-panel selection active" id="schedule-form" role="tabpanel"
                 aria-labelledby="tab-schedule-form" aria-hidden="false">
                <?php
                if (!empty($this->model->params['showDepartments'])) {
                    ?>
                    <div id="department-input" class="input-wrapper">
                        <select id="department" multiple data-input="static"
                                data-placeholder="<?php echo JText::_("COM_THM_ORGANIZER_DEPARTMENT_SELECT_PLACEHOLDER"); ?>">
                            <?php
                            foreach ($this->getModel()->departments as $id => $department) {
                                echo "<option value='" . $id . "'>$department</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <?php
                }
                ?>
                <div id="category-input" class="input-wrapper">
                    <select id="category" required data-input="static"
                            data-placeholder="<?php echo JText::_("COM_THM_ORGANIZER_SELECT_CATEGORY"); ?>">
                        <?php
                        if (!empty($this->model->params['showPrograms'])) {
                            echo '<option value="program" selected>' . JText::_("COM_THM_ORGANIZER_PROGRAMS") . '</option>';
                        }

                        if (!empty($this->model->params['showRooms'])) {
                            echo '<option value="roomType">' . JText::_("COM_THM_ORGANIZER_ROOM_PLANS") . '</option>';
                        }

                        if (!empty($this->model->params['showTeachers'])) {
                            echo '<option value="teacher">' . JText::_("COM_THM_ORGANIZER_TEACHERPLAN") . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div id="program-input" class="input-wrapper">
                    <select id="program" data-next="pool"
                            data-placeholder="<?php echo JText::_("COM_THM_ORGANIZER_PROGRAM_SELECT_PLACEHOLDER"); ?>">
                        <!-- filled by ajax -->
                    </select>
                </div>
                <div id="pool-input" class="input-wrapper">
                    <select id="pool" data-next="lesson"
                            data-placeholder="<?php echo JText::_("COM_THM_ORGANIZER_POOL_SELECT_PLACEHOLDER"); ?>">
                        <!-- filled by ajax -->
                    </select>
                </div>
                <div id="roomType-input" class="input-wrapper">
                    <select id="roomType" data-next="room"
                            data-placeholder="<?php echo JText::_("COM_THM_ORGANIZER_ROOM_TYPE_SELECT_PLACEHOLDER"); ?>">
                        <!-- filled by ajax -->
                    </select>
                </div>
                <div id="room-input" class="input-wrapper">
                    <select id="room" data-next="lesson"
                            data-placeholder="<?php echo JText::_("COM_THM_ORGANIZER_ROOM_SELECT_PLACEHOLDER"); ?>">
                        <!-- filled by ajax -->
                    </select>
                </div>
                <div id="teacher-input" class="input-wrapper">
                    <select id="teacher" data-next="lesson"
                            data-placeholder="<?php echo JText::_("COM_THM_ORGANIZER_TEACHER_SELECT_PLACEHOLDER"); ?>">
                        <!-- filled by ajax -->
                    </select>
                </div>
            </div>

            <div class="tab-panel" id="selected-schedules" role="tabpanel"
                 aria-labelledby="tab-selected-schedules" aria-hidden="false">
            </div>

            <div class="tab-panel" id="time-selection" role="tabpanel" aria-labelledby="tab-time" aria-hidden="false">
                <select id="grid" required onchange="scheduleApp.changeGrid();">
                    <?php
                    foreach ($this->getModel()->grids as $grid) {
                        $selected = ($grid->name == $this->defaultGrid->name) ? 'selected' : '';
                        echo "<option value='" . $grid->id . "' $selected >$grid->name</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="tab-panel" id="exports" role="tabpanel" aria-labelledby="tab-exports-menu" aria-hidden="false">
                <div class="link-item">
                    <a onclick="scheduleApp.handleExport('pdf.a4');">
                        <span class="icon-file-pdf"></span>
                        <?php echo JText::_('COM_THM_ORGANIZER_PDF_DOCUMENT'); ?>
                    </a>
                </div>
                <div class="link-item">
                    <a onclick="scheduleApp.handleExport('xls.si');">
                        <span class="icon-file-xls"></span>
                        <?php echo JText::_('COM_THM_ORGANIZER_XLS_SPREADSHEET'); ?>
                    </a>
                </div>
                <div class="link-item">
                    <a onclick="scheduleApp.handleExport('ics');">
                        <span class="icon-info-calender"></span>
                        <?php echo JText::_('COM_THM_ORGANIZER_ICS_CALENDAR'); ?>
                    </a>
                </div>
                <div class="link-item">
                    <a href="index.php?option=com_thm_organizer&view=schedule_export" target="_blank">
                        <span class="icon-plus"></span>
                        <?php echo JText::_('COM_THM_ORGANIZER_OTHER_EXPORT_OPTIONS'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php
    $daysOfTheWeek  = [
        JText::_('MON'),
        JText::_('TUE'),
        JText::_('WED'),
        JText::_('THU'),
        JText::_('FRI'),
        JText::_('SAT'),
        JText::_('SUN')
    ];
    $datesOfTheWeek = [
        THM_OrganizerHelperComponent::formatDate('monday this week'),
        THM_OrganizerHelperComponent::formatDate('tuesday this week'),
        THM_OrganizerHelperComponent::formatDate('wednesday this week'),
        THM_OrganizerHelperComponent::formatDate('thursday this week'),
        THM_OrganizerHelperComponent::formatDate('friday this week'),
        THM_OrganizerHelperComponent::formatDate('saturday this week'),
        THM_OrganizerHelperComponent::formatDate('sunday this week')
    ];
    $grid           = json_decode($this->defaultGrid->grid);
    $periods        = get_object_vars($grid->periods);
    $activeDay      = date("w");
    ?>

    <div id="scheduleWrapper" class="scheduleWrapper">
        <?php
        if (JFactory::getUser()->guest) {
            ?>
            <input id="default-input" class="schedule-input" checked="checked" type="radio" name="schedules">
            <div id="default-schedule" class="schedule-table">
                <table>
                    <thead>
                    <tr>
                        <th><?php echo JText::_('COM_THM_ORGANIZER_TIME'); ?></th>
                        <?php
                        for ($weekday = $grid->startDay - 1; $weekday < $grid->endDay; ++$weekday) {
                            if ($activeDay == $weekday + 1) {
                                echo "<th class='activeColumn'>$daysOfTheWeek[$weekday]</th>";
                            } else {
                                echo "<th>$daysOfTheWeek[$weekday]</th>";
                            }
                        }
                        ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    for ($period = 1; $period <= count($periods); ++$period) {
                        echo "<tr>";
                        echo "<td>";
                        echo THM_OrganizerHelperComponent::formatTime($periods[$period]->startTime);
                        echo "<br> - <br>";
                        echo THM_OrganizerHelperComponent::formatTime($periods[$period]->endTime);
                        echo "</td>";

                        for ($weekday = $grid->startDay - 1; $weekday < $grid->endDay; ++$weekday) {
                            $class = ($activeDay == $weekday + 1) ? ' class="activeColumn"' : '';
                            echo '<td' . $class . '></td>';
                        }

                        echo "</tr>";
                    } // Periods
                    ?>
                    </tbody>
                </table>
            </div>
            <?php
        } // Guest table
        ?>
    </div>

    <div class="lesson-menu">
        <button class="icon-cancel" onclick="this.parentElement.style.display='none';"></button>
        <div class="lesson-data">
            <div class="subjectNameNr">
                <span class="subject"></span>
                <span class="module"></span>
            </div>
            <div class="persons"></div>
            <div class="rooms"></div>
            <div class="pools"></div>
            <span class="description"></span>
        </div>
        <div class="save">
            <button id="save-mode-semester">
                <?php echo JText::_('COM_THM_ORGANIZER_SAVE_LESSON_SEMESTER') ?>
            </button>
            <button id="save-mode-period">
                <?php echo JText::_('COM_THM_ORGANIZER_SAVE_LESSON_PERIOD') ?>
            </button>
            <button id="save-mode-instance">
                <?php echo JText::_('COM_THM_ORGANIZER_SAVE_LESSON_INSTANCE') ?>
            </button>
        </div>
        <div class="delete">
            <button id="delete-mode-semester">
                <?php echo JText::_('COM_THM_ORGANIZER_DELETE_LESSON_SEMESTER') ?>
            </button>
            <button id="delete-mode-period">
                <?php echo JText::_('COM_THM_ORGANIZER_DELETE_LESSON_PERIOD') ?>
            </button>
            <button id="delete-mode-instance">
                <?php echo JText::_('COM_THM_ORGANIZER_DELETE_LESSON_INSTANCE') ?>
            </button>
        </div>
    </div>

    <div id="next-date-selection" class="message pop-up">
        <p><?php echo JText::_("COM_THM_ORGANIZER_JUMP_DATE"); ?></p>
        <button class="icon-cancel" onclick="this.parentElement.style.display='none';"></button>
        <button id="past-date" onclick="scheduleApp.nextDateEventHandler(event);">
            <span class="icon-arrow-left-2"></span>
            <?php echo sprintf(JText::_('COM_THM_ORGANIZER_JUMP_TO_DATE'), date("d.m.Y")); ?>
        </button>
        <button id="future-date" onclick="scheduleApp.nextDateEventHandler(event);">
            <span class="icon-arrow-right-2"></span>
            <?php echo sprintf(JText::_('COM_THM_ORGANIZER_JUMP_TO_DATE'), date("d.m.Y")); ?>
        </button>
    </div>

    <div id="no-lessons" class="message pop-up">
        <p>
            <span class="icon-notification"></span>
            <span><?php echo JText::_("COM_THM_ORGANIZER_NO_LESSONS"); ?></span>
        </p>
        <button class="icon-cancel" onclick="this.parentElement.style.display='none';"></button>
    </div>

    <div id="reg-fifo" class="message pop-up">
        <p>
            <span class="icon-notification"></span>
            <span><?php echo JText::_("COM_THM_ORGANIZER_COURSE_MAIL_STATUS_REGISTERED"); ?></span>
        </p>
        <button class="icon-cancel" onclick="this.parentElement.style.display='none';"></button>
    </div>

    <div id="reg-manual" class="message pop-up">
        <p>
            <span class="icon-notification"></span>
            <span><?php echo JText::_("COM_THM_ORGANIZER_COURSE_MAIL_STATUS_WAIT_LIST"); ?></span>
        </p>
        <button class="icon-cancel" onclick="this.parentElement.style.display='none';"></button>
    </div>
</div>
