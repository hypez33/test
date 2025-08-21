# Autozentrum Kiener – Vercel-Paket (Stand: 2025-08-21)

Dieses Paket macht deine bestehende Seite **Vercel-kompatibel**, ohne sie komplett umzubauen.

## Struktur
```
dashboard/
  api/
    health.php
    refresh.php
    vehicles.php
    img.php
  assets/
    css/style.css
    js/main.js
  lib/
    config.php
    utils.php
  storage/               # lokal; auf Vercel nicht persistent
  datenschutz.html
  impressum.html
  index.html
  kontakt.html
  vercel.json
```

## Einrichtung (Vercel)
1. **Project Root** in Vercel auf `dashboard` setzen.
2. **Environment Variables** unter *Settings → Environment Variables* anlegen:
   - `MOBILE_DE_USER` = *API Benutzername*
   - `MOBILE_DE_PASSWORD` = *API Passwort*
   - `MOBILE_DE_CUSTOMER_ID` = *seller-key / customerId* (oder alternativ `MOBILE_DE_CUSTOMER_NUMBER`)
3. **Deploy**. PHP-Funktionen werden durch `vercel-php` automatisch bereitgestellt.
4. **Test**:
   - `/api/health.php` → Status 200 mit JSON
   - `/api/vehicles.php` → liefert Fahrzeuge (mit `page`, `size`, `q`, `fuel`, `sort`)

### Hinweise
- Vercel-Funktionen haben **ephemerales Dateisystem**. Caching in `/storage` ist auf Vercel nicht dauerhaft.
- `main.js` rendert die Karten mobil-optimiert und greift auf `/api/vehicles.php` zu.
- Dark-Mode arbeitet über `tailwind` im **class-mode** (Button in der Topbar).

## Frontend API
- **GET** `/api/vehicles.php?page=1&size=12&q=BMW%203&fuel=Benzin&sort=price-asc`
  - Antwort: `{ status, total, page, pageSize, items:[{id,title,price,priceFormatted,mileage,mileageFormatted,year,fuel,gearbox,image,url}] }`
- **GET** `/api/refresh.php?pages=2` → optionales Warmup
- **GET** `/api/health.php`

Viel Erfolg!
