<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Joomla\CMS\Factory;

/**
 * Provides general functions for participant access checks, data retrieval and display.
 */
class Participants
{
    const WAIT_LIST = 0;
    const REGISTERED = 1;
    const REMOVED = 2;

    /**
     * Changes a participants state.
     *
     * @param int $participantID the participant's id
     * @param int $courseID      the course's id
     * @param int $state         the requested state
     *
     * @return bool true on success, otherwise false
     */
    public static function changeState($participantID, $courseID, $state)
    {
        switch ($state) {
            case self::WAIT_LIST:
            case self::REGISTERED:
                $table = OrganizerHelper::getTable('User_Lessons');

                $data = [
                    'lessonID' => $courseID,
                    'userID'   => $participantID
                ];

                $table->load($data);

                $now                   = date('Y-m-d H:i:s');
                $data['user_date']     = $now;
                $data['status_date']   = $now;
                $data['status']        = $state;
                $data['configuration'] = Courses::getInstances($courseID);

                $success = $table->save($data);

                break;

            case self::REMOVED:
                $dbo   = Factory::getDbo();
                $query = $dbo->getQuery(true);
                $query->delete('#__thm_organizer_user_lessons');
                $query->where("userID = '$participantID'");
                $query->where("lessonID = '$courseID'");
                $dbo->setQuery($query);
                $success = (bool)OrganizerHelper::executeQuery('execute');
                if (!$success) {
                    return false;
                }

                break;
        }

        if (empty($success)) {
            return false;
        }

        self::notify($participantID, $courseID, $state);

        return true;
    }

    /**
     * Notify user if registration state was changed
     *
     * @param int $participantID the participant's id
     * @param int $courseID      the course's id
     * @param int $state         the requested state
     *
     * @return void
     */
    private static function notify($participantID, $courseID, $state)
    {
        $mailer = Factory::getMailer();
        $input  = OrganizerHelper::getInput();

        $user       = Factory::getUser($participantID);
        $userParams = json_decode($user->params, true);
        $mailer->addRecipient($user->email);

        if (!empty($userParams['language'])) {
            $input->set('languageTag', explode('-', $userParams['language'])[0]);
        } else {
            $officialAbbreviation = Courses::getCourse($courseID)['instructionLanguage'];
            $tag                  = strtoupper($officialAbbreviation) === 'E' ? 'en' : 'de';
            $input->set('languageTag', $tag);
        }

        $params = OrganizerHelper::getParams();
        $sender = Factory::getUser($params->get('mailSender'));

        if (empty($sender->id)) {
            return;
        }

        $mailer->setSender([$sender->email, $sender->name]);

        $course   = Courses::getCourse($courseID);
        $dateText = Courses::getDateDisplay($courseID);

        if (empty($course) or empty($dateText)) {
            return;
        }

        $campus     = Courses::getCampus($courseID);
        $courseName = (empty($campus) or empty($campus['name'])) ?
            $course['name'] : "{$course['name']} ({$campus['name']})";
        $mailer->setSubject($courseName);
        $body = Languages::_('THM_ORGANIZER_GREETING') . ',\n\n';

        $dates = explode(' - ', $dateText);

        if (count($dates) == 1 or $dates[0] == $dates[1]) {
            $body .= sprintf(Languages::_('THM_ORGANIZER_CIRCULAR_BODY_ONE_DATE') . ':\n\n', $courseName, $dates[0]);
        } else {
            $body .= sprintf(
                Languages::_('THM_ORGANIZER_CIRCULAR_BODY_TWO_DATES') . ':\n\n',
                $courseName,
                $dates[0],
                $dates[1]
            );
        }

        $statusText = '';

        switch ($state) {
            case 0:
                $statusText .= Languages::_('THM_ORGANIZER_COURSE_MAIL_STATUS_WAIT_LIST');
                break;
            case 1:
                $statusText .= Languages::_('THM_ORGANIZER_COURSE_MAIL_STATUS_REGISTERED');
                break;
            case 2:
                $statusText .= Languages::_('THM_ORGANIZER_COURSE_MAIL_STATUS_REMOVED');
                break;
            default:
                return;
        }

        $body .= ' => ' . $statusText . '\n\n';

        $body .= Languages::_('THM_ORGANIZER_CLOSING') . ',\n';
        $body .= $sender->name . '\n\n';
        $body .= $sender->email . '\n';

        $addressParts = explode(' – ', $params->get('address'));

        foreach ($addressParts as $aPart) {
            $body .= $aPart . '\n';
        }

        $contactParts = explode(' – ', $params->get('contact'));

        foreach ($contactParts as $cPart) {
            $body .= $cPart . '\n';
        }

        $mailer->setBody($body);
        $mailer->Send();
    }
}
