<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfileFieldsToUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'firstname' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'username',
            ],
            'lastname' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'firstname',
            ],
            'age' => [
                'type'       => 'INT',
                'constraint' => 3,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'lastname',
            ],
            'address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'age',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['firstname', 'lastname', 'age', 'address']);
    }
}
