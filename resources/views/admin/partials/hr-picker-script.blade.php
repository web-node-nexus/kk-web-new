<script>
(() => {
  function escapeHtml(str) {
    return String(str ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function initHrPicker(root) {
    if (!root || root.dataset.ready) return;
    root.dataset.ready = '1';
    const url = root.dataset.searchUrl;
    const emailInput = root.querySelector('.kk-hr-picker__email');
    const search = root.querySelector('.kk-hr-picker__search');
    const results = root.querySelector('.kk-hr-picker__results');
    const selectedWrap = root.querySelector('.kk-hr-picker__selected');
    const searchWrap = root.querySelector('.kk-hr-picker__search-wrap');
    const selectedLabel = root.querySelector('.kk-hr-picker__selected-label');
    const clearBtn = root.querySelector('.kk-hr-picker__clear');
    let timer = null;

    const showSelected = (label, email) => {
      emailInput.value = email;
      selectedLabel.textContent = label;
      selectedWrap.style.display = 'flex';
      selectedWrap.style.alignItems = 'center';
      selectedWrap.style.gap = '10px';
      selectedWrap.style.flexWrap = 'wrap';
      selectedWrap.style.padding = '9px 12px';
      selectedWrap.style.border = '1px solid #99f6e4';
      selectedWrap.style.borderRadius = '10px';
      selectedWrap.style.background = '#f0fdfa';
      searchWrap.style.display = 'none';
      results.style.display = 'none';
      results.innerHTML = '';
      search.value = '';
    };

    const showSearch = () => {
      selectedWrap.style.display = 'none';
      searchWrap.style.display = '';
      searchWrap.style.position = 'relative';
      emailInput.value = '';
      setTimeout(() => search.focus(), 30);
    };

    clearBtn?.addEventListener('click', showSearch);

    const render = (items) => {
      if (!items.length) {
        results.innerHTML = '<div style="padding:12px;color:#64748b;font-size:13px">No HR / employee found. Try another name or email.</div>';
        results.style.display = '';
        return;
      }
      results.innerHTML = items.map((item) => {
        const email = escapeHtml(item.email);
        const name = escapeHtml(item.name);
        const role = escapeHtml(item.role_title || '');
        const code = escapeHtml(item.employee_code || '');
        const label = escapeHtml(item.label || (item.name + ' — ' + item.email));
        const meta = [item.email, item.role_title, item.employee_code].filter(Boolean).map(escapeHtml).join(' · ');
        return `
        <button type="button" class="kk-hr-picker__option" data-email="${email}" data-label="${label}"
          style="display:block;width:100%;text-align:left;border:0;background:#fff;padding:10px 12px;cursor:pointer;border-bottom:1px solid #f1f5f9">
          <strong style="display:block;font-size:13px;color:#0f172a">${name}</strong>
          <span style="font-size:12px;color:#64748b">${meta}</span>
        </button>`;
      }).join('');
      results.style.display = '';
      results.querySelectorAll('.kk-hr-picker__option').forEach((btn) => {
        btn.addEventListener('click', () => showSelected(btn.dataset.label, btn.dataset.email));
        btn.addEventListener('mouseenter', () => { btn.style.background = '#f8fafc'; });
        btn.addEventListener('mouseleave', () => { btn.style.background = '#fff'; });
      });
    };

    const runSearch = async (q) => {
      results.innerHTML = '<div style="padding:12px;color:#64748b;font-size:13px">Searching…</div>';
      results.style.display = '';
      try {
        const res = await fetch(url + '?q=' + encodeURIComponent(q || ''), {
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        render(Array.isArray(data) ? data : []);
      } catch (e) {
        results.innerHTML = '<div style="padding:12px;color:#b91c1c;font-size:13px">Search failed. Refresh and try again.</div>';
        results.style.display = '';
      }
    };

    search?.addEventListener('input', () => {
      clearTimeout(timer);
      timer = setTimeout(() => runSearch(search.value.trim()), 220);
    });
    search?.addEventListener('focus', () => {
      if (!results.dataset.loaded) {
        results.dataset.loaded = '1';
        runSearch('');
      } else if (results.innerHTML) {
        results.style.display = '';
      }
    });
    document.addEventListener('click', (e) => {
      if (!root.contains(e.target)) results.style.display = 'none';
    });

    if (emailInput.value) {
      selectedLabel.textContent = emailInput.value;
      selectedWrap.style.display = '';
      searchWrap.style.display = 'none';
    }
  }

  document.querySelectorAll('[data-hr-picker]').forEach(initHrPicker);
})();
</script>
