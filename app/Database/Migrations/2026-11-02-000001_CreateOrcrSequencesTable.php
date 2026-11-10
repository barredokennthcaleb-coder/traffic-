<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrcrSequencesTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('orcr_sequences')) {
            $this->forge->addField([
                'id' => [
                    'type'       => 'TINYINT',
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'last_number' => [
                    'type'       => 'BIGINT',
                    'unsigned'   => true,
                    'null'       => false,
                    'default'    => 0,
                ],
                'number_width' => [
                    'type'       => 'SMALLINT',
                    'unsigned'   => true,
                    'null'       => false,
                    'default'    => 6,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('orcr_sequences', true);
        }

        $existing = $this->db->table('orcr_sequences')->where('id', 1)->get()->getRowArray();
        if ($existing) {
            return;
        }

        $lastNumber = 0;
        $numberWidth = 6;
        if ($this->db->tableExists('violations')) {
            $receipts = $this->db->table('violations')
                ->select('receipt_number')
                ->where('status', 'Paid')
                ->where('receipt_number IS NOT NULL', null, false)
                ->get()
                ->getResultArray();

            foreach ($receipts as $row) {
                $number = (string) ($row['receipt_number'] ?? '');
                if ($number !== '' && ctype_digit($number)) {
                    $lastNumber = max($lastNumber, (int) $number);
                    $numberWidth = max($numberWidth, strlen($number));
                }
            }
        }

        $this->db->table('orcr_sequences')->insert([
            'id'           => 1,
            'last_number'  => $lastNumber,
            'number_width' => $numberWidth,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        if ($this->db->tableExists('orcr_sequences')) {
            $this->forge->dropTable('orcr_sequences', true);
        }
    }
}
