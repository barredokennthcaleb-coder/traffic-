<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddViolatorDetailsToViolationsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $fieldsToAdd = [];

        $fields = [
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'AFTER'      => 'driver_name',
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'AFTER'      => 'first_name',
            ],
            'age' => [
                'type'       => 'INT',
                'constraint' => 3,
                'null'       => true,
                'AFTER'      => 'last_name',
            ],
            'address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'AFTER'      => 'age',
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
        $columns = ['first_name', 'last_name', 'age', 'address'];

        foreach ($columns as $column) {
            if ($db->fieldExists($column, 'violations')) {
                $this->forge->dropColumn('violations', $column);
            }
        }
    }
}
