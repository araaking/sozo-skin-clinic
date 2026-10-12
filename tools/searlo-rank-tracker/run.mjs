#!/usr/bin/env node
// Rank check sozoskinclinic.com via Searlo /rankings/batch. Output TSV + Markdown siap paste ke spreadsheet.
// Pakai (dari folder ini): node --env-file=.env run.mjs

import { existsSync, mkdirSync, readFileSync, writeFileSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";

const BASE = "https://api.searlo.tech/api/v1";
const DOMAIN = "sozoskinclinic.com";
const PARAMS = { depth: 30, gl: "id", hl: "id", device: "desktop", cache: false };
const CREDIT_PER_KEYWORD = 9; // 3 credit per 10 hasil, depth 30
const BATCH_SIZE = 20;
const MAX_RETRY = 3;

const here = dirname(fileURLToPath(import.meta.url));
const API_KEY = process.env.SEARLO_API_KEY;
if (!API_KEY) {
  console.error("SEARLO_API_KEY belum di-set. Isi file .env di folder ini, lalu jalankan: node --env-file=.env run.mjs");
  process.exit(1);
}

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
let creditsUsed = 0;
let lastRemaining = null;

async function call(method, path, body) {
  for (let attempt = 0; ; attempt++) {
    let res, data;
    try {
      res = await fetch(BASE + path, {
        method,
        headers: { "x-api-key": API_KEY, ...(body ? { "content-type": "application/json" } : {}) },
        body: body ? JSON.stringify(body) : undefined,
      });
      const text = await res.text();
      try { data = JSON.parse(text); } catch { data = { message: text.slice(0, 200) }; }
    } catch (err) {
      return { ok: false, status: 0, data: { message: `network error: ${err.message}` } };
    }

    const deducted = res.headers.get("x-credits-deducted");
    if (deducted) creditsUsed += Number(deducted) || 0;
    const remaining = res.headers.get("x-credits-remaining");
    if (remaining !== null) lastRemaining = Number(remaining);

    if ((res.status === 429 || res.status === 503) && data?.retryable === true && attempt < MAX_RETRY) {
      const wait = Number(res.headers.get("retry-after")) || 5;
      console.log(`  ${path}: HTTP ${res.status} ${data?.code ?? ""}, tunggu ${wait}s (retry ${attempt + 1}/${MAX_RETRY})`);
      await sleep(wait * 1000);
      continue;
    }
    return { ok: res.ok && data?.success !== false, status: res.status, data };
  }
}

const errText = (r) => `HTTP ${r.status} ${r.data?.code ?? ""} ${r.data?.message ?? ""}`.replace(/\s+/g, " ").trim();

// Saldo ada di data.available (format terverifikasi 12 Okt 2026).
async function getBalance() {
  const r = await call("GET", "/credit/balance");
  const d = r.data?.data;
  const bal = d?.available ?? d?.balance;
  return r.ok && typeof bal === "number" ? bal : null;
}

function readKeywords() {
  const file = join(here, "keywords.txt");
  if (!existsSync(file)) {
    console.error(`keywords.txt tidak ditemukan di ${here}`);
    process.exit(1);
  }
  const seen = new Map();
  const list = [];
  readFileSync(file, "utf8").split(/\r?\n/).forEach((line, i) => {
    const kw = line.trim().replace(/\s+/g, " ");
    if (!kw || kw.startsWith("#")) return;
    const key = kw.toLowerCase();
    if (seen.has(key)) {
      console.warn(`⚠ Duplikat: "${kw}" (baris ${i + 1}) sama dengan baris ${seen.get(key)}. Dicek sekali saja.`);
      return;
    }
    seen.set(key, i + 1);
    list.push(kw);
  });
  return list;
}

function wibNow() {
  const d = new Date(Date.now() + 7 * 3600 * 1000); // WIB = UTC+7
  const iso = d.toISOString();
  return { date: iso.slice(0, 10), stamp: `${iso.slice(0, 10)} ${iso.slice(11, 16)} WIB` };
}

const clean = (s) => String(s).replace(/[\t\r\n]+/g, " ");

async function main() {
  const keywords = readKeywords();
  if (!keywords.length) {
    console.error("keywords.txt kosong.");
    process.exit(1);
  }

  // Cek credit sebelum run
  const balance = await getBalance();
  const need = keywords.length * CREDIT_PER_KEYWORD;
  const needWithBuffer = Math.ceil(need * 1.1);
  if (balance === null) {
    console.error("Gagal membaca saldo dari /credit/balance. Run dibatalkan.");
    process.exit(1);
  }
  console.log(`Keyword: ${keywords.length} | kebutuhan: ${need} credit (+10% = ${needWithBuffer}) | saldo: ${balance}`);
  if (balance < needWithBuffer) {
    console.error(`Saldo kurang. Saldo ${balance}, kebutuhan ${needWithBuffer}, kurang ${needWithBuffer - balance}. Run dibatalkan.`);
    process.exit(1);
  }

  // Batch per 20 keyword. Respons: { results: [{ success, keyword, position, url, found, code?, message? }], credits, meta }
  const results = new Map(); // keyword lowercase -> { position, url, found } | { error }
  for (let i = 0; i < keywords.length; i += BATCH_SIZE) {
    const chunk = keywords.slice(i, i + BATCH_SIZE);
    console.log(`Batch ${i / BATCH_SIZE + 1}: ${chunk.length} keyword...`);
    const r = await call("POST", "/rankings/batch", { keywords: chunk, domain: DOMAIN, ...PARAMS });
    const rows = Array.isArray(r.data?.results) ? r.data.results : null;
    if (!r.ok || !rows) {
      console.log(`  batch gagal: ${errText(r)}`);
      for (const kw of chunk) results.set(kw.toLowerCase(), { error: `batch gagal: ${errText(r)}` });
      continue;
    }
    chunk.forEach((kw, idx) => {
      const row = rows.find((x) => String(x.keyword ?? "").toLowerCase() === kw.toLowerCase()) ?? rows[idx];
      if (!row) results.set(kw.toLowerCase(), { error: "tidak ada di respons batch" });
      else if (row.success === false) results.set(kw.toLowerCase(), { error: `${row.code ?? ""} ${row.message ?? ""}`.trim() || "success=false" });
      else results.set(kw.toLowerCase(), { position: row.position ?? null, url: row.url ?? null, found: row.found === true });
    });
  }

  // Coba ulang sekali per keyword yang gagal lewat /rankings/check
  const failed = keywords.filter((kw) => results.get(kw.toLowerCase())?.error);
  for (const kw of failed) {
    console.log(`Ulang "${kw}" lewat /rankings/check (sebelumnya: ${results.get(kw.toLowerCase()).error})`);
    const r = await call("POST", "/rankings/check", { keyword: kw, domain: DOMAIN, ...PARAMS });
    const res = r.data?.result;
    if (r.ok && res) results.set(kw.toLowerCase(), { position: res.position ?? null, url: res.url ?? null, found: res.found === true });
    else {
      console.log(`  masih gagal: ${errText(r)}`);
      results.set(kw.toLowerCase(), { error: errText(r) });
    }
  }

  // Susun baris sesuai urutan keywords.txt
  const table = keywords.map((kw, i) => {
    const r = results.get(kw.toLowerCase());
    if (!r || r.error) return { no: i + 1, kw, rank: "gagal", url: "-", error: r?.error };
    if (r.found && r.position !== null) return { no: i + 1, kw, rank: r.position, url: r.url ?? "-" };
    return { no: i + 1, kw, rank: "-", url: "-" };
  });

  const { date, stamp } = wibNow();
  const outDir = join(here, "output");
  mkdirSync(outDir, { recursive: true });
  const tsvPath = join(outDir, `rank-${date}.tsv`);
  const mdPath = join(outDir, `rank-${date}.md`);

  const tsv = ["#\tKeyword\tRank\tHalaman yang Rank", ...table.map((t) => [t.no, t.kw, t.rank, t.url].map(clean).join("\t"))];
  writeFileSync(tsvPath, tsv.join("\n") + "\n");

  const md = [
    `# Rank Report ${DOMAIN}`, ``,
    `Tanggal cek: ${stamp}  `,
    `Google Indonesia · Top 30 organik`, ``,
    `| # | Keyword | Rank | Halaman yang Rank |`,
    `| --: | :-- | :-- | :-- |`,
    ...table.map((t) => `| ${t.no} | ${clean(t.kw).replace(/\|/g, "\\|")} | ${t.rank} | ${t.url} |`),
  ];
  const errors = table.filter((t) => t.error);
  if (errors.length) md.push(``, `Gagal:`, ``, ...errors.map((t) => `- ${t.kw}: ${t.error}`));
  writeFileSync(mdPath, md.join("\n") + "\n");

  // Ringkasan terminal
  const nums = table.map((t) => t.rank).filter((x) => typeof x === "number");
  const count = (lo, hi) => nums.filter((x) => x >= lo && x <= hi).length;
  const after = await getBalance();
  const remaining = after ?? lastRemaining;

  console.log(`\nRingkasan (${stamp})`);
  console.log(`  Posisi 1–3   : ${count(1, 3)}`);
  console.log(`  Posisi 4–10  : ${count(4, 10)}`);
  console.log(`  Posisi 11–20 : ${count(11, 20)}`);
  console.log(`  Posisi 21–30 : ${count(21, 30)}`);
  console.log(`  Tidak ranking: ${table.filter((t) => t.rank === "-").length}`);
  console.log(`  Gagal        : ${errors.length}`);
  console.log(`  Credit terpakai: ${after !== null ? balance - after : creditsUsed} | sisa: ${remaining ?? "tidak terbaca"}`);
  console.log(`  Output: ${tsvPath}`);
  console.log(`          ${mdPath}`);
}

main();
