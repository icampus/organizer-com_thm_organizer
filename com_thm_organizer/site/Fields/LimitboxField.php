<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Organizer\Fields;

/**
 * Field to load a list of possible item count limits
 */
class LimitboxField extends OptionsField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Limitbox';

    /**
     * Cached array of the category items.
     *
     * @var    array
     */
    protected static $options = array();

    /**
     * Default options
     *
     * @var  array
     */
    protected $defaultLimits = array(5, 10, 15, 20, 25, 30, 50, 100, 200, 500);

    /**
     * Method to get the options to populate to populate list
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        // Accepted modifiers
        $hash = md5($this->element);

        if (!isset(static::$options[$hash])) {
            static::$options[$hash] = parent::getOptions();

            $options = array();
            $limits  = $this->defaultLimits;

            // Limits manually specified
            if (isset($this->element['limits'])) {
                $limits = explode(',', $this->element['limits']);
            }

            // User wants to add custom limits
            if (isset($this->element['append'])) {
                $limits = array_unique(array_merge($limits, explode(',', $this->element['append'])));
            }

            // User wants to remove some default limits
            if (isset($this->element['remove'])) {
                $limits = array_diff($limits, explode(',', $this->element['remove']));
            }

            // Order the options
            asort($limits);

            // Add an option to show all?
            $showAll = isset($this->element['showall']) ? (string)$this->element['showall'] === 'true' : true;

            if ($showAll) {
                $limits[] = 0;
            }

            if (!empty($limits)) {
                foreach ($limits as $value) {
                    $options[] = (object)array(
                        'value' => $value,
                        'text'  => ($value != 0) ? \JText::_('J' . $value) : \JText::_('JALL'),
                    );
                }

                static::$options[$hash] = array_merge(static::$options[$hash], $options);
            }
        }

        return static::$options[$hash];
    }
}
