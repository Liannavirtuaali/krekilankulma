# Blogi & Sisältösivut

Validoidut päätökset blogipostauksen sivulle.
Lähde: Sketch 012 (blogi-postaus, voittaja C: Artikkeli + sticky sidebar).

## Design-päätökset

**Sivurakenne: artikkeli + sticky sidebar**
```css
.post-layout {
  max-width: 1100px;
  margin: 0 auto;
  padding: var(--space-8) var(--space-6);
  display: grid;
  grid-template-columns: 1fr 260px;
  gap: var(--space-8);
  align-items: start;
}

.post-sidebar {
  position: sticky;
  top: 68px; /* header-korkeus + pieni väli */
}
```

**Artikkelin typografia**
```css
.post-body {
  line-height: 1.8;
  font-size: var(--text-base);
  color: var(--color-text);
  font-family: var(--font-serif);
}
.post-body p { margin-bottom: 1.25rem; }

/* Dropcap — ensimmäinen kirjain */
.post-body p:first-child::first-letter {
  font-size: 3.2rem;
  font-family: var(--font-serif);
  color: var(--color-primary);
  float: left;
  line-height: 1;
  margin-right: 6px;
  margin-top: 4px;
}
```

**Blockquote**
```css
.post-body blockquote {
  border-left: 4px solid var(--color-gold);
  padding: var(--space-3) var(--space-6);
  margin: var(--space-6) 0;
  background: var(--color-surface-warm);
  border-radius: 0 var(--radius-md) var(--radius-md) 0;
  font-style: italic;
  color: var(--color-text-muted);
}
```

**Kuvat artikkelissa**
```css
.post-body img {
  width: 100%;
  border-radius: var(--radius-md);
  margin: var(--space-6) 0;
  box-shadow: var(--shadow-md);
}
```

**Edellinen / seuraava -navigointi**
```css
.post-nav {
  display: flex;
  justify-content: space-between;
  gap: var(--space-4);
  margin-top: var(--space-8);
  padding-top: var(--space-6);
  border-top: 1px solid var(--color-border);
}
.post-nav a {
  display: flex; flex-direction: column; gap: 4px;
  text-decoration: none; flex: 1;
  padding: var(--space-4);
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border);
  background: var(--color-surface);
  transition: box-shadow 0.15s, transform 0.15s;
}
.post-nav a:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
.post-nav a.prev { align-items: flex-start; }
.post-nav a.next { align-items: flex-end; text-align: right; }
.post-nav .nav-dir {
  font-family: var(--font-sans); font-size: var(--text-xs);
  color: var(--color-text-light); text-transform: uppercase; letter-spacing: .06em;
}
.post-nav .nav-title {
  font-family: var(--font-serif); font-size: var(--text-base);
  color: var(--color-primary); font-weight: normal;
}
```

**Arkistosidebar**
```css
.archive-sidebar {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  overflow: hidden;
}
.archive-sidebar-title {
  background: var(--color-primary); color: var(--color-cream);
  padding: var(--space-3) var(--space-4);
  font-family: var(--font-sans); font-size: var(--text-sm);
  font-weight: 600; letter-spacing: .03em;
  border-bottom: 2px solid var(--color-gold);
}
.archive-year-btn {
  width: 100%; text-align: left;
  padding: var(--space-3) var(--space-4);
  background: var(--color-surface-warm);
  border: none; cursor: pointer;
  font-family: var(--font-sans); font-size: var(--text-sm);
  font-weight: 600; color: var(--color-primary);
  display: flex; justify-content: space-between; align-items: center;
  transition: background 0.12s;
}
.archive-year-btn:hover { background: var(--color-parchment); }
.archive-month {
  display: flex; justify-content: space-between;
  padding: var(--space-2) var(--space-6);
  text-decoration: none; color: var(--color-text-muted);
  font-family: var(--font-sans); font-size: var(--text-sm);
  border-bottom: 1px solid var(--color-border);
  transition: background 0.12s, color 0.12s;
}
.archive-month:hover { background: var(--color-surface-warm); color: var(--color-primary); }
.archive-month.active { color: var(--color-accent); font-weight: 600; }
.archive-month .count {
  font-size: var(--text-xs); color: var(--color-text-light);
  background: var(--color-bg); border-radius: var(--radius-full);
  padding: 1px 7px; border: 1px solid var(--color-border);
}
```

**Kompakti navigaatioboxi sidebarissa**
```html
<div style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-lg);padding:var(--space-4);margin-bottom:var(--space-4);">
  <div style="font-family:var(--font-sans);font-size:var(--text-xs);text-transform:uppercase;letter-spacing:.06em;color:var(--color-gold);font-weight:700;margin-bottom:var(--space-3);">Navigointi</div>
  <a href="...">← Edellinen otsikko</a>
  <a href="...">Seuraava otsikko →</a>
</div>
```

## HTML-rakenne (pohja)

```html
<!-- Sivuotsikkopalkki -->
<div class="page-header">
  <div class="page-header-inner">
    <div class="breadcrumb"><a href="/">Etusivu</a> › <a href="/blogi">Ajankohtaista</a> › <?= e($post['title']) ?></div>
    <h1><?= e($post['title']) ?></h1>
    <div class="post-meta"><?= e($post['created_at']) ?></div>
  </div>
</div>

<!-- Sisältö -->
<div class="post-layout">
  <article>
    <img src="..." alt="...">
    <div class="post-body">
      <?= $post['content'] /* sanitoitu HTML tai escapattu teksti */ ?>
    </div>
    <nav class="post-nav">
      <a href="..." class="prev">...</a>
      <a href="..." class="next">...</a>
    </nav>
  </article>
  <aside class="post-sidebar">
    <!-- kompakti nav + archive-sidebar -->
  </aside>
</div>
```

## Mitä välttää

- **A: Staattinen sidebar** — toimii, mutta menettää kontekstin pitkissä artikkeleissa; sticky on selvästi parempi
- **B: Koko leveys + arkisto alla** — artikkeli leveämpi, mutta arkisto hautautuu pohjalle eikä ohjaa navigointia

## Alkuperäiset sketchit
- 012-blogi-postaus — .planning/sketches/012-blogi-postaus/
