<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateViolationTypesTable extends Migration
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
            'violation_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'unique'     => true,
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'fine_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'points' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 0,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
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
        $this->forge->createTable('violation_types');

        // Seed default violation types
        $seedData = [
            ['violation_name' => 'Speeding', 'description' => 'Exceeding the speed limit', 'fine_amount' => 150.00, 'points' => 3],
            ['violation_name' => 'Running Red Light', 'description' => 'Failing to stop at a red light', 'fine_amount' => 200.00, 'points' => 4],
            ['violation_name' => 'Illegal Parking', 'description' => 'Parking in prohibited areas', 'fine_amount' => 75.00, 'points' => 2],
            ['violation_name' => 'Driving Without License', 'description' => 'Operating a vehicle without valid license', 'fine_amount' => 500.00, 'points' => 6],
            ['violation_name' => 'Using Mobile While Driving', 'description' => 'Holding a mobile phone while driving', 'fine_amount' => 100.00, 'points' => 2],
            ['violation_name' => 'Not Wearing Seatbelt', 'description' => 'Driver or passenger not wearing seatbelt', 'fine_amount' => 50.00, 'points' => 1],
            ['violation_name' => 'Overloading', 'description' => 'Vehicle exceeding passenger/load limit', 'fine_amount' => 300.00, 'points' => 4],
            ['violation_name' => 'Running Stop Sign', 'description' => 'Failing to stop at a stop sign', 'fine_amount' => 175.00, 'points' => 3],
        ];

        foreach ($seedData as $data) {
            $this->forge->reset();
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'violation_name' => ['type' => 'VARCHAR', 'constraint' => 100],
                'description' => ['type' => 'TEXT', 'null' => true],
                'fine_amount' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
                'points' => ['type' => 'INT', 'constraint' => 3],
                'status' => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->createTable('violation_types', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('violation_types');
    }
}
