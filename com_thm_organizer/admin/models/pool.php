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

/**
 * Class which manages stored (subject) pool data.
 */
class THM_OrganizerModelPool extends JModelLegacy
{
    /**
     * Attempts to delete the selected subject pool entries and related mappings
     *
     * @return boolean true on success, otherwise false
     * @throws Exception
     */
    public function delete()
    {
        $poolIDs = JFactory::getApplication()->input->get('cid', [], 'array');
        if (!empty($poolIDs)) {
            $this->_db->transactionStart();
            foreach ($poolIDs as $poolID) {
                $deleted = $this->deleteEntry($poolID);

                if (!$deleted) {
                    $this->_db->transactionRollback();

                    return false;
                }
            }
            $this->_db->transactionCommit();
        }

        return true;
    }

    /**
     * Removes a single pool and mappings. No access checks because of the contexts in which it is called.
     *
     * @param int $poolID the pool id
     *
     * @return boolean  true on success, otherwise false
     */
    public function deleteEntry($poolID)
    {
        $model           = JModelLegacy::getInstance('mapping', 'THM_OrganizerModel');
        $mappingsDeleted = $model->deleteByResourceID($poolID, 'pool');

        if (!$mappingsDeleted) {
            return false;
        }

        $table       = JTable::getInstance('pools', 'thm_organizerTable');
        $poolDeleted = $table->delete($poolID);

        if (!$poolDeleted) {
            return false;
        }

        return true;
    }

    /**
     * Saves
     *
     * @param bool $new whether or not the pool is a new item
     *
     * @return mixed  integer on successful pool creation, otherwise boolean
     *                 true/false on success/failure
     * @throws Exception
     */
    public function save($new = false)
    {
        $data = JFactory::getApplication()->input->get('jform', [], 'array');

        if ($new) {
            unset($data['id']);
            unset($data['asset_id']);
        }

        if (empty($data['fieldID'])) {
            unset($data['fieldID']);
        }

        $table = JTable::getInstance('pools', 'thm_organizerTable');
        $this->_db->transactionStart();

        $success = $table->save($data);

        if (!$success or empty($table->id)) {
            $this->_db->transactionRollback();

            return false;
        }

        $mappingsIrrelevant = (empty($data['programID']) or empty($data['parentID']));

        // Successfully inserted a new pool
        if ($mappingsIrrelevant) {
            $this->_db->transactionCommit();

            return $table->id;
        } // Process mapping information
        else {
            $model      = JModelLegacy::getInstance('mapping', 'THM_OrganizerModel');
            $data['id'] = $table->id;

            // No mappings desired
            if (empty($data['parentID'])) {
                $mappingsDeleted = $model->deleteByResourceID($table->id, 'pool');
                if ($mappingsDeleted) {
                    $this->_db->transactionCommit();

                    return $table->id;
                } else {
                    $this->_db->transactionRollback();

                    return false;
                }
            } else {
                $mappingSaved = $model->savePool($data);
                if ($mappingSaved) {
                    $this->_db->transactionCommit();

                    return $table->id;
                } else {
                    $this->_db->transactionRollback();

                    return false;
                }
            }
        }
    }

    /**
     * Saves
     *
     * @return mixed  integer on successful pool creation, otherwise boolean
     *                 true/false on success/failure
     * @throws Exception
     */
    public function save2copy()
    {
        return $this->save(true);
    }
}
