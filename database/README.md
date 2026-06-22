# Database Foundation

Deze map bevat de databasebasis voor een toekomstige PHP/MySQL- of MariaDB-versie van Kadena Hubenil / Sociale Kaart BES. De bestaande statische demo blijft voorlopig los bestaan en wordt niet door dit schema aangepast.

## Doel

Het schema in `schema.sql` legt de beheersbare kern vast voor MVP 1:

- organisaties;
- eilanden;
- thema's;
- doelgroepen;
- contactgegevens;
- jongerenprofielen;
- professionalprofielen;
- vertaalstatussen;
- feedback en aanmeldingen;
- gebruikers, rollen en auditlog.

Activiteiten en los hulpaanbod zitten bewust nog niet in MVP 1. Die kunnen later als eigen tabellen worden toegevoegd en aan organisaties, eilanden, thema's en doelgroepen gekoppeld worden.

## Eilanden

De oplossing is vanaf het begin BES-breed voorbereid.

- `islands` bevat `bonaire`, `statia` en `saba`.
- `organization_islands` koppelt organisaties aan een of meer eilanden.
- Bonaire is de eerste MVP, maar het model ondersteunt direct organisaties die op meerdere eilanden actief zijn.
- `organization_islands.is_primary` geeft aan welk eiland als hoofdcontext gebruikt wordt voor sortering of redactionele workflow.

Gebruik dus niet een enkel eilandveld op `organizations` als definitieve bron. De koppeltabel is leidend.

## Talen En Fallback

De vaste talen staan in `languages`:

- `nl`: Nederlands, brontekst en fallback;
- `pap`: Papiamentu;
- `en`: Engels;
- `es`: Spaans.

Publieke rendering probeert altijd eerst de gekozen taal. Voor Bonaire is Nederlands de fallback. In de eilandcontexten Saba en Statia valt de frontend terug op Engels wanneer het Nederlandse veld leeg is, omdat de aangeleverde broncontent voor deze eilanden Engelstalig is.

Alle tabellen gebruiken `utf8mb4` met `utf8mb4_unicode_ci`, zodat Papiamentu, Spaanse tekens en andere Unicode-inhoud behouden blijven.

## Publicatie En Bronstatus

Contentpublicatie gebruikt:

- `draft`: intern concept;
- `published`: publiek zichtbaar;
- `needs_review`: redactioneel te controleren;
- `archived`: niet publiek, wel historisch bewaard.

Bronstatus gebruikt:

- `demo`: demo- of placeholderinformatie;
- `submitted`: aangeleverd door organisatie of via datacollectie;
- `verified`: gecontroleerd door redactie/beheer;
- `needs_check`: moet gecontroleerd worden;
- `expired`: mogelijk verouderd.

Alleen centrale admins/editors mogen in MVP 1 publiceren. Organisatieaccounts of magic links komen later.

## Vertaalstatus

Vertaalvelden gebruiken:

- `missing`: ontbreekt of leeg;
- `draft`: conceptvertaling;
- `reviewed`: gecontroleerd, maar nog niet noodzakelijk publiek;
- `published`: mag publiek gebruikt worden.

Nederlands is de bron en krijgt bij gevulde velden normaal `published`. Voor andere talen mag de publieke site alleen `published` tonen, tenzij expliciet anders besloten wordt. Ongepubliceerde vertalingen vallen terug op Nederlands.

## Profielen

Jongeren- en professionalprofielen worden opgeslagen in `organization_profile_answers`.

Jongerenprofiel:

- `audience_code = youth`;
- `group_key` blijft leeg;
- `field_key` bevat vaste velden zoals `who_we_are`, `what_help`, `how_to_access`.

Professionalprofiel:

- `audience_code = professional`;
- `group_key` bevat `general`, `referral`, `practical` of `additional`;
- `field_key` bevat de vaste vraagvelden binnen die groep.

Elke antwoordregel heeft een taal, vertaalstatus en `source_locked`. Aangeleverde organisatie-antwoorden moeten exact bewaard worden en mogen niet automatisch herschreven of vertaald worden.

## Later Belangrijk

Deze tabellen zijn in MVP 1 al aanwezig, maar worden vooral later belangrijk:

- `submissions`: feedback en organisatie-aanmeldingen verwerken;
- `users`, `roles`, `user_roles`: adminrollen en login;
- `audit_log`: wijzigingen terugvinden;
- `organization_keywords`: zoekkwaliteit verbeteren;
- JSON-kolommen op organisaties en contact: tijdelijke opvang voor legacy- of detailinformatie die nog niet in vaste velden past.

Voor fase 2 kunnen tabellen voor activiteiten en hulpaanbod worden toegevoegd. Die moeten dezelfde patronen volgen: eilanden via koppeltabellen, vertalingen met status, publicatiestatus, en geen automatische inhoudelijke herschrijving.

## Seed Import Genereren

Het importscripttje leest de huidige statische brondata uit `data/kadena_hubentut_seeddata_bonaire_v0_1.json` en schrijft een SQL-importbestand zonder teksten te corrigeren, herschrijven of vertalen.

Dry-run:

```bash
python tools/import_seed_to_sql.py --dry-run
```

SQL-bestand genereren:

```bash
python tools/import_seed_to_sql.py --output database/seed_import.sql
```

Het gegenereerde bestand gebruikt UTF-8 en behoudt bestaande inhoud exact, inclusief bestaande mojibake in de brondata.

## Saba En Statia Importeren

De aparte, idempotente contentimport staat in:

- `data/youthcare_compass_saba_statia_v0_1.json`;
- `database/saba_statia_import.sql`.

De import bevat uitsluitend de vier Statia- en vijf Saba-organisaties en wijzigt geen bestaande Bonaire-organisaties. Voer hem pas uit nadat `schema.sql` en de algemene `seed_import.sql` aanwezig zijn:

```bash
python tools/import_seed_to_sql.py \
  --seed data/youthcare_compass_saba_statia_v0_1.json \
  --output database/saba_statia_import.sql
```

Importeer daarna `database/saba_statia_import.sql` in dezelfde database. Het bestand gebruikt `INSERT ... ON DUPLICATE KEY UPDATE` en kan veilig opnieuw worden uitgevoerd. Alle negen organisaties krijgen `source_status = submitted`, publicatiestatus `published` en `last_checked_at = 2026-06-22`.

Controle:

```sql
SELECT i.code, COUNT(*) AS organizations
FROM organizations o
JOIN organization_islands oi ON oi.organization_id = o.id
JOIN islands i ON i.id = oi.island_id
WHERE i.code IN ('statia', 'saba')
GROUP BY i.code;
```

Verwacht: `statia = 4`, `saba = 5`.

## Importeren In Plesk/phpMyAdmin

Aanbevolen stappen:

1. Maak in Plesk een nieuwe MySQL/MariaDB database aan.
2. Maak een databasegebruiker aan met rechten op alleen deze database.
3. Gebruik voor de database waar mogelijk charset/collation `utf8mb4` en `utf8mb4_unicode_ci`.
4. Open phpMyAdmin vanuit Plesk.
5. Selecteer de nieuwe database.
6. Importeer eerst `database/schema.sql`.
7. Importeer daarna `database/seed_import.sql`.
8. Controleer de aantallen met SQL queries.

Controlequeries:

```sql
SELECT COUNT(*) AS organizations FROM organizations;
SELECT COUNT(*) AS themes FROM themes;
SELECT COUNT(*) AS active_organizations
FROM organizations
WHERE status = 'published';
SELECT COUNT(*) AS archived_organizations
FROM organizations
WHERE status = 'archived';
SELECT COUNT(*) AS profile_answers
FROM organization_profile_answers;
```

Verwachte aantallen voor de huidige seed:

- 29 organisaties;
- 8 thema's;
- 18 gepubliceerde organisaties uit actieve seedentries;
- 11 gearchiveerde organisaties;
- 3016 profielantwoordrijen.

## Lokale PHP Databaseconfig

Kopieer lokaal:

```bash
cp config/database.example.php config/database.php
```

Vul daarna de lokale of Plesk databasegegevens in `config/database.php` in. Dit bestand staat in `.gitignore` en mag niet worden gecommit.
