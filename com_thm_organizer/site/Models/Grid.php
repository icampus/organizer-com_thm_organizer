<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

defined('_JEXEC') or die;

use Exception;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class which manages stored grid data.
 */
class Grid extends BaseModel
{
    /**
     * Save the form data for a new grid
     *
     * @return bool true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function save()
    {
        if (!Access::isAdmin()) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $data = OrganizerHelper::getFormInput();

        // Save grids in json by foreach because the index is not numeric
        $periods = [];
        $index   = 1;
        foreach ($data['grid'] as $row) {
            $periods[$index] = $row;
            ++$index;
        }

        $grid         = ['periods' => $periods, 'startDay' => $data['startDay'], 'endDay' => $data['endDay']];
        $data['grid'] = json_encode($grid);

        return $this->getTable()->save($data);
    }
}
