<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOwnerAddressToViolations extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();
        if (! $this->db->fieldExists('owner_address', 'violations')) {
            $this->forge->addColumn('violations', [
                'owner_address' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'owner_name',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->db->resetDataCache();
        if ($this->db->fieldExists('owner_address', 'violations')) {
            $this->forge->dropColumn('violations', 'owner_address');
        }
    }
}
