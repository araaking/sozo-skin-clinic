#!/usr/bin/env node
// Tes komparasi akurasi rank tracking Searlo: /search/web vs /rankings/check (+ /serp sebagai pembanding).
// Pakai: node --env-file=tools/searlo-rank-test/.env tools/searlo-rank-test/run.mjs [--domain sozoskinclinic.com]
// Output: tools/searlo-rank-test/output/<timestamp>/{results.csv, raw.json, summary.md}

import { mkdirSync, writeFileSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";

const BASE = "https://api.searlo.tech/api/v1";
const GL = "id";
const HL = "id";
const DEPTH = 30;
const MAX_RETRY = 3;

const KEYWORDS = [
  { keyword: "klinik kecantikan terdekat", local: true },
  { keyword: "beauty treatment", local: false },
  { keyword: "hair treatment", local: false },
  { keyword: "hair growth treatment", local: false },
  { keyword: "perawatan rambut rontok", local: false },
  { keyword: "prp hair treatment", local: false },
];

// Situs Indonesia ber-TLD non-.id untuk sanity check gl=id. Domain di luar daftar ini
// dan tanpa akhiran .id dihitung "bukan Indonesia" dan ditampilkan di laporan untuk dicek manual.
const KNOWN_ID_SITES = [
  "alodokter.com", "halodoc.com", "klikdokter.com", "hellosehat.com", "sehatq.com", "doktersehat.com",
  "detik.com", "kompas.com", "liputan6.com", "tribunnews.com", "kumparan.com", "idntimes.com",
  "okezone.com", "cnnindonesia.com", "suara.com", "fimela.com", "popbela.com", "beautynesia.id",
  "femaledaily.com", "sociolla.com", "tokopedia.com", "bukalapak.com", "blibli.com", "lazada.co.id",
  "erhaclinic.com", "zapclinic.com", "sozoskinclinic.com", "siloamhospitals.com",
];

const argDomain = process.argv.indexOf("--domain");
const DOMAIN = (argDomain > -1 ? process.argv[argDomain + 1] : "sozoskinclinic.com").toLowerCase();

const API_KEY = process.env.SEARLO_API_KEY;
if (!API_KEY) {
  console.error("SEARLO_API_KEY belum di-set. Set dulu environment variable-nya.");
  process.exit(1);
}

const here = dirname(fileURLToPath(import.meta.url));
const stamp = new Date().toISOString().replace(/[:.]/g, "-");
const outDir = join(here, "output", stamp);
mkdirSync(outDir, { recursive: true });

const raw = { started_at: new Date().toISOString(), domain: DOMAIN, gl: GL, hl: HL, requests: [] };
let lastRemaining = null;

class StopTest extends Error {}

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

// Tes berhenti hanya kalau API menolak gl=id: semua 422, atau 400 yang menyebut negara/lokasi/gl.
// 400 lain (mis. validasi keyword) dicatat sebagai error keyword itu saja.
function rejectsGl(status, data) {
  if (status === 422) return true;
  if (status !== 400) return false;
  const text = `${data?.code ?? ""} ${data?.message ?? ""} ${JSON.stringify(data?.details ?? data?.errors ?? "")}`;
  return /COUNTRY|LOCATION|\bgl\b/i.test(text);
}

async function call(method, path, { params, body, label }) {
  const url = new URL(BASE + path);
  if (params) for (const [k, v] of Object.entries(params)) url.searchParams.set(k, String(v));

  for (let attempt = 0; attempt <= MAX_RETRY; attempt++) {
    const sentAt = new Date().toISOString();
    let res, data, text;
    try {
      res = await fetch(url, {
        method,
        headers: { "x-api-key": API_KEY, ...(body ? { "content-type": "application/json" } : {}) },
        body: body ? JSON.stringify(body) : undefined,
      });
      text = await res.text();
      try { data = JSON.parse(text); } catch { data = null; }
    } catch (err) {
      const entry = { label, method, path, params, body, attempt, sent_at: sentAt, network_error: String(err) };
      raw.requests.push(entry);
      if (attempt < MAX_RETRY) { await sleep(2000); continue; }
      return { ok: false, status: 0, data: null, entry };
    }

    const deducted = res.headers.get("x-credits-deducted");
    const remaining = res.headers.get("x-credits-remaining");
    if (remaining !== null) lastRemaining = remaining;

    const entry = {
      label, method, path, params, body, attempt, sent_at: sentAt,
      status: res.status,
      headers: {
        "x-credits-deducted": deducted,
        "x-credits-remaining": remaining,
        "x-cache": res.headers.get("x-cache"),
        "retry-after": res.headers.get("retry-after"),
      },
      response: data ?? text,
    };
    raw.requests.push(entry);

    if (rejectsGl(res.status, data)) {
      throw new StopTest(`API menolak gl=${GL} di ${label}: HTTP ${res.status} ${data?.code ?? ""} ${data?.message ?? ""}`.trim());
    }
    if ((res.status === 429 || res.status === 503) && data?.retryable === true && attempt < MAX_RETRY) {
      const wait = Number(res.headers.get("retry-after")) || 5;
      console.log(`  ${label}: ${res.status} ${data?.code ?? ""}, tunggu ${wait}s (retry ${attempt + 1}/${MAX_RETRY})`);
      await sleep(wait * 1000);
      continue;
    }
    return { ok: res.ok && data?.success !== false, status: res.status, data, entry };
  }
}

function hostOf(urlOrDomain) {
  if (!urlOrDomain) return null;
  try {
    const h = urlOrDomain.includes("://") ? new URL(urlOrDomain).hostname : urlOrDomain.split("/")[0];
    return h.toLowerCase().replace(/^www\./, "");
  } catch {
    return null;
  }
}

const matchesTarget = (host) => !!host && (host === DOMAIN || host.endsWith("." + DOMAIN));

const isIndonesianSite = (host) =>
  !!host && (host.endsWith(".id") || KNOWN_ID_SITES.some((d) => host === d || host.endsWith("." + d)));

function credit(entry, bodyFallback) {
  const h = entry?.headers?.["x-credits-deducted"];
  if (h !== null && h !== undefined && h !== "") return Number(h);
  return bodyFallback ?? null;
}

function normUrl(u) {
  if (!u) return null;
  try {
    const x = new URL(u);
    return (x.hostname.replace(/^www\./, "") + x.pathname.replace(/\/$/, "")).toLowerCase();
  } catch {
    return u.toLowerCase();
  }
}

async function runKeyword({ keyword, local }) {
  console.log(`\n▶ ${keyword}`);
  const row = { keyword, local, errors: [] };

  // Ketiga endpoint ditembak paralel supaya waktunya sedekat mungkin.
  const [rank, webPages, serp] = await Promise.all([
    call("POST", "/rankings/check", {
      label: `rankings/check | ${keyword}`,
      body: { keyword, domain: DOMAIN, depth: DEPTH, gl: GL, hl: HL, cache: false },
    }),
    Promise.all([1, 2, 3].map((page) =>
      call("GET", "/search/web", {
        label: `search/web p${page} | ${keyword}`,
        params: { q: keyword, limit: 10, page, gl: GL, hl: HL },
      }))),
    call("GET", "/serp", {
      label: `serp | ${keyword}`,
      params: { q: keyword, gl: GL, hl: HL, pages: 1, num: 10, aioverview: false, cache: false },
    }),
  ]);

  // /rankings/check
  if (rank.ok && rank.data?.result) {
    const r = rank.data.result;
    row.rank_check_position = r.position ?? null;
    row.rank_check_url = r.url ?? null;
    row.rank_check_found = r.found === true;
    row.rank_check_gl = r.gl ?? null;
    row.competitors_above = r.competitors_above ?? [];
  } else {
    row.errors.push(`rankings/check gagal: HTTP ${rank.status} ${rank.data?.code ?? ""} ${rank.data?.message ?? ""}`.trim());
  }

  // /search/web: deteksi reset rank, hitung posisi absolut
  const pageItems = webPages.map((p, i) => {
    if (!p.ok) {
      row.errors.push(`search/web p${i + 1} gagal: HTTP ${p.status} ${p.data?.code ?? ""} ${p.data?.message ?? ""}`.trim());
      return null;
    }
    return p.data?.items ?? [];
  });

  const laterFirstRanks = [2, 3]
    .map((page) => ({ page, rank: pageItems[page - 1]?.[0]?.rank }))
    .filter((x) => typeof x.rank === "number");
  let rankReset = null;
  if (laterFirstRanks.length) {
    if (laterFirstRanks.every((x) => x.rank === 1)) rankReset = true;
    else if (laterFirstRanks.every((x) => x.rank === (x.page - 1) * 10 + 1)) rankReset = false;
    else rankReset = "tidak konsisten: " + laterFirstRanks.map((x) => `p${x.page} mulai ${x.rank}`).join(", ");
  }
  row.web_rank_reset = rankReset;
  row.web_page_first_ranks = pageItems.map((items, i) => ({ page: i + 1, first_rank: items?.[0]?.rank ?? null, count: items?.length ?? null }));

  row.web_position = null;
  row.web_url = null;
  const allWebOk = pageItems.every((x) => x !== null);
  outer: for (let i = 0; i < pageItems.length; i++) {
    const items = pageItems[i] ?? [];
    for (let j = 0; j < items.length; j++) {
      const it = items[j];
      if (matchesTarget(hostOf(it.link) ?? hostOf(it.domain))) {
        const r = typeof it.rank === "number" ? it.rank : j + 1;
        row.web_position = rankReset === false ? r : i * 10 + r;
        row.web_url = it.link;
        break outer;
      }
    }
  }
  row.web_found = row.web_position !== null;
  row.web_top10 = (pageItems[0] ?? []).slice(0, 10).map((it) => hostOf(it.link) ?? hostOf(it.domain));

  // /serp: hanya organic
  if (serp.ok && Array.isArray(serp.data?.organic)) {
    row.serp_top10 = serp.data.organic.slice(0, 10).map((o) => hostOf(o.url) ?? hostOf(o.domain));
    row.serp_id_sites = row.serp_top10.filter(isIndonesianSite);
    row.serp_non_id_sites = row.serp_top10.filter((d) => !isIndonesianSite(d));
    row.jumlah_situs_indonesia = row.serp_id_sites.length;
  } else {
    row.serp_top10 = null;
    row.jumlah_situs_indonesia = null;
    row.errors.push(`serp gagal: HTTP ${serp.status} ${serp.data?.code ?? ""} ${serp.data?.message ?? ""}`.trim());
  }

  // Perbandingan. undefined = data tidak lengkap, null = tidak berlaku.
  const rp = row.rank_check_position, wp = row.web_position;
  if (row.rank_check_found !== undefined && allWebOk) {
    if (rp !== null && wp !== null) row.selisih_posisi = Math.abs(rp - wp);
    else if (rp === null && wp === null) row.selisih_posisi = 0; // keduanya tidak ketemu di top 30
    else row.selisih_posisi = null; // satu ketemu, satu tidak
    row.found_sepakat = row.rank_check_found === row.web_found;
    row.url_sama = rp !== null && wp !== null ? normUrl(row.rank_check_url) === normUrl(row.web_url) : (rp === null && wp === null ? null : false);
  }

  if (row.web_top10.length && row.serp_top10) {
    const s = new Set(row.serp_top10.filter(Boolean));
    row.overlap = new Set(row.web_top10.filter((d) => d && s.has(d))).size;
  } else {
    row.overlap = null;
  }

  const credits = [
    credit(rank.entry, rank.data?.credits?.used),
    ...webPages.map((p) => credit(p.entry)),
    credit(serp.entry),
  ];
  row.credits_terpakai = credits.every((c) => c === null) ? null : credits.reduce((a, c) => a + (c ?? 0), 0);
  row.credits_header_missing = credits.some((c) => c === null);

  console.log(`  rank_check=${rp ?? "-"} web=${wp ?? "-"} overlap=${row.overlap ?? "-"} reset=${rankReset}`);
  if (row.errors.length) console.log("  error: " + row.errors.join(" | "));
  return row;
}

const fmt = (v) => (v === undefined ? "error" : v === null ? "" : v);
const yn = (v) => (v === undefined ? "error" : v === null ? "n/a" : v ? "ya" : "tidak");
const selisihText = (v) => (v === null ? "satu tidak ketemu" : fmt(v));
const csvCell = (v) => {
  const s = String(v);
  return /[",\n]/.test(s) ? `"${s.replace(/"/g, '""')}"` : s;
};

function writeCsv(rows) {
  const head = ["keyword", "rank_check_position", "rank_check_url", "web_position", "web_url", "selisih_posisi",
    "url_sama", "found_sepakat", "overlap_top10_web_vs_serp", "jumlah_situs_indonesia_top10", "credits_terpakai"];
  const lines = [head.join(",")];
  for (const r of rows) {
    lines.push([r.keyword, fmt(r.rank_check_position), fmt(r.rank_check_url), fmt(r.web_position), fmt(r.web_url),
      selisihText(r.selisih_posisi), yn(r.url_sama), yn(r.found_sepakat), fmt(r.overlap),
      fmt(r.jumlah_situs_indonesia), fmt(r.credits_terpakai)].map(csvCell).join(","));
  }
  writeFileSync(join(outDir, "results.csv"), lines.join("\n") + "\n");
}

function summarize(rows, countriesInfo, stopReason) {
  const L = [];
  L.push(`# Searlo rank tracking test`, ``);
  L.push(`- Waktu: ${raw.started_at}`, `- Domain: ${DOMAIN}`, `- gl=${GL}, hl=${HL}, depth=${DEPTH}`, ``);
  L.push(`## Persiapan: /search/countries`, ``, countriesInfo, ``);
  if (stopReason) L.push(`**Tes dihentikan:** ${stopReason}`, ``);

  const fail = rows.filter((r) => r.errors.length);
  if (fail.length) {
    L.push(`## Request gagal`, ``);
    for (const r of fail) for (const e of r.errors) L.push(`- ${r.keyword}: ${e}`);
    L.push(``);
  }

  const section = (title, set) => {
    const valid = set.filter((r) => r.selisih_posisi !== undefined);
    const d = valid.map((r) => r.selisih_posisi);
    const ov = set.map((r) => r.overlap).filter((x) => typeof x === "number");
    L.push(`## ${title}`, ``);
    L.push(`| Keyword | rank_check | gl rank_check | search/web | Selisih | URL sama | Found sepakat | Overlap |`, `| :-- | --: | :-- | --: | :-- | :-- | :-- | --: |`);
    for (const r of set) {
      L.push(`| ${r.keyword} | ${fmt(r.rank_check_position) || "-"} | ${fmt(r.rank_check_gl) || "-"} | ${fmt(r.web_position) || "-"} | ${selisihText(r.selisih_posisi)} | ${yn(r.url_sama)} | ${yn(r.found_sepakat)} | ${fmt(r.overlap)} |`);
    }
    L.push(``);
    L.push(`- Keyword dengan data lengkap: ${valid.length}/${set.length}`);
    L.push(`- Posisi persis sama: ${d.filter((x) => x === 0).length}`);
    L.push(`- Selisih ≤1: ${d.filter((x) => x !== null && x <= 1).length}`);
    L.push(`- Selisih ≤3: ${d.filter((x) => x !== null && x <= 3).length}`);
    L.push(`- Selisih >3 (termasuk satu ketemu, satu tidak): ${d.filter((x) => x === null || x > 3).length}`);
    L.push(`- Status found berbeda: ${valid.filter((r) => r.found_sepakat === false).length}`);
    L.push(`- Rata-rata overlap top 10 /search/web vs /serp: ${ov.length ? (ov.reduce((a, b) => a + b, 0) / ov.length).toFixed(1) : "n/a"} (dari ${ov.length} keyword)`);
    const both = valid.filter((r) => r.rank_check_position === null && r.web_position === null);
    if (both.length) L.push(`- Catatan: ${both.map((r) => r.keyword).join(", ")} tidak ketemu di kedua endpoint (top ${DEPTH}); dihitung selisih 0.`);
    L.push(``);
    return { valid, d, ov };
  };

  const nonLocal = rows.filter((r) => !r.local);
  const local = rows.filter((r) => r.local);
  const s = section("Keyword non-lokal (dasar kesimpulan)", nonLocal);
  if (local.length) {
    section("Catatan: keyword lokal (tidak dihitung)", local);
    L.push(`Searlo hanya mendukung level negara. Hasil organik "klinik kecantikan terdekat" di Google asli bergantung lokasi pencari, jadi angka di atas tidak mewakili hasil yang dilihat user di kota tertentu.`, ``);
  }

  L.push(`## Field rank di /search/web`, ``);
  for (const r of rows) {
    const status = r.web_rank_reset === true ? "di-reset per halaman" : r.web_rank_reset === false ? "lanjut (11, 21)" : r.web_rank_reset ?? "tidak bisa dicek";
    const pages = r.web_page_first_ranks.map((p) => `p${p.page}: mulai ${p.first_rank ?? "-"} (${p.count ?? "gagal"} item)`).join(", ");
    L.push(`- ${r.keyword}: ${status}. ${pages}`);
  }
  L.push(``);

  L.push(`## Sanity check situs Indonesia (top 10 organik /serp)`, ``);
  L.push(`Dihitung Indonesia: domain berakhiran .id (termasuk .co.id) atau ada di daftar KNOWN_ID_SITES di run.mjs. Domain lain ditampilkan supaya bisa dicek manual.`, ``);
  const weak = [];
  for (const r of rows) {
    if (r.serp_top10 === null) {
      L.push(`- ${r.keyword}: /serp gagal, tidak bisa dicek`);
      continue;
    }
    const total = r.serp_top10.length;
    const majorityNonId = total > 0 && r.serp_non_id_sites.length > total / 2;
    if (majorityNonId) weak.push(r.keyword);
    L.push(`- ${r.keyword}: ${r.jumlah_situs_indonesia}/${total} situs Indonesia${majorityNonId ? " ⚠️" : ""}. Bukan/tidak dikenali: ${r.serp_non_id_sites.join(", ") || "-"}`);
  }
  L.push(``);
  if (weak.length) {
    L.push(`**Peringatan:** mayoritas hasil /serp bukan situs Indonesia untuk ${weak.join(", ")}. gl=${GL} kemungkinan tidak efektif dan posisi tidak mewakili Google Indonesia. Cek dulu daftar domain di atas, karena situs Indonesia ber-TLD .com yang tidak ada di daftar ikut terhitung "bukan".`, ``);
  }

  const totalCredit = rows.reduce((a, r) => a + (r.credits_terpakai ?? 0), 0);
  L.push(`## Credit`, ``);
  L.push(`- Total terpakai (dari header X-Credits-Deducted, fallback credits.used): ${totalCredit}`);
  if (rows.some((r) => r.credits_header_missing)) L.push(`- Sebagian respons tidak membawa header credit; angka total bisa kurang dari tagihan sebenarnya. Cek raw.json.`);
  L.push(`- Sisa credit (X-Credits-Remaining terakhir): ${lastRemaining ?? "header tidak ada"}`, ``);

  L.push(`## Kesimpulan (5 keyword non-lokal)`, ``);
  if (s.valid.length < nonLocal.length || nonLocal.length === 0) {
    L.push(`Data tidak lengkap (${s.valid.length}/${KEYWORDS.filter((k) => !k.local).length} keyword). Kesimpulan tidak bisa ditarik; ulangi tes untuk keyword yang gagal.`);
  } else {
    const c1 = s.d.filter((x) => x !== null && x <= 1).length;
    const c2 = s.valid.every((r) => r.found_sepakat);
    const avgOv = s.ov.length ? s.ov.reduce((a, b) => a + b, 0) / s.ov.length : null;
    const c3 = avgOv !== null && avgOv >= 8;
    const layak = c1 >= 4 && c2 && c3;
    L.push(`- Selisih ≤1: ${c1}/5 (syarat ≥4) → ${c1 >= 4 ? "lolos" : "gagal"}`);
    L.push(`- Found sepakat 100%: ${c2 ? "lolos" : "gagal"}`);
    L.push(`- Rata-rata overlap ≥8: ${avgOv === null ? "n/a" : avgOv.toFixed(1)} → ${c3 ? "lolos" : "gagal"}`, ``);
    L.push(layak
      ? `**/search/web LAYAK dipakai** untuk rank tracking Google Indonesia. Ketiga syarat terpenuhi.`
      : `**Rekomendasi: pakai /rankings/check.** /search/web tidak memenuhi semua syarat di atas.`);
    const worst = [...s.valid].sort((a, b) => (b.selisih_posisi ?? 999) - (a.selisih_posisi ?? 999) || (a.overlap ?? 0) - (b.overlap ?? 0))[0];
    if (worst) L.push(``, `Keyword paling beda: **${worst.keyword}** (rank_check ${worst.rank_check_position ?? "tidak ketemu"}, search/web ${worst.web_position ?? "tidak ketemu"}, overlap ${worst.overlap ?? "n/a"}).`);
  }
  const text = L.join("\n") + "\n";
  writeFileSync(join(outDir, "summary.md"), text);
  return text;
}

async function main() {
  const rows = [];
  let countriesInfo = "";
  let stopReason = null;
  try {
    // /search/countries hanya informasi. Gagal atau tanpa ID tidak menghentikan tes.
    let c;
    try {
      c = await call("GET", "/search/countries", { label: "search/countries" });
    } catch (err) {
      c = { ok: false, status: "-", data: { message: String(err.message ?? err) } };
    }
    const list = c.data?.countries;
    if (!c.ok || !Array.isArray(list)) {
      countriesInfo = `/search/countries gagal: HTTP ${c.status} ${c.data?.code ?? ""} ${c.data?.message ?? ""}`.trim();
    } else {
      const meta = Object.entries(c.data).filter(([k]) => k !== "countries").map(([k, v]) => `${k}=${JSON.stringify(v)}`).join(", ");
      const lines = [
        `Respons: ${meta}. Respons tidak membawa keterangan arti daftar ini.`,
        ``,
        `| Code | Nama | states | exit |`, `| :-- | :-- | :-- | :-- |`,
        ...list.map((x) => `| ${x.code} | ${x.name} | ${x.states} | ${x.exit} |`),
        ``,
      ];
      const id = list.find((x) => String(x.code).toUpperCase() === "ID");
      lines.push(id
        ? `Indonesia ada di daftar (exit=${id.exit}).`
        : `Indonesia (ID) tidak ada di daftar ${list.length} negara. Menurut docs Searlo, request search untuk gl di luar daftar ini dikirim dari exit AS dengan gl diteruskan ke Google. Request tes ini kemungkinan dikirim dari luar Indonesia.`);
      countriesInfo = lines.join("\n");
    }
    console.log(countriesInfo);

    for (const k of KEYWORDS) {
      try {
        rows.push(await runKeyword(k));
      } catch (err) {
        if (err instanceof StopTest) throw err;
        console.log(`  error tak terduga: ${err.message ?? err}`);
        rows.push({ ...k, errors: [`error tak terduga: ${err.message ?? err}`], web_page_first_ranks: [], serp_top10: null, jumlah_situs_indonesia: null });
      }
    }
  } catch (err) {
    if (!(err instanceof StopTest)) throw err;
    stopReason = err.message;
    console.error("\nTes dihentikan: " + stopReason);
  } finally {
    raw.finished_at = new Date().toISOString();
    raw.rows = rows;
    writeFileSync(join(outDir, "raw.json"), JSON.stringify(raw, null, 2));
    writeCsv(rows);
    const summary = summarize(rows, countriesInfo || "Tidak berhasil dicek.", stopReason);
    console.log("\n" + summary);
    console.log(`Output: ${outDir}`);
  }
}

main();
