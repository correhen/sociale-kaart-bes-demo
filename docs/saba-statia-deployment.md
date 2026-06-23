# Saba en Statia publiceren

## Voorbereiding

1. Review de negen aangeleverde organisatieprofielen in admin.
2. Controleer dat `config/database.php` alleen op de server staat en niet in Git.
3. Importeer `database/saba_statia_import.sql` in de bestaande database.
4. Importeer daarna `database/saba_statia_direct_help_patch.sql` voor Guana Chat 918 en KPCN/911 op beide eilanden.
5. Controleer in admin met het eilandfilter:
   - Statia: 6 organisaties (4 profielen + Guana Chat 918 + KPCN/911);
   - Saba: 7 organisaties (5 profielen + Guana Chat 918 + KPCN/911).

## Routes

De frontend bepaalt het eiland uit `window.ISLAND_CONTEXT` en geeft dit door aan `api/seeddata.php?island=...`.

- `/statia/`
- `/statia/jongeren/`
- `/statia/professionals/`
- `/statia/jongeren/organisaties/`
- `/statia/professionals/organisaties/`
- `/saba/`
- `/saba/jongeren/`
- `/saba/professionals/`
- `/saba/jongeren/organisaties/`
- `/saba/professionals/organisaties/`

Organisatiekaarten gebruiken `detail.html?slug=...`. Voor de expliciet gevraagde publieke detail-URL's zijn daarnaast nette route-aliases toegevoegd, zoals:

- `/saba/jongeren/organisaties/saba-lions-foundation/`;
- `/saba/jongeren/organisaties/child-focus-foundation-saba/`;
- `/statia/jongeren/organisaties/a-plus-academics-statia/`.

## Publiceren via main en Plesk

1. Laat de branch `saba-statia-content` reviewen.
2. Merge de goedgekeurde branch naar `main`.
3. Push `main` naar de remote repository.
4. Start in Plesk de bestaande Git-deploy voor `main`.
5. Importeer de idempotente Saba/Statia-SQL en Direct Help-patch als dat nog niet is gebeurd.
6. Open de routes hierboven en controleer branding, filters, details en Engelse fallback.

De import verwijdert geen records en raakt geen bestaande Bonaire-slugs.
