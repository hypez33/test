<?php
declare(strict_types=1);

// lib/config.php — environment configuration
function env_str(string $key, ?string $default=null): string {
  $v = getenv($key);
  if($v===false || $v==='') return $default ?? '';
  return $v;
}
function is_vercel(): bool { return (bool) getenv('VERCEL'); }
function cache_path(string $file): string {
  $tmp = sys_get_temp_dir();
  if(is_writable($tmp)) return rtrim($tmp, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
  $storage = __DIR__ . '/../storage';
  if(!is_dir($storage)) @mkdir($storage, 0777, true);
  return $storage . DIRECTORY_SEPARATOR . $file;
}

// REQUIRED ENV VARS (configure in Vercel Project Settings → Environment Variables)
define('MOBILE_DE_USER', env_str('MOBILE_DE_USER', ''));
define('MOBILE_DE_PASSWORD', env_str('MOBILE_DE_PASSWORD', ''));
define('MOBILE_DE_CUSTOMER_ID', env_str('MOBILE_DE_CUSTOMER_ID', '')); // seller-key / customerId
define('MOBILE_DE_CUSTOMER_NUMBER', env_str('MOBILE_DE_CUSTOMER_NUMBER', '')); // optional alternative filter
