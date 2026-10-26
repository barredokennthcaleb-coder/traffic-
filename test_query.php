<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';
\CodeIgniter\Boot::bootTest($paths);
$db = \Config\Database::connect();
$rows = $db->table('violations')->select('id, ticket_id, created_at')->orderBy('id', 'DESC')->limit(15)->get()->getResultArray();
foreach ($rows as $r) {
    echo $r['id'] . "\t" . $r['ticket_id'] . "\t" . $r['created_at'] . "\n";
}
unlink(__FILE__);
