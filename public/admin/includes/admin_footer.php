  </div><!-- /.admin-main -->
</div><!-- /.admin-shell -->
<script>
/* ── Autocomplete-komponentti ─────────────────────────────────────────────
   Käyttö HTML:ssä:
   <div class="ac-wrap"
        data-items='[{"id":1,"label":"Nimi"}]'
        data-input-id="my_field_id"
        data-hidden-name="my_field_name"
        data-current-id="42"
        data-current-label="Nykyinen nimi"
        data-placeholder="Hae...">
   </div>
──────────────────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.ac-wrap').forEach(wrap => {
    const items       = JSON.parse(wrap.dataset.items || '[]');
    const placeholder = wrap.dataset.placeholder || 'Hae...';
    const currentId   = wrap.dataset.currentId   || '';
    const currentLabel= wrap.dataset.currentLabel|| '';
    const inputId     = wrap.dataset.inputId;
    const hiddenName  = wrap.dataset.hiddenName;

    // Rakenne
    wrap.innerHTML = `
      <input type="text"  id="${inputId}_text"  class="ac-text"
             autocomplete="off" placeholder="${placeholder}"
             value="${currentLabel.replace(/"/g,'&quot;')}">
      <input type="hidden" id="${inputId}" name="${hiddenName}"
             value="${currentId}">
      <ul class="ac-list" role="listbox"></ul>`;

    const textEl   = wrap.querySelector('.ac-text');
    const hiddenEl = wrap.querySelector(`#${inputId}`);
    const listEl   = wrap.querySelector('.ac-list');
    let activeIdx  = -1;

    function render(q) {
      const lower = q.toLowerCase();
      const matches = q.length === 0 ? [] :
        items.filter(i => i.label.toLowerCase().includes(lower)).slice(0, 30);
      listEl.innerHTML = matches.map((m, idx) =>
        `<li class="ac-item" data-idx="${idx}" data-id="${m.id}"
              data-label="${m.label.replace(/"/g,'&quot;')}" role="option">
           ${m.label.replace(new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi'),
             '<strong>$1</strong>')}
         </li>`).join('');
      activeIdx = -1;
      listEl.classList.toggle('open', matches.length > 0);
      listEl.querySelectorAll('.ac-item').forEach(li => {
        li.addEventListener('mousedown', e => { e.preventDefault(); select(li); });
      });
    }

    function select(li) {
      textEl.value   = li.dataset.label;
      hiddenEl.value = li.dataset.id;
      listEl.classList.remove('open');
      activeIdx = -1;
    }

    function clearIfNoMatch() {
      const val = textEl.value.trim().toLowerCase();
      const exact = items.find(i => i.label.toLowerCase() === val);
      if (!exact) { hiddenEl.value = ''; }
    }

    textEl.addEventListener('input',  () => render(textEl.value.trim()));
    textEl.addEventListener('focus',  () => { if (textEl.value.trim()) render(textEl.value.trim()); });
    textEl.addEventListener('blur',   () => { setTimeout(() => listEl.classList.remove('open'), 150); clearIfNoMatch(); });
    textEl.addEventListener('keydown', e => {
      const lis = listEl.querySelectorAll('.ac-item');
      if (!lis.length) return;
      if (e.key === 'ArrowDown') { e.preventDefault(); activeIdx = Math.min(activeIdx + 1, lis.length - 1); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); activeIdx = Math.max(activeIdx - 1, 0); }
      else if (e.key === 'Enter' && activeIdx >= 0) { e.preventDefault(); select(lis[activeIdx]); return; }
      else if (e.key === 'Escape') { listEl.classList.remove('open'); return; }
      lis.forEach((li, i) => li.classList.toggle('ac-active', i === activeIdx));
      if (activeIdx >= 0) lis[activeIdx].scrollIntoView({ block: 'nearest' });
    });
  });
});

function adminToggleExpand(id) {
  const row = document.getElementById('cl-exp-' + id);
  const btn = document.getElementById('cl-btn-' + id);
  if (!row) return;
  const isOpen = row.classList.contains('open');
  document.querySelectorAll('.cl-expanded.open').forEach(r => r.classList.remove('open'));
  document.querySelectorAll('.cl-expand-btn').forEach(b => { if (b.id !== 'cl-btn-' + id) b.textContent = '▸'; });
  if (!isOpen) { row.classList.add('open'); if (btn) btn.textContent = '▾'; }
  else { if (btn) btn.textContent = '▸'; }
}
function adminOpenSlide(panelId) {
  document.getElementById('slide-overlay-' + panelId).classList.add('open');
  document.getElementById('slide-panel-' + panelId).classList.add('open');
}
function adminCloseSlide(panelId) {
  document.getElementById('slide-overlay-' + panelId).classList.remove('open');
  document.getElementById('slide-panel-' + panelId).classList.remove('open');
}
function adminOpenModal(modalId) {
  document.getElementById('modal-overlay-' + modalId).classList.add('open');
}
function adminCloseModal(modalId) {
  document.getElementById('modal-overlay-' + modalId).classList.remove('open');
}
</script>
</body>
</html>
