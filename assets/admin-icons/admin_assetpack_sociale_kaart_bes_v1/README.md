# Admin assetpack — Sociale Kaart BES / Kadena Hubenil / YouthCare Compass

Dit pakket bevat echte losse assets voor de adminomgeving.

## Waar plaatsen?

Upload of kopieer de inhoud van deze map naar:

```text
assets/admin-icons/
```

Dan krijg je bijvoorbeeld:

```text
assets/admin-icons/icons/status/review-needed.svg
assets/admin-icons/icons/actions/edit.svg
assets/admin-icons/flags/svg/flag-pap.svg
assets/admin-icons/css/admin-icons.css
```

## Iconen

Alle gewone iconen zijn:
- losse SVG-bestanden;
- `24x24 viewBox`;
- stroke-based;
- `2px` lijn;
- `stroke="currentColor"`;
- zonder externe dependencies;
- zonder fontbestanden.

Daardoor kunnen ze in CSS gekleurd worden wanneer ze inline als SVG worden gebruikt.

## Vlaggen

De taalvlaggen staan in `flags/svg/` en `flags/png/`.

Belangrijk:
- `flag-pap.svg` en `flag-pap.png` gebruiken bewust de vlag van Bonaire.
- `flag-bonaire.svg` en `flag-bonaire.png` zijn aliassen/kopieën van dezelfde Bonaire-vlag.
- PNG's zijn aanwezig op 128px en 32px.

## Voorbeeld gebruik in PHP/HTML

Als gewone afbeelding:

```php
<img class="admin-icon admin-icon--sm" src="/assets/admin-icons/icons/actions/edit.svg" alt="" aria-hidden="true">
```

Voor taalvlag:

```php
<img class="admin-flag" src="/assets/admin-icons/flags/png/flag-pap.png" alt="Papiamentu">
```

Voor dynamische kleur met `currentColor`: inline de SVG in de HTML of gebruik een CSS-mask. Bij `<img>` kan `currentColor` niet via de parent worden aangepast.

## Preview

Open lokaal:

```text
preview/icon-index.html
```

Daarin staan alle iconen per categorie gegroepeerd.