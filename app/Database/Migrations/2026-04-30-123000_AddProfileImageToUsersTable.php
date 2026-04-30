<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfileImageToUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'profile_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'address',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'profile_image');
    }
}
