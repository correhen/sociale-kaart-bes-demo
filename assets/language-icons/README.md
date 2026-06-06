# Kadena Hubenil language flag icon pack v2

Deze versie gebruikt de door de gebruiker aangeleverde correcte Bonaire-vlag voor PAP.

Taalkeuze met vlagstijl:
- NL = Nederlandse vlag
- EN = Union Jack / UK-vlag
- PAP = Bonaire-vlag uit `svg/source/Flag_of_Bonaire.svg`
- ES = Spaanse vlag, vereenvoudigd zonder wapen voor goede leesbaarheid op klein formaat

Inhoud:
- `svg/source/Flag_of_Bonaire.svg` â€” originele aangeleverde Bonaire-vlag
- `svg/rect/` â€” rechthoekige vlaggen, 4:3
- `svg/rounded/` â€” afgeronde vierkante web-icons
- `svg/circle/` â€” ronde vlag-icons
- `png/rect/512x384/`
- `png/rounded/512/` en `png/rounded/128/`
- `png/circle/512/` en `png/circle/128/`
- `css/language-flags.css`

Aanbevolen projectpad:
`app/static/icons/languages/flags/`

Voorbeeld:
```html
<button class="language-flag is-active" aria-label="Nederlands" aria-current="true">
  <img src="/static/icons/languages/flags/svg/circle/flag-nl.svg" alt="">
</button>

<button class="language-flag" aria-label="Papiamentu">
  <img src="/static/icons/languages/flags/svg/circle/flag-pap.svg" alt="">
</button>
```

Tip:
Gebruik in de frontend naast de vlag ook altijd de taalcode of taalnaam in tekst/aria-label.
