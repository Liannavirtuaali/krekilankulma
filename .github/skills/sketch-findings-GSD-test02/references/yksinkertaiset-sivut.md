# Yksinkertaiset tietosivut

Validoidut päätökset yhteystiedot-tyyppisille sivuille, joilla on vähän sisältöä.
Lähde: Sketch 004 (yhteystiedot, voittaja A: Korttikortti).

## Design-päätökset

**Rakenne: yksi kortti sivun keskellä**
- Valittu A: Korttikortti — `max-width: 520px; margin: 48px auto`
- Hylätty B: Jaettu layout — turhan monimutkainen niin vähälle sisällölle
- Hylätty C: Kirjemainen — hauska mutta henkilökohtainen tyyli ei sovi kaikkialle

**Kortin tyyli**
```css
.contact-card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  padding: 40px 48px;
  text-align: center;
}
```

**Yhteystietorivi ikonilla**
```css
.contact-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 0;
  border-bottom: 1px solid var(--color-border);
  text-align: left;
}
.contact-row:last-child { border-bottom: none; }

.contact-icon {
  width: 32px; height: 32px;
  background: var(--color-surface-accent);
  border-radius: var(--radius-sm);
  display: flex; align-items: center; justify-content: center;
  font-size: 15px;
  flex-shrink: 0;
}

.contact-label {
  font-size: var(--text-xs);
  color: var(--color-text-light);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 2px;
}

.contact-value { font-size: var(--text-base); color: var(--color-text); }
.contact-value a { color: var(--color-accent); text-decoration: none; font-weight: 500; }
.contact-value a:hover { text-decoration: underline; }
```

**VRL-tunnuksen tyyli — erottuu tekniseksi tiedoksi**
```css
.vrl-code {
  font-family: var(--font-mono);
  font-size: var(--text-sm);
  background: var(--color-parchment);
  padding: 2px 8px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border-warm);
  color: var(--color-text-muted);
}
```

**CTA-painike (Lähetä sähköpostia)**
```css
.btn-primary {
  display: inline-block;
  background: var(--color-primary);
  color: var(--color-cream);
  padding: 11px 28px;
  border-radius: var(--radius-md);
  text-decoration: none;
  font-size: var(--text-sm);
  font-weight: 500;
  transition: background 0.15s;
}
.btn-primary:hover { background: var(--color-primary-hover); }
```

**Sivuotsikkoalue (page-title-band) — käytetään kaikilla sisäsivuilla**
```css
.page-title-band {
  background: var(--color-surface-accent);
  border-bottom: 1px solid var(--color-border-warm);
  padding: 28px 32px;
  text-align: center;
}
.page-title-band h1 {
  font-family: var(--font-serif);
  font-size: var(--text-2xl);
  color: var(--color-primary);
  font-weight: normal;
}
.page-title-band .breadcrumb {
  font-size: var(--text-xs);
  color: var(--color-text-muted);
  margin-top: 4px;
}
```

## HTML-rakenne

```html
<!-- Sivuotsikko (kaikille sisäsivuille) -->
<div class="page-title-band">
  <h1>Yhteystiedot</h1>
  <div class="breadcrumb">Etusivu › Yhteystiedot</div>
</div>

<!-- Sisältö: kortti keskellä -->
<main style="max-width:520px; margin:48px auto; padding:0 16px;">
  <div class="contact-card">
    <!-- Kuvake + otsikko -->
    <div class="contact-icon-large">👤</div>
    <h2>Tallinomistaja</h2>
    <span class="vrl-badge vrl-code">VRL-12345</span>

    <!-- Yhteystietorivit -->
    <div class="contact-row">
      <div class="contact-icon">✉️</div>
      <div>
        <div class="contact-label">Sähköposti</div>
        <div class="contact-value"><a href="mailto:talli@example.com">talli@example.com</a></div>
      </div>
    </div>
    <div class="contact-row">
      <div class="contact-icon">🏷️</div>
      <div>
        <div class="contact-label">Nimimerkki</div>
        <div class="contact-value">TallinOmistaja</div>
      </div>
    </div>

    <!-- CTA -->
    <a class="btn-primary" href="mailto:talli@example.com">✉ Lähetä sähköpostia</a>
  </div>
</main>
```

## Mitä välttää

- **Kaksisaraketta** (Sketch B) vähäiselle sisällölle — täyttää tilaa tarpeettomasti
- **Kirjemuotoa** (Sketch C) jos halutaan neutraalimpi tyyli — sopii vain persoonalliseen talli-brändiin
- **Sähköpostin piilottaminen** linkin taakse ilman näkyvää osoitetta — tärkein tieto pitää näkyä suoraan

## Alkuperäinen sketch
- 004-yhteystiedot — sources/004-yhteystiedot/
