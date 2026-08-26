# 📍 Dokumentasi Tracking & Analitik Halaman Lokasi Sozo Skin Clinic

Dokumen ini menjelaskan spesifikasi teknis, arsitektur data, dan panduan konfigurasi sistem tracking untuk seluruh halaman lokasi Sozo Skin Clinic (baik hub utama `lokasi/index.html` maupun landing page cabang kota seperti `makassar/`, `yogyakarta/`, `medan/`, `bandung/`, dll.).

Sistem tracking ini menggabungkan **Google Tag Manager (GTM) + Google Analytics 4 (GA4)** untuk analitik kuantitatif serta **Microsoft Clarity** untuk analitik kualitatif (heatmaps, tapmaps, dan session replays).

---

## 1. Arsitektur Unified Tracking

Untuk mencegah duplikasi kode dan menjaga performa web, seluruh event interaksi ditangani oleh satu fungsi wrapper terpadu: `trackEvent(eventName, params)`.

```text
[Interaksi User: Search / Filter / Klik Tombol Cabang / Accordion]
                              │
                              ▼
                     trackEvent(name, params)
                     ┌────────┴────────┐
                     ▼                 ▼
           window.dataLayer.push   window.clarity("event" & "set")
                     │                 │
                     ▼                 ▼
            Google Tag Manager   Microsoft Clarity
                     │           (Heatmaps, Tap Maps, Replays,
                     ▼            Custom Tags Filter)
             Google Analytics 4 
          (Laporan Ranking, Tabel, 
           Grafik, & Konversi Lead)
```

---

## 2. Skema Event & Parameter Data

### A. Hub Utama Pencarian Lokasi (`lokasi/index.html`)

| Event Name | Parameter Data | Trigger / Aksi User | Tujuan Analitik |
| :-- | :-- | :-- | :-- |
| `location_search` | `search_keyword`<br>`filter_province`<br>`results_found` | User mengetik kata kunci atau submit pencarian | Mengetahui lokasi/cabang apa yang paling banyak dicari pengunjung |
| `location_zero_result` | `zero_search_keyword`<br>`zero_province_filter` | Pencarian tidak menghasilkan klinik (0 hasil) | Mendeteksi area/kota dengan demand tinggi untuk potensi pembukaan cabang baru |
| `branch_wa_click` | `clicked_branch_name`<br>`clicked_branch_province` | User klik tombol **"Buat Jadwal"** (WhatsApp) di kartu cabang | Mengukur cabang mana yang menghasilkan konversi lead/reservasi tertinggi |
| `branch_maps_click` | `clicked_branch_name`<br>`clicked_branch_province` | User klik tombol **"Lihat Maps"** di kartu cabang | Mengukur cabang mana yang paling sering dicari rute/navigasi fisiknya |
| `quick_link_click` | `quick_city` | User klik salah satu tombol kota cepat (Jakarta, Jogja, dll.) | Mengetahui kota populer yang langsung dituju tanpa mengetik di search bar |
| `filter_province_apply`| `selected_province` | User memilih provinsi di modal filter dan klik "TERAPKAN" | Mengetahui sebaran pencarian per provinsi |
| `modal_location_open` | *(tanpa parameter)* | User membuka popup modal "Pilih Lokasi" | Mengukur interaksi penggunaan filter modal |
| `pagination_click` | `target_page` | User klik nomor halaman atau panah pagination | Mengetahui kedalaman penelusuran daftar klinik |
| `reset_search_click` | *(tanpa parameter)* | User klik tombol "Reset Pencarian" di Empty State | Mengukur aksi pemulihan saat pencarian gagal |
| `floating_promo_click`| `destination` | User klik tombol floating promo di pojok kanan bawah | Mengukur daya tarik tombol promo melayang |

### B. Landing Page Cabang Kota (`lokasi/[kota]/index.html`)

| Event Name | Parameter Data | Trigger / Aksi User | Tujuan Analitik |
| :-- | :-- | :-- | :-- |
| `branch_view` | `branch_name`<br>`branch_province` | User memilih cabang aktif di multi-branch selector (misal: Panakkukang vs Tamalanrea) | Mengetahui cabang spesifik mana yang paling banyak dilihat detailnya |
| `branch_wa_click` | `clicked_branch_name`<br>`clicked_branch_province` | User klik tombol WhatsApp utama, promo card, atau banner CTA | Mengukur konversi lead reservasi per kota/cabang |
| `branch_maps_click` | `clicked_branch_name`<br>`clicked_branch_province` | User klik tombol "Buka Maps >" | Mengukur minat navigasi langsung ke cabang fisik |
| `directions_toggle` | `direction_target`<br>`city` | User membuka accordion panduan rute "Cara Menuju Lokasi" | Mengetahui cabang yang rute transportasinya paling diperhatikan user |
| `faq_toggle` | `faq_question`<br>`city` | User membuka salah satu pertanyaan pada accordion FAQ | Mengetahui informasi atau keraguan utama pasien di kota tersebut |

---

## 3. Implementasi di Front-End

### A. Unmasking Text Input (Khusus Microsoft Clarity)
Secara default, Microsoft Clarity menyensor input teks menjadi `*****`. Agar kata kunci yang diketik user (misal: "Jogja", "Kemang", "Bali", "Panakkukang") terlihat jelas di rekaman session replay, atribut `data-clarity-unmask="true"` dipasang pada input:

```html
<!-- Search bar utama -->
<input type="text" id="sozoSearchInput" data-clarity-unmask="true" placeholder="Ketik lokasi / nama cabang klinik...">

<!-- Search bar modal provinsi -->
<input type="text" id="sozoModalSearchInput" data-clarity-unmask="true" placeholder="Cari Provinsi / Kota...">
```

### B. Atribut Data Semantik
Elemen interaktif dilengkapi atribut data untuk identifikasi elemen yang akurat pada heatmap:
- Tombol quick link: `data-city="Jakarta"`
- Kartu cabang: `data-branch-name="..."` dan `data-branch-province="..."`
- Floating promo: `id="sozoFloatingPromoBtn"`

### C. Helper Function di JavaScript
```javascript
function trackEvent(eventName, params = {}) {
  // 1. Dorong ke dataLayer untuk GTM & GA4
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push({
    event: eventName,
    ...params
  });

  // 2. Kirim ke Microsoft Clarity (Custom Events & Custom Tags)
  if (typeof window.clarity === "function") {
    window.clarity("event", eventName);
    Object.keys(params).forEach(k => {
      if (params[k] !== undefined && params[k] !== null && params[k] !== "") {
        window.clarity("set", k, String(params[k]));
      }
    });
  }
}
```

---

## 4. Konfigurasi Google Tag Manager (GTM)

### A. Data Layer Variables (Menu `Variables` > `User-Defined Variables`)
Tipe variabel: **Data Layer Variable** (Version 2).

1. `dlv - search_keyword` ➜ Data Layer Variable Name: `search_keyword`
2. `dlv - filter_province` ➜ Data Layer Variable Name: `filter_province`
3. `dlv - results_found` ➜ Data Layer Variable Name: `results_found`
4. `dlv - zero_search_keyword` ➜ Data Layer Variable Name: `zero_search_keyword`
5. `dlv - clicked_branch_name` ➜ Data Layer Variable Name: `clicked_branch_name`
6. `dlv - clicked_branch_province` ➜ Data Layer Variable Name: `clicked_branch_province`
7. `dlv - quick_city` ➜ Data Layer Variable Name: `quick_city`
8. `dlv - branch_name` ➜ Data Layer Variable Name: `branch_name`
9. `dlv - branch_province` ➜ Data Layer Variable Name: `branch_province`
10. `dlv - direction_target` ➜ Data Layer Variable Name: `direction_target`
11. `dlv - faq_question` ➜ Data Layer Variable Name: `faq_question`
12. `dlv - city` ➜ Data Layer Variable Name: `city`

### B. Custom Event Triggers (Menu `Triggers`)
Tipe trigger: **Custom Event** (Fires on: *All Custom Events*).

1. `CE - location_search` ➜ Event Name: `location_search`
2. `CE - location_zero_result` ➜ Event Name: `location_zero_result`
3. `CE - branch_wa_click` ➜ Event Name: `branch_wa_click`
4. `CE - branch_maps_click` ➜ Event Name: `branch_maps_click`
5. `CE - quick_link_click` ➜ Event Name: `quick_link_click`
6. `CE - branch_view` ➜ Event Name: `branch_view`
7. `CE - directions_toggle` ➜ Event Name: `directions_toggle`
8. `CE - faq_toggle` ➜ Event Name: `faq_toggle`

### C. GA4 Event Tags (Menu `Tags`)
Tipe tag: **Google Analytics: GA4 Event**.

1. **`GA4 - Event - Location Search`**
   - Event Name: `location_search`
   - Parameters:
     - `search_keyword` ➜ `{{dlv - search_keyword}}`
     - `filter_province` ➜ `{{dlv - filter_province}}`
     - `results_found` ➜ `{{dlv - results_found}}`
   - Trigger: `CE - location_search`

2. **`GA4 - Event - Location Zero Result`**
   - Event Name: `location_zero_result`
   - Parameters:
     - `zero_search_keyword` ➜ `{{dlv - zero_search_keyword}}`
   - Trigger: `CE - location_zero_result`

3. **`GA4 - Event - Branch WA Click`**
   - Event Name: `branch_wa_click`
   - Parameters:
     - `branch_name` ➜ `{{dlv - clicked_branch_name}}`
     - `branch_province` ➜ `{{dlv - clicked_branch_province}}`
   - Trigger: `CE - branch_wa_click`

4. **`GA4 - Event - Branch Maps Click`**
   - Event Name: `branch_maps_click`
   - Parameters:
     - `branch_name` ➜ `{{dlv - clicked_branch_name}}`
     - `branch_province` ➜ `{{dlv - clicked_branch_province}}`
   - Trigger: `CE - branch_maps_click`

5. **`GA4 - Event - Quick Link Click`**
   - Event Name: `quick_link_click`
   - Parameters:
     - `city_name` ➜ `{{dlv - quick_city}}`
   - Trigger: `CE - quick_link_click`

6. **`GA4 - Event - Branch View`**
   - Event Name: `branch_view`
   - Parameters:
     - `branch_name` ➜ `{{dlv - branch_name}}`
     - `branch_province` ➜ `{{dlv - branch_province}}`
   - Trigger: `CE - branch_view`

7. **`GA4 - Event - Directions Toggle`**
   - Event Name: `directions_toggle`
   - Parameters:
     - `direction_target` ➜ `{{dlv - direction_target}}`
     - `city` ➜ `{{dlv - city}}`
   - Trigger: `CE - directions_toggle`

8. **`GA4 - Event - FAQ Toggle`**
   - Event Name: `faq_toggle`
   - Parameters:
     - `faq_question` ➜ `{{dlv - faq_question}}`
     - `city` ➜ `{{dlv - city}}`
   - Trigger: `CE - faq_toggle`

---

## 5. Konfigurasi Google Analytics 4 (GA4)

Agar parameter event dapat ditampilkan di tabel laporan standar dan menu Exploration, daftarkan parameter sebagai **Custom Dimension** di GA4:

*Navigasi:* **Admin (Ikon Gerigi)** ➜ **Data display** ➜ **Custom definitions** ➜ **Create custom dimension**.

| Dimension Name | Scope | Event Parameter | Deskripsi |
| :-- | :-- | :-- | :-- |
| **Search Keyword** | Event | `search_keyword` | Kata kunci pencarian lokasi klinik di hub utama |
| **Filter Province** | Event | `filter_province` | Provinsi yang difilter user |
| **Zero Search Keyword** | Event | `zero_search_keyword` | Kata kunci pencarian tanpa hasil klinik |
| **Branch Name** | Event | `branch_name` | Nama cabang klinik yang diklik / dilihat |
| **Branch Province** | Event | `branch_province` | Provinsi cabang klinik yang diklik / dilihat |
| **Quick City** | Event | `city_name` | Kota dari tombol quick link |
| **Direction Target** | Event | `direction_target` | Judul cabang/rute transportasi yang dibuka |
| **FAQ Question** | Event | `faq_question` | Teks pertanyaan FAQ yang dibuka pengunjung |
| **City** | Event | `city` | Nama kota halaman yang sedang dibuka |

---

## 6. Cara Analisis Data di Dashboard

### A. Membaca Laporan di Google Analytics 4 (GA4)

1. **Ranking Cabang Paling Populer (WhatsApp vs Maps):**
   - Masuk ke **Explore** ➜ Buat *Free-form report*.
   - **Dimensions:** Masukkan `Branch Name` dan `Event name`.
   - **Metrics:** Masukkan `Event count`.
   - **Filters:** `Event name` *matches regex* `branch_wa_click|branch_maps_click`.
   - *Hasil:* Tabel perbandingan jumlah chat WA dan pembukaan Maps per cabang.

2. **Daftar Lokasi Paling Banyak Dicari (Demand Analysis):**
   - **Dimensions:** Masukkan `Search Keyword`.
   - **Metrics:** Masukkan `Event count`.
   - **Filters:** `Event name` *equals* `location_search`.
   - *Hasil:* Daftar urutan kota/area yang paling sering dicari user.

3. **Demand Cabang Baru (Zero Search Result):**
   - **Dimensions:** Masukkan `Zero Search Keyword`.
   - **Metrics:** Masukkan `Event count`.
   - **Filters:** `Event name` *equals* `location_zero_result`.
   - *Hasil:* Daftar kota yang dicari pengunjung namun belum memiliki cabang klinik Sozo.

4. **Pertanyaan Pasien Paling Populer (FAQ Analysis):**
   - **Dimensions:** Masukkan `FAQ Question` dan `City`.
   - **Metrics:** Masukkan `Event count`.
   - **Filters:** `Event name` *equals* `faq_toggle`.
   - *Hasil:* Topik pertanyaan yang paling banyak memicu rasa penasaran atau keraguan pasien sebelum booking.

### B. Membaca Laporan di Microsoft Clarity

1. **Heatmap & Tap Map:**
   - Buka tab **Heatmaps** ➜ Pilih URL halaman lokasi.
   - Filter tipe perangkat (Mobile / Desktop).
   - Amati persentase klik pada area quick links vs input search vs tombol kartu klinik vs FAQ accordion.
2. **Session Recordings Tersegmentasi:**
   - Buka tab **Recordings** ➜ Klik **Filters** ➜ **Custom Tags**.
   - Pilih tag yang ingin dievaluasi, misalnya:
     - `clicked_branch_name = "Sozo Skin Clinic - Makassar"` (melihat perjalanan user yang konversi di Makassar).
     - `zero_search_keyword` (melihat gestur user saat pencariannya tidak membuahkan hasil).

---

## 7. Struktur Folder & Halaman Lokasi

```text
lokasi/
├── index.html            # Halaman Hub Utama (Locator semua cabang + Tracking)
├── readme.md             # Dokumentasi teknis sistem tracking & analitik
├── style.css             # Stylesheet global halaman lokasi
├── balikpapan/           # Landing page cabang Balikpapan (multi-branch: Ruhui Rahayu & BSB Superblock)
├── bandung/              # Template landing page cabang Bandung
├── bekasi/
│   └── cikarang/         # Landing page cabang Cikarang (single location: Ruko CBC)
├── cirebon/              # Template landing page cabang Cirebon (single location)
├── makassar/             # Template landing page cabang Makassar (multi-branch: Panakkukang & Tamalanrea)
├── medan/                # Template landing page cabang Medan (multi-branch: Iskandar Muda & Cemara Asri)
├── palembang/            # Landing page cabang Palembang (single location: Kemuning, Basuki Rachmat)
├── semarang/             # Template landing page cabang Semarang
├── solo/                 # Landing page cabang Solo (single location: Banjarsari)
├── surabaya/             # Template landing page cabang Surabaya
└── yogyakarta/           # Template landing page cabang Yogyakarta (single location)
    ├── index.html        # Layout front-end Elementor + single centered badge + FAQ
    └── style.css         # Styling spesifik cabang Yogyakarta
```
