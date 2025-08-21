<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/utils.php';

/** refresh.php
 * Optional endpoint to force-refresh (warm) cache by fetching first 3 pages.
 * Note: On Vercel PHP the filesystem is ephemeral; this endpoint simply confirms connectivity.
 */

$pages = max(1, min(3, (int)($_GET['pages'] ?? 1)));
$total = 0; $fetched = 0;

for($p=1; $p<=$pages; $p++){
  $url = sprintf('%s?page=%d&size=%d', dirname($_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) . '/vehicles.php', $p, 20);
  list($status, $body) = http_get(str_replace('http:/', 'http://', $url)); // simple
  if($status !== 200) error_out("Fetch Fehler bei Seite $p: $status", 502);
  $data = json_decode($body, true);
  if(isset($data['total'])) $total = (int)$data['total'];
  if(isset($data['items'])) $fetched += count($data['items']);
}

json_out(['status'=>'ok','message'=>'Refreshed','pages'=>$pages,'fetched'=>$fetched,'total'=>$total]);
