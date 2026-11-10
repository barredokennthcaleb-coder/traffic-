<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfileFieldsToUsersTable extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();
        $columns = [
            'firstname' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'lastname' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'age' => [
                'type'       => 'INT',
                'constraint' => 3,
                'unsigned'   => true,
                'null'       => true,
            ],
            'address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ];

        $missing = [];

        foreach ($columns as $name => $definition) {
            if (! $this->db->fieldExists($name, 'users')) {
                $missing[$name] = $definition;
            }
        }

        if ($missing !== []) {
            $this->forge->addColumn('users', $missing);
        }
    }

    public function down()
    {
        $this->db->resetDataCache();
        foreach (['firstname', 'lastname', 'age', 'address'] as $name) {
            if ($this->db->fieldExists($name, 'users')) {
                $this->forge->dropColumn('users', $name);
            }
        }
    }
}
