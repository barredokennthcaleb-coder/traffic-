<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Gives every violator record an optional photo, the same way users carry
 * a profile_image. The file itself lives under public/uploads/violators/;
 * only the generated filename is stored here.
 */
class AddProfileImageToViolatorsTable extends Migration
{
    public function up()
    {
        $this->db->resetDataCache();
        if (! $this->db->fieldExists('profile_image', 'violators')) {
            $this->forge->addColumn('violators', [
                'profile_image' => [
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
        if ($this->db->fieldExists('profile_image', 'violators')) {
            $this->forge->dropColumn('violators', 'profile_image');
        }
    }
}
