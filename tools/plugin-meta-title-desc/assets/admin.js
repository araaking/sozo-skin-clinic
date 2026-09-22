(function () {
  'use strict';

  var cfg = window.bmeAdmin || null;
  if (!cfg) return;

  var LIMITS = { title: 60, desc: 160 };
  var dirty = false;

  function qs(sel, ctx) {
    return (ctx || document).querySelector(sel);
  }

  function qsa(sel, ctx) {
    return Array.prototype.slice.call((ctx || document).querySelectorAll(sel));
  }

  function setCounterReady(input) {
    var field = input.getAttribute('data-bme-field');
    if (!field || !LIMITS[field]) return;

    var cell = input.closest('td');
    var counter = cell ? cell.querySelector('.bme-counter') : null;
    if (!counter) return;

    var len = input.value.length;
    counter.textContent = len + ' / ' + LIMITS[field];
    counter.classList.toggle('over', len > LIMITS[field]);
    counter.classList.toggle('good', len > 0 && len <= LIMITS[field]);
  }

  qsa('.bme-count-title, .bme-count-desc').forEach(setCounterReady);

  function setFieldMessage(postId, field, text, type) {
    var msg = qs('.bme-field-msg[data-post-id="' + postId + '"][data-bme-field="' + field + '"]');
    if (!msg) return;
    msg.textContent = text;
    msg.className = 'bme-field-msg bme-msg-' + type;
  }

  function markDirty(input) {
    var row = input.closest('tr.bme-row');
    if (row) row.classList.add('bme-dirty');

    dirty = true;

    var status = document.getElementById('bme-save-status');
    if (status) {
      status.textContent = cfg.strings.unsaved;
      status.className = 'bme-save-status bme-warn';
    }
  }

  document.addEventListener('input', function (event) {
    var el = event.target;
    if (!el || !el.classList) return;

    if (el.classList.contains('bme-count-title') || el.classList.contains('bme-count-desc')) {
      setCounterReady(el);
    }

    if (el.getAttribute('data-bme-field') && el.closest('.bme-table')) {
      markDirty(el);
    }
  });

  window.addEventListener('beforeunload', function (event) {
    if (!dirty) return;
    event.preventDefault();
    event.returnValue = '';
    return '';
  });

  /* ----------------------------------------------------------------
   * Save editor
   * -------------------------------------------------------------- */

  var form = document.getElementById('bme-form');

  function collectPayload() {
    var payload = { rows: {}, originals: {} };

    qsa('input[data-bme-field]:not([data-bme-orig]), textarea[data-bme-field]:not([data-bme-orig])', form).forEach(function (el) {
      var pid = el.getAttribute('data-post-id');
      var field = el.getAttribute('data-bme-field');
      if (!pid || !field) return;
      if (!payload.rows[pid]) payload.rows[pid] = {};
      payload.rows[pid][field] = el.value;
    });

    qsa('input[data-bme-orig]', form).forEach(function (el) {
      var pid = el.getAttribute('data-post-id');
      var field = el.getAttribute('data-bme-field');
      if (!pid || !field) return;
      if (!payload.originals[pid]) payload.originals[pid] = {};
      payload.originals[pid][field] = el.value;
    });

    return payload;
  }

  function buildBody(action, groups) {
    var body = new URLSearchParams();
    body.append('action', action);
    body.append('nonce', cfg.nonce);

    Object.keys(groups).forEach(function (group) {
      Object.keys(groups[group]).forEach(function (pid) {
        Object.keys(groups[group][pid]).forEach(function (field) {
          body.append(group + '[' + pid + '][' + field + ']', groups[group][pid][field]);
        });
      });
    });

    return body;
  }

  function applyResults(results) {
    Object.keys(results).forEach(function (pid) {
      var fields = results[pid];

      Object.keys(fields).forEach(function (field) {
        var res = fields[field];
        var input = qs('input[data-post-id="' + pid + '"][data-bme-field="' + field + '"]:not([data-bme-orig]), textarea[data-post-id="' + pid + '"][data-bme-field="' + field + '"]');
        var orig = qs('input[data-bme-orig][data-post-id="' + pid + '"][data-bme-field="' + field + '"]');
        var row = input ? input.closest('tr.bme-row') : null;

        if (res.status === 'saved' || res.status === 'unchanged') {
          if (orig) orig.value = res.current;
          if (row) row.classList.remove('bme-dirty');
          setFieldMessage(pid, field, res.status === 'saved' ? cfg.strings.saved : cfg.strings.unchanged, 'ok');
          return;
        }

        if (res.status === 'conflict') {
          if (orig) orig.value = res.current;
          setFieldMessage(pid, field, cfg.strings.conflict + ' Nilai di server: "' + res.current + '"', 'error');
          return;
        }

        setFieldMessage(pid, field, cfg.strings.error, 'error');
      });
    });
  }

  function refreshDirtyState() {
    var remaining = qsa('tr.bme-row.bme-dirty');
    dirty = remaining.length > 0;
    return remaining.length;
  }

  function saveForm() {
    var button = document.getElementById('bme-save-button');
    var status = document.getElementById('bme-save-status');
    var payload = collectPayload();

    if (button) button.disabled = true;
    if (status) {
      status.textContent = cfg.strings.saving;
      status.className = 'bme-save-status';
    }

    fetch(cfg.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: buildBody('bme_save', payload)
    })
      .then(function (res) {
        return res.json();
      })
      .then(function (json) {
        if (!json || !json.success) throw new Error(cfg.strings.error);

        var data = json.data || {};
        applyResults(data.results || {});
        refreshDirtyState();

        if (status) {
          var message = data.saved + ' disimpan';
          if (data.conflicts) message += ', ' + data.conflicts + ' konflik';
          if (data.unchanged) message += ', ' + data.unchanged + ' tidak berubah';
          status.textContent = message;
          status.className = 'bme-save-status ' + (data.conflicts ? 'bme-warn' : 'bme-ok');
        }
      })
      .catch(function () {
        if (status) {
          status.textContent = cfg.strings.error;
          status.className = 'bme-save-status bme-error';
        }
      })
      .finally(function () {
        if (button) button.disabled = false;
      });
  }

  if (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      saveForm();
    });
  }

  /* ----------------------------------------------------------------
   * Restore history
   * -------------------------------------------------------------- */

  qsa('.bme-restore-form').forEach(function (restoreForm) {
    restoreForm.addEventListener('submit', function (event) {
      if (!window.confirm(cfg.strings.confirmRestore)) {
        event.preventDefault();
      }
    });
  });

  /* ----------------------------------------------------------------
   * Autocomplete post (halaman history)
   * -------------------------------------------------------------- */

  qsa('[data-bme-autocomplete]').forEach(function (wrap) {
    var input = qs('.bme-ac-input', wrap);
    var hidden = qs('.bme-ac-value', wrap);
    var results = qs('.bme-ac-results', wrap);
    if (!input || !hidden || !results) return;

    var timer = null;
    var controller = null;

    function render(items) {
      results.innerHTML = '';

      if (!items.length) {
        var empty = document.createElement('div');
        empty.className = 'bme-ac-empty';
        empty.textContent = cfg.strings.noResults;
        results.appendChild(empty);
        results.hidden = false;
        return;
      }

      items.forEach(function (item) {
        var row = document.createElement('div');
        row.className = 'bme-ac-item';
        row.textContent = item.text;
        row.addEventListener('click', function () {
          input.value = item.text;
          hidden.value = item.id;
          results.hidden = true;
        });
        results.appendChild(row);
      });

      results.hidden = false;
    }

    function search(term) {
      if (controller) controller.abort();
      controller = new AbortController();

      var url = cfg.ajaxUrl +
        '?action=bme_search_posts&nonce=' + encodeURIComponent(cfg.nonce) +
        '&term=' + encodeURIComponent(term);

      fetch(url, { credentials: 'same-origin', signal: controller.signal })
        .then(function (res) {
          return res.json();
        })
        .then(function (json) {
          if (!json || !json.success) throw new Error();
          render(json.data || []);
        })
        .catch(function () {});
    }

    input.addEventListener('input', function () {
      hidden.value = '';
      var term = input.value.trim();

      if (timer) clearTimeout(timer);

      if (term.length < 2) {
        results.hidden = true;
        results.innerHTML = '';
        return;
      }

      timer = setTimeout(function () {
        search(term);
      }, 250);
    });

    input.addEventListener('focus', function () {
      if (results.innerHTML !== '') results.hidden = false;
    });

    document.addEventListener('click', function (event) {
      if (!wrap.contains(event.target)) results.hidden = true;
    });
  });

  /* ----------------------------------------------------------------
   * Apply import CSV
   * -------------------------------------------------------------- */

  var applyButton = document.getElementById('bme-import-apply');

  if (applyButton) {
    applyButton.addEventListener('click', function () {
      var token = applyButton.getAttribute('data-token');
      var total = parseInt(applyButton.getAttribute('data-total'), 10) || 0;
      var offset = 0;
      var counts = { applied: 0, unchanged: 0, errors: 0 };
      var status = document.getElementById('bme-import-status');
      var progress = document.getElementById('bme-import-progress');
      var bar = document.getElementById('bme-import-bar');

      applyButton.disabled = true;
      if (progress) progress.hidden = false;

      function next() {
        var body = new URLSearchParams();
        body.append('action', 'bme_import_apply');
        body.append('nonce', cfg.nonce);
        body.append('token', token);
        body.append('offset', String(offset));

        if (status) status.textContent = cfg.strings.importRunning + ' ' + offset + '/' + total;

        fetch(cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body })
          .then(function (res) {
            return res.json();
          })
          .then(function (json) {
            if (!json || !json.success) {
              throw new Error(json && json.data && json.data.message ? json.data.message : cfg.strings.importError);
            }

            var data = json.data;
            counts.applied += data.counts.applied;
            counts.unchanged += data.counts.unchanged;
            counts.errors += data.counts.errors;
            offset = data.next;

            if (bar && total) {
              bar.style.width = Math.min(100, Math.round((offset / total) * 100)) + '%';
            }

            if (data.done) {
              if (status) status.textContent = cfg.strings.importDone;
              var url = new URL(window.location.href);
              url.searchParams.delete('import_token');
              url.searchParams.set('bme_notice', 'done');
              window.location.href = url.toString();
              return;
            }

            next();
          })
          .catch(function (error) {
            if (status) status.textContent = error && error.message ? error.message : cfg.strings.importError;
            applyButton.disabled = false;
          });
      }

      next();
    });
  }
})();
