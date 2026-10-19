<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class LookupController extends Controller
{
    public function index()
    {
        $db = \Config\Database::connect();
        $queryStr = trim((string) $this->request->getGet('query'));

        $records = [];
        $searched = false;
        $stats = [
            'total'          => 0,
            'pending_count'  => 0,
            'pending_amount' => 0.00,
            'paid_count'     => 0,
            'paid_amount'    => 0.00,
            'violator_name'  => '',
            'license_plate'  => '',
        ];

        if ($queryStr !== '') {
            $searched = true;
            $builder  = $db->table('violations');

            $builder->select('ticket_id, MAX(driver_name) as driver_name, MAX(first_name) as first_name, MAX(last_name) as last_name, MAX(address) as address, MAX(license_plate) as license_plate, MAX(location) as location, MAX(violation_date) as violation_date, MAX(created_at) as created_at, GROUP_CONCAT(violation_type SEPARATOR ", ") as violation_type, SUM(penalty_amount) as penalty_amount, MAX(status) as status, MAX(receipt_number) as receipt_number');
            $builder->groupStart()
                ->like('license_plate', $queryStr)
                ->orLike('driver_name', $queryStr)
                ->orLike('first_name', $queryStr)
                ->orLike('last_name', $queryStr)
                ->orLike('ticket_id', $queryStr)
                ->orLike('license_number', $queryStr)
                ->groupEnd();

            $builder->groupBy('ticket_id');
            $builder->orderBy('MAX(id)', 'DESC');
            $records = $builder->get()->getResultArray();

            $stats['total'] = count($records);

            foreach ($records as $r) {
                $status = strtolower($r['status'] ?? '');
                $amount = (float) ($r['penalty_amount'] ?? 0);

                if ($stats['violator_name'] === '' && !empty($r['driver_name'])) {
                    $stats['violator_name'] = $r['driver_name'];
                }
                if ($stats['license_plate'] === '' && !empty($r['license_plate'])) {
                    $stats['license_plate'] = strtoupper($r['license_plate']);
                }

                if ($status === 'paid') {
                    $stats['paid_count']++;
                    $stats['paid_amount'] += $amount;
                } else if ($status === 'pending') {
                    $stats['pending_count']++;
                    $stats['pending_amount'] += $amount;
                }
            }
        }

        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');

        return view('lookup/index', [
            'query'    => $queryStr,
            'searched' => $searched,
            'records'  => $records,
            'stats'    => $stats,
        ]);
    }
}
