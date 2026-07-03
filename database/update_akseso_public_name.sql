-- Update public display name for Akseso without changing slug, contact details or themes.
-- Safe to run more than once.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;

START TRANSACTION;

UPDATE organization_translations ot
INNER JOIN organizations o ON o.id = ot.organization_id
SET
  ot.name = 'Sentro Akseso Boneiru',
  ot.youth_title = 'Sentro Akseso Boneiru'
WHERE o.external_key = 'org_akseso'
  AND o.slug = 'akseso'
  AND ot.language_code IN ('nl', 'pap', 'en', 'es');

UPDATE organization_profile_answers opa
INNER JOIN organizations o ON o.id = opa.organization_id
SET opa.answer_text = 'Sentro Akseso Boneiru'
WHERE o.external_key = 'org_akseso'
  AND o.slug = 'akseso'
  AND opa.audience_code = 'professional'
  AND opa.group_key = 'general'
  AND opa.field_key = 'organisation_name'
  AND opa.language_code IN ('nl', 'pap', 'en', 'es')
  AND opa.answer_text IN ('Akseso', 'Akseso / Sentro Akseso Boneiru', 'Sentro Akseso Boneiru');

UPDATE organizations
SET legacy_sections_json = REPLACE(
    CAST(legacy_sections_json AS CHAR CHARACTER SET utf8mb4),
    'Akseso / Sentro Akseso Boneiru',
    'Sentro Akseso Boneiru'
  )
WHERE external_key = 'org_akseso'
  AND slug = 'akseso'
  AND legacy_sections_json IS NOT NULL;

INSERT INTO organization_keywords (organization_id, language_code, keyword)
SELECT o.id, 'nl', required_keywords.keyword
FROM organizations o
JOIN (
  SELECT 'Akseso' AS keyword
  UNION SELECT 'Sentro Akseso Boneiru'
  UNION SELECT 'Akseso / Sentro Akseso Boneiru'
  UNION SELECT 'Loket Aksesibel'
  UNION SELECT 'Guiami'
) AS required_keywords
WHERE o.external_key = 'org_akseso'
  AND o.slug = 'akseso'
ON DUPLICATE KEY UPDATE keyword = VALUES(keyword);

COMMIT;
