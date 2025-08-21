<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/utils.php';

/**
 * /api/vehicles.php
 * Proxy to mobile.de Search API for a given seller (customerId or customerNumber).
 * Supports: page, size, q, fuel, sort (price-asc/price-desc/km-asc/km-desc/year-asc/year-desc).
 */
$page = max(1, (int)($_GET['page'] ?? 1));
$size = min(60, max(1, (int)($_GET['size'] ?? 12)));
$q = trim((string)($_GET['q'] ?? ''));
$fuel = trim((string)($_GET['fuel'] ?? ''));
$sort = (string)($_GET['sort'] ?? 'price-asc');

$qs = [
  'page.size' => (string)$size,
  'page.number' => (string)$page,
];

// Filter by seller
if(MOBILE_DE_CUSTOMER_ID !== ''){
  $qs['customerId'] = MOBILE_DE_CUSTOMER_ID;
}elseif(MOBILE_DE_CUSTOMER_NUMBER !== ''){
  $qs['customerNumber'] = MOBILE_DE_CUSTOMER_NUMBER;
}

// Mapping sort → API params
switch($sort){
  case 'price-asc':  $qs['sort.field']='PRICE'; $qs['sort.order']='ASCENDING'; break;
  case 'price-desc': $qs['sort.field']='PRICE'; $qs['sort.order']='DESCENDING'; break;
  case 'km-asc':     $qs['sort.field']='MILEAGE'; $qs['sort.order']='ASCENDING'; break;
  case 'km-desc':    $qs['sort.field']='MILEAGE'; $qs['sort.order']='DESCENDING'; break;
  case 'year-asc':   $qs['sort.field']='FIRST_REGISTRATION'; $qs['sort.order']='ASCENDING'; break;
  case 'year-desc':  $qs['sort.field']='FIRST_REGISTRATION'; $qs['sort.order']='DESCENDING'; break;
}

if($q !== ''){
  $qs['modelDescription.contains'] = $q;
}
if($fuel !== ''){
  $map = ['Benzin'=>'PETROL','Diesel'=>'DIESEL','Hybrid'=>'HYBRID','Elektrisch'=>'ELECTRIC'];
  $qs['fuel'] = $map[$fuel] ?? $fuel;
}

$query = http_build_query($qs);
$url = "https://services.mobile.de/search-api/search?".$query;

list($status, $body) = http_get($url);
if($status !== 200) error_out("Upstream mobile.de Fehler ($status)", 502);

$payload = json_decode($body, true);
if(!is_array($payload)) error_out("Antwort von mobile.de ist kein JSON.", 502);

$total = (int)($payload['total'] ?? 0);
$ads = $payload['ads'] ?? [];

$items = [];
foreach($ads as $ad){
  $price = $ad['price']['consumerPriceGross'] ?? null;
  $mileage = $ad['mileage'] ?? null;
  $firstReg = (string)($ad['firstRegistration'] ?? '');
  $year = $firstReg !== '' ? (int)substr($firstReg, 0, 4) : null;

  // Image
  $img = null;
  if(isset($ad['images']['images'][0])){
    $im = $ad['images']['images'][0];
    $img = $im['l'] ?? $im['m'] ?? $im['xl'] ?? $im['s'] ?? $im['icon'] ?? null;
  }

  $title = trim(($ad['make'] ?? '') . ' ' . ($ad['model'] ?? '') . ' ' . ($ad['modelDescription'] ?? ''));
  $items[] = [
    'id' => $ad['mobileAdId'] ?? null,
    'title' => $title !== '' ? $title : ($ad['modelDescription'] ?? 'Fahrzeug'),
    'price' => $price,
    'priceFormatted' => fmt_price($price),
    'mileage' => $mileage,
    'mileageFormatted' => fmt_km($mileage),
    'year' => $year,
    'fuel' => map_fuel($ad['fuel'] ?? null),
    'gearbox' => map_gearbox($ad['gearbox'] ?? null),
    'image' => $img,
    'url' => $ad['detailPageUrl'] ?? null,
  ];
}

json_out(['status'=>'ok','total'=>$total,'page'=>$page,'pageSize'=>$size,'items'=>$items]);
