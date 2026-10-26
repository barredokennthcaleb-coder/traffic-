<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAssignedAreaToUsers extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('assigned_area', 'users')) {
            $this->forge->addColumn('users', [
                'assigned_area' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'assigned_station',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('assigned_area', 'users')) {
            $this->forge->dropColumn('users', 'assigned_area');
        }
    }
}