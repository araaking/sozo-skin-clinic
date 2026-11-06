<script data-no-optimize="1" data-no-defer="1">
/* ============================================================
   KATALOG TREATMENT - perilaku halaman /treatment/
   Pasangan: treatment/index.html. Kartu sudah ada di HTML (statis),
   jadi semua link treatment terbaca crawler tanpa render JS.
   Script ini hanya menyaring kartu: cari, chip keluhan, tab kategori,
   dan pagination. Tidak ada data atau CSS di file ini.

   Pagination memakai URL ?halaman=n (bukan ?page=n: WordPress
   me-redirect ?page=2 dengan 301 ke /treatment/). Yoast tetap memberi
   canonical /treatment/ untuk ?halaman=n karena isi HTML-nya sama.
   ?halaman hanya ditulis saat daftar tidak difilter, supaya link yang
   dibagikan selalu menunjuk halaman yang sama.
   ============================================================ */
(function () {
  'use strict';

  var PAGE_SIZE = 12; // harus sama dengan CSS .tc-card:nth-child(n+13) di index.html
  var PAGE_PARAM = 'halaman';

  function init() {
    var app = document.getElementById('sozo-react-app');
    var grid = document.getElementById('tc-grid');
    if (!app || !grid || grid.classList.contains('is-ready')) return Boolean(grid);

    var cards = Array.prototype.slice.call(grid.querySelectorAll('.tc-card'));
    var input = document.getElementById('tc-search-input');
    var chips = Array.prototype.slice.call(app.querySelectorAll('.tc-chip'));
    var tabs = Array.prototype.slice.call(app.querySelectorAll('.tc-tab'));
    var count = document.getElementById('tc-count');
    var filterDesc = document.getElementById('tc-filter-desc');
    var empty = document.getElementById('tc-empty');
    var emptyDesc = document.getElementById('tc-empty-desc');
    var pager = document.getElementById('tc-pagination');
    var reset = document.getElementById('tc-reset');
    var catalog = document.getElementById('semua-treatment') || grid;

    var state = { q: '', concern: '', cat: 'all', page: 1 };
    var totalPages = 1;

    function matches(card) {
      if (state.cat !== 'all' && card.getAttribute('data-cat') !== state.cat) return false;
      if (state.concern && (' ' + card.getAttribute('data-concerns') + ' ').indexOf(' ' + state.concern + ' ') === -1) return false;
      if (state.q && card.getAttribute('data-search').indexOf(state.q) === -1) return false;
      return true;
    }

    function isUnfiltered() {
      return !state.q && !state.concern && state.cat === 'all';
    }

    // ---------- URL ?halaman=n ----------
    function readPageFromUrl() {
      var n = parseInt(new URLSearchParams(window.location.search).get(PAGE_PARAM), 10);
      return isFinite(n) && n > 1 ? n : 1;
    }

    function syncUrl(push) {
      try {
        var url = new URL(window.location.href);
        if (state.page > 1 && isUnfiltered()) url.searchParams.set(PAGE_PARAM, String(state.page));
        else url.searchParams.delete(PAGE_PARAM);
        if (url.href === window.location.href) return;
        window.history[push ? 'pushState' : 'replaceState']({ tcPage: state.page }, '', url.href);
      } catch (e) {
        // History API tidak tersedia: pagination tetap jalan tanpa ubah URL
      }
    }

    // ---------- Render ----------
    function render() {
      var matched = cards.filter(matches);
      var total = matched.length;
      totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
      if (state.page > totalPages) state.page = totalPages;

      var start = (state.page - 1) * PAGE_SIZE;
      var end = start + PAGE_SIZE;
      cards.forEach(function (card) { card.hidden = true; });
      matched.slice(start, end).forEach(function (card) { card.hidden = false; });

      count.textContent = total
        ? 'Menampilkan ' + (start + 1) + '–' + Math.min(end, total) + ' dari ' + total + ' treatment'
        : '';
      empty.hidden = total !== 0;
      if (!total) {
        emptyDesc.textContent = state.q
          ? 'Tidak ada treatment untuk "' + input.value.trim() + '". Coba kata kunci lain atau pilih kategori yang berbeda.'
          : 'Coba kata kunci lain atau pilih kategori yang berbeda.';
      }
      renderPager();
    }

    function pageRange(current, total) {
      // 1 … (current-1) current (current+1) … total
      var out = [];
      var last = 0;
      for (var i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= current - 1 && i <= current + 1)) {
          if (last && i - last === 2) out.push(last + 1);
          else if (last && i - last > 2) out.push('…');
          out.push(i);
          last = i;
        }
      }
      return out;
    }

    function pageButton(label, page, opts) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'tc-page' + (opts.arrow ? ' tc-page--arrow' : '');
      b.textContent = label;
      b.setAttribute('aria-label', opts.aria);
      if (opts.current) b.setAttribute('aria-current', 'page');
      if (opts.disabled) b.disabled = true;
      b.addEventListener('click', function (e) {
        // e.detail === 0: diklik lewat keyboard (Enter/Spasi), bukan mouse/tap
        if (!opts.disabled && !opts.current) goToPage(page, e.detail === 0);
      });
      return b;
    }

    function renderPager() {
      pager.textContent = '';
      if (totalPages <= 1) {
        pager.hidden = true;
        return;
      }
      pager.hidden = false;
      pager.appendChild(pageButton('‹', state.page - 1, { arrow: true, aria: 'Halaman sebelumnya', disabled: state.page === 1 }));
      pageRange(state.page, totalPages).forEach(function (p) {
        if (p === '…') {
          var dots = document.createElement('span');
          dots.className = 'tc-page tc-page--dots';
          dots.setAttribute('aria-hidden', 'true');
          dots.textContent = '…';
          pager.appendChild(dots);
        } else {
          pager.appendChild(pageButton(String(p), p, { aria: 'Halaman ' + p, current: p === state.page }));
        }
      });
      pager.appendChild(pageButton('›', state.page + 1, { arrow: true, aria: 'Halaman berikutnya', disabled: state.page === totalPages }));
    }

    function scrollToCatalog() {
      var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      catalog.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
    }

    function goToPage(page, fromKeyboard) {
      state.page = Math.min(Math.max(1, page), totalPages);
      render();
      syncUrl(true);
      scrollToCatalog();
      // Pengguna keyboard: fokus pindah ke kartu pertama halaman baru, karena tombol
      // yang tadi diklik sudah dibuat ulang. Untuk mouse/tap tidak, supaya kartu
      // tidak tampil ber-outline (:focus-within).
      if (fromKeyboard) {
        var first = cards.filter(function (c) { return !c.hidden; })[0];
        var target = first && (first.querySelector('.tc-card__title a') || first.querySelector('a'));
        if (target) target.focus({ preventScroll: true });
      }
    }

    // Filter apa pun selalu kembali ke halaman 1 dan menghapus ?halaman
    function applyFilter() {
      state.page = 1;
      render();
      syncUrl(false);
    }

    // ---------- Filter UI ----------
    function renderFilterDesc(tab) {
      var desc = tab.getAttribute('data-desc');
      var link = tab.getAttribute('data-link');
      var title = tab.getAttribute('data-title');
      filterDesc.textContent = '';
      if (tab.getAttribute('data-cat') === 'all' || (!desc && !link)) {
        filterDesc.hidden = true;
        return;
      }
      if (desc) filterDesc.appendChild(document.createTextNode(desc + ' '));
      if (link) {
        var a = document.createElement('a');
        a.href = link;
        a.textContent = 'Lihat halaman ' + title + ' →';
        filterDesc.appendChild(a);
      }
      filterDesc.hidden = false;
    }

    function setPressed(list, active) {
      list.forEach(function (el) { el.setAttribute('aria-pressed', String(el === active)); });
    }

    var timer = null;
    input.addEventListener('input', function () {
      clearTimeout(timer);
      timer = setTimeout(function () {
        state.q = input.value.trim().toLowerCase();
        applyFilter();
      }, 120);
    });

    chips.forEach(function (chip) {
      chip.addEventListener('click', function () {
        var id = chip.getAttribute('data-concern');
        state.concern = state.concern === id ? '' : id;
        setPressed(chips, state.concern ? chip : null);
        applyFilter();
      });
    });

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        state.cat = tab.getAttribute('data-cat');
        setPressed(tabs, tab);
        renderFilterDesc(tab);
        applyFilter();
      });
    });

    reset.addEventListener('click', function () {
      input.value = '';
      state = { q: '', concern: '', cat: 'all', page: 1 };
      setPressed(chips, null);
      setPressed(tabs, tabs[0]);
      renderFilterDesc(tabs[0]);
      applyFilter();
      input.focus();
    });

    // Tombol back/forward browser
    window.addEventListener('popstate', function () {
      state.page = readPageFromUrl();
      render();
      scrollToCatalog();
    });

    grid.classList.add('is-ready');
    // Buka langsung di halaman dari URL; nilai di luar rentang dinormalkan
    state.page = readPageFromUrl();
    render();
    syncUrl(false);
    return true;
  }

  var attempts = 0;
  function run() {
    if (init()) return;
    if (attempts++ < 30) setTimeout(run, 200);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run);
  else run();

  window.addEventListener('elementor/frontend/init', function () {
    if (window.elementorFrontend && window.elementorFrontend.hooks) {
      window.elementorFrontend.hooks.addAction('frontend/element_ready/html.default', run);
    }
  });
})();
</script>
