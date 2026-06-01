# Sociale Kaart BES - Demo

> Deze demo bevat voorbeelddata. Organisatienamen, aanbod en activiteiten moeten door de stagiair/opdrachtgever worden gecontroleerd voordat publicatie plaatsvindt.

Statische demo voor bespreking met stagiair/opdrachtgever.

## Openen
Open `index.html` in een browser. Bij sommige browsers werkt `fetch()` lokaal niet door beveiliging. Start dan lokaal een simpele server:

```bash
python -m http.server 8000
```

En ga naar: http://localhost:8000

## Inhoud
- `index.html` - homepage
- `organisaties.html` - filterbare organisatielijst
- `aanbod.html` - filterbaar hulp/aanbod overzicht
- `activiteiten.html` - activiteitenoverzicht
- `data/*.json` - fictieve demo-data
- `assets/styles.css` - styling
- `assets/app.js` - filterlogica

## Doel
Deze demo is bedoeld als voorzet. Niet als definitieve technische keuze. De structuur is later te vertalen naar Laravel, WordPress of een ander CMS/platform.


## Site vullen

De demo gebruikt JSON-bestanden in de map `data/`:

- `organizations.json` voor organisaties
- `services.json` voor hulp/aanbod
- `events.json` voor activiteiten
- `themes.json` voor thema's

Voor een snelle demo kun je deze bestanden handmatig aanpassen. Voor een echte inventarisatie is het slimmer om eerst het Excel-template `datacollectie_template_sociale_kaart_BES.xlsx` te laten invullen en daarna de data om te zetten naar JSON.

Belangrijke codes:

- Eilanden: `bonaire`, `saba`, `statia`
- Doelgroepen: `jongeren`, `ouders`, `volwassenen`, `professionals`
- Thema's staan in `data/themes.json`; gebruik de `slug` als code.

## Slimme zoekbalk

De zoekbalk op organisaties, hulp & aanbod en activiteiten toont nu suggesties zodra je minimaal 2 letters typt. De suggesties komen uit de JSON-data. Klik je op een suggestie, dan wordt de zoekterm ingevuld en filtert de pagina direct.

Let op: open je de bestanden direct via `file://`, dan kan je browser het laden van JSON blokkeren. Start lokaal liever met:

```bash
python -m http.server 8000
```

en open daarna `http://localhost:8000`.
