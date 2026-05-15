<?php defined('BASEPATH') OR exit('No direct script access allowed');

Class Migration_Seeder_users extends CI_Migration {
    /**
     * Table name.
     * 
     * @param string
     */
    private $name;

    public function __construct() {
        parent::__construct();
        $this->name = 'users';
    }

    /**
     * Migration.
     * 
     * @return void
     */
    public function up() {
        $this->db->truncate($this->name);

        $date = date('Y-m-d H:i:s');
        $param[] = [ //1
            'username' => 'superadmin',
            'full_name' => 'Administrator',
            'password' => password_hash('password1', PASSWORD_DEFAULT),
            'created_by' => 'superadmin',
            'created_at' => $date,
            'updated_by' => 'superadmin',
            'updated_at' => $date,
            'phone' => '0',
        ];
        $param[] = [ //2
            'username' => 'admin',
            'full_name' => 'Admin',
            'password' => password_hash('password1', PASSWORD_DEFAULT),
            'created_by' => 'superadmin',
            'created_at' => $date,
            'updated_by' => 'superadmin',
            'updated_at' => $date,
            'phone' => '0',
        ];
        $param[] = [ //3
            'username' => 'user',
            'full_name' => 'User',
            'password' => password_hash('password1', PASSWORD_DEFAULT),
            'created_by' => 'superadmin',
            'created_at' => $date,
            'updated_by' => 'superadmin',
            'updated_at' => $date,
            'phone' => '0',
        ];
        $this->db->insert_batch($this->name, $param);
    }

    /**
     * Rollback migration.
     * 
     * @return void
     */
    public function down() {
        $this->db->truncate($this->name);
    }
}
