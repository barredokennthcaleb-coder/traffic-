<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * E-signature payloads captured on the citation form.
 *
 * `ViolatorRecord::$allowedFields` and `OfficerController::store()` write
 * `driver_signature` / `officer_signature` on every citation, but no migration
 * ever created the columns — fresh deployments rejected every citation with
 * "Unknown column". Guarded so existing databases (which already have them)
 * are untouched.
 */
class AddSignaturesToViolations extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();
        if (! $this->db->fieldExists('driver_signature', 'violations')) {
            $this->forge->addColumn('violations', [
                'driver_signature' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
            ]);
        }

        if (! $this->db->fieldExists('officer_signature', 'violations')) {
            $this->forge->addColumn('violations', [
                'officer_signature' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        $this->db->resetDataCache();
        // Intentional no-op: signatures are live data; rollback must not drop
        // columns that exist on databases which predate this migration.
    }
}
