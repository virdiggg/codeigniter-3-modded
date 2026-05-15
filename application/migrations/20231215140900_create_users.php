<?php defined('BASEPATH') OR exit('No direct script access allowed');

Class Migration_Create_users extends CI_Migration {
    /**
     * Array table fields.
     * 
     * @param array $fields
     */
    private $fields;

    /**
     * Primary key.
     * 
     * @param array
     */
    private $primary = 'id';

    /**
     * Table name.
     * 
     * @param string $name
     */
    private $name = 'users';

    /**
     * DB name.
     * 
     * @param string $dbName
     */
    private $dbName = 'codeigniter';

    public function __construct() {
        parent::__construct();
        $this->fields = [
            $this->primary => [
                'type' => 'BIGINT',
                'unsigned' => TRUE,
                'auto_increment' => TRUE,
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'full_name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => TRUE,
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
            ],
            'token' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => TRUE,
            ],
            'auth_token' => [
                'type' => 'TEXT',
                'null' => TRUE,
            ],
            'last_login' => [
                'type' => 'TIMESTAMP',
                'null' => TRUE,
            ],
            'is_active' => [
                'type' => 'BOOL',
                'default' => TRUE,
            ],
            'created_by' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => TRUE,
            ],
            'updated_by' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => TRUE,
            ],
        ];
    }

    /**
     * Migration.
     * 
     * @return void
     */
    public function up() {
        $this->load->dbutil();

        // Check whether database exists or not
        if ($this->dbutil->database_exists($this->dbName)) {
            // If exists, drop it
            $this->dbforge->drop_database($this->dbName);
        }

        // Create a new database
        $this->dbforge->create_database($this->dbName);

        // if ($this->db->table_exists($this->name)) {
        //     $this->dbforge->drop_table($this->name, TRUE);
        // }

        // Then migrate the table
        $this->dbforge->add_field($this->fields);
        $this->dbforge->add_key($this->primary, TRUE);
        $this->dbforge->create_table($this->name);
    }

    /**
     * Rollback migration.
     * 
     * @return void
     */
    public function down() {
        if ($this->db->table_exists($this->name)) {
            $this->dbforge->drop_table($this->name, TRUE);
        }
    }
}
