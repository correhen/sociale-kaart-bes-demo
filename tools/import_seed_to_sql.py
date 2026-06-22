#!/usr/bin/env python3
"""Generate a MySQL import file from the current Kadena seed JSON.

The script treats the JSON as source material. It does not rewrite,
summarize, translate, or otherwise change organization answers.
"""

from __future__ import annotations

import argparse
import json
from pathlib import Path
from typing import Any, Iterable


ROOT = Path(__file__).resolve().parents[1]
DEFAULT_SEED = ROOT / "data" / "kadena_hubentut_seeddata_bonaire_v0_1.json"
LANGUAGES = ("nl", "pap", "en", "es")
YOUTH_FIELDS = (
    "who_we_are",
    "who_for",
    "what_help",
    "how_to_access",
    "how_we_help",
    "duration",
    "partners",
    "contact",
)
DEFAULT_PROFESSIONAL_GROUPS = {
    "general": (
        "organisation_name",
        "short_description",
        "target_group",
        "support_offer",
        "services",
        "methods",
        "execution",
        "problems",
        "trajectory",
        "average_duration",
    ),
    "referral": (
        "when_appropriate",
        "criteria",
        "indications_required",
    ),
    "practical": (
        "contact_details",
        "opening_hours",
        "waiting_times",
    ),
    "additional": (
        "partners",
        "other_information",
    ),
}


def read_json(path: Path) -> dict[str, Any]:
    with path.open("r", encoding="utf-8") as handle:
        return json.load(handle)


def sql_string(value: Any) -> str:
    if value is None:
        return "NULL"
    text = str(value)
    return "'" + text.replace("\\", "\\\\").replace("'", "''").replace("\0", "") + "'"


def sql_json(value: Any) -> str:
    if value in (None, {}, []):
        return "NULL"
    return sql_string(json.dumps(value, ensure_ascii=False, separators=(",", ":")))


def sql_int(value: Any, default: int = 0) -> str:
    try:
        return str(int(value))
    except (TypeError, ValueError):
        return str(default)


def sql_bool(value: Any) -> str:
    return "1" if bool(value) else "0"


def insert(table: str, columns: Iterable[str], values: Iterable[str]) -> str:
    return f"INSERT INTO {table} ({', '.join(columns)}) VALUES ({', '.join(values)});"


def upsert(table: str, columns: Iterable[str], values: Iterable[str], updates: Iterable[str]) -> str:
    return (
        f"INSERT INTO {table} ({', '.join(columns)}) VALUES ({', '.join(values)}) "
        f"ON DUPLICATE KEY UPDATE {', '.join(updates)};"
    )


def org_id_subquery(external_key: str) -> str:
    return f"(SELECT id FROM organizations WHERE external_key = {sql_string(external_key)})"


def theme_id_subquery(external_key: str) -> str:
    return f"(SELECT id FROM themes WHERE external_key = {sql_string(external_key)})"


def island_id_subquery(code: str) -> str:
    return f"(SELECT id FROM islands WHERE code = {sql_string(code)})"


def audience_id_subquery(code: str) -> str:
    return f"(SELECT id FROM audiences WHERE code = {sql_string(code)})"


def map_publication_status(status: str) -> str:
    if status == "active":
        return "published"
    if status in {"demo_extra_archived", "archived"}:
        return "archived"
    if status in {"draft", "published", "needs_review"}:
        return status
    return "needs_review"


def map_source_status(org: dict[str, Any]) -> str:
    raw = str(org.get("source_status") or "").strip()
    if raw == "supplied_by_organisation":
        return "submitted"
    if raw in {"demo", "submitted", "verified", "needs_check", "expired"}:
        return raw
    if org.get("demo_status"):
        return "demo"
    return "needs_check"


def map_translation_status(raw: Any, text_values: Iterable[Any], language: str) -> str:
    values = [str(value or "") for value in text_values]
    has_text = any(value != "" for value in values)
    raw_text = str(raw or "").strip()
    if language == "nl":
        return "published" if has_text else "missing"
    if raw_text in {"published", "reviewed", "draft", "missing"}:
        return raw_text
    if raw_text in {"reviewed_by_papiamentu_speakers", "reviewed_by_human", "checked"}:
        return "reviewed"
    if raw_text in {"ai_draft", "needs_papiamentu_review", "needs_review"}:
        return "draft" if has_text else "missing"
    return "draft" if has_text else "missing"


def translated_text(value: Any, language: str) -> str:
    if isinstance(value, dict):
        item = value.get(language, "")
        if isinstance(item, dict):
            return str(item.get("text") or item.get("value") or "")
        return str(item or "")
    if language == "nl" and value is not None:
        return str(value)
    return ""


def profile_value(profile: dict[str, Any], field: str, language: str) -> str:
    return translated_text(profile.get(field, ""), language)


def seed_reference_tables(lines: list[str]) -> None:
    islands = (
        ("bonaire", "Bonaire", 1),
        ("statia", "Sint Eustatius", 2),
        ("saba", "Saba", 3),
    )
    for code, name, sort_order in islands:
        lines.append(
            upsert(
                "islands",
                ("code", "name", "status", "sort_order"),
                (sql_string(code), sql_string(name), "'published'", str(sort_order)),
                ("name = VALUES(name)", "status = VALUES(status)", "sort_order = VALUES(sort_order)"),
            )
        )

    languages = (
        ("nl", "Nederlands", 1, 1, 1),
        ("pap", "Papiamentu", 0, 1, 2),
        ("en", "English", 0, 1, 3),
        ("es", "Español", 0, 1, 4),
    )
    for code, name, is_source, is_public, sort_order in languages:
        lines.append(
            upsert(
                "languages",
                ("code", "name", "is_source", "is_public", "sort_order"),
                (sql_string(code), sql_string(name), str(is_source), str(is_public), str(sort_order)),
                (
                    "name = VALUES(name)",
                    "is_source = VALUES(is_source)",
                    "is_public = VALUES(is_public)",
                    "sort_order = VALUES(sort_order)",
                ),
            )
        )

    audiences = (
        ("youth", "Jongere", 1),
        ("parents", "Ouder/verzorger", 2),
        ("professional", "Professional", 3),
    )
    for code, label, sort_order in audiences:
        lines.append(
            upsert(
                "audiences",
                ("code", "label_nl", "sort_order"),
                (sql_string(code), sql_string(label), str(sort_order)),
                ("label_nl = VALUES(label_nl)", "sort_order = VALUES(sort_order)"),
            )
        )

    roles = (
        ("admin", "Admin", "Kan gebruikers beheren, content wijzigen en publiceren."),
        ("editor", "Redacteur", "Kan content wijzigen en publiceren."),
        ("translator", "Vertaler", "Kan vertalingen bewerken en status voorstellen."),
        ("viewer", "Lezer", "Kan admincontent bekijken zonder te wijzigen."),
    )
    for code, name, description in roles:
        lines.append(
            upsert(
                "roles",
                ("code", "name", "description"),
                (sql_string(code), sql_string(name), sql_string(description)),
                ("name = VALUES(name)", "description = VALUES(description)"),
            )
        )


def theme_translation_values(theme: dict[str, Any], language: str) -> tuple[str, str, str]:
    data = theme.get("translations", {}).get(language, {}) or {}
    name = str(data.get("name") or "")
    short = str(data.get("short") or "")
    status = map_translation_status(data.get("translation_status"), (name, short), language)
    return name, short, status


def seed_themes(seed: dict[str, Any], lines: list[str]) -> None:
    for theme in seed.get("themes", []):
        external_key = str(theme.get("id") or theme.get("slug") or "")
        lines.append(
            upsert(
                "themes",
                ("external_key", "slug", "color", "status", "sort_order"),
                (
                    sql_string(external_key),
                    sql_string(theme.get("slug", "")),
                    sql_string(theme.get("color") or ""),
                    "'published'",
                    sql_int(theme.get("order")),
                ),
                (
                    "slug = VALUES(slug)",
                    "color = VALUES(color)",
                    "status = VALUES(status)",
                    "sort_order = VALUES(sort_order)",
                ),
            )
        )
        for language in LANGUAGES:
            name, short, status = theme_translation_values(theme, language)
            lines.append(
                upsert(
                    "theme_translations",
                    ("theme_id", "language_code", "name", "short", "translation_status"),
                    (
                        theme_id_subquery(external_key),
                        sql_string(language),
                        sql_string(name),
                        sql_string(short),
                        sql_string(status),
                    ),
                    (
                        "name = VALUES(name)",
                        "short = VALUES(short)",
                        "translation_status = VALUES(translation_status)",
                    ),
                )
            )


def seed_theme_translation_overrides(seed: dict[str, Any], lines: list[str]) -> None:
    for override in seed.get("theme_translation_overrides", []):
        external_key = str(override.get("theme_id") or "")
        if not external_key:
            continue
        for language, data in (override.get("translations") or {}).items():
            if language not in LANGUAGES or not isinstance(data, dict):
                continue
            name = str(data.get("name") or "")
            short = str(data.get("short") or "")
            status = map_translation_status(data.get("translation_status"), (name, short), language)
            lines.append(
                upsert(
                    "theme_translations",
                    ("theme_id", "language_code", "name", "short", "translation_status"),
                    (
                        theme_id_subquery(external_key),
                        sql_string(language),
                        sql_string(name),
                        sql_string(short),
                        sql_string(status),
                    ),
                    (
                        "name = VALUES(name)",
                        "short = VALUES(short)",
                        "translation_status = VALUES(translation_status)",
                    ),
                )
            )


def organization_translation_fields(org: dict[str, Any], language: str) -> tuple[list[str], str]:
    data = org.get("translations", {}).get(language, {}) or {}
    values = [
        str(data.get("name") or ""),
        str(data.get("youth_title") or ""),
        str(data.get("youth_short") or ""),
        str(data.get("youth_where_can_you_go") or ""),
        str(data.get("youth_how_it_works") or ""),
        str(data.get("professional_summary") or ""),
        str(data.get("professional_referral_or_access") or ""),
        str(data.get("professional_notes") or ""),
        str(data.get("type") or ""),
        str(data.get("age_range") or ""),
    ]
    status = map_translation_status(data.get("translation_status"), values, language)
    return values, status


def seed_organization_base(org: dict[str, Any], lines: list[str]) -> None:
    external_key = str(org.get("id") or org.get("slug") or "")
    publication_status = map_publication_status(str(org.get("status") or ""))
    visibility_public = org.get("visibility", {}).get("default") is not False and publication_status == "published"
    legacy_sections = {
        "youth_sections": org.get("youth_sections", []),
        "professional_sections": org.get("professional_sections", []),
    }
    lines.append(
        upsert(
            "organizations",
            (
                "external_key",
                "slug",
                "status",
                "source_status",
                "entity_type",
                "visibility_public",
                "source_locked",
                "review_flags_json",
                "source_format_json",
                "legacy_sections_json",
                "last_checked_at",
                "published_at",
            ),
            (
                sql_string(external_key),
                sql_string(org.get("slug", "")),
                sql_string(publication_status),
                sql_string(map_source_status(org)),
                sql_string(org.get("entity_type") or org.get("type") or "organisation"),
                sql_bool(visibility_public),
                "1",
                sql_json(org.get("review_flags")),
                sql_json(org.get("source_format")),
                sql_json(legacy_sections),
                sql_string(org.get("last_checked_at")) if org.get("last_checked_at") else "NULL",
                "CURRENT_TIMESTAMP" if publication_status == "published" else "NULL",
            ),
            (
                "slug = VALUES(slug)",
                "status = VALUES(status)",
                "source_status = VALUES(source_status)",
                "entity_type = VALUES(entity_type)",
                "visibility_public = VALUES(visibility_public)",
                "source_locked = VALUES(source_locked)",
                "review_flags_json = VALUES(review_flags_json)",
                "source_format_json = VALUES(source_format_json)",
                "legacy_sections_json = VALUES(legacy_sections_json)",
                "last_checked_at = VALUES(last_checked_at)",
                "published_at = COALESCE(organizations.published_at, VALUES(published_at))",
            ),
        )
    )


def seed_organization_relations(org: dict[str, Any], lines: list[str]) -> None:
    external_key = str(org.get("id") or org.get("slug") or "")
    islands = org.get("islands")
    if not isinstance(islands, list) or not islands:
        islands = [org.get("island") or "bonaire"]
    for index, island in enumerate(dict.fromkeys(str(item) for item in islands if item)):
        lines.append(
            upsert(
                "organization_islands",
                ("organization_id", "island_id", "is_primary"),
                (org_id_subquery(external_key), island_id_subquery(island), "1" if index == 0 else "0"),
                ("is_primary = VALUES(is_primary)",),
            )
        )

    primary_themes = [str(item) for item in org.get("primary_theme_ids", [])]
    secondary_themes = [str(item) for item in org.get("secondary_theme_ids", [])]
    seen_themes: set[str] = set()
    for sort_order, theme in enumerate(primary_themes + secondary_themes, start=1):
        if not theme or theme in seen_themes:
            continue
        seen_themes.add(theme)
        lines.append(
            upsert(
                "organization_theme",
                ("organization_id", "theme_id", "is_primary", "sort_order"),
                (
                    org_id_subquery(external_key),
                    theme_id_subquery(theme),
                    "1" if theme in primary_themes else "0",
                    str(sort_order),
                ),
                ("is_primary = VALUES(is_primary)", "sort_order = VALUES(sort_order)"),
            )
        )

    for audience in dict.fromkeys(str(item) for item in org.get("target_audiences", []) if item):
        lines.append(
            upsert(
                "organization_audience",
                ("organization_id", "audience_id"),
                (org_id_subquery(external_key), audience_id_subquery(audience)),
                ("organization_id = VALUES(organization_id)",),
            )
        )


def seed_organization_translations(org: dict[str, Any], lines: list[str]) -> None:
    external_key = str(org.get("id") or org.get("slug") or "")
    columns = (
        "organization_id",
        "language_code",
        "name",
        "youth_title",
        "youth_short",
        "youth_where_can_you_go",
        "youth_how_it_works",
        "professional_summary",
        "professional_referral_or_access",
        "professional_notes",
        "type_label",
        "age_range",
        "translation_status",
    )
    for language in LANGUAGES:
        values, status = organization_translation_fields(org, language)
        lines.append(
            upsert(
                "organization_translations",
                columns,
                (
                    org_id_subquery(external_key),
                    sql_string(language),
                    *(sql_string(value) for value in values),
                    sql_string(status),
                ),
                tuple(f"{column} = VALUES({column})" for column in columns[2:]),
            )
        )


def seed_contact(org: dict[str, Any], lines: list[str]) -> None:
    external_key = str(org.get("id") or org.get("slug") or "")
    contact = org.get("contact") or {}
    address = contact.get("address") if isinstance(contact.get("address"), dict) else {}
    lines.append(
        upsert(
            "organization_contacts",
            ("organization_id", "phone", "whatsapp", "email", "website", "address_nl", "contact_json"),
            (
                org_id_subquery(external_key),
                sql_string(contact.get("phone") or ""),
                sql_string(contact.get("phone_whatsapp") or contact.get("whatsapp") or ""),
                sql_string(contact.get("email") or ""),
                sql_string(contact.get("website") or ""),
                sql_string(address.get("nl") or ""),
                sql_json(contact),
            ),
            (
                "phone = VALUES(phone)",
                "whatsapp = VALUES(whatsapp)",
                "email = VALUES(email)",
                "website = VALUES(website)",
                "address_nl = VALUES(address_nl)",
                "contact_json = VALUES(contact_json)",
            ),
        )
    )


def seed_keywords(org: dict[str, Any], lines: list[str]) -> None:
    external_key = str(org.get("id") or org.get("slug") or "")
    for keyword in dict.fromkeys(str(item) for item in org.get("search_keywords_nl", []) if item):
        lines.append(
            upsert(
                "organization_keywords",
                ("organization_id", "language_code", "keyword"),
                (org_id_subquery(external_key), "'nl'", sql_string(keyword)),
                ("keyword = VALUES(keyword)",),
            )
        )


def profile_groups(seed: dict[str, Any]) -> dict[str, tuple[str, ...]]:
    groups = seed.get("content_format", {}).get("professional_profile_groups")
    if not isinstance(groups, dict):
        return DEFAULT_PROFESSIONAL_GROUPS
    return {str(key): tuple(str(item) for item in value) for key, value in groups.items()}


def seed_profile_answers(org: dict[str, Any], seed: dict[str, Any], lines: list[str]) -> int:
    external_key = str(org.get("id") or org.get("slug") or "")
    inserted = 0
    youth_profile = org.get("youth_profile") or {}
    for sort_order, field in enumerate(YOUTH_FIELDS, start=1):
        for language in LANGUAGES:
            answer = profile_value(youth_profile, field, language)
            status = map_translation_status(None, (answer,), language)
            lines.append(
                profile_answer_insert(
                    external_key,
                    "youth",
                    "",
                    field,
                    language,
                    answer,
                    status,
                    sort_order,
                )
            )
            inserted += 1

    professional_profile = org.get("professional_profile") or {}
    sort_order = 0
    for group, fields in profile_groups(seed).items():
        group_values = professional_profile.get(group) or {}
        for field in fields:
            sort_order += 1
            for language in LANGUAGES:
                answer = profile_value(group_values, field, language)
                status = map_translation_status(None, (answer,), language)
                lines.append(
                    profile_answer_insert(
                        external_key,
                        "professional",
                        group,
                        field,
                        language,
                        answer,
                        status,
                        sort_order,
                    )
                )
                inserted += 1
    return inserted


def profile_answer_insert(
    external_key: str,
    audience_code: str,
    group_key: str,
    field_key: str,
    language: str,
    answer: str,
    status: str,
    sort_order: int,
) -> str:
    columns = (
        "organization_id",
        "audience_code",
        "group_key",
        "field_key",
        "language_code",
        "answer_text",
        "answer_format",
        "translation_status",
        "source_locked",
        "sort_order",
    )
    return upsert(
        "organization_profile_answers",
        columns,
        (
            org_id_subquery(external_key),
            sql_string(audience_code),
            sql_string(group_key),
            sql_string(field_key),
            sql_string(language),
            sql_string(answer),
            "'plain_text'",
            sql_string(status),
            "1",
            str(sort_order),
        ),
        (
            "answer_text = VALUES(answer_text)",
            "answer_format = VALUES(answer_format)",
            "translation_status = VALUES(translation_status)",
            "source_locked = VALUES(source_locked)",
            "sort_order = VALUES(sort_order)",
        ),
    )


def build_sql(seed: dict[str, Any], source_name: str = "seed JSON") -> tuple[str, dict[str, int]]:
    lines: list[str] = [
        f"-- Generated from {source_name}",
        "-- Text is copied as-is. No translations or rewrites are generated.",
        "SET NAMES utf8mb4;",
        "SET FOREIGN_KEY_CHECKS = 1;",
        "START TRANSACTION;",
    ]
    seed_reference_tables(lines)
    seed_themes(seed, lines)
    seed_theme_translation_overrides(seed, lines)

    profile_answer_count = 0
    for org in seed.get("organizations", []):
        seed_organization_base(org, lines)
    for org in seed.get("organizations", []):
        seed_organization_relations(org, lines)
        seed_organization_translations(org, lines)
        seed_contact(org, lines)
        seed_keywords(org, lines)
        profile_answer_count += seed_profile_answers(org, seed, lines)

    lines.append("COMMIT;")
    lines.append("")
    organizations = seed.get("organizations", [])
    stats = {
        "organizations": len(organizations),
        "themes": len(seed.get("themes", [])),
        "active": sum(1 for org in organizations if org.get("status") == "active"),
        "archived": sum(1 for org in organizations if map_publication_status(str(org.get("status") or "")) == "archived"),
        "profile_answers": profile_answer_count,
        "non_ascii_characters": sum(1 for line in lines for ch in line if ord(ch) > 127),
    }
    return "\n".join(lines), stats


def print_report(stats: dict[str, int], output: Path | None) -> None:
    print("Seed import report")
    print(f"- organizations: {stats['organizations']}")
    print(f"- themes: {stats['themes']}")
    print(f"- active source organizations: {stats['active']}")
    print(f"- archived organizations: {stats['archived']}")
    print(f"- profile answer rows: {stats['profile_answers']}")
    print(f"- non-ASCII characters preserved in SQL text: {stats['non_ascii_characters']}")
    if output:
        print(f"- SQL written to: {output}")
    else:
        print("- dry-run only: no SQL file written")


def main() -> int:
    parser = argparse.ArgumentParser(description="Generate SQL import from Kadena seed JSON.")
    parser.add_argument("--seed", type=Path, default=DEFAULT_SEED, help="Path to seed JSON.")
    parser.add_argument("--output", type=Path, help="Write generated SQL to this file.")
    parser.add_argument("--dry-run", action="store_true", help="Generate and report without writing SQL.")
    args = parser.parse_args()

    seed = read_json(args.seed)
    sql, stats = build_sql(seed, str(args.seed))

    encoded = sql.encode("utf-8")
    encoded.decode("utf-8")

    if args.output and not args.dry_run:
        args.output.parent.mkdir(parents=True, exist_ok=True)
        args.output.write_text(sql, encoding="utf-8", newline="\n")
        print_report(stats, args.output)
    else:
        print_report(stats, None)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
