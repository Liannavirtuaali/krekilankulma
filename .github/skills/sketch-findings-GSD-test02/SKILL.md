---
name: sketch-findings-GSD-test02
description: "Validoidut design-päätökset, CSS-kaavat ja visuaalinen suunta sketch-kokeiluista. Auto-ladataan UI-toteutuksessa projektissa GSD-test02."
applyTo: "**/*.php, **/*.css, **/*.html"
---

<context>
## Projekti: GSD-test02 — Virtuaalitalli

Lämmin, rustiikkinen virtuaalitalli — tuntuu kuin oikeasti kävelisi puiseen talliin.
Tummanruskea värimaailma (#3d2b1f), kermainen pohja, kultaiset aksentit.
Georgia-serif leipätekstille. Hevonen on sivuston hahmo ja päähenkilö, ei vain tietokannan rivi.
Tunnelma: arvokas, maanläheinen, yhteisöllinen.

**Referenssit:** Olemassa oleva style.css, rustiikkinen horse community -sivusto.

Sketch-sessiot: 2024-06-18, 2026-06-18
Wrap-up: 2026-06-18
</context>

<design_direction>
## Kokonaissuunta

**Väripaletti** — Pohja: `var(--color-bg): #f9f7f4` (kermainen). Pinta: `var(--color-surface): #fff`.
Pääväri: `var(--color-primary): #3d2b1f` (tummanruskea, otsikoissa ja nav-palkissa).
Aksentti: `var(--color-gold): #c9a84c` (korostuksiin, rajalinjoihin, numeroihin).
Korostus: `var(--color-accent): #a0633a` (linkkeihin, tageihin).

**Typografia** — Otsikoissa `font-family: var(--font-serif)` (Georgia), `font-weight: normal`.
Käyttöliittymäelementeissä (nav, badge, meta) `var(--font-sans)`.
VRL-tunnuksissa ja koodimaisessa tekstissä `var(--font-mono)`.

**Kortit** — `border: 1px solid var(--color-border)`, `border-radius: var(--radius-md)` tai `--radius-lg`,
`box-shadow: var(--shadow-sm)` levossa. Hover: `var(--shadow-md)` + liike.

**Navigaatiopalkki** — `background: var(--color-primary)`, alareunan korostus `border-bottom: 2/3px solid var(--color-gold)`.
Tekstit `var(--color-cream)`. Aktiivinen elementti `var(--color-gold)`.

**Sivuotsikko (sisäsivut)** — `background: var(--color-surface-accent)`, `border-bottom: 1px solid var(--color-border-warm)`,
otsikko Georgia-fontilla, leivänmurut `var(--color-text-muted)`.

**Interaktiot** — `transition: all 0.15s ease` kaikkialle. Listakorteissa `translateX(3px)` hover.
Nostokorteissa `translateY(-3px)` hover. Suodatinpainikkeet: pill-muoto (`border-radius: var(--radius-full)`).
</design_direction>

<findings_index>
## Design-alueet

| Alue | Referenssi | Pääpäätös |
|------|-----------|-----------|
| Listaus & Suodatus | references/listaus-suodatus.md | Vaakasuuntainen listakortti (160×90px kuva), pill-suodattimet, translateX hover |
| Hero & Etusivu | references/hero-etusivu.md | Koko leveyden hero kuvapohjalla + gradient-overlay; etusivulla overlay-kortit heroon; Ajankohtaista-blogi editoriaalinen nostokortti |
| Yksinkertaiset tietosivut | references/yksinkertaiset-sivut.md | Yksi kortti sivun keskellä, ikonirivirakenne yhteystiedoille |
| Admin Shell & Navigaatio | references/admin-shell.md | 220px tummanruskea sivupalkki; kompakti+laajennettava -lista hevosille |
| Admin Hallintasivut | references/admin-hallintasivut.md | Slide-in paneeli lomakkeille; kilpailut kompaktilista+modal; kuvat 1:1-ruudukko |
| Blogi & Sisältösivut | references/blogi-sisaltosivu.md | Artikkeli + sticky sidebar (260px); arkisto accordion vuosi→kuukausi; dropcap; blockquote kultareunalla |

## Teematiedosto

Voittava teema: `sources/themes/default.css` — kaikki CSS-muuttujat (värit, typografia, välit, varjot, pyöristykset).

## Lähdetiedostot

Alkuperäiset sketch-HTML-tiedostot: `.planning/sketches/`
</findings_index>

<key_patterns>
## Tärkeimmät kaavat

### Kortti (perusrakenne)
```css
.card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md); /* tai --radius-lg */
  box-shadow: var(--shadow-sm);
  transition: box-shadow 0.15s, transform 0.15s;
}
.card:hover { box-shadow: var(--shadow-md); }
```

### Vaakalistakortti (hevoset, varsat)
```css
.list-card {
  display: flex;
  gap: 0;
  overflow: hidden;
}
.list-card img { width: 160px; height: 90px; object-fit: cover; flex-shrink: 0; }
.list-card:hover { transform: translateX(3px); }
```

### Suodatinpainike (pill)
```css
.filter-btn {
  border-radius: var(--radius-full);
  padding: 4px 14px;
  border: 1px solid var(--color-border);
  background: var(--color-surface);
  color: var(--color-text-muted);
}
.filter-btn.active {
  background: var(--color-primary);
  color: var(--color-cream);
  border-color: var(--color-primary);
}
```

### Hero-banneri
```css
.hero-banner {
  background:
    linear-gradient(160deg, rgba(42,26,16,0.82) 0%, rgba(61,43,31,0.60) 100%),
    url('kuva.jpg') center/cover no-repeat;
}
```

### Overlay-kortit heroon (etusivu)
```css
.overlay-cards {
  margin: -80px auto 0; /* negatiivinen marginaali hero-alueen päälle */
  position: relative;
  z-index: 2;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  box-shadow: var(--shadow-lg); /* isompi varjo kelluvalle kortille */
}
```

### VRL-tunnus / koodityyppinen arvo
```css
.vrl-code {
  font-family: var(--font-mono);
  background: var(--color-parchment);
  border: 1px solid var(--color-border-warm);
  border-radius: var(--radius-sm);
  padding: 2px 8px;
}
```

### Placeholder-kuvat (kehitys)
- Hero-tausta: `https://picsum.photos/seed/stable/1600/600`
- Listakorttikuva: `https://picsum.photos/seed/horse/320/160`
- Profiilisivu hero: `https://picsum.photos/seed/horse/1200/400`
- Käytä aina `seed`-parametria — sama seed antaa aina saman kuvan

### Admin sivupalkki — nav-item aktiivinen tila
```css
.admin-nav-item.active {
  color: var(--color-gold);
  background: rgba(201,168,76,0.12);
  border-right: 3px solid var(--color-gold);
}
```

### Admin kompakti lista — grid-rivit
```css
/* Hevoset: */
.admin-compact-row { grid-template-columns: 2fr 1fr 1fr 80px 28px; }
/* Kasvatus: */
.admin-compact-row { grid-template-columns: 2fr 1fr 1fr 80px 28px; }
/* Kilpailut: */
.admin-compact-row { grid-template-columns: 2fr 100px 60px 70px 28px; }

.admin-compact-row:hover { background: var(--color-surface-warm); }
.admin-expanded-panel.open { display: block; }
```

### Slide-in paneeli (lomakkeet)
```css
.admin-slide-panel {
  position: fixed;
  top: 0; right: -440px;
  width: 420px; height: 100vh;
  transition: right 0.25s ease;
  border-left: 1px solid var(--color-border);
}
.admin-slide-panel.open { right: 0; }
/* Header: sama primary+gold kuin sidebar-logo */
.admin-slide-header {
  background: var(--color-primary);
  border-bottom: 3px solid var(--color-gold);
}
```

### Kompakti kuvaruudukko (kuvat)
```css
.admin-photo-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: var(--space-3);
}
.admin-photo-thumb {
  aspect-ratio: 1;
  border-radius: var(--radius-md);
  overflow: hidden;
}
/* Profiilikuva-merkintä (eka kuva, sort_order=1) */
.photo-profile-badge {
  position: absolute; bottom: 4px; right: 4px;
  background: var(--color-gold); color: #3d2b1f;
  font-size: 9px; font-weight: 700; text-transform: uppercase;
  padding: 1px 5px; border-radius: 3px;
}
```

### Sijoitusbadget (kilpailut)
```css
.place-1 { background: #fff3cd; color: #7a5f00; border: 1px solid #f0c040; }
.place-2 { background: #f0f0f0; color: #555;    border: 1px solid #ccc; }
.place-3 { background: #fde8d8; color: #8a3d00; border: 1px solid #e0a070; }
```

### Horse context -banneri (admin-sisäsivut)
```css
.horse-ctx {
  background: var(--color-parchment);
  border-bottom: 1px solid var(--color-border-warm);
  padding: var(--space-3) var(--space-8);
  display: flex; align-items: center; gap: var(--space-4);
}
```
</key_patterns>

<metadata>
## Käsitellyt sketchit

- 001-hevoslista (voittaja B: Listakortit)
- 002-hevonen-profiili (voittaja B: Hero + Sidebar)
- 003-kasvatus (voittaja C: Kompakti lista)
- 004-yhteystiedot (voittaja A: Korttikortti)
- 005-etusivu (voittaja C: Overlay-kortit)
- 006-admin-layout (voittaja A: Sivupalkki)
- 007-horse-list (voittaja C: Kompakti + laajennettava)
- 008-admin-kasvatus (voittaja B: Slide-in paneeli)
- 009-admin-kilpailut (voittaja A: Kompakti lista + modal)
- 010-admin-kuvat (voittaja B: Kompakti ruudukko)
- 011-ajankohtaista-homepage (voittaja C: Editoriaalinen nostokortti)
- 012-blogi-postaus (voittaja C: Artikkeli + sticky sidebar)
</metadata>
