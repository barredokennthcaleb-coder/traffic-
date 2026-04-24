<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingColumnsToViolationsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $fieldsToAdd = [];

        $fields = [
            'points' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'AFTER'      => 'penalty_amount',
            ],
            'remarks' => [
                'type'       => 'TEXT',
                'null'       => true,
                'AFTER'      => 'receipt_number',
            ],
            'location' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'AFTER'      => 'remarks',
            ],
            'notes' => [
                'type'       => 'TEXT',
                'null'       => true,
                'AFTER'      => 'location',
            ],
        ];

        foreach ($fields as $columnName => $config) {
            if (!$db->fieldExists($columnName, 'violations')) {
                $fieldsToAdd[$columnName] = $config;
            }
        }

        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('violations', $fieldsToAdd);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('violations', 'points');
        $this->forge->dropColumn('violations', 'remarks');
        $this->forge->dropColumn('violations', 'location');
        $this->forge->dropColumn('violations', 'notes');
    }
}
