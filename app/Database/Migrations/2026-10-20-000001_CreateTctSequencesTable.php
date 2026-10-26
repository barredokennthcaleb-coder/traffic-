<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTctSequencesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('tct_sequences')) {
            return;
        }

        $this->forge->addField([
            'year' => [
                'type'       => 'SMALLINT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'last_sequence' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('year', true);
        $this->forge->createTable('tct_sequences', true);
    }

    public function down()
    {
        if ($this->db->tableExists('tct_sequences')) {
            $this->forge->dropTable('tct_sequences', true);
        }
    }
}
