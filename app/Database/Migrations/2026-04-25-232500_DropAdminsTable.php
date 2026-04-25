<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropAdminsTable extends Migration
{
    public function up()
    {
        // Remove legacy table; all auth now uses `users`.
        $this->forge->dropTable('admins', true);
    }

    public function down()
    {
        // Recreate legacy admins table (best-effort rollback).
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'unique'     => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'unique'     => true,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('admins', true);
    }
}

