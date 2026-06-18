  </div><!-- /.admin-main -->
</div><!-- /.admin-shell -->
<script>
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
