# Admin Setup

Deze admin-MVP is een eerste read-only beheerschil voor Kadena Hubenil / Sociale Kaart BES. De publieke statische site blijft ongewijzigd en `/admin/` wordt nergens in de publieke navigatie gelinkt.

## Databaseconfig

De admin gebruikt dezelfde databaseconfig als `_dbtest/`:

```text
config/database.php
```

Maak dit bestand door het voorbeeld te kopieren:

```bash
cp config/database.example.php config/database.php
```

Vul daarna host, database, gebruiker en wachtwoord in. `config/database.php` staat in `.gitignore` en mag niet worden gecommit.

## Eerste Admingebruiker

Gebruik het CLI-script alleen lokaal of server-side:

```bash
php tools/create_admin_user.php --name="Admin" --email="admin@example.org"
```

Het script vraagt om een wachtwoord als `--password` niet wordt meegegeven. Het wachtwoord moet minimaal 12 tekens hebben en wordt opgeslagen met `password_hash`; er wordt geen plaintext wachtwoord opgeslagen.

Het script:

- maakt of actualiseert de rollen `admin`, `editor`, `translator` en `viewer`;
- maakt of actualiseert de gebruiker;
- koppelt de gebruiker aan de rol `admin`.

Geef geen echte wachtwoorden op in bestanden die naar Git gaan.

## Admin Openen

Open na database-import en gebruiker-aanmaak:

```text
/admin/
```

Niet-ingelogde bezoekers worden doorgestuurd naar:

```text
/admin/login.php
```

Login is sessie-gebaseerd en gebruikt:

- PHP sessions;
- `password_verify`;
- PDO prepared statements;
- CSRF-token op het loginformulier.

In deze fase mogen alleen gebruikers met rol `admin`, `editor` of `viewer` inloggen. `translator` bestaat al als rol, maar krijgt later toegang wanneer vertaalworkflow wordt gebouwd.

## Beschikbare Pagina's

- `/admin/dashboard.php`: read-only statistieken.
- `/admin/organizations.php`: read-only organisatielijst met filters.
- `/admin/organization.php?id=...`: read-only organisatiedetail.
- `/admin/logout.php`: sessie uitloggen.

De detailpagina toont lege velden bewust als `ontbreekt`, omdat beheer moet kunnen zien wat nog mist. Dit verschilt van de publieke site, waar lege velden verborgen worden.

## Bewust Nog Niet Gebouwd

Deze fase bevat nog niet:

- content-editor;
- publiceren of status wijzigen;
- vertaalbewerking;
- organisatieaccounts;
- magic links;
- registratiepagina;
- feedbackbeheer;
- gebruikersbeheer;
- thema-editor;
- activiteiten of los aanbod;
- koppeling vanuit publieke navigatie.

## Veiligheidsnotities

- Commit nooit `config/database.php`.
- Commit geen echte wachtwoorden of database-secrets.
- Houd `/admin/` achter normale hostingbeveiliging en HTTPS.
- Gebruik het admin-user script niet via de browser.
- Alle adminpagina's behalve `login.php` vereisen een sessie-login.
