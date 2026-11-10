<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * The driver's license number on a driver account.
 *
 * Used to link a signed-in driver to their violator records (matched against
 * `violators.license_key`). Optional: name matching remains the fallback.
 */
class AddLicenseNumberToUsers extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();

        if (! $this->db->fieldExists('license_number', 'users')) {
            $this->forge->addColumn('users', [
                'license_number' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
            ]);
        }
    }

    public function down()
    {
        $this->db->resetDataCache();

        if ($this->db->fieldExists('license_number', 'users')) {
            $this->forge->dropColumn('users', 'license_number');
        }
    }
}
