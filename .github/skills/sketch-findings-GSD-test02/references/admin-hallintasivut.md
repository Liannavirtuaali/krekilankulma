# Admin Hallintasivut

## Design Decisions

### Kasvatus — Slide-in lomakepaneeli (voittaja 008-B)
Uuden varsan lisäys avaa 420px leveän paneelin oikealta (`right: -440px → 0`, `transition: right 0.25s ease`). Musta overlay taustalla, klikkaus overlayhin sulkee paneelin. Header paneelissa on `var(--color-primary)` taustaväri ja `border-bottom: 3px solid var(--color-gold)` — sama kuin sivupalkin logo-osio, luo yhteenkuuluvuutta.

Varsamerkintöjen lista käyttää samaa kompakti+laajennettava -rakennetta kuin hevosten lista (007-C). Grid: nimi+sukupuoli | isä/emä | syntymä | tila | expand.

Status-badget: `status-born` (vihreä) ja `status-expected` (keltainen).

**Miksi slide-in voitti:** Inline-lomake (A) sekoittui listaan — ei selvää rajaa "olen lomakkeessa" vs. "olen listassa". Slide-in antaa lomakkeelle oman kontekstin.

### Kilpailut — Kompakti lista + modal (voittaja 009-A)
Yhteenvetotilastot yläosassa (pieni 3-korttirivistö: kilpailumäärä, kokonaispisteet, voitot). Kompakti grid-lista sarakkein: nimi (2fr) | päivämäärä (100px) | sijoitusbadge (60px) | pisteet (70px) | expand (28px).

Sijoitusbadget värikoodattu: kulta (#fff3cd/f0c040), hopea (#f0f0f0/ccc), pronssi (#fde8d8/e0a070), muut (neutraali).

Uusi kilpailu avautuu modaali-dialogissa (ei slide-in). Modaalin header `var(--color-primary)` + kultaraja. Lomakkeessa: nimi, päivämäärä, sijoitus, pisteet, muistiinpanot.

**Miksi lista voitti:** Aikajana (B) näytti hienolta mutta oli hitaampi skannata nopeasti. Lista on informaatiorikkaampaa ja tutumpi hallintaympäristössä. Aikajana voi toimia käyttäjäpuolella.

### Kuvat — Kompakti ruudukko (voittaja 010-B)
1:1-kumaruudukko `grid-template-columns: repeat(auto-fill, minmax(100px, 1fr))`. Järjestysnumero bottom-left (musta semi-transparent overlay, valkoinen teksti). Ensimmäinen kuva = profiili, merkintä "PROFIILI" (`background: var(--color-gold), color: #3d2b1f`).

Poistopainike (✕) top-right, näkyy hover-tilassa. Kuvan klikkaus avaa lightboxin.

Rajapalkki ylhäällä: `X / 10 kuvaa` tekstinä + header-napin viereissä (ei erillinen dropzone). Upload-progressi näkyy lisäyksen yhteydessä palkki-animaationa.

**Miksi kompakti voitti:** Iso dropzone (A) vei ylhäältä paljon tilaa ja tuntui toistuvalta — admin lataa kuvat harvoin, mutta katselee listaa usein. Kompakti ruudukko priorisoi selailun.

## CSS Patterns

### Slide-in paneeli
```css
.admin-slide-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.3);
  z-index: 500;
  display: none;
}
.admin-slide-overlay.open { display: block; }

.admin-slide-panel {
  position: fixed;
  top: 0;
  right: -440px;
  width: 420px;
  height: 100vh;
  background: var(--color-surface);
  box-shadow: var(--shadow-lg);
  z-index: 501;
  display: flex;
  flex-direction: column;
  transition: right 0.25s ease;
  border-left: 1px solid var(--color-border);
}
.admin-slide-panel.open { right: 0; }

.admin-slide-header {
  background: var(--color-primary);
  padding: var(--space-5) var(--space-6);
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 3px solid var(--color-gold);
}
.admin-slide-header h2 {
  font-family: var(--font-serif);
  font-size: var(--text-xl);
  color: var(--color-cream);
  font-weight: normal;
}
.admin-slide-close {
  background: none;
  border: none;
  color: var(--color-cream);
  opacity: 0.6;
  cursor: pointer;
  font-size: 20px;
}
.admin-slide-close:hover { opacity: 1; }

.admin-slide-body {
  flex: 1;
  overflow-y: auto;
  padding: var(--space-6);
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}
.admin-slide-footer {
  padding: var(--space-4) var(--space-6);
  border-top: 1px solid var(--color-border);
  display: flex;
  gap: var(--space-3);
}
```

### Modaali (kilpailut)
```css
.admin-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.4);
  z-index: 500;
  display: none;
  align-items: center;
  justify-content: center;
}
.admin-modal-overlay.open { display: flex; }

.admin-modal {
  background: var(--color-surface);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  width: 520px;
  max-width: 95vw;
  overflow: hidden;
}
.admin-modal-header {
  background: var(--color-primary);
  padding: var(--space-5) var(--space-6);
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 3px solid var(--color-gold);
}
.admin-modal-header h2 {
  font-family: var(--font-serif);
  font-size: var(--text-xl);
  color: var(--color-cream);
  font-weight: normal;
}
.admin-modal-body {
  padding: var(--space-6);
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}
.admin-modal-footer {
  padding: var(--space-4) var(--space-6);
  border-top: 1px solid var(--color-border);
  display: flex;
  gap: var(--space-3);
}
```

### Sijoitusbadget (kilpailut)
```css
.place-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: var(--radius-full);
  font-size: var(--text-xs);
  font-weight: 700;
}
.place-1 { background: #fff3cd; color: #7a5f00; border: 1px solid #f0c040; }
.place-2 { background: #f0f0f0; color: #555;    border: 1px solid #ccc; }
.place-3 { background: #fde8d8; color: #8a3d00; border: 1px solid #e0a070; }
.place-other { background: var(--color-surface-warm); color: var(--color-text-muted); border: 1px solid var(--color-border); }
```

### Status-badget (kasvatus)
```css
.status-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: var(--radius-full);
  font-size: var(--text-xs);
  font-weight: 600;
}
.status-born     { background: #e8f5e8; color: #2b6b2b; }
.status-expected { background: #fff8e0; color: #9a7000; }
```

### Kompakti kuvaruudukko (kuvat)
```css
.admin-photo-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: var(--space-3);
}

.admin-photo-thumb {
  width: 100%;
  aspect-ratio: 1;
  position: relative;
  cursor: pointer;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  overflow: hidden;
  background: var(--color-parchment);
  transition: all 0.15s;
}
.admin-photo-thumb:hover {
  border-color: var(--color-accent);
  transform: scale(1.04);
  box-shadow: var(--shadow-md);
}

.admin-photo-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* Järjestysnumero */
.photo-order-badge {
  position: absolute;
  bottom: 4px;
  left: 4px;
  background: rgba(0,0,0,0.5);
  color: white;
  font-size: 10px;
  padding: 1px 5px;
  border-radius: 3px;
  font-family: var(--font-mono);
}

/* Profiilikuvan merkintä (eka kuva) */
.photo-profile-badge {
  position: absolute;
  bottom: 4px;
  right: 4px;
  background: var(--color-gold);
  color: #3d2b1f;
  font-size: 9px;
  padding: 1px 5px;
  border-radius: 3px;
  font-weight: 700;
  text-transform: uppercase;
}

/* Poistopainike (hover) */
.photo-delete-btn {
  position: absolute;
  top: 4px;
  right: 4px;
  background: rgba(138,48,48,0.85);
  color: white;
  border: none;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  font-size: 10px;
  cursor: pointer;
  display: none;
  align-items: center;
  justify-content: center;
}
.admin-photo-thumb:hover .photo-delete-btn { display: flex; }
```

### Lomakekentät (admin-yleiset)
```css
.admin-form-group {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}
.admin-form-group label {
  font-size: var(--text-xs);
  color: var(--color-text-muted);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.admin-form-group input,
.admin-form-group select,
.admin-form-group textarea {
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: var(--space-2) var(--space-3);
  font-size: var(--text-sm);
  font-family: var(--font-sans);
  background: var(--color-surface);
  color: var(--color-text);
  transition: border-color 0.15s;
}
.admin-form-group input:focus,
.admin-form-group select:focus,
.admin-form-group textarea:focus {
  outline: none;
  border-color: var(--color-accent);
}
```

### Pisteyhteenveto-kortit (kilpailut)
```css
.admin-stat-row {
  display: flex;
  gap: var(--space-4);
  margin-bottom: var(--space-6);
}
.admin-stat-card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: var(--space-4) var(--space-5);
  flex: 1;
  box-shadow: var(--shadow-sm);
}
.admin-stat-num {
  font-family: var(--font-serif);
  font-size: var(--text-2xl);
  color: var(--color-gold);
  line-height: 1;
}
.admin-stat-label {
  font-size: var(--text-xs);
  color: var(--color-text-muted);
  margin-top: 2px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
```

### Upload-rajaviiva (kuvat — ei dropzone, vain kuvalaskuri headerissa)
```css
.photo-limit-indicator {
  font-size: var(--text-xs);
  color: var(--color-text-muted);
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-full);
  padding: 3px 10px;
}
.photo-limit-indicator.near-full { color: var(--color-danger); border-color: #c9a0a0; }
```

## HTML Structures

### Slide-in paneeli (PHP-template)
```html
<!-- Lisää-painike headerissa -->
<button class="btn-primary" onclick="openSlidePanel()">+ Lisää varsa</button>

<!-- Overlay + paneeli (sivun lopussa) -->
<div class="admin-slide-overlay" id="slide-overlay" onclick="closeSlidePanel()"></div>
<div class="admin-slide-panel" id="slide-panel">
  <div class="admin-slide-header">
    <h2>Lisää varsa — <?= e($horse['name']) ?></h2>
    <button class="admin-slide-close" onclick="closeSlidePanel()">✕</button>
  </div>
  <div class="admin-slide-body">
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
      <input type="hidden" name="action" value="add">
      <!-- lomakekentät -->
    </form>
  </div>
  <div class="admin-slide-footer">
    <button type="submit" form="foal-form" class="btn-primary">Tallenna</button>
    <button type="button" class="btn-ghost" onclick="closeSlidePanel()">Peruuta</button>
  </div>
</div>
<script>
function openSlidePanel() {
  document.getElementById('slide-overlay').classList.add('open');
  document.getElementById('slide-panel').classList.add('open');
}
function closeSlidePanel() {
  document.getElementById('slide-overlay').classList.remove('open');
  document.getElementById('slide-panel').classList.remove('open');
}
</script>
```

### Kilpailu-modaali (PHP-template)
```html
<div class="admin-modal-overlay" id="comp-modal" onclick="if(event.target===this)closeModal()">
  <div class="admin-modal">
    <div class="admin-modal-header">
      <h2>Lisää kilpailu</h2>
      <button onclick="closeModal()">✕</button>
    </div>
    <div class="admin-modal-body">
      <form id="comp-form" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
        <input type="hidden" name="action" value="add">
        <!-- kentät -->
      </form>
    </div>
    <div class="admin-modal-footer">
      <button type="submit" form="comp-form" class="btn-primary">Tallenna kilpailu</button>
      <button type="button" class="btn-ghost" onclick="closeModal()">Peruuta</button>
    </div>
  </div>
</div>
```

## What to Avoid

- **Inline lomake listalla (008-A)** — sekoittuu listaan, käyttäjä ei hahmota onko lomake auki vai lista vain pitkä.
- **Kilpailun aikajana admin-puolella (009-B)** — hieno mutta hidas skannata. Säilytetään ideana käyttäjäpuolelle.
- **Iso drag-and-drop dropzone kuvasivulla (010-A)** — vie liikaa tilaa. Admin lisää kuvia harvoin, katsoo usein. Kompakti ruudukko palvelee paremmin.
- **Liian monta saraketta taulukossa** — kilpailut ja kasvatus toimivat 5 sarakkeella; lisäsarakkeet (notes) kuuluvat laajennuspaneeliin.

## Origin

Synthesized from sketches: 008, 009, 010
Source files available in: sources/008-admin-kasvatus/, sources/009-admin-kilpailut/, sources/010-admin-kuvat/
