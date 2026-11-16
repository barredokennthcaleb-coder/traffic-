<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * One-time account-recovery codes.
 *
 * An administrator generates a code for an account and hands it to the owner
 * in person. Only the bcrypt hash is stored; the code can be used once on the
 * Forgot Password page and is burned after a successful reset.
 */
class AddRecoveryCodeToUsers extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();

        if (! $this->db->fieldExists('recovery_code_hash', 'users')) {
            $this->forge->addColumn('users', [
                'recovery_code_hash' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
            ]);
        }

        if (! $this->db->fieldExists('recovery_code_set_at', 'users')) {
            $this->forge->addColumn('users', [
                'recovery_code_set_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        $this->db->resetDataCache();

        if ($this->db->fieldExists('recovery_code_hash', 'users')) {
            $this->forge->dropColumn('users', 'recovery_code_hash');
        }

        if ($this->db->fieldExists('recovery_code_set_at', 'users')) {
            $this->forge->dropColumn('users', 'recovery_code_set_at');
        }
    }
}
