<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDutyStatusToUsers extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('duty_status', 'users')) {
            $this->forge->addColumn('users', [
                'duty_status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'Active Patrol',
                    'null'       => false,
                    'after'      => 'assigned_area',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('duty_status', 'users')) {
            $this->forge->dropColumn('users', 'duty_status');
        }
    }
}
