/**
 * ==========================================================
 * AHREFS RANK TRACKER → GOOGLE SHEETS (RAW DATA)
 * ==========================================================
 * Tarik data posisi keyword dari Ahrefs Rank Tracker (API v3)
 * dan append ke tab "raw-data-ahrefs", untuk desktop + mobile.
 *
 * SETUP SEBELUM DIJALANKAN:
 * 1. Project Settings (ikon gerigi) > Script Properties
 *    Tambah: AHREFS_API_KEY = <API key kalian>
 * 2. Sesuaikan CONFIG di bawah kalau nama tab / project ID beda.
 * 3. Jalankan fungsi `pullAhrefsRankTracker` sekali manual dulu
 *    untuk test & authorize izin akses.
 * 4. Kalau sudah oke, pasang trigger mingguan (lihat instruksi
 *    di bagian paling bawah file ini).
 * ==========================================================
 */

const CONFIG = {
  SHEET_NAME: 'raw-data-ahrefs',
  PROJECT_ID: 8121190,
  DEVICES: ['desktop', 'mobile'],
  // Kolom yang ditarik dari Ahrefs. Urutan HARUS sinkron dengan
  // urutan penulisan di appendRowsToSheet().
  // NOTE: 'position_prev' sengaja TIDAK dimasukkan di sini karena field itu
  // cuma tersedia di response kalau request-nya juga kirim date_compared.
  // Previous position lebih baik dihitung dari histori raw data ini sendiri
  // (baris minggu lalu vs minggu ini), lewat formula di tab Report.
  SELECT_FIELDS: ['keyword', 'url', 'position', 'volume'],
  VOLUME_MODE: 'monthly'
};

/**
 * Fungsi utama — panggil ini manual atau lewat trigger.
 */
function pullAhrefsRankTracker() {
  const apiKey = PropertiesService.getScriptProperties().getProperty('AHREFS_API_KEY');
  if (!apiKey) {
    throw new Error('AHREFS_API_KEY belum diset di Script Properties. Cek Project Settings > Script Properties.');
  }

  const sheet = getOrCreateSheet_(CONFIG.SHEET_NAME);
  const today = formatDate_(new Date());

  let totalRowsAdded = 0;

  CONFIG.DEVICES.forEach(function (device) {
    try {
      const rows = fetchRankTrackerOverview_(apiKey, device, today);
      appendRowsToSheet_(sheet, rows, today, device);
      totalRowsAdded += rows.length;
      Logger.log('Device ' + device + ': ' + rows.length + ' baris berhasil ditarik.');
    } catch (err) {
      Logger.log('ERROR saat narik device ' + device + ': ' + err.message);
      // Lanjut ke device berikutnya walau satu device gagal,
      // supaya satu error tidak menggagalkan seluruh run.
    }
  });

  Logger.log('Selesai. Total baris ditambahkan: ' + totalRowsAdded);
}

/**
 * Panggil endpoint GET /v3/rank-tracker/overview untuk satu device.
 * Return array of objects mentah dari Ahrefs.
 */
function fetchRankTrackerOverview_(apiKey, device, date) {
  const baseUrl = 'https://api.ahrefs.com/v3/rank-tracker/overview';
  const params = {
    project_id: CONFIG.PROJECT_ID,
    date: date,
    device: device,
    select: CONFIG.SELECT_FIELDS.join(','),
    volume_mode: CONFIG.VOLUME_MODE,
    limit: 1000, // naikkan kalau tracked keywords kalian >1000
    output: 'json'
  };

  const url = baseUrl + '?' + buildQueryString_(params);

  const options = {
    method: 'get',
    headers: {
      Authorization: 'Bearer ' + apiKey,
      Accept: 'application/json'
    },
    muteHttpExceptions: true
  };

  const response = UrlFetchApp.fetch(url, options);
  const code = response.getResponseCode();
  const body = response.getContentText();

  if (code !== 200) {
    throw new Error('Ahrefs API error (' + code + '): ' + body);
  }

  const json = JSON.parse(body);
  return json.overviews || [];
}

/**
 * Tulis baris-baris baru ke sheet, APPEND ke bawah (tidak menimpa).
 * Urutan kolom: date_pulled, keywords, url, position, previous_position, volume, device
 */
function appendRowsToSheet_(sheet, rows, datePulled, device) {
  if (!rows || rows.length === 0) return;

  const values = rows.map(function (row) {
    return [
      datePulled,
      row.keyword || '',
      row.url || '',
      row.position !== undefined && row.position !== null ? row.position : '',
      '', // previous_position: dikosongkan di sini, dihitung via formula di tab Report
          // dari baris date_pulled minggu sebelumnya untuk keyword+device yang sama.
      row.volume !== undefined && row.volume !== null ? row.volume : '',
      device
    ];
  });

  const lastRow = sheet.getLastRow();
  sheet.getRange(lastRow + 1, 1, values.length, values[0].length).setValues(values);
}

/**
 * Ambil sheet berdasarkan nama; bikin baru + header kalau belum ada.
 */
function getOrCreateSheet_(sheetName) {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  let sheet = ss.getSheetByName(sheetName);

  if (!sheet) {
    sheet = ss.insertSheet(sheetName);
    const headers = ['date_pulled', 'keywords', 'url', 'position', 'previous_position', 'volume', 'device'];
    sheet.getRange(1, 1, 1, headers.length).setValues([headers]);
    sheet.setFrozenRows(1);
  }

  return sheet;
}

/**
 * Format Date object jadi string YYYY-MM-DD (sesuai format yang diminta Ahrefs).
 */
function formatDate_(date) {
  return Utilities.formatDate(date, Session.getScriptTimeZone(), 'yyyy-MM-dd');
}

/**
 * Bangun query string dari object params.
 */
function buildQueryString_(params) {
  return Object.keys(params)
    .map(function (key) {
      return encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
    })
    .join('&');
}

/**
 * ==========================================================
 * CARA PASANG TRIGGER MINGGUAN (jalan otomatis tiap Senin):
 * ==========================================================
 * 1. Di sidebar kiri Apps Script, klik ikon jam (Triggers).
 * 2. Klik "+ Add Trigger" di kanan bawah.
 * 3. Pilih:
 *    - Function to run: pullAhrefsRankTracker
 *    - Event source: Time-driven
 *    - Type of time based trigger: Week timer
 *    - Day of week: Monday (atau sesuai kebutuhan)
 *    - Time of day: pilih jam, misal 06:00-07:00
 * 4. Save.
 *
 * Rank Tracker di plan Lite update datanya mingguan, jadi
 * trigger mingguan sudah cukup — tidak perlu harian.
 * ==========================================================
 */
