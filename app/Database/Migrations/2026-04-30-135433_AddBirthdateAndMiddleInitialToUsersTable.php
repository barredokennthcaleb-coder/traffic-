<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBirthdateAndMiddleInitialToUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'middle_initial' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'after'      => 'firstname',
            ],
            'birthdate' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'age',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['middle_initial', 'birthdate']);
    }
}
