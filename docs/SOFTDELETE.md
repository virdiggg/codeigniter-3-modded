# Soft Delete trait

### How to use:
- Include the trait in model
```php
<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Require the trait
require_once APPPATH . 'traits/SoftDelete.php';

class M_Users extends CI_Model
{
    // Use the trait
    use SoftDelete;
}
```
- Use the trait in query
```php
public function example() {
    $this->db->select();
    $this->db->from('users u');
    $this->db->join('addresses add', 'add.user_id = u.id', 'left');

    // Add these line
    // ===================
    // If false, soft delete is not used, so setParam() and with() is ignored.
    // $this->usingSoftDelete(false);

    // This means, the soft delete parameters used will be 'add.deleted_by' and 'add.deleted_at' from table 'addresses'.
    $this->setParams(['add.deleted_by', 'add.deleted_at']);

    // This means, only row which is not deleted will be returned.
    $this->with('clean');
    // $this->with('trashed');
    // $this->with($this->session->userdata('is_admin') ? 'all' : 'clean');
    // ===================

    return $this->db->get()->result();
}
```
- Parameter `with()` which can be used
    1. `clean`, only row which is **not deleted** will be returned
    1. `trashed`, only row which is **deleted** will be returned
    1. `all`, will return all row (including deleted ones)