<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLicenseNumberToViolationsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
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
        $db = \Config\Database::connect();
        if ($db->fieldExists('license_number', 'violations')) {
            $this->forge->dropColumn('violations', 'license_number');
        }
    }
}
