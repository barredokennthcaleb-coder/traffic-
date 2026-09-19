<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ViolationTypesSeeder extends Seeder
{
    public function run()
    {
        // Clear existing violation types
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        $this->db->table('violation_types')->truncate();
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

        $data = [
            [
                'violation_name' => 'Unlicensed Driver',
                'description'    => 'Operating a motor vehicle without a valid driver\'s license',
                'fine_amount'    => 1000.00,
                'points'         => 0,
                'status'         => 'active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'violation_name' => 'Unregistered MV',
                'description'    => 'Operating an unregistered motor vehicle',
                'fine_amount'    => 500.00,
                'points'         => 0,
                'status'         => 'active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'violation_name' => 'Colorum/Unfranchised Operation',
                'description'    => 'Operating a public utility vehicle without a valid franchise or authority',
                'fine_amount'    => 5000.00,
                'points'         => 0,
                'status'         => 'active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'violation_name' => 'Invalid or Suspended/Revoked/Expired CR',
                'description'    => 'Operating with an invalid, suspended, revoked, or expired Certificate of Registration',
                'fine_amount'    => 1000.00,
                'points'         => 0,
                'status'         => 'active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'violation_name' => 'Out of Route',
                'description'    => 'Operating a public utility vehicle outside its authorized route',
                'fine_amount'    => 1000.00,
                'points'         => 0,
                'status'         => 'active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'violation_name' => 'Discourteous Driver/Conduct',
                'description'    => 'Rude, disrespectful, or improper behavior towards passengers or other motorists',
                'fine_amount'    => 500.00,
                'points'         => 0,
                'status'         => 'active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'violation_name' => 'CR/OR Not Carried',
                'description'    => 'Failure to carry Certificate of Registration and Official Receipt while operating',
                'fine_amount'    => 1000.00,
                'points'         => 0,
                'status'         => 'active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'violation_name' => 'Unauthorized Improvised Plates',
                'description'    => 'Using unauthorized, improvised, or tampered license plates',
                'fine_amount'    => 1000.00,
                'points'         => 0,
                'status'         => 'active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'violation_name' => 'No Required MV Parts/Acc.',
                'description'    => 'Operating without required motor vehicle parts or accessories',
                'fine_amount'    => 500.00,
                'points'         => 0,
                'status'         => 'active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'violation_name' => 'No Body (Plate) Number, For Hire MV',
                'description'    => 'For-hire motor vehicle operating without a visible body or plate number',
                'fine_amount'    => 500.00,
                'points'         => 0,
                'status'         => 'active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'violation_name' => 'Allowing Passenger on Top of MV',
                'description'    => 'Permitting passengers to ride on the roof or top of a motor vehicle',
                'fine_amount'    => 500.00,
                'points'         => 0,
                'status'         => 'active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'violation_name' => 'Reckless Driving',
                'description'    => 'Driving in a manner that endangers the safety of persons or property',
                'fine_amount'    => 500.00,
                'points'         => 0,
                'status'         => 'active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'violation_name' => 'Obstruction',
                'description'    => 'Causing obstruction on public roads or highways',
                'fine_amount'    => 500.00,
                'points'         => 0,
                'status'         => 'active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'violation_name' => 'Other Violations',
                'description'    => 'Any other traffic violations not specifically listed above',
                'fine_amount'    => 500.00,
                'points'         => 0,
                'status'         => 'active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('violation_types')->insertBatch($data);
    }
}
