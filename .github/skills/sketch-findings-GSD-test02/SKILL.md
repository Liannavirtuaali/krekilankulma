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

Sketch-sessiot: 2024-06-18
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
| Hero & Etusivu | references/hero-etusivu.md | Koko leveyden hero kuvapohjalla + gradient-overlay; etusivulla overlay-kortit heroon |
| Yksinkertaiset tietosivut | references/yksinkertaiset-sivut.md | Yksi kortti sivun keskellä, ikonirivirakenne yhteystiedoille |

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
</key_patterns>

<metadata>
## Käsitellyt sketchit

- 001-hevoslista (voittaja B: Listakortit)
- 002-hevonen-profiili (voittaja B: Hero + Sidebar)
- 003-kasvatus (voittaja C: Kompakti lista)
- 004-yhteystiedot (voittaja A: Korttikortti)
- 005-etusivu (voittaja C: Overlay-kortit)
</metadata>
