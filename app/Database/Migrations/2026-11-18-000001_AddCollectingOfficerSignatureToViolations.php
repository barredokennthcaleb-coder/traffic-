<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * E-signature of the collecting officer on the official receipt.
 *
 * The signature (a base64 PNG/WebP/JPEG data URL) and the officer's printed
 * name are captured on the Record Payment form and stored with the settled
 * ticket so the printed receipt can show who received the payment. Guarded so
 * existing databases are left untouched when the columns are already there.
 */
class AddCollectingOfficerSignatureToViolations extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();

        if (! $this->db->fieldExists('collecting_officer_name', 'violations')) {
            $this->forge->addColumn('violations', [
                'collecting_officer_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
            ]);
        }

        if (! $this->db->fieldExists('collecting_officer_signature', 'violations')) {
            $this->forge->addColumn('violations', [
                'collecting_officer_signature' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        $this->db->resetDataCache();
        // Intentional no-op: signatures are live receipt data. Rolling back a
        // migration must not drop columns that hold issued receipts.
    }
}
