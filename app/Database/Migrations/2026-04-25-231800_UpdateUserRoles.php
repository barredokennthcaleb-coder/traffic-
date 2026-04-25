<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateUserRoles extends Migration
{
    public function up()
    {
        // Make role flexible (avoid ENUM alteration issues) and normalize values.
        if ($this->db->fieldExists('role', 'users')) {
            $this->forge->modifyColumn('users', [
                'role' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'driver',
                    'null'       => false,
                ],
            ]);
        }

        // Normalize existing values
        $this->db->query("UPDATE users SET role = 'driver' WHERE role = 'user'");
        $this->db->query("UPDATE users SET role = 'enforcer' WHERE role = 'traffic_officer'");
    }

    public function down()
    {
        // Best-effort rollback: map back to previous values.
        $this->db->query("UPDATE users SET role = 'user' WHERE role = 'driver'");
        $this->db->query("UPDATE users SET role = 'traffic_officer' WHERE role = 'enforcer'");
    }
}

