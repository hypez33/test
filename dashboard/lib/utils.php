<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

function json_out($data, int $status=200): void {
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store');
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}
function error_out(string $message, int $status=500): void {
  json_out(['status'=>'error','message'=>$message], $status);
}
function basic_auth_header(): string {
  if(!MOBILE_DE_USER || !MOBILE_DE_PASSWORD) error_out("Server: MOBILE_DE_USER / MOBILE_DE_PASSWORD fehlen.", 500);
  $token = base64_encode(MOBILE_DE_USER . ':' . MOBILE_DE_PASSWORD);
  return "Authorization: Basic " . $token;
}
function http_get(string $url): array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => [
      'Accept: application/vnd.de.mobile.api+json', // New JSON format
      basic_auth_header(),
      'Accept-Encoding: gzip',
    ],
    CURLOPT_ENCODING => '',
    CURLOPT_TIMEOUT => 20,
  ]);
  $body = curl_exec($ch);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err = curl_error($ch);
  curl_close($ch);
  if($body === false) error_out("cURL-Fehler: ".$err, 502);
  return [$status, $body];
}
function map_fuel(?string $f): string {
  switch($f){
    case 'PETROL': return 'Benzin';
    case 'DIESEL': return 'Diesel';
    case 'ELECTRIC': return 'Elektrisch';
    case 'HYBRID': return 'Hybrid';
    case 'CNG': return 'Erdgas';
    case 'LPG': return 'Autogas';
    default: return $f ?? '—';
  }
}
function map_gearbox(?string $g): string {
  switch($g){
    case 'AUTOMATIC': return 'Automatik';
    case 'MANUAL': return 'Manuell';
    default: return $g ?? '—';
  }
}
function fmt_price($v): string {
  if($v===null || $v==='') return 'Preis auf Anfrage';
  $n = number_format((float)$v, 0, ',', '.');
  return $n . ' €';
}
function fmt_km($km): string {
  if($km===null || $km==='') return '— km';
  return number_format((int)$km, 0, ',', '.') . ' km';
}
