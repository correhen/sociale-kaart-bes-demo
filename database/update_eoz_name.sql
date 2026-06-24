-- Update the visible EOZ organisation name. Slug and routes stay unchanged.
-- Idempotent and intentionally limited to the name/search fields.

START TRANSACTION;

UPDATE organization_translations
SET name = 'Expertise Onderwijs en Zorg (EOZ)'
WHERE organization_id = (SELECT id FROM organizations WHERE external_key = 'org_eoz')
  AND language_code IN ('nl', 'pap', 'en', 'es');

UPDATE organization_profile_answers
SET answer_text = 'Expertise Onderwijs en Zorg (EOZ)'
WHERE organization_id = (SELECT id FROM organizations WHERE external_key = 'org_eoz')
  AND audience_code = 'professional'
  AND group_key = 'general'
  AND field_key = 'organisation_name'
  AND language_code = 'nl';

INSERT INTO organization_keywords (organization_id, language_code, keyword)
SELECT id, 'nl', 'Expertise Onderwijs en Zorg (EOZ)'
FROM organizations
WHERE external_key = 'org_eoz'
ON DUPLICATE KEY UPDATE keyword = VALUES(keyword);

COMMIT;
