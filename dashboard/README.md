# Autozentrum Kiener – Vercel-Paket (Neuaufbau, 2025-08-21)

Dieses Paket nutzt **deine vorhandene** `assets/css/style.css` und `assets/js/main.js` 1:1 und liefert nur die **notwendigen PHP-APIs + vercel.json** für Vercel.

## Quickstart (Vercel)
1. **Project Root** auf `dashboard` setzen.
2. **Environment Variables** anlegen:
   - `MOBILE_DE_USER`
   - `MOBILE_DE_PASSWORD`
   - `MOBILE_DE_CUSTOMER_ID` *(oder alternativ `MOBILE_DE_CUSTOMER_NUMBER`)*
3. Deploy.
4. Test: `/api/health.php`, `/api/vehicles.php?page=1&size=12`

## Warum der frühere Fehler?
Der Fehler _“invalid runtime: api/health (nodejs18.x)”_ entsteht, wenn irgendwo eine **Node‑Runtime** zugewiesen wird. Dieses Paket weist **nur** die PHP‑Runtime zu:
```json
{
  "functions": {
    "api/**/*.php": { "runtime": "vercel-php@0.7.4" }
  }
}
```
Entferne ggf. alte Ordner/Dateien wie `api/health` (ohne `.php`) oder alte `functions`‑Einträge für Node.

## Endpunkte
- `GET /api/vehicles.php?page=1&size=12&q=BMW&fuel=Benzin&sort=price-asc`
  - Antwort: `{ status,total,page,pageSize,items:[{ id,title,price,priceFormatted,mileage,mileageFormatted,year,fuel,gearbox,image,url }] }`
- `GET /api/refresh.php?pages=1` – einfacher Connectivity‑Check
- `GET /api/health.php` – `{ status:"ok" }`
- `GET /img.php?u=<remote>` – Bildproxy (für externe Bilder)

**Hinweis:** Vercel‑Functions haben ein **ephemeres Filesystem** – Persistenz via `/storage` ist dort nicht garantiert.
