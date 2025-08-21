<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/utils.php';

/**
 * /api/refresh.php
 * Minimal warm-up fetch to validate connectivity on Vercel.
 */
$pages = max(1, min(3, (int)($_GET['pages'] ?? 1)));
$total = 0; $fetched = 0;

for($p=1; $p<=$pages; $p++){
  $url = sprintf('%s://%s/api/vehicles.php?page=%d&size=%d',
      $_SERVER['REQUEST_SCHEME'] ?? 'https',
      $_SERVER['HTTP_HOST'] ?? 'localhost',
      $p, 20
  );
  list($status, $body) = http_get($url);
  if($status !== 200) error_out("Fetch Fehler bei Seite $p: $status", 502);
  $data = json_decode($body, true);
  if(isset($data['total'])) $total = (int)$data['total'];
  if(isset($data['items'])) $fetched += count($data['items']);
}

json_out(['status'=>'ok','message'=>'Refreshed','pages'=>$pages,'fetched'=>$fetched,'total'=>$total]);
