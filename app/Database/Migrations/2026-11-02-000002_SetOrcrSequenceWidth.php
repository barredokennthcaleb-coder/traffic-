<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SetOrcrSequenceWidth extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('orcr_sequences')) {
            return;
        }

        $sequence = $this->db->table('orcr_sequences')
            ->where('id', 1)
            ->get()
            ->getRowArray();

        if (! $sequence) {
            return;
        }

        $lastNumber = (int) $sequence['last_number'];
        $this->db->table('orcr_sequences')
            ->where('id', 1)
            ->update([
                'number_width' => max(6, strlen((string) $lastNumber)),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
    }

    public function down()
    {
        if (! $this->db->tableExists('orcr_sequences')) {
            return;
        }

        $this->db->table('orcr_sequences')
            ->where('id', 1)
            ->update(['number_width' => 7]);
    }
}
