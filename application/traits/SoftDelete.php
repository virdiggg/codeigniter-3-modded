<?php
defined('BASEPATH') or exit('No direct script access allowed');

trait SoftDelete
{
    /**
     * State if Soft Delete is used or not. By default, it's true.
     * If set to false, the query won't be affected with soft delete traits.
     * 
     * @param bool
     */
    protected $usingSoftDelete = true;

    /**
     * Soft Delete field names.
     * 
     * @param array
     */
    protected $softDeleteParams = ['deleted_by', 'deleted_at'];

    /**
     * Change state to use soft delete or not.
     * 
     * @param bool $useSoftDelete
     * 
     * @return $this
     */
    public function usingSoftDelete($useSoftDelete = true)
    {
        $this->usingSoftDelete = $useSoftDelete;
        return $this;
    }

    /**
     * State to use soft delete or not
     * 
     * @param array $params
     * 
     * @return $this
     */
    public function setParams($params = ['deleted_by', 'deleted_at'])
    {
        if (count($params) === 0) {
            return $this;
        }

        $this->softDeleteParams = (array) $params;
        return $this;
    }

    /**
     * Determine whether we want to show all the record or not
     * - 'clean' only return all record that IS NOT deleted.
     * - 'trashed' only return all record that IS deleted.
     * - 'all' return all record.
     * Default is 'clean'.
     * 
     * @param string $switchParam
     * 
     * @return $this
     */
    public function with($switchParam = 'clean')
    {
        if ($this->usingSoftDelete) {
            switch ($switchParam) {
                case 'trashed':
                    $this->db->group_start();
                        foreach ($this->softDeleteParams as $param) {
                            $this->db->where("$param IS NOT NULL");
                        }
                    $this->db->group_end();
                    break;
                case 'all':
                    break;
                case 'clean':
                default:
                    $this->db->group_start();
                        foreach ($this->softDeleteParams as $param) {
                            $this->db->where("$param IS NULL");
                        }
                    $this->db->group_end();
                    break;
            }
        }

        return $this;
    }
}
