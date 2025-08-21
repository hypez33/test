<?php
declare(strict_types=1);
// /api/img.php?src=<remote url>
$src = $_GET['src'] ?? '';
if(!$src){ http_response_code(400); echo 'Missing src'; exit; }
$ch = curl_init($src);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_TIMEOUT => 15,
]);
$body = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);
header('Content-Type: ' . ($info['content_type'] ?? 'image/jpeg'));
header('Cache-Control: public, max-age=3600');
echo $body;
