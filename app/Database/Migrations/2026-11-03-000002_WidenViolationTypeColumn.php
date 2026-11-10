<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * `violations.violation_type` stores a copy of the offense name. The original
 * migration created it as VARCHAR(255), but the live database drifted to
 * VARCHAR(50), while offense names may be up to 100 characters — issuing a
 * citation with a long name could fail with "Data too long for column".
 * Align the column with the migrated schema.
 */
class WidenViolationTypeColumn extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();
        if ($this->db->fieldExists('violation_type', 'violations')) {
            $this->forge->modifyColumn('violations', [
                'violation_type' => [
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
        // Intentional no-op: narrowing the column again could truncate data.
    }
}
