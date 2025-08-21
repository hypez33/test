<?php
declare(strict_types=1);
// /img.php?u=<remote>
$src = $_GET['u'] ?? '';
if(!$src){ http_response_code(400); header('Content-Type:text/plain'); echo 'Missing u'; exit; }
$ch = curl_init($src);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_TIMEOUT => 15,
  CURLOPT_SSL_VERIFYPEER => false,
]);
$body = curl_exec($ch);
$ct = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: 'image/jpeg';
curl_close($ch);
header('Content-Type: '.$ct);
header('Cache-Control: public, max-age=3600');
echo $body;
