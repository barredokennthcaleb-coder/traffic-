<?php

namespace App\Database\Migrations;

use App\Models\ViolatorModel;
use CodeIgniter\Database\Migration;

/**
 * Creates the violator register and links every existing citation ticket to a
 * single violator record.
 *
 * Historical rows are grouped by ticket first: all infractions printed on one
 * ticket belong to one attempt. Tickets are then matched to a violator using,
 * in order, the driver's licence number, the normalized name plus a real plate
 * number, or a ticket-only fallback for legacy anonymous records.
 */
class CreateViolatorsAndLinkViolations extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('violators')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'identity_key' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 191,
                    'null'       => false,
                ],
                'license_key' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'name_key' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 191,
                    'null'       => true,
                ],
                'plate_key' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'license_number' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'driver_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'first_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'middle_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'last_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'age' => [
                    'type'       => 'INT',
                    'constraint' => 3,
                    'null'       => true,
                ],
                'address' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'license_plate' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                ],
                'owner_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('identity_key');
            $this->forge->addKey('license_key');
            $this->forge->addKey(['name_key', 'plate_key']);
            $this->forge->createTable('violators', true);
        }

        if (! $this->db->fieldExists('violator_id', 'violations')) {
            $this->forge->addColumn('violations', [
                'violator_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'id',
                ],
            ]);
        }

        $hasIndex = false;

        foreach ($this->db->getIndexData('violations') as $index) {
            if (($index->name ?? '') === 'violations_violator_id') {
                $hasIndex = true;
                break;
            }

            if (($index->fields ?? []) === ['violator_id']) {
                $hasIndex = true;
                break;
            }
        }

        if (! $hasIndex) {
            $this->forge->addKey('violator_id', false, false, 'violations_violator_id');
            $this->forge->processIndexes('violations');
        }

        $this->backfill();
    }

    public function down()
    {
        if ($this->db->fieldExists('violator_id', 'violations')) {
            foreach ($this->db->getIndexData('violations') as $index) {
                if (($index->name ?? '') === 'violations_violator_id') {
                    $this->forge->dropKey('violations', 'violations_violator_id');
                    break;
                }
            }

            $this->forge->dropColumn('violations', 'violator_id');
        }

        if ($this->db->tableExists('violators')) {
            $this->forge->dropTable('violators', true);
        }
    }

    /**
     * Link historical violations without creating duplicate people.
     */
    private function backfill(): void
    {
        $pending = $this->db->table('violations')
            ->select('id, ticket_id, first_name, middle_name, last_name, age, address, driver_name, license_number, license_plate, owner_name')
            ->where('violator_id', null)
            ->orderBy('violation_date', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        if ($pending === []) {
            return;
        }

        $tickets = [];

        foreach ($pending as $row) {
            $ticketId = trim((string) ($row['ticket_id'] ?? ''));
            $key      = $ticketId !== '' ? $ticketId : 'ROW-' . (int) $row['id'];

            if (! isset($tickets[$key])) {
                $tickets[$key] = [
                    'ticket_id' => $ticketId,
                    'rows'      => [],
                    'ids'       => [],
                ];
            }

            $tickets[$key]['rows'][] = $row;
            $tickets[$key]['ids'][]  = (int) $row['id'];
        }

        $model = new ViolatorModel();

        foreach ($tickets as $ticket) {
            $representative = $this->representativeRow($ticket['rows']);
            $violatorId     = $model->resolveForTicket($representative, null, $ticket['ticket_id']);

            $this->db->table('violations')
                ->whereIn('id', $ticket['ids'])
                ->update(['violator_id' => $violatorId]);
        }
    }

    /**
     * Pick the most complete row from a ticket so the violator snapshot uses
     * the best available identifiers.
     */
    private function representativeRow(array $rows): array
    {
        $best     = $rows[0];
        $bestRank = -1;

        foreach ($rows as $row) {
            $rank = 0;

            foreach (['license_number', 'license_plate', 'first_name', 'last_name', 'address'] as $field) {
                $value = strtoupper(trim((string) ($row[$field] ?? '')));

                if ($value !== '' && ! in_array($value, ['NO', 'NONE', 'N/A', 'NA', '-', '--'], true)) {
                    $rank++;
                }
            }

            if ($rank > $bestRank) {
                $best     = $row;
                $bestRank = $rank;
            }
        }

        return $best;
    }
}
