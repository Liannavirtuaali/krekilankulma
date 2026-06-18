# Hero & Etusivu

Validoidut päätökset hero-osioiden ja etusivun rakenteesta.
Lähde: Sketch 002 (hevosen profiili, voittaja B) + Sketch 005 (etusivu, voittaja C).

## Design-päätökset

**Hero-banneri: koko leveys, gradient-overlay tekstille**
```css
.hero-banner {
  width: 100%;
  height: 320px; /* profiilisivu */
  /* tai min-height: 560px; etusivu */
  overflow: hidden;
  position: relative;
  background:
    linear-gradient(160deg, rgba(42,26,16,0.82) 0%, rgba(61,43,31,0.72) 45%, rgba(90,64,48,0.60) 100%),
    url('kuva.jpg') center/cover no-repeat;
}

/* Tekstioverlay heroon alareunaan (profiilisivu) */
.hero-overlay {
  position: absolute; bottom: 0; left: 0; right: 0;
  background: linear-gradient(transparent, rgba(61,43,31,0.9));
  padding: 2rem 2rem 1.5rem;
  color: white;
}
```

**Profiilisivun kaksisarakerakenne (Hero + Sidebar)**
```css
.content-layout {
  max-width: 1100px; margin: 0 auto; padding: 1.5rem;
  display: grid;
  grid-template-columns: 1fr 300px; /* pääsisältö + kiinteä sidebar */
  gap: 1.5rem;
  align-items: start;
}
```

**Sidebar-kortit**
```css
.sidebar-card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: 1rem;
  box-shadow: var(--shadow-sm);
}
```

**Hero-pill-tagit (profiilisivulla herossa)**
```css
.hero-pill {
  background: rgba(255,255,255,0.18);
  border: 1px solid rgba(255,255,255,0.3);
  border-radius: var(--radius-full);
  padding: 2px 12px;
  font-size: .78rem;
  font-family: var(--font-sans);
  color: white;
}
```

**Sektionotsikko (keltainen palkki vasemmalla)**
```css
.main-col section h2 {
  font-size: 1.1rem;
  color: var(--color-primary);
  border-left: 4px solid var(--color-gold);
  padding-left: .75rem;
  margin-bottom: 1rem;
}
```

**Overlay-kortit heroon (etusivu, valittu C)**
```css
.c-cards-overlay {
  position: relative;
  max-width: 1000px;
  margin: -80px auto 0; /* kelluu hero-alueen päälle */
  padding: 0 32px;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  z-index: 2;
}

.c-card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  padding: 28px 24px;
  box-shadow: var(--shadow-lg); /* isompi varjo koska kelluu */
  transition: transform 0.15s, box-shadow 0.15s;
}
.c-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 16px 32px rgba(61,43,31,0.18);
}
```

**Tilastoanimaatio (JS, animoidut laskurit)**
```js
function animateCount(el, target, duration) {
  let start = 0;
  const step = target / (duration / 16);
  const timer = setInterval(() => {
    start = Math.min(start + step, target);
    el.textContent = Math.round(start);
    if (start >= target) clearInterval(timer);
  }, 16);
}
// Käyttö: animateCount(el, 12, 800);
```

**Tilastonumero-tyyli**
```css
.stat-number {
  font-family: var(--font-serif); /* Georgia */
  font-size: 3rem;               /* tai var(--text-3xl) = 2rem */
  color: var(--color-gold);
  line-height: 1;
  display: block;
}
.stat-label {
  font-size: var(--text-xs);
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-top: 6px;
}
```

**Placeholder-kuvat kehityksessä**
- Hero-tausta: `https://picsum.photos/seed/stable/1600/600`
- Kortit (vaaka): `https://picsum.photos/seed/horses1/320/160`
- Esittely-banneri: `https://picsum.photos/seed/barn/600/200`
- Käytä `seed`-parametria pysyviin kuviin (sama seed = sama kuva)

## HTML-rakenne

**Profiilisivu (002-B voittaja):**
```html
<div class="hero-banner">
  <img src="..." alt="Hevonen">
  <div class="hero-overlay">
    <h1>Hevosen Nimi</h1>
    <div class="callname">Kutsumanimi</div>
    <div class="hero-pills">
      <span class="hero-pill">Tamma</span>
      <span class="hero-pill">Rotu</span>
      <span class="hero-pill">2019</span>
    </div>
  </div>
</div>

<div class="content-layout">
  <main class="main-col">
    <section>
      <h2>Perustiedot</h2>
      <!-- dl tai info-grid -->
    </section>
    <section>
      <h2>Kilpailutulokset</h2>
    </section>
  </main>
  <aside class="sidebar">
    <div class="sidebar-card">Sukutaulu</div>
    <div class="sidebar-card">Kalenteri</div>
  </aside>
</div>
```

**Etusivu (005-C voittaja):**
```html
<!-- Hero koko leveys -->
<div class="hero"> ... </div>

<!-- Kortit kelluvat heroon päälle -->
<div class="c-cards-overlay">
  <div class="c-card">
    <img src="..."> <!-- picsum placeholder -->
    <div class="c-card-stat">12</div>
    <h3>Hevosta tallissa</h3>
    <a href="#">Katso kaikki →</a>
  </div>
  <!-- ... 3 korttia -->
</div>

<!-- Esittely + uutinen alla -->
<div class="c-main"> ... </div>
```

---

## Ajankohtaista / Blogi-nostokortti etusivulla (Sketch 011-C)

Validoitu paikka uusimman blogipostauksen näyttämiselle: `.frontpage-uutinen`-alue etusivun 2-kolumni-rakenteessa.

**Editoriaalinen nostokortti:**
```css
.editorial-uutinen {
  background: var(--color-surface-warm);       /* lämmin parchment-tausta */
  border: 1px solid var(--color-border-warm);
  border-left: 4px solid var(--color-gold);    /* kultainen vasen korostusreuna */
  border-radius: var(--radius-lg);
  padding: var(--space-6) var(--space-8);
  box-shadow: var(--shadow-sm);
}
```

**Osion tag:**
```css
.uutinen-tag {
  font-family: var(--font-sans);
  font-size: var(--text-xs);
  text-transform: uppercase;
  letter-spacing: .08em;
  color: var(--color-gold);
  font-weight: 700;
  margin-bottom: var(--space-3);
  display: block;
}
```

**Otsikko, ingressi, footer:**
```css
.editorial-uutinen h3 {
  font-family: var(--font-serif);
  font-size: var(--text-xl);
  font-weight: normal;
  color: var(--color-primary);
  margin-bottom: var(--space-3);
  line-height: 1.3;
}
.editorial-uutinen .body-text {
  font-family: var(--font-sans);
  font-size: var(--text-sm);
  color: var(--color-text-muted);
  line-height: 1.65;
  margin-bottom: var(--space-4);
}
.editorial-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.editorial-footer .date {
  font-family: var(--font-sans);
  font-size: var(--text-xs);
  color: var(--color-text-light);
}
```

**"Lue lisää" pill-nappi (täyttyy hoverilla):**
```css
.editorial-footer a {
  font-family: var(--font-sans);
  font-size: var(--text-sm);
  color: var(--color-accent);
  font-weight: 600;
  text-decoration: none;
  border: 1px solid var(--color-accent);
  padding: 5px 14px;
  border-radius: var(--radius-full);
  transition: all 0.15s;
}
.editorial-footer a:hover {
  background: var(--color-accent);
  color: #fff;
}
```

**HTML-rakenne:**
```html
<div class="editorial-uutinen">
  <span class="uutinen-tag">📰 Ajankohtaista</span>
  <h3><?= e($post['title']) ?></h3>
  <p class="body-text"><?= e(mb_substr($post['content'], 0, 220)) ?>…</p>
  <div class="editorial-footer">
    <span class="date"><?= e($post['created_at']) ?></span>
    <a href="#">Lue lisää →</a>
  </div>
</div>
```

**Hylätyt vaihtoehdot (Sketch 011):**
- **A: Uutinen overlay-korttigrideissä** — kortti liian ahdas pitkälle tekstille; ei tarpeeksi editoriaalinen tunne
- **B: Erillinen leveä uutispalkki** — näkyvä mutta lisää scrollia; painottaa uutista enemmän kuin tilastoja

## Mitä välttää

- **Lineaarinen scroll** (Sketch 002 A) profiilisivulle — liikaa scrollausta, informaatio hukkuu
- **Overlay-kortit ilman `box-shadow: var(--shadow-lg)`** — kelluminen ei erotu ilman vahvempaa varjoa
- **Tilastonumerot ilman animaatiota** — laskurianimaatio tekee numerot huomioiduiksi
- **Uutinen overlay-korttigrideissä** — kortti ahdas; törmää tilastojen kanssa hierarkiassa

## Alkuperäiset sketchit
- 002-hevonen-profiili — sources/002-hevonen-profiili/
- 005-etusivu — sources/005-etusivu/
- 011-ajankohtaista-homepage — .planning/sketches/011-ajankohtaista-homepage/
