<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLicenseNumberToViolationsTable extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();
        $db = $this->db;
        if (!$db->fieldExists('license_number', 'violations')) {
            $this->forge->addColumn('violations', [
                'license_number' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'AFTER'      => 'driver_name',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->db->resetDataCache();
        $db = $this->db;
        if ($db->fieldExists('license_number', 'violations')) {
            $this->forge->dropColumn('violations', 'license_number');
        }
    }
}
