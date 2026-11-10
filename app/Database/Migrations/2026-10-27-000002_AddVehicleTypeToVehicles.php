<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * What was actually driven on a citation (a specific tricycle, motorcycle, van…).
 *
 * Stored on the citation, and mirrored onto the violator's snapshot so the
 * enforcer ledger can show the vehicle and its MTOP number without reading the
 * person's tickets again.
 */
class AddVehicleTypeToVehicles extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();
        if (! $this->db->fieldExists('vehicle_type', 'violations')) {
            $this->forge->addColumn('violations', [
                'vehicle_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'license_plate',
                ],
            ]);
        }

        if (! $this->db->fieldExists('vehicle_type', 'violators')) {
            $this->forge->addColumn('violators', [
                'vehicle_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'license_plate',
                ],
            ]);
        }

        if (! $this->db->fieldExists('mtop_number', 'violators')) {
            $this->forge->addColumn('violators', [
                'mtop_number' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'vehicle_type',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->db->resetDataCache();
        if ($this->db->fieldExists('vehicle_type', 'violations')) {
            $this->forge->dropColumn('violations', 'vehicle_type');
        }

        if ($this->db->fieldExists('vehicle_type', 'violators')) {
            $this->forge->dropColumn('violators', 'vehicle_type');
        }

        if ($this->db->fieldExists('mtop_number', 'violators')) {
            $this->forge->dropColumn('violators', 'mtop_number');
        }
    }
}
