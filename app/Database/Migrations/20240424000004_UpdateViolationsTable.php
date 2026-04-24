<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateViolationsTable extends Migration
{
    public function up()
    {
        $fields = [
            'ticket_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'AFTER'      => 'id',
            ],
            'officer_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'AFTER'      => 'license_plate',
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
        ];

        $this->forge->addColumn('violations', $fields);

        // Generate ticket_id for existing records
        $db = \Config\Database::connect();
        $violations = $db->table('violations')->get()->getResultArray();
        foreach ($violations as $violation) {
            $ticket_id = 'TKT-' . strtoupper(substr(md5($violation['id']), 0, 8));
            $db->table('violations')->where('id', $violation['id'])->update(['ticket_id' => $ticket_id]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('violations', 'ticket_id');
        $this->forge->dropColumn('violations', 'officer_id');
        $this->forge->dropColumn('violations', 'created_by');
        $this->forge->dropColumn('violations', 'paid_date');
        $this->forge->dropColumn('violations', 'payment_method');
        $this->forge->dropColumn('violations', 'receipt_number');
    }
}
