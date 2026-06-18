# Listaus & Suodatus

Validoidut päätökset hevos- ja varsalistojen esittämiseen.
Lähde: Sketch 001 (hevoslista, voittaja B) + Sketch 003 (kasvatus, voittaja C).

## Design-päätökset

**Korttirakenne: vaakasuuntainen lista, ei ruudukko**
- Valittu B: Listakortit (001) — `display: flex`, kiinteä kuva vasemmalla, teksti oikealla
- Hylätty A: Grid — liian vähän informaatiota per kortti ilman kuvausta
- Hylätty C: Mosaiikki — toimii vain isoilla, laadukkailla kuvilla

**Kuvakoko listoissa**
- Hevoslista: `width: 160px; height: 90px; object-fit: cover` — leveä thumbnail
- Kasvatuslista (kompakti): `width: 64px; height: 64px` — pieni neliö

**Hover-efekti kortissa**
```css
.horse-list-card:hover {
  box-shadow: var(--shadow-md);
  transform: translateX(3px); /* vaakasuuntainen liike, ei ylös */
}
```

**Suodatuspainikkeet (filter bar)**
```css
.filter-btn {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-full); /* pill-muoto */
  padding: 4px 14px;
  font-size: .82rem;
  font-family: var(--font-sans);
  color: var(--color-text-muted);
  transition: all 0.15s;
}
.filter-btn.active {
  background: var(--color-primary);
  color: var(--color-cream);
  border-color: var(--color-primary);
}
```

**Status-badget (kasvatus)**
```css
/* Odotettu varsa */
.status-expected {
  background: #fff8e6; color: #8a6500;
  border: 1px solid #e6c84a;
  border-radius: var(--radius-full);
  padding: 2px 10px; font-size: .72rem;
}
/* Syntynyt varsa */
.status-born {
  background: #f0f5ef; color: #3d6b38;
  border: 1px solid #8ab884;
  border-radius: var(--radius-full);
  padding: 2px 10px; font-size: .72rem;
}
```

**Nimetön varsa / ei kuvaa**
```css
.no-photo {
  background: repeating-linear-gradient(
    45deg,
    var(--color-surface-accent), var(--color-surface-accent) 4px,
    var(--color-parchment) 4px, var(--color-parchment) 8px
  );
  /* Vinoviivakuvio — selvästi eri kuin oikea kuva */
}
```

**Sivuleveysrajoitus**
- Hevoslista: `max-width: 1100px` — leveämpi, enemmän kortit rinnakkain
- Kasvatuslista: `max-width: 900px` — kapea, luettavampi listalle

## HTML-rakenne

**Hevoslista-kortti (001-B voittaja):**
```html
<div class="horse-list-card">
  <img src="..." alt="Hevonen" width="160" height="90">
  <div class="card-body">
    <h3><a href="#">Hevosen Nimi</a></h3>
    <div class="meta-row">
      <span>Rotu</span>
      <span>Tamma</span>
      <span>2019</span>
    </div>
    <p class="desc">Lyhyt kuvaus...</p>
    <span class="discipline-tag">Koulu</span>
  </div>
</div>
```

**Kompakti varsarivi (003-C voittaja):**
```html
<div class="foal-card-c">
  <div class="foal-thumb"><!-- 64×64 kuva tai no-photo --></div>
  <div class="foal-info">
    <span class="foal-name">Varsan Nimi</span>
    <span class="foal-parents">Isä × Emä</span>
    <span class="foal-year">2024</span>
  </div>
  <span class="status-born">Syntynyt</span>
</div>
```

## Mitä välttää

- **Ruudukkoa (grid)** hevoslistauksessa — menettää kuvaukset, kortit näyttävät tasapaksuilta
- **Liian isoa thumbnail** kompaktissa kasvatuslistassa — rikkoo kompaktiuden idean
- **Ylös-hover** (`translateY(-2px)`) listakortissa — vaakaliike (`translateX(3px)`) sopii paremmin vaakakorttiin
- **Mosaiikkia ilman oikeita kuvia** — riippuu liikaa laadukkaan kuvasisällön olemassaolosta

## Alkuperäiset sketchit
- 001-hevoslista — sources/001-hevoslista/
- 003-kasvatus — sources/003-kasvatus/
