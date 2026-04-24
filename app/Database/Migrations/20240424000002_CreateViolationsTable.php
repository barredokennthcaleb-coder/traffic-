<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateViolationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'driver_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'license_plate' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'violation_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'penalty_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Pending', 'Paid', 'Cancelled'],
                'default'    => 'Pending',
            ],
            'violation_date' => [
                'type' => 'DATETIME',
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
        $this->forge->createTable('violations');
    }

    public function down()
    {
        $this->forge->dropTable('violations');
    }
}
