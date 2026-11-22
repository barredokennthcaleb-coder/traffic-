<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Optional backup password for user accounts (admin user management).
 *
 * The backup password is a reusable second credential that can be used at
 * sign-in when the main password is forgotten. As with the main password and
 * the recovery code, only the bcrypt hash is stored - never the plaintext.
 */
class AddBackupPasswordToUsers extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();

        if (! $this->db->fieldExists('backup_password_hash', 'users')) {
            $this->forge->addColumn('users', [
                'backup_password_hash' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
            ]);
        }

        if (! $this->db->fieldExists('backup_password_set_at', 'users')) {
            $this->forge->addColumn('users', [
                'backup_password_set_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        $this->db->resetDataCache();

        if ($this->db->fieldExists('backup_password_hash', 'users')) {
            $this->forge->dropColumn('users', 'backup_password_hash');
        }

        if ($this->db->fieldExists('backup_password_set_at', 'users')) {
            $this->forge->dropColumn('users', 'backup_password_set_at');
        }
    }
}
