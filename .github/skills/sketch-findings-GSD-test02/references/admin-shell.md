# Admin Shell & Navigaatio

## Design Decisions

### Sivupalkki (voittaja 006-A)
Kiinteä vasen sivupalkki, leveys 220px. Täyteväri `var(--color-primary)` (#3d2b1f). Kultainen korostusraja alareunasta (`border-bottom: 3px solid var(--color-gold)` sivulla, ei sidebarissa). Sidebarissa aktiivinen nav-item erottuu kolmella tavalla: kultainen teksti, `rgba(201,168,76,0.12)` tausta, ja `border-right: 3px solid var(--color-gold)`.

Tavallinen nav-item: `rgba(232,213,183,0.75)` teksti, ei taustaa. Hover: `color: var(--color-cream)` + `rgba(255,255,255,0.06)` tausta.

Logo-osio yläreunassa, footer käyttäjänimellä ja logout-linkillä alarajalla. Nav-osiot eroteltu section-otsikoin (`font-size: var(--text-xs)`, isot kirjaimet, `rgba(201,168,76,0.5)` — hyvin hiljainen, ei kilpaile varsinaisten linkkien kanssa).

**Miksi sivupalkki voitti:** Antaa enemmän pysyvyyttä navigaatiolle kuin yläpalkki. Sivupalkki pysyy näkyvissä pitkissäkin listoissa. Icon-only-sidebar (C) hylättiin — admin tarvitsee tekstit, sillä osioita ei ole paljon.

### Kompakti hevosten lista (voittaja 007-C)
Grid-pohjainen rivirakenne ilman täydellistä HTML-taulukkoa. Sarakkeet: nimi+rotu (2fr) | sukupuoli (80px) | syntymä (1fr) | VH-tunnus (140px) | expand-btn (32px). Hover avaa laajennuspaneelin toiminnoilla.

Toiminnot (Muokkaa, Kuvat, Poista) ovat piilotettuina laajennuspaneelissa — ei näy levossa, näkyy klikkauksen jälkeen. Tämä pitää taulukon siistinä suurella datamäärällä.

**Miksi kompakti voitti:** Pitkä taulukko (vaihtoehto A) piiloitti toiminnot hover-takana, mikä tuntui epäluotettavalta. Korttirivit (B) veivät liikaa tilaa per hevonen. Kompakti+laajennettava on eniten tilaa säästävä ja toiminnoiltaan selkein.

### Horse Context -banneri
Jokaisella hevoskohtaisella admin-sivulla (kasvatus, kilpailut, kuvat) on context-banneri suoraan page-headerin alla. Tausta `var(--color-parchment)`, rajalinja `var(--color-border-warm)`. Sisältää hevosen ikonin, nimen (Georgia-fontilla), metatiedot ja "Vaihda hevosta"-linkin oikealla.

## CSS Patterns

### Sivupalkki — rakenne
```css
.admin-sidebar {
  width: 220px;
  flex-shrink: 0;
  background: var(--color-primary);
  display: flex;
  flex-direction: column;
  padding: var(--space-4) 0;
  height: 100vh; /* tai calc(100vh - topbar) */
}

.admin-sidebar-logo {
  padding: var(--space-4) var(--space-6) var(--space-5);
  border-bottom: 1px solid rgba(201,168,76,0.25);
  margin-bottom: var(--space-4);
}

.admin-sidebar-logo .logo-text {
  font-family: var(--font-serif);
  font-size: var(--text-lg);
  color: var(--color-cream);
}

.admin-sidebar-logo .logo-sub {
  font-size: var(--text-xs);
  color: var(--color-gold);
  opacity: 0.8;
  margin-top: 2px;
}
```

### Sivupalkki — nav-itemit
```css
.admin-nav-section {
  font-size: var(--text-xs);
  color: rgba(201,168,76,0.5);
  padding: var(--space-3) var(--space-6) var(--space-2);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-top: var(--space-2);
}

.admin-nav-item {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-3) var(--space-6);
  color: rgba(232,213,183,0.75);
  font-size: var(--text-sm);
  text-decoration: none;
  transition: all 0.15s ease;
}

.admin-nav-item:hover {
  color: var(--color-cream);
  background: rgba(255,255,255,0.06);
}

.admin-nav-item.active {
  color: var(--color-gold);
  background: rgba(201,168,76,0.12);
  border-right: 3px solid var(--color-gold);
}
```

### Sivupalkki — footer
```css
.admin-sidebar-footer {
  margin-top: auto;
  padding: var(--space-4) var(--space-6);
  border-top: 1px solid rgba(201,168,76,0.2);
}

.admin-sidebar-footer .user-name {
  color: var(--color-cream);
  font-size: var(--text-xs);
}

.admin-sidebar-footer .logout-link {
  color: var(--color-gold);
  font-size: var(--text-xs);
  opacity: 0.7;
}

.admin-sidebar-footer .logout-link:hover { opacity: 1; }
```

### Page shell (flex-pohja)
```css
.admin-shell {
  display: flex;
  height: 100vh; /* tai 100% */
}

.admin-main {
  flex: 1;
  overflow-y: auto;
  background: var(--color-bg);
  display: flex;
  flex-direction: column;
}

.admin-page-header {
  background: var(--color-surface);
  border-bottom: 1px solid var(--color-border);
  padding: var(--space-4) var(--space-8);
  display: flex;
  align-items: center;
  gap: var(--space-4);
}

.admin-page-header h1 {
  font-family: var(--font-serif);
  font-size: var(--text-2xl);
  color: var(--color-primary);
  font-weight: normal;
}

.admin-page-header .page-actions { margin-left: auto; }
```

### Horse context -banneri
```css
.horse-ctx {
  background: var(--color-parchment);
  border-bottom: 1px solid var(--color-border-warm);
  padding: var(--space-3) var(--space-8);
  display: flex;
  align-items: center;
  gap: var(--space-4);
}

.horse-ctx .horse-name {
  font-family: var(--font-serif);
  font-size: var(--text-lg);
  color: var(--color-primary);
}

.horse-ctx .horse-meta {
  font-size: var(--text-xs);
  color: var(--color-text-muted);
}

.horse-ctx .change-link {
  margin-left: auto;
  font-size: var(--text-xs);
  color: var(--color-accent);
}
```

### Kompakti lista (007-C tyyli)
```css
.admin-compact-list {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}

.admin-compact-header {
  display: grid;
  /* sarakkeet määritellään per-sivu: esim. 2fr 1fr 1fr 80px 28px */
  gap: var(--space-4);
  padding: var(--space-3) var(--space-5);
  background: var(--color-surface-accent);
  border-bottom: 1px solid var(--color-border);
  font-size: var(--text-xs);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--color-text-muted);
  font-weight: 600;
}

.admin-compact-row {
  display: grid;
  gap: var(--space-4);
  align-items: center;
  padding: var(--space-3) var(--space-5);
  border-bottom: 1px solid var(--color-border);
  transition: background 0.1s;
  cursor: pointer;
}

.admin-compact-row:last-of-type { border-bottom: none; }
.admin-compact-row:hover { background: var(--color-surface-warm); }

.admin-compact-name {
  font-weight: 600;
  color: var(--color-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.admin-compact-meta {
  font-size: var(--text-xs);
  color: var(--color-text-muted);
}

.admin-expand-btn {
  background: none;
  border: none;
  color: var(--color-text-muted);
  cursor: pointer;
  font-size: 14px;
  width: 26px;
  height: 26px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-sm);
  transition: all 0.15s;
}

.admin-expand-btn:hover {
  background: var(--color-surface-accent);
  color: var(--color-primary);
}

.admin-expanded-panel {
  display: none;
  padding: var(--space-4) var(--space-5);
  border-bottom: 1px solid var(--color-border);
  background: var(--color-surface-warm);
}

.admin-expanded-panel.open { display: block; }
```

### Sukupuoli-badget
```css
.gender-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: var(--radius-full);
  font-size: var(--text-xs);
  font-weight: 600;
}
.gender-ori   { background: #e8f0ff; color: #3b5bdb; }
.gender-tamma { background: #fce8f3; color: #9c36b5; }
.gender-ruuna { background: #e8f5e8; color: #2b8a3e; }
```

## HTML Structures

### Admin-sivun perusrunko (PHP)
```html
<div class="admin-shell">
  <!-- Sivupalkki -->
  <aside class="admin-sidebar">
    <div class="admin-sidebar-logo">
      <div class="logo-text">🐴 Virtuaalitalli</div>
      <div class="logo-sub">Hallintapaneeli</div>
    </div>
    <nav>
      <div class="admin-nav-section">Päävalikko</div>
      <a class="admin-nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>"
         href="<?= e(SITE_URL) ?>/admin/">⊞ Dashboard</a>
      <a class="admin-nav-item <?= $activePage === 'horses' ? 'active' : '' ?>"
         href="<?= e(SITE_URL) ?>/admin/horses.php">🐎 Hevoset</a>
      <a class="admin-nav-item <?= $activePage === 'foals' ? 'active' : '' ?>"
         href="<?= e(SITE_URL) ?>/admin/foals.php">🌱 Kasvatus</a>
      <a class="admin-nav-item <?= $activePage === 'competitions' ? 'active' : '' ?>"
         href="<?= e(SITE_URL) ?>/admin/competitions.php">🏆 Kilpailut</a>
      <div class="admin-nav-section">Media</div>
      <a class="admin-nav-item <?= $activePage === 'photos' ? 'active' : '' ?>"
         href="<?= e(SITE_URL) ?>/admin/photos.php">📷 Kuvat</a>
    </nav>
    <div class="admin-sidebar-footer">
      <div class="user-name"><?= e($_SESSION['admin_username']) ?></div>
      <a class="logout-link" href="<?= e(SITE_URL) ?>/admin/logout.php">Kirjaudu ulos</a>
    </div>
  </aside>

  <!-- Sisältöalue -->
  <div class="admin-main">
    <!-- page-header, page-body jne. -->
  </div>
</div>
```

### Kompakti lista (JavaScript-pohjainen sketchi, PHP:ssä foreach)
```html
<div class="admin-compact-list">
  <div class="admin-compact-header" style="grid-template-columns: 2fr 1fr 1fr 80px 28px">
    <div>Nimi / Rotu</div>
    <div>Sukupuoli</div>
    <div>Syntymä</div>
    <div>VH-tunnus</div>
    <div></div>
  </div>

  <?php foreach ($horses as $horse): ?>
  <div class="admin-compact-row"
       style="grid-template-columns: 2fr 1fr 1fr 80px 28px"
       onclick="toggleExpand(<?= (int)$horse['id'] ?>)">
    <div>
      <div class="admin-compact-name"><?= e($horse['name']) ?></div>
      <div class="admin-compact-meta"><?= e($horse['breed']) ?></div>
    </div>
    <div><span class="gender-badge gender-<?= strtolower(e($horse['gender'])) ?>"><?= e($horse['gender']) ?></span></div>
    <div class="admin-compact-meta"><?= $horse['birth_date'] ? formatDate($horse['birth_date']) : '' ?></div>
    <div class="admin-compact-meta" style="font-family:var(--font-mono)"><?= e($horse['vh_id']) ?></div>
    <div><button class="admin-expand-btn" id="ebtn-<?= (int)$horse['id'] ?>">▸</button></div>
  </div>
  <div class="admin-expanded-panel" id="exp-<?= (int)$horse['id'] ?>">
    <a href="horse_edit.php?id=<?= (int)$horse['id'] ?>" class="btn-sm btn-sm-accent">✏️ Muokkaa</a>
    <a href="photos.php?horse_id=<?= (int)$horse['id'] ?>" class="btn-sm">📷 Kuvat</a>
    <!-- delete form -->
  </div>
  <?php endforeach; ?>
</div>
```

## What to Avoid

- **Pelkkä ikonisidebar (006-C)** — liian pienen tilan takia navigaatiolomakkeet piiloutuivat, tooltips eivät toimi PHP-sivuilla ilman JS-infraa.
- **Koko leveyden yläpalkki (006-B)** — vie liikaa pystykorkeutta lyhyillä näytöillä. Sivupalkki on parempi kun navigaatiolinkkejä on useampi kuin 3.
- **Hover-piilotetut toiminnot taulukossa (007-A)** — ei toimi mobiililla ja tuntui epäluotettavalta desktopillakin. Laajennuspaneeli on eksplisiittisempi.
- **Korttirivit hevoslistassa (007-B)** — vie liikaa pystykorkeutta. Taulukko/grid on informaatiorikkaampaa.

## Origin

Synthesized from sketches: 006, 007
Source files available in: sources/006-admin-layout/, sources/007-horse-list/
