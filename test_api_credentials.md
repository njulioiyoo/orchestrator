# Testing API Credentials Admin Interface

## Langkah-langkah untuk Testing

### 1. Login sebagai Admin
- Username: `admin@admin.com` atau `admin@orchestrator.com`
- Password: sesuai dengan yang ada di database

### 2. Navigasi ke Tenant Show Page
- Masuk ke menu **System → Tenants**
- Klik **View** pada salah satu tenant
- Scroll ke bagian **API Credentials**

### 3. Ekspektasi yang Harus Terlihat

#### Section "API Credentials" harus ada dengan:
- **Header**: "API Credentials" dengan tombol "Generate New"
- **Tenant Type Badge**: "Regular" atau "API Only" 
- **Web Login Badge**: "Allowed" atau "Blocked"
- **Table API Credentials** (kosong jika belum ada)

#### Jika tidak terlihat, kemungkinan masalah:
1. **Asset belum di-build** (perlu npm run build)
2. **Browser cache** (perlu clear cache)
3. **JavaScript error** (check console browser)
4. **Permission issue** (login sebagai admin)

### 4. Test Generate API Credentials
- Klik tombol **"Generate New"**
- Modal harus muncul dengan form:
  - Rate Limits (per minute)
  - Rate Limits (per hour)
  - Allowed Domains (textarea)
  - Expires At (datetime)
  - **☑️ Make this tenant API-only** (checkbox)

### 5. Test dengan API-Only Option
- Centang checkbox **"Make this tenant API-only"**
- Warning harus muncul: "This will disable web login"
- Submit form
- Alert harus muncul dengan API Key & Secret
- Page refresh otomatis
- Tenant type badge berubah jadi "API Only"
- Web login badge berubah jadi "Blocked"

### 6. Test Manual via API (Backup)
Jika UI tidak muncul, bisa test via API:

```bash
# Test create API credentials
curl -X POST http://localhost:8982/api/v1/tenants/1/api-credentials \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "make_api_only": true,
    "rate_limits": {
      "requests_per_minute": 100,
      "requests_per_hour": 1000
    }
  }'
```

### 7. Debugging Tips

#### Check Database:
```sql
SELECT id, name, tenant_type, allow_web_login FROM tenants;
SELECT * FROM tenant_api_credentials;
```

#### Check Routes:
```bash
php artisan route:list | grep api-credentials
```

#### Check Permissions:
```bash
php artisan tinker
$user = App\Models\User::where('email', 'admin@admin.com')->first();
$user->getAllPermissions();
```

### 8. Troubleshooting

#### Problem: UI Section tidak muncul
- **Solution**: Clear browser cache, check console errors
- **Check**: Apakah login sebagai admin user yang benar

#### Problem: API calls gagal
- **Solution**: Check network tab, verify API endpoints
- **Check**: Apakah CSRF token valid

#### Problem: Permission denied
- **Solution**: Pastikan user memiliki permission `manage_tenants`
- **Check**: User role = super-admin

### 9. Expected Result

Setelah semua berjalan, di halaman Tenant Show harus terlihat:

```
[Tenant: Example Tenant]

API Credentials                               [Generate New]
┌─────────────────────────────────────────────────────────────┐
│ Tenant Type: [Regular] | Web Login: [Allowed]              │
│                                                             │
│ API Key              | Status | Created    | Actions       │
│ ak_xxxxxxxxxxxxx...  | Active | 2025-07-16 | [👁️][🔄][🗑️] │
└─────────────────────────────────────────────────────────────┘
```

### 10. Manual Fix (If Needed)

Jika UI masih tidak muncul, bisa tambahkan section manual:

1. Buka file `resources/js/pages/system/tenants/Show.vue`
2. Cari section setelah "Tenant Information"
3. Tambahkan HTML untuk API Credentials
4. Build ulang assets: `npm run build`
5. Clear cache browser

### 11. Success Criteria

✅ Section "API Credentials" terlihat di halaman
✅ Button "Generate New" berfungsi
✅ Modal form muncul dengan semua field
✅ Checkbox "Make API-only" ada dan berfungsi
✅ Tenant type berubah setelah generate
✅ Web login status berubah setelah API-only
✅ Table menampilkan API credentials
✅ Actions (view, regenerate, delete) berfungsi