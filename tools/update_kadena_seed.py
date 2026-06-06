import json
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
SEED_PATH = ROOT / "data" / "kadena_hubentut_seeddata_bonaire_v0_1.json"


THEMES = [
    ("help_support", "hulp-ondersteuning", 1, "#03A8AF", "Hulp en ondersteuning", "Yudansa i sosten", "Jeugdhulp & ondersteuning", "Kuido i Sosten pa Hóben"),
    ("school_future", "school-toekomst", 2, "#F4B612", "School en toekomst", "Skol i futuro", "Onderwijs & ontwikkeling", "Edukashon i Desaroyo"),
    ("health_wellbeing", "gezondheid-welzijn", 3, "#58AD3F", "Gezondheid en welzijn", "Salú i bienestar", "Gezondheid & welzijn", "Salú i Bienestar"),
    ("safety_rights", "veiligheid-rechten", 4, "#964BA8", "Veiligheid en je rechten", "Seguridat i hustisia", "Veiligheid & justitie", "Seguridat i Hustisia"),
    ("family_system", "familie-omgang", 5, "#FB8304", "Familie en omgang", "Apoyo di famia", "Gezins- en systeemondersteuning", "Sosten di Famia i Sistemátiko"),
    ("free_time_development", "vrije-tijd-ontwikkeling", 6, "#174A91", "Vrije tijd en ontwikkeling", "Tempu liber i hòbi", "Vrije tijdsbesteding & talentenontwikkeling", "Aktividatnan pa desaroyo di Talento"),
    ("housing_stay", "wonen-verblijf", 7, "#5D6F92", "Wonen en verblijf", "Residensia temporal", "Woonbegeleiding & opvangvoorzieningen", "Fasilidatnan di residensia"),
    ("direct_help", "directe-hulp", 8, "#F76B05", "Directe hulp", "Yudansa di emergensia", "Acute hulp & noodsituatie", "Yudansa di emergensia"),
]


def theme_entries():
    rows = []
    for tid, slug, order, color, ynl, ypap, pnl, ppap in THEMES:
        rows.append({
            "id": tid,
            "slug": slug,
            "order": order,
            "color": color,
            "labels": {
                "youth": {"nl": ynl, "pap": ypap},
                "professional": {"nl": pnl, "pap": ppap},
            },
            "translations": {
                "nl": {"name": ynl, "short": pnl},
                "pap": {"name": ypap, "short": ppap, "translation_status": "reviewed_by_papiamentu_speakers"},
                "en": {"name": draft_en(tid, "youth"), "short": draft_en(tid, "professional"), "translation_status": "ai_draft"},
                "es": {"name": draft_es(tid, "youth"), "short": draft_es(tid, "professional"), "translation_status": "ai_draft"},
            },
        })
    return rows


EN = {
    "help_support": ("Help and support", "Youth care and support"),
    "school_future": ("School and future", "Education and development"),
    "health_wellbeing": ("Health and wellbeing", "Health and wellbeing"),
    "safety_rights": ("Safety and your rights", "Safety and justice"),
    "family_system": ("Family and contact", "Family and system support"),
    "free_time_development": ("Free time and development", "Leisure and talent development"),
    "housing_stay": ("Housing and stay", "Supported housing and shelter"),
    "direct_help": ("Direct help", "Urgent help and emergency situations"),
}

ES = {
    "help_support": ("Ayuda y apoyo", "Ayuda juvenil y apoyo"),
    "school_future": ("Escuela y futuro", "Educación y desarrollo"),
    "health_wellbeing": ("Salud y bienestar", "Salud y bienestar"),
    "safety_rights": ("Seguridad y tus derechos", "Seguridad y justicia"),
    "family_system": ("Familia y contacto", "Apoyo familiar y sistémico"),
    "free_time_development": ("Tiempo libre y desarrollo", "Tiempo libre y desarrollo de talentos"),
    "housing_stay": ("Vivienda y alojamiento", "Acompañamiento residencial y acogida"),
    "direct_help": ("Ayuda directa", "Ayuda urgente y emergencias"),
}


def draft_en(tid, audience):
    return EN[tid][0 if audience == "youth" else 1]


def draft_es(tid, audience):
    return ES[tid][0 if audience == "youth" else 1]


def source_format():
    return {
        "general_information": {},
        "service_offer": {},
        "access_and_referral": {},
        "practical_information": {},
        "professional_information": {},
    }


def org(id_, name, slug, themes, youth, pro, type_, phone="", website="", email="", audiences=None, extra=None, archived=False):
    audiences = audiences or ["youth", "parents", "professional"]
    keywords = set([name, slug.replace("-", " ")])
    for token in ["MHC", "EOZ", "FKPD", "KPCN", "ZJCN", "BWB"]:
        if token in name or token in (extra or {}).get("keywords", []):
            keywords.add(token)
    data = {
        "id": id_,
        "name": name,
        "slug": slug,
        "island": "bonaire",
        "status": "demo_extra_archived" if archived else "active",
        "visibility": {"default": not archived},
        "target_audiences": audiences,
        "primary_theme_ids": themes[:2],
        "secondary_theme_ids": themes[2:],
        "search_keywords_nl": sorted(keywords | set((extra or {}).get("keywords", []))),
        "contact": {
            "phone": phone,
            "email": email,
            "website": website,
            "address": {"nl": "Nog aan te vullen"},
        },
        "translations": {
            "nl": {
                "name": name,
                "youth_title": name,
                "youth_short": youth,
                "youth_where_can_you_go": youth,
                "youth_how_it_works": "Neem contact op met de organisatie of vraag een volwassene/professional om mee te kijken.",
                "professional_summary": pro,
                "professional_referral_or_access": "Verwijsinformatie wordt nog met de organisatie gecontroleerd. Gebruik de contactgegevens voor actuele toegang.",
                "professional_notes": "Demo-informatie; inhoud en verwijscriteria moeten voor lancering worden bevestigd.",
                "type": type_,
                "age_range": "Jongeren en gezinnen op Bonaire",
            },
            "pap": {
                "name": name,
                "youth_title": name,
                "youth_short": youth,
                "professional_summary": pro,
                "translation_status": "needs_papiamentu_review",
            },
            "en": {"name": name, "youth_short": youth, "professional_summary": pro, "translation_status": "ai_draft"},
            "es": {"name": name, "youth_short": youth, "professional_summary": pro, "translation_status": "ai_draft"},
        },
        "review_flags": {
            "needs_content_review": True,
            "replace_with_official_format_when_available": True,
        },
        "demo_status": "demo_extra_archived" if archived else "needs_verification",
        "source_format": source_format(),
    }
    if extra:
        if extra.get("contact"):
            data["contact"].update(extra["contact"])
        if extra.get("review_flags"):
            data["review_flags"].update(extra["review_flags"])
        if extra.get("translations"):
            for lang, fields in extra["translations"].items():
                data["translations"].setdefault(lang, {}).update(fields)
        for key in ["service_labels", "opening_hours"]:
            if key in extra:
                data[key] = extra[key]
    return data


ACTIVE = [
    org("org_akseso", "Akseso", "akseso", ["help_support", "family_system"], "Je kunt bij Akseso terecht als je thuis, op school, met je gezondheid of met praktische problemen hulp nodig hebt.", "Akseso is een toegangspunt voor ondersteuning rond jeugd, gezin en praktische hulpvragen op Bonaire.", "Toegangspunt / ondersteuning", website="https://www.akseso.cw/", extra={"keywords": ["Sentro Akseso Boneiru"]}),
    org("org_zjcn", "Zorg en Jeugd Caribisch Nederland (ZJCN)", "zorg-en-jeugd-caribisch-nederland", ["help_support", "family_system", "housing_stay"], "Zorg en Jeugd Caribisch Nederland kan helpen als er meer ondersteuning nodig is voor jou of je gezin.", "Zorg en Jeugd Caribisch Nederland (ZJCN) biedt en organiseert jeugdhulp en gezinsondersteuning in Caribisch Nederland.", "Jeugdhulp / gezinsondersteuning", extra={"keywords": ["Jeugdzorg Caribisch Nederland", "ZJCN", "Begeleid Wonen Bonaire", "BWB"], "service_labels": ["Begeleid Wonen Bonaire (BWB)"]}),
    org("org_eoz", "Expertisecenter Onderwijs Zorg Bonaire (EOZ)", "expertisecenter-onderwijs-zorg-bonaire", ["school_future"], "Als school moeilijk gaat of je extra ondersteuning nodig hebt, kan Expertisecenter Onderwijs Zorg Bonaire (EOZ) samen met school meedenken.", "Expertisecenter Onderwijs Zorg Bonaire (EOZ) ondersteunt leerlingen en studenten die vastlopen in het onderwijs.", "Onderwijszorg", extra={"keywords": ["EOZ"]}),
    org("org_fundashon_forma", "Fundashon Forma", "fundashon-forma", ["school_future"], "Fundashon Forma helpt met leren, vaardigheden en stappen richting opleiding of werk.", "Fundashon Forma biedt onderwijs-, ontwikkel- en trajectbegeleiding voor jongeren en volwassenen.", "Onderwijs / ontwikkeling", extra={"keywords": ["Fundashon FORMA", "SKJ"]}),
    org("org_rebound", "Rebound", "rebound", ["school_future"], "Rebound kan helpen als school tijdelijk niet goed lukt en je ondersteuning nodig hebt om weer verder te komen.", "Rebound biedt tijdelijke onderwijs- en begeleidingsondersteuning voor jongeren die extra structuur nodig hebben.", "Onderwijsondersteuning"),
    org("org_mhc", "Mental Health Caribbean (MHC)", "mental-health-caribbean", ["health_wellbeing"], "Als je veel angst, somberheid, boosheid, stress of andere psychische klachten ervaart, kan Mental Health Caribbean (MHC) helpen.", "Mental Health Caribbean (MHC) biedt geestelijke gezondheidszorg en begeleiding.", "Geestelijke gezondheidszorg", extra={"keywords": ["MHC", "Mental Health Caribbean Kind Jeugd", "Hi-5"]}),
    org("org_ggd_bonaire", "GGD Bonaire", "ggd-bonaire", ["health_wellbeing"], "GGD Bonaire geeft informatie en hulp rond gezondheid, preventie en publieke gezondheid.", "GGD Bonaire werkt aan publieke gezondheid, preventie en gezondheidsvoorlichting.", "Publieke gezondheid", extra={"keywords": ["Salubridat Públiko"]}),
    org("org_fkpd", "Fundashon Kuido pa Personanan Desabilitá (FKPD)", "fundashon-kuido-pa-personanan-desabilita", ["health_wellbeing"], "Fundashon Kuido pa Personanan Desabilitá (FKPD) ondersteunt mensen met een beperking en hun omgeving.", "Fundashon Kuido pa Personanan Desabilitá (FKPD) biedt ondersteuning voor personen met een beperking.", "Ondersteuning bij beperking", extra={"keywords": ["FKPD"]}),
    org("org_kpcn", "Korps Politie Caribisch Nederland (KPCN)", "korps-politie-caribisch-nederland", ["safety_rights", "direct_help"], "Bel de politie als je direct gevaar ziet of hulp nodig hebt bij veiligheid.", "Korps Politie Caribisch Nederland (KPCN) is de politieorganisatie voor veiligheid en noodsituaties.", "Politie / veiligheid", phone="911", extra={"keywords": ["KPCN", "politie"]}),
    org("org_voogdijraad_cn", "Voogdijraad Caribisch Nederland", "voogdijraad-caribisch-nederland", ["safety_rights", "family_system"], "De Voogdijraad komt in beeld bij vragen over bescherming, gezag en veiligheid van kinderen.", "De Voogdijraad Caribisch Nederland voert wettelijke taken uit rond kinderbescherming, gezag en jeugdstrafrecht.", "Kinderbescherming / wettelijke taken"),
    org("org_reclassering_cn", "Reclassering Caribisch Nederland", "reclassering-caribisch-nederland", ["safety_rights"], "Reclassering Caribisch Nederland begeleidt mensen die met justitie te maken hebben.", "Reclassering Caribisch Nederland werkt binnen justitiële trajecten aan begeleiding, toezicht en terugkeer.", "Reclassering / justitie"),
    org("org_jong_bonaire", "Jong Bonaire", "jong-bonaire", ["free_time_development"], "Bij Jong Bonaire kun je terecht voor activiteiten, ontmoeting, talentontwikkeling en begeleiding.", "Jong Bonaire biedt jongerenwerk, activiteiten en ontwikkelingsmogelijkheden.", "Jongerenwerk / activiteiten"),
    org("org_rosa_di_sharon", "Fundashon Rosa di Sharon", "fundashon-rosa-di-sharon", ["housing_stay"], "Fundashon Rosa di Sharon biedt opvang of ondersteuning rond wonen wanneer dat nodig is.", "Fundashon Rosa di Sharon is relevant voor opvang en woonondersteuning.", "Opvang / woonondersteuning"),
    org("org_tabitha", "Tabitha", "tabitha", ["housing_stay"], "Tabitha biedt ondersteuning rond wonen, opvang of begeleiding.", "Tabitha is relevant voor woonbegeleiding en opvangvoorzieningen.", "Woonbegeleiding / opvang"),
    org("org_stichting_project", "Stichting Project", "stichting-project", ["housing_stay"], "Stichting Project kan ondersteuning bieden rond wonen, begeleiding en weer grip krijgen op je situatie.", "Stichting Project biedt begeleiding en ondersteuning rond wonen en praktische hulpvragen.", "Begeleiding / wonen"),
    org("org_guana_chat_918", "Guana Chat 918", "guana-chat-918", ["direct_help"], "Zit je ergens mee? Je kunt gratis en anoniem bellen met Guana Chat via 918. Je mag bellen over alles wat je bezighoudt.", "Guana Chat 918 is een gratis en anonieme hulplijn voor kinderen en jongeren in Caribisch Nederland. Professionals kunnen jongeren wijzen op 918 wanneer zij behoefte hebben aan een laagdrempelige plek om te praten.", "Kindertelefoon / Child and Youth Help Line", phone="918", website="https://guanachat918.com/", audiences=["youth", "professional"], extra={"keywords": ["918", "hulplijn", "kindertelefoon"], "opening_hours": {"value": "14:00 - 18:00", "status": "verify_before_launch"}, "review_flags": {"opening_hours_verify_before_launch": True}, "translations": {"nl": {"youth_how_it_works": "Bel gratis en anoniem via 918. Je kunt laagdrempelig praten over wat je bezighoudt.", "professional_referral_or_access": "Professionals kunnen jongeren wijzen op bellen via 918. Openingstijd 14:00 - 18:00 moet voor lancering worden gecontroleerd."}}}),
]

ARCHIVED = [
    org("org_halt_cn", "Halt Caribisch Nederland", "halt-caribisch-nederland", ["safety_rights"], "Gearchiveerde demo-extra.", "Gearchiveerde demo-extra.", "Demo-extra", archived=True),
    org("org_bureau_slachtofferhulp", "Bureau Slachtofferhulp", "bureau-slachtofferhulp", ["safety_rights"], "Gearchiveerde demo-extra.", "Gearchiveerde demo-extra.", "Demo-extra", archived=True),
    org("org_olb_schoolspullen", "Openbaar Lichaam Bonaire - schoolspullen", "olb-vergoeding-schoolspullen", ["school_future"], "Gearchiveerde demo-extra.", "Gearchiveerde demo-extra.", "Demo-extra", archived=True),
    org("org_stichting_jongeren_toekomst", "Stichting Jongeren en Toekomst Bonaire", "stichting-jongeren-en-toekomst-bonaire", ["school_future"], "Gearchiveerde demo-extra.", "Gearchiveerde demo-extra.", "Demo-extra", archived=True),
    org("org_guiami", "Guiami", "guiami", ["safety_rights", "family_system"], "Gearchiveerde legacy-entry.", "Gearchiveerde legacy-entry.", "Legacy-entry", archived=True),
    org("org_sgb", "Scholengemeenschap Bonaire (SGB)", "scholengemeenschap-bonaire", ["school_future"], "Gearchiveerde legacy-entry.", "Gearchiveerde legacy-entry.", "Legacy-entry", archived=True),
    org("org_jeugdzorg_cn_legacy", "Jeugdzorg Caribisch Nederland", "jeugdzorg-caribisch-nederland", ["help_support"], "Gearchiveerde legacy-entry; gebruik Zorg en Jeugd Caribisch Nederland (ZJCN).", "Gearchiveerde legacy-entry; gebruik Zorg en Jeugd Caribisch Nederland (ZJCN).", "Legacy-entry", archived=True),
    org("org_mhc_kind_jeugd_legacy", "Mental Health Caribbean - Kind & Jeugd", "mental-health-caribbean-kind-jeugd", ["health_wellbeing"], "Gearchiveerde legacy-entry; gebruik Mental Health Caribbean (MHC).", "Gearchiveerde legacy-entry; gebruik Mental Health Caribbean (MHC).", "Legacy-entry", archived=True),
    org("org_fundashon_forma_skj_legacy", "Fundashon Forma / SKJ", "fundashon-forma-skj", ["school_future"], "Gearchiveerde legacy-entry; gebruik Fundashon Forma.", "Gearchiveerde legacy-entry; gebruik Fundashon Forma.", "Legacy-entry", archived=True),
    org("org_ggd_bonaire_legacy", "GGD Bonaire / Salubridat Públiko", "ggd-bonaire-salubridat-publiko", ["health_wellbeing"], "Gearchiveerde legacy-entry; gebruik GGD Bonaire.", "Gearchiveerde legacy-entry; gebruik GGD Bonaire.", "Legacy-entry", archived=True),
    org("org_sentro_akseso_legacy", "Sentro Akseso Boneiru", "sentro-akseso-boneiru", ["help_support"], "Gearchiveerde legacy-entry; gebruik Akseso.", "Gearchiveerde legacy-entry; gebruik Akseso.", "Legacy-entry", archived=True),
]


def detail_html(audience, brand, rel_prefix, title_suffix):
    body_class = "audience-professional" if audience == "professional" else "audience-youth"
    header_class = "site-header kh-header professional-header" if audience == "professional" else "site-header kh-header"
    home = "../../" if audience == "professional" else "../../"
    other = "../../../jongeren/" if audience == "professional" else "../../../professionals/"
    own = "../../" if audience == "professional" else "../../"
    active_text = "Organisatieoverzicht" if audience == "professional" else "Alle organisaties"
    return f"""<!doctype html><html lang=\"nl\"><head><meta charset=\"utf-8\" /><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" /><title>{title_suffix}</title><link rel=\"stylesheet\" href=\"../../../assets/styles.css\" /></head><body class=\"{body_class}\" data-page=\"organization-detail\" data-audience=\"{audience}\"><header class=\"{header_class}\"><a class=\"brand\" href=\"../../../index.html\">{brand} <span>demo</span></a><nav><a href=\"../../../jongeren/\" data-audience-link=\"youth\">Jongere</a><a href=\"../../../professionals/\" data-audience-link=\"professional\">Professional</a><a class=\"active\" href=\"../\">{active_text}</a><a href=\"../../../feedback.html\">Feedback</a></nav><div data-language-slot></div></header><main id=\"organizationDetail\"></main><footer class=\"site-footer\"><strong>{brand}</strong><p>Demo-informatie is voorlopig en wordt nog gecontroleerd.</p></footer><script>window.SEED_BASE='../../../';</script><script src=\"../../../assets/app.js\"></script><script>initOrganizationDetail('{audience}');</script></body></html>"""


def write_detail_pages(orgs):
    for item in orgs:
        slug = item["slug"]
        for audience, base, brand in [
            ("youth", ROOT / "jongeren" / "organisaties" / slug, "Kadena Hubenil"),
            ("professional", ROOT / "professionals" / "organisaties" / slug, "Sociale Kaart Bonaire"),
        ]:
            base.mkdir(parents=True, exist_ok=True)
            suffix = f"{item['name']} - {brand}"
            (base / "index.html").write_text(detail_html(audience, brand, "../../../", suffix), encoding="utf-8")


def main():
    seed = json.loads(SEED_PATH.read_text(encoding="utf-8"))
    seed["metadata"]["project"] = "Kadena Hubenil / Sociale Kaart Bonaire"
    seed["metadata"]["scope_note"] = "Statische MVP voor Bonaire; datastructuur houdt later uitbreiding naar Saba en Statia mogelijk."
    seed["metadata"]["brand"] = {
        "youth": "Kadena Hubenil",
        "professional": "Sociale Kaart Bonaire",
        "logo": "assets/brand/logo-kadena-hubenil.png",
    }
    seed["themes"] = theme_entries()
    seed["organizations"] = ACTIVE + ARCHIVED
    SEED_PATH.write_text(json.dumps(seed, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    write_detail_pages(ACTIVE + ARCHIVED)


if __name__ == "__main__":
    main()
