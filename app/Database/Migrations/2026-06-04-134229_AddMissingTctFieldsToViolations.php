<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingTctFieldsToViolations extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $fieldsToAdd = [];

        $fields = [
            'middle_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'AFTER'      => 'first_name',
            ],
            'license_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'AFTER'      => 'age',
            ],
            'mtop_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'AFTER'      => 'license_plate',
            ],
            'owner_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'AFTER'      => 'mtop_number',
            ],
            'acknowledgment' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'AFTER'      => 'notes',
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
        $db = \Config\Database::connect();
        $columns = ['middle_name', 'license_number', 'mtop_number', 'owner_name', 'acknowledgment'];

        foreach ($columns as $column) {
            if ($db->fieldExists($column, 'violations')) {
                $this->forge->dropColumn('violations', $column);
            }
        }
    }
}
