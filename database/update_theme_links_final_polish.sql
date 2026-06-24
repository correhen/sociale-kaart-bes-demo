-- Final theme-link polish for MHC, Stichting Project and Begeleid Wonen Bonaire.
-- Idempotent: inserts missing links and removes only the requested help_support links.

START TRANSACTION;

INSERT INTO organization_theme (organization_id, theme_id, is_primary, sort_order)
SELECT o.id, t.id, 0, 2
FROM organizations o
JOIN themes t ON t.external_key = 'help_support'
WHERE o.external_key IN ('org_mhc', 'org_mhc_saba', 'org_mhc_statia')
ON DUPLICATE KEY UPDATE
  is_primary = VALUES(is_primary),
  sort_order = VALUES(sort_order);

DELETE ot
FROM organization_theme ot
JOIN organizations o ON o.id = ot.organization_id
JOIN themes t ON t.id = ot.theme_id
WHERE o.external_key IN ('org_stichting_project', 'org_begeleid_wonen_bonaire')
  AND t.external_key = 'help_support';

UPDATE organization_theme ot
JOIN organizations o ON o.id = ot.organization_id
JOIN themes t ON t.id = ot.theme_id
SET ot.is_primary = 1,
    ot.sort_order = 1
WHERE o.external_key IN ('org_stichting_project', 'org_begeleid_wonen_bonaire')
  AND t.external_key = 'housing_stay';

COMMIT;
