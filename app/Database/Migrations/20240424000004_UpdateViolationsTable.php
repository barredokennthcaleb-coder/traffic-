<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateViolationsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $fieldsToAdd = [];

        $fields = [
            'ticket_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'AFTER'      => 'id',
            ],
            'violation_type_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'AFTER'      => 'license_plate',
            ],
            'officer_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'AFTER'      => 'violation_type_id',
            ],
            'points' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'AFTER'      => 'penalty_amount',
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'AFTER'      => 'officer_id',
            ],
            'paid_date' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'AFTER'      => 'updated_at',
            ],
            'payment_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'AFTER'      => 'paid_date',
            ],
            'receipt_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'AFTER'      => 'payment_method',
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

        // Generate ticket_id for existing records
        $violations = $db->table('violations')->get()->getResultArray();
        foreach ($violations as $violation) {
            if (empty($violation['ticket_id'])) {
                $ticket_id = 'TKT-' . strtoupper(substr(md5($violation['id']), 0, 8));
                $db->table('violations')->where('id', $violation['id'])->update(['ticket_id' => $ticket_id]);
            }
        }
    }

    public function down()
    {
        $this->forge->dropColumn('violations', 'ticket_id');
        $this->forge->dropColumn('violations', 'violation_type_id');
        $this->forge->dropColumn('violations', 'officer_id');
        $this->forge->dropColumn('violations', 'points');
        $this->forge->dropColumn('violations', 'created_by');
        $this->forge->dropColumn('violations', 'paid_date');
        $this->forge->dropColumn('violations', 'payment_method');
        $this->forge->dropColumn('violations', 'receipt_number');
        $this->forge->dropColumn('violations', 'remarks');
        $this->forge->dropColumn('violations', 'location');
        $this->forge->dropColumn('violations', 'notes');
    }
}
