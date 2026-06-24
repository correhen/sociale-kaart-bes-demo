-- Put Direct help first in the theme order without re-importing seed data.
-- Idempotent: repeated runs keep the same sort_order values.

START TRANSACTION;

UPDATE themes
SET sort_order = CASE external_key
  WHEN 'direct_help' THEN 1
  WHEN 'help_support' THEN 10
  WHEN 'school_future' THEN 20
  WHEN 'health_wellbeing' THEN 30
  WHEN 'safety_rights' THEN 40
  WHEN 'family_system' THEN 50
  WHEN 'free_time_development' THEN 60
  WHEN 'housing_stay' THEN 70
  ELSE sort_order
END
WHERE external_key IN (
  'direct_help',
  'help_support',
  'school_future',
  'health_wellbeing',
  'safety_rights',
  'family_system',
  'free_time_development',
  'housing_stay'
);

COMMIT;
