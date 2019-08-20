<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Organizer\Helpers\Persons;

/**
 * Class creates a form field for room type selection
 */
class TeachersField extends OptionsField
{
    /**
     * @var  string
     */
    protected $type = 'Teachers';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        $options  = parent::getOptions();
        $teachers = Persons::getOptions();

        return array_merge($options, $teachers);
    }
}
