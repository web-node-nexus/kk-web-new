(() => {
  const token = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
  const toast = (msg, err) => {
    if (!msg) return;
    document.querySelectorAll('.kk-toast,.ep-toast').forEach((n) => n.remove());
    const el = document.createElement('div');
    el.className = (document.body.classList.contains('ep-body') ? 'ep-toast' : 'kk-toast') + (err ? ' is-err' : '');
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 4200);
  };

  const submitAjax = async (form) => {
    form.querySelectorAll('[data-editor-source]').forEach((area) => {
      const id = area.getAttribute('data-editor-source');
      const hidden = id ? document.getElementById(id) : null;
      if (hidden) hidden.value = area.innerHTML;
    });
    const btn = form.querySelector('[type="submit"]');
    if (btn) btn.disabled = true;
    try {
      const res = await fetch(form.action, {
        method: (form.getAttribute('method') || 'POST').toUpperCase(),
        body: new FormData(form),
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': token(),
        },
      });
      const data = await res.json().catch(() => null);
      if (!res.ok) {
        const first = data?.message || Object.values(data?.errors || {})[0]?.[0] || 'Request failed.';
        toast(first, true);
        return;
      }
      toast(data?.message || 'Saved.');
      if (data?.reply) {
        document.dispatchEvent(new CustomEvent('kk:reply', { detail: data.reply }));
      }
      if (data?.redirect) {
        window.location.href = data.redirect;
        return;
      }
      if (form.hasAttribute('data-ajax-reload')) {
        window.location.reload();
        return;
      }
      if (!form.querySelector('input[type="file"]')) {
        const keep = form.querySelector('[name="_method"], [name="_token"]');
        form.querySelectorAll('textarea, input[type="text"], input[type="search"]').forEach((el) => {
          if (el.name === '_token' || el.name === '_method') return;
          if (el.closest('.kk-chat__composer, .ep-chat__composer')) return;
          el.value = '';
        });
      }
    } catch (e) {
      toast('Network error. Try again.', true);
    } finally {
      if (btn) btn.disabled = false;
    }
  };

  document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.hasAttribute('data-no-ajax')) return;
    if ((form.getAttribute('method') || 'get').toLowerCase() === 'get') return;
    if (form.id === 'chatForm') return;
    if ((form.action || '').includes('/logout')) return;
    e.preventDefault();
    submitAjax(form);
  });
})();
