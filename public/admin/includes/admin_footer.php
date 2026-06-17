</div><!-- /.admin-main -->
<footer class="admin-footer">
  <form method="post" action="<?= e(SITE_URL) ?>/admin/logout.php">
    <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
    <button type="submit">Kirjaudu ulos (<?= e($_SESSION['admin_username'] ?? '') ?>)</button>
  </form>
</footer>
</body>
</html>
