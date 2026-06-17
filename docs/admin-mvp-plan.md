# Admin MVP Plan

Dit plan beschrijft de eerste beheerbare PHP/MySQL-versie voor Kadena Hubenil / Sociale Kaart BES. De bestaande publieke statische demo blijft in deze stap ongemoeid.

## Doel Van MVP 1

MVP 1 maakt de kern van de sociale kaart beheerbaar:

- organisaties;
- eilanden;
- thema's;
- doelgroepen;
- contactgegevens;
- jongerenprofiel;
- professionalprofiel;
- vertaalstatussen;
- publicatieflow;
- feedback en aanmeldingen.

De admin-interface mag Nederlands-only zijn. Publieke content ondersteunt wel vanaf het begin `nl`, `pap`, `en` en `es`, met Nederlands als bron en fallback.

## Adminpagina's

Aanbevolen pagina's:

- Dashboard: aantallen organisaties, concepten, review-items, ontbrekende vertalingen, recente feedback.
- Organisaties: lijst met filters op eiland, status, bronstatus, thema, doelgroep en zoekterm.
- Organisatie bewerken: basisgegevens, slug, publicatiestatus, bronstatus, zichtbaarheid.
- Eilanden en doelgroepen: koppelen van organisatie aan Bonaire, Statia en/of Saba en aan doelgroepen.
- Thema's: thema's beheren en vertaalvelden/status per taal vastleggen.
- Contact: telefoon, WhatsApp, e-mail, website, adres en ruwe contactbron.
- Jongerenprofiel: vaste vraagvelden per taal, met Nederlands als bron naast vertaalvelden.
- Professionalprofiel: vaste groepen `general`, `referral`, `practical`, `additional`.
- Vertaaloverzicht: per organisatie zien welke talen ontbreken, concept zijn, reviewed zijn of gepubliceerd zijn.
- Feedback/aanmeldingen: inzendingen bekijken, markeren als verwerkt of spam, eventueel koppelen aan organisatie.
- Gebruikers en rollen: admins, editors, translators en viewers beheren.
- Auditlog: wijzigingen terugzien per gebruiker, entiteit en datum.

## Rollen

MVP 1 gebruikt centrale rollen:

- `admin`: beheert gebruikers, rollen, instellingen en alle content.
- `editor`: beheert content en mag publiceren.
- `translator`: bewerkt vertaalvelden en zet vertaalstatus tot maximaal `reviewed`.
- `viewer`: kan alleen lezen.

Organisatieaccounts, magic links en beperkte self-service komen later en horen niet in MVP 1.

## Publicatieflow

Aanbevolen flow:

1. Nieuwe of geimporteerde organisatie start als `draft`, `needs_review`, `published` of `archived`, afhankelijk van bronstatus/import.
2. Redacteur controleert basisgegevens, eilanden, thema's, doelgroepen en contact.
3. Redacteur controleert jongeren- en professionalprofiel zonder organisatie-antwoorden te herschrijven.
4. Alleen `admin` of `editor` kan status naar `published` zetten.
5. `archived` blijft bewaard voor historie en redirects, maar is niet publiek zichtbaar.

Bronstatus staat los van publicatiestatus. Een organisatie kan bijvoorbeeld `published` zijn met bronstatus `submitted`, zolang de redactie dat acceptabel vindt.

## Vertaalflow

Vertaalstatussen:

- `missing`: geen bruikbare vertaling.
- `draft`: conceptvertaling.
- `reviewed`: gecontroleerd, maar nog niet publiek.
- `published`: publiek inzetbaar.

Nederlands is bron/fallback. De publieke site toont een vertaling alleen wanneer die `published` is. Als een vertaling ontbreekt of niet gepubliceerd is, wordt Nederlands getoond.

De admin moet per veld laten zien:

- Nederlandse brontekst;
- vertaalveld per taal;
- vertaalstatus;
- laatste wijziging;
- waarschuwing wanneer de Nederlandse bron later is gewijzigd dan de vertaling.

Er worden geen vertalingen gegenereerd in MVP 1.

## Feedback En Aanmeldingen

Feedback en aanmeldingen komen binnen in `submissions`.

MVP-functionaliteit:

- lijst van nieuwe inzendingen;
- detailweergave van payload en bericht;
- status aanpassen: `new`, `in_review`, `resolved`, `spam`, `archived`;
- optioneel koppelen aan bestaande organisatie;
- handmatig een nieuwe organisatie aanmaken op basis van een aanmelding.

Automatisch publiceren op basis van een inzending gebeurt niet.

## Bewust Niet In MVP 1

Niet bouwen in de eerste admin-MVP:

- WordPress;
- volledige frontend redesign;
- organisatieaccounts;
- magic links voor organisaties;
- automatische vertalingen;
- herschrijven of samenvatten van organisatie-antwoorden;
- activiteitenbeheer;
- los aanbod/dienstenbeheer;
- geavanceerde workflow met meerdere goedkeuringslagen;
- publieke API;
- analyticsdashboard.

## Aanbevolen Eerste Bouwstap

Start met een kleine PHP 8.2+ applicatie met PDO, MySQL/MariaDB, server-side rendered templates en een eenvoudige router. Bouw eerst login, rollen, organisatieslijst, organisatie-detailbeheer en import vanuit de huidige seeddata. Pas daarna publieke PHP-routes aan op de databaseversie.
