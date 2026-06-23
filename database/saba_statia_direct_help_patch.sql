-- Idempotent Direct Help patch for YouthCare Compass Saba and Statia.
-- Adds four island-specific public records. Existing Bonaire records are untouched.

SET NAMES utf8mb4;
START TRANSACTION;

INSERT INTO organizations
  (external_key, slug, status, source_status, entity_type, visibility_public, source_locked, last_checked_at, published_at)
VALUES
  ('org_guana_chat_918_statia', 'guana-chat-918-statia', 'published', 'submitted', 'organisation', 1, 1, '2026-06-22', CURRENT_TIMESTAMP),
  ('org_kpcn_statia', 'kpcn-statia', 'published', 'submitted', 'organisation', 1, 1, '2026-06-22', CURRENT_TIMESTAMP),
  ('org_guana_chat_918_saba', 'guana-chat-918-saba', 'published', 'submitted', 'organisation', 1, 1, '2026-06-22', CURRENT_TIMESTAMP),
  ('org_kpcn_saba', 'kpcn-saba', 'published', 'submitted', 'organisation', 1, 1, '2026-06-22', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE
  slug = VALUES(slug),
  status = VALUES(status),
  source_status = VALUES(source_status),
  visibility_public = VALUES(visibility_public),
  last_checked_at = VALUES(last_checked_at),
  published_at = COALESCE(organizations.published_at, VALUES(published_at));

INSERT INTO organization_islands (organization_id, island_id, is_primary)
SELECT o.id, i.id, 1
FROM organizations o
JOIN islands i ON
  (o.external_key IN ('org_guana_chat_918_statia', 'org_kpcn_statia') AND i.code = 'statia')
  OR (o.external_key IN ('org_guana_chat_918_saba', 'org_kpcn_saba') AND i.code = 'saba')
ON DUPLICATE KEY UPDATE is_primary = VALUES(is_primary);

INSERT INTO organization_theme (organization_id, theme_id, is_primary, sort_order)
SELECT o.id, t.id, 1, 1
FROM organizations o
JOIN themes t ON t.external_key = 'direct_help'
WHERE o.external_key IN (
  'org_guana_chat_918_statia', 'org_kpcn_statia',
  'org_guana_chat_918_saba', 'org_kpcn_saba'
)
ON DUPLICATE KEY UPDATE is_primary = VALUES(is_primary), sort_order = VALUES(sort_order);

INSERT INTO organization_theme (organization_id, theme_id, is_primary, sort_order)
SELECT o.id, t.id, 0, 2
FROM organizations o
JOIN themes t ON t.external_key = 'safety_rights'
WHERE o.external_key IN ('org_kpcn_statia', 'org_kpcn_saba')
ON DUPLICATE KEY UPDATE is_primary = VALUES(is_primary), sort_order = VALUES(sort_order);

INSERT INTO organization_audience (organization_id, audience_id)
SELECT o.id, a.id
FROM organizations o
JOIN audiences a ON a.code IN ('youth', 'professional')
WHERE o.external_key IN (
  'org_guana_chat_918_statia', 'org_kpcn_statia',
  'org_guana_chat_918_saba', 'org_kpcn_saba'
)
ON DUPLICATE KEY UPDATE organization_id = VALUES(organization_id);

INSERT INTO organization_audience (organization_id, audience_id)
SELECT o.id, a.id
FROM organizations o
JOIN audiences a ON a.code = 'parents'
WHERE o.external_key IN ('org_kpcn_statia', 'org_kpcn_saba')
ON DUPLICATE KEY UPDATE organization_id = VALUES(organization_id);

INSERT INTO organization_translations (
  organization_id, language_code, name, youth_title, youth_short,
  youth_where_can_you_go, youth_how_it_works, professional_summary,
  professional_referral_or_access, professional_notes, type_label,
  age_range, translation_status
)
SELECT
  o.id, 'en', 'Guana Chat 918', 'Guana Chat 918',
  'Free and anonymous support for children and young people. Call 918 when you need someone to talk to.',
  'Call about anything that worries you or when you need immediate emotional support.',
  'Call 918 free of charge and anonymously.',
  'Guana Chat 918 is a free and anonymous helpline for children and young people in the Caribbean Netherlands.',
  'No referral is needed. Young people can call 918 directly.',
  '', 'Child and youth helpline', 'Children and young people', 'published'
FROM organizations o
WHERE o.external_key IN ('org_guana_chat_918_statia', 'org_guana_chat_918_saba')
ON DUPLICATE KEY UPDATE
  name = VALUES(name), youth_title = VALUES(youth_title),
  youth_short = VALUES(youth_short), youth_where_can_you_go = VALUES(youth_where_can_you_go),
  youth_how_it_works = VALUES(youth_how_it_works),
  professional_summary = VALUES(professional_summary),
  professional_referral_or_access = VALUES(professional_referral_or_access),
  professional_notes = VALUES(professional_notes), type_label = VALUES(type_label),
  age_range = VALUES(age_range), translation_status = VALUES(translation_status);

INSERT INTO organization_translations (
  organization_id, language_code, name, youth_title, youth_short,
  youth_where_can_you_go, youth_how_it_works, professional_summary,
  professional_referral_or_access, professional_notes, type_label,
  age_range, translation_status
)
SELECT
  o.id, 'en', 'Caribbean Netherlands Police Force (KPCN)', 'Police emergency 911',
  'Call 911 when there is immediate danger or you urgently need police assistance.',
  'Immediate danger, violence, abuse, threats or another police emergency.',
  'Call 911 in an emergency.',
  'KPCN provides police assistance and emergency response in the Caribbean Netherlands.',
  'Call 911 for emergencies.',
  '', 'Police and emergency response', 'Everyone', 'published'
FROM organizations o
WHERE o.external_key IN ('org_kpcn_statia', 'org_kpcn_saba')
ON DUPLICATE KEY UPDATE
  name = VALUES(name), youth_title = VALUES(youth_title),
  youth_short = VALUES(youth_short), youth_where_can_you_go = VALUES(youth_where_can_you_go),
  youth_how_it_works = VALUES(youth_how_it_works),
  professional_summary = VALUES(professional_summary),
  professional_referral_or_access = VALUES(professional_referral_or_access),
  professional_notes = VALUES(professional_notes), type_label = VALUES(type_label),
  age_range = VALUES(age_range), translation_status = VALUES(translation_status);

INSERT INTO organization_contacts (organization_id, phone, website, address_nl, contact_json)
SELECT o.id, '918', 'https://guanachat918.com/', '', JSON_OBJECT(
  'phone', '918',
  'website', 'https://guanachat918.com/',
  'address', JSON_OBJECT('en', 'Available throughout the Caribbean Netherlands')
)
FROM organizations o
WHERE o.external_key IN ('org_guana_chat_918_statia', 'org_guana_chat_918_saba')
ON DUPLICATE KEY UPDATE
  phone = VALUES(phone), website = VALUES(website), contact_json = VALUES(contact_json);

INSERT INTO organization_contacts (organization_id, phone, website, address_nl, contact_json)
SELECT o.id, '911', '', '', JSON_OBJECT(
  'phone', '911',
  'address', JSON_OBJECT(
    'en',
    CASE WHEN o.external_key = 'org_kpcn_statia'
      THEN 'Police services on St. Eustatius'
      ELSE 'Police services on Saba'
    END
  )
)
FROM organizations o
WHERE o.external_key IN ('org_kpcn_statia', 'org_kpcn_saba')
ON DUPLICATE KEY UPDATE
  phone = VALUES(phone), website = VALUES(website), contact_json = VALUES(contact_json);

COMMIT;
