<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfileImageToUsersTable extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();
        if (! $this->db->fieldExists('profile_image', 'users')) {
            $this->forge->addColumn('users', [
                'profile_image' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
            ]);
        }
    }

    public function down()
    {
        $this->db->resetDataCache();
        if ($this->db->fieldExists('profile_image', 'users')) {
            $this->forge->dropColumn('users', 'profile_image');
        }
    }
}
