<script data-no-optimize="1" data-no-defer="1">
/* ============================================================
   KATALOG TREATMENT - perilaku halaman /treatment/
   Pasangan: treatment/index.html. Kartu sudah ada di HTML (statis,
   dibuat build-treatment-list.mjs dari treatments.json). Script ini
   hanya menyaring kartu: cari, chip keluhan, tab kategori, dan
   "Muat lebih banyak". Tidak ada data atau CSS di file ini.
   ============================================================ */
(function () {
  'use strict';

  var PAGE_SIZE = 12; // harus sama dengan INITIAL_VISIBLE di build-treatment-list.mjs

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
    var more = document.getElementById('tc-more');
    var reset = document.getElementById('tc-reset');

    var state = { q: '', concern: '', cat: 'all', limit: PAGE_SIZE };

    function matches(card) {
      if (state.cat !== 'all' && card.getAttribute('data-cat') !== state.cat) return false;
      if (state.concern && (' ' + card.getAttribute('data-concerns') + ' ').indexOf(' ' + state.concern + ' ') === -1) return false;
      if (state.q && card.getAttribute('data-search').indexOf(state.q) === -1) return false;
      return true;
    }

    function render() {
      var shown = 0;
      var total = 0;
      cards.forEach(function (card) {
        var ok = matches(card);
        if (ok) total++;
        var visible = ok && total <= state.limit;
        card.hidden = !visible;
        if (visible) shown++;
      });

      count.textContent = total ? 'Menampilkan ' + shown + ' dari ' + total + ' treatment' : '';
      more.hidden = shown >= total;
      empty.hidden = total !== 0;
      if (!total) {
        emptyDesc.textContent = state.q
          ? 'Tidak ada treatment untuk "' + input.value.trim() + '". Coba kata kunci lain atau pilih kategori yang berbeda.'
          : 'Coba kata kunci lain atau pilih kategori yang berbeda.';
      }
    }

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
        state.limit = PAGE_SIZE;
        render();
      }, 120);
    });

    chips.forEach(function (chip) {
      chip.addEventListener('click', function () {
        var id = chip.getAttribute('data-concern');
        state.concern = state.concern === id ? '' : id;
        setPressed(chips, state.concern ? chip : null);
        state.limit = PAGE_SIZE;
        render();
      });
    });

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        state.cat = tab.getAttribute('data-cat');
        setPressed(tabs, tab);
        renderFilterDesc(tab);
        state.limit = PAGE_SIZE;
        render();
      });
    });

    more.addEventListener('click', function () {
      var firstNew = state.limit;
      state.limit += PAGE_SIZE;
      render();
      // Pindahkan fokus ke kartu pertama yang baru muncul (keyboard & screen reader)
      var visible = cards.filter(function (c) { return !c.hidden; });
      var target = visible[firstNew] && visible[firstNew].querySelector('a');
      if (target) target.focus({ preventScroll: true });
    });

    reset.addEventListener('click', function () {
      input.value = '';
      state = { q: '', concern: '', cat: 'all', limit: PAGE_SIZE };
      setPressed(chips, null);
      setPressed(tabs, tabs[0]);
      renderFilterDesc(tabs[0]);
      render();
      input.focus();
    });

    grid.classList.add('is-ready');
    render();
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
