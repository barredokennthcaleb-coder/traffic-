<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBirthdateAndMiddleInitialToUsersTable extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();
        $columns = [
            'middle_initial' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'birthdate' => [
                'type' => 'DATE',
                'null' => true,
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
        foreach (['middle_initial', 'birthdate'] as $name) {
            if ($this->db->fieldExists($name, 'users')) {
                $this->forge->dropColumn('users', $name);
            }
        }
    }
}
