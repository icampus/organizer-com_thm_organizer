<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/courses.php';

/**
 * Class which manages stored course data.
 */
class THM_OrganizerModelCourse extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{
    /**
     * Saves data for participants when administrator changes state in manager
     *
     * @return bool true on success, false on error
     * @throws \Exception => unauthorized access
     */
    public function changeParticipantState()
    {
        $input    = THM_OrganizerHelperComponent::getInput();
        $data     = $input->getArray();
        $formData = $data['jform'];

        if (!THM_OrganizerHelperCourses::authorized($formData['id'])) {
            throw new \Exception(\JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $participantIDs = $data['checked'];
        $state          = (int)$data['participantState'];
        $invalidState   = ($state < 0 or $state > 2);

        if (empty($participantIDs) or empty($formData['id']) or $invalidState) {
            return false;
        }

        $return = true;

        foreach ($data['checked'] as $participantID) {
            $success = THM_OrganizerHelperParticipants::changeState($participantID, $formData['id'], $state);

            if (empty($success)) {
                return false;
            }

            if ($state === 0) {
                THM_OrganizerHelperCourses::refreshWaitList($formData['id']);
            }

            $return = ($return and $success);
        }

        return $return;
    }

    /**
     * Sends a circular mail to all course participants
     *
     * @return bool true on success, false on error
     * @throws \Exception => not found / unauthorized access
     */
    public function circular()
    {
        $input = THM_OrganizerHelperComponent::getInput();

        $courseID = $input->get('lessonID', 0);

        if (empty($courseID)) {
            throw new \Exception(\JText::_('COM_THM_ORGANIZER_404'), 404);
        }

        if (empty(THM_OrganizerHelperCourses::authorized($courseID))) {
            throw new \Exception(\JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $data = $input->get('jform', [], 'array');

        if (empty($data['text'])) {
            return false;
        }

        $sender = \JFactory::getUser(THM_OrganizerHelperComponent::getParams()->get('mailSender'));

        if (empty($sender->id)) {
            return false;
        }

        $recipients = THM_OrganizerHelperCourses::getFullParticipantData($courseID, (bool)$data['includeWaitList']);

        if (empty($recipients)) {
            return false;
        }

        $mailer = \JFactory::getMailer();
        $mailer->setSender([$sender->email, $sender->name]);
        $mailer->setSubject($data['subject']);

        foreach ($recipients as $recipient) {
            $mailer->addRecipient($recipient['email']);
        }

        $mailer->setBody($data['text']);
        $sent = $mailer->Send();

        if (!$sent) {
            return false;
        }

        return true;
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param string $name    The table name. Optional.
     * @param string $prefix  The class prefix. Optional.
     * @param array  $options Configuration array for model. Optional.
     *
     * @return \JTable  A \JTable object
     */
    public function getTable($name = 'lessons', $prefix = 'THM_OrganizerTable', $options = [])
    {
        \JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_thm_organizer/tables');

        return \JTable::getInstance($name, $prefix, $options);
    }

    /**
     * Saves changes to courses. Adjusting the course wait list as appropriate.
     *
     * @return bool true on success, otherwise false
     * @throws \Exception invalid request / unauthorized access
     */
    public function save()
    {
        $data = THM_OrganizerHelperComponent::getInput()->get('jform', [], 'array');

        if (!isset($data['id'])) {
            throw new \Exception(\JText::_('COM_THM_ORGANIZER_400'), 400);
        } elseif (!THM_OrganizerHelperCourses::authorized($data['id'])) {
            throw new \Exception(\JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $table = $this->getTable();
        $table->load($data['id']);
        $table->campusID         = $data['campusID'];
        $table->max_participants = $data['max_participants'];
        $table->deadline         = $data['deadline'];
        $table->fee              = $data['fee'];

        $success = $table->store();

        if (empty($success)) {
            return false;
        }

        THM_OrganizerHelperCourses::refreshWaitList($data['id']);

        return true;
    }
}
