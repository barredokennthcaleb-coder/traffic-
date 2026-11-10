<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * The station an account belongs to.
 *
 * The column exists on the live/legacy databases but was never created by a
 * migration, so `AddAssignedAreaToUsers` (which positions `assigned_area`
 * AFTER it) failed with "Unknown column 'assigned_station'" on any database
 * built purely from migrations.
 */
class AddAssignedStationToUsers extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();
        if (! $this->db->fieldExists('assigned_station', 'users')) {
            $this->forge->addColumn('users', [
                'assigned_station' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
            ]);
        }
    }

    public function down()
    {
        $this->db->resetDataCache();
        // Intentional no-op: on existing databases the column predates this
        // migration, so rolling back must not drop it (and its data).
    }
}
