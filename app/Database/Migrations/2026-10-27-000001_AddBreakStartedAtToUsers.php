<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBreakStartedAtToUsers extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();
        if (! $this->db->fieldExists('break_started_at', 'users')) {
            $this->forge->addColumn('users', [
                'break_started_at' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                    'after'   => 'duty_status',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->db->resetDataCache();
        if ($this->db->fieldExists('break_started_at', 'users')) {
            $this->forge->dropColumn('users', 'break_started_at');
        }
    }
}
