// Generator katalog halaman /treatment/.
// Membaca treatment/treatments.json, lalu menulis chip keluhan, tab kategori,
// dan kartu treatment sebagai HTML statis ke treatment/index.html (di antara
// penanda <!-- GEN:... --> dan <!-- /GEN:... -->). Kartu ditulis statis supaya
// semua link treatment terbaca Google tanpa menjalankan JavaScript.
//
// Pakai: node treatment/build-treatment-list.mjs

import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const here = dirname(fileURLToPath(import.meta.url));
const dataPath = join(here, 'treatments.json');
const pagePath = join(here, 'index.html');

const INITIAL_VISIBLE = 12; // harus sama dengan PAGE_SIZE di data-treatment.js
const WA_GENERAL = 'https://api.whatsapp.com/send?phone=6285175225664&text=Halo%2C%20saya%20mau%20booking%20promo%20treatment%20General%20di%20Sozo%20%5Bsrc%3AORG-General%5D';

const data = JSON.parse(readFileSync(dataPath, 'utf8'));
const { categories, concerns, treatments } = data;
const catById = Object.fromEntries(categories.map(c => [c.id, c]));

const esc = s => String(s)
  .replace(/&/g, '&amp;')
  .replace(/</g, '&lt;')
  .replace(/>/g, '&gt;')
  .replace(/"/g, '&quot;');

const lower = s => String(s).toLowerCase();

function concernIds(t) {
  const sol = t.solutions.map(lower);
  return concerns
    .filter(c => c.solutions.some(s => sol.includes(lower(s))))
    .map(c => c.id);
}

// Treatment baru di depan, sisanya mengikuti urutan di JSON
const ordered = [...treatments.filter(t => t.isNew), ...treatments.filter(t => !t.isNew)];

for (const t of ordered) {
  if (!catById[t.category]) throw new Error(`Kategori tidak dikenal untuk ${t.id}: ${t.category}`);
}

const iconClock = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';

function card(t, i) {
  const cat = catById[t.category];
  const search = lower([t.name, cat.label, cat.title, ...t.solutions].join(' '));
  const title = t.detailLink
    ? `<a href="${esc(t.detailLink)}">${esc(t.name)}</a>`
    : esc(t.name);
  const action = t.detailLink
    ? `<span class="btn btn--outline btn--sm btn--block tc-card__action" aria-hidden="true">Lihat Detail</span>`
    : `<a href="${WA_GENERAL}" target="_blank" rel="noopener noreferrer" class="btn btn--primary btn--sm btn--block tc-card__action" data-wa-src="organic"><i class="fab fa-whatsapp btn__icon" aria-hidden="true"></i><span>Buat Jadwal</span></a>`;
  const badge = t.isNew ? `\n          <span class="tc-card__badge">Baru</span>` : '';
  return `        <article class="tc-card${t.detailLink ? ' tc-card--link' : ''}" data-cat="${esc(t.category)}" data-concerns="${concernIds(t).join(' ')}" data-search="${esc(search)}">
          <div class="tc-card__media">
            <img src="${esc(t.img)}" alt="${esc(t.name)} di SOZO Skin Clinic" width="800" height="600" loading="lazy" decoding="async">
          </div>${badge}
          <div class="tc-card__body">
            <p class="tc-card__category">${esc(cat.label)}</p>
            <h3 class="tc-card__title">${title}</h3>
            <p class="tc-card__meta">${iconClock}<span>${esc(t.duration)}</span></p>
            <div class="tc-card__footer">
              <p class="tc-card__price">${esc(t.price)}</p>
              ${action}
            </div>
          </div>
        </article>`;
}

const concernsHtml = concerns
  .map(c => `        <button type="button" class="tc-chip" data-concern="${esc(c.id)}" aria-pressed="false">${esc(c.label)}</button>`)
  .join('\n');

const tabsHtml = [
  `        <button type="button" class="tc-tab" data-cat="all" aria-pressed="true">Semua</button>`,
  ...categories.map(c => `        <button type="button" class="tc-tab" data-cat="${esc(c.id)}" data-title="${esc(c.title)}" data-link="${esc(c.link)}" data-desc="${esc(c.description || '')}" aria-pressed="false">${esc(c.label)}</button>`),
].join('\n');

const cardsHtml = ordered.map(card).join('\n');
const countHtml = `Menampilkan ${Math.min(INITIAL_VISIBLE, ordered.length)} dari ${ordered.length} treatment`;

let page = readFileSync(pagePath, 'utf8');
const eol = page.includes('\r\n') ? '\r\n' : '\n';
page = page.replace(/\r\n/g, '\n');

function fill(name, html) {
  const re = new RegExp(`(<!-- GEN:${name} -->)[\\s\\S]*?(\\s*<!-- /GEN:${name} -->)`);
  if (!re.test(page)) throw new Error(`Penanda GEN:${name} tidak ditemukan di index.html`);
  page = page.replace(re, (_, open, close) => `${open}\n${html}${close}`);
}

fill('CONCERNS', concernsHtml);
fill('TABS', tabsHtml);
fill('COUNT', `        ${countHtml}`);
fill('CARDS', cardsHtml);

writeFileSync(pagePath, page.replace(/\n/g, eol));
console.log(`index.html diperbarui: ${ordered.length} kartu, ${categories.length} kategori, ${concerns.length} keluhan`);
