-- Kadena Hubenil / Sociale Kaart BES
-- Database foundation for a PHP/MySQL or MariaDB admin-managed version.
-- Public static HTML/CSS/JS is intentionally not touched by this schema.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE islands (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(32) NOT NULL,
  name VARCHAR(120) NOT NULL,
  status ENUM('draft', 'published', 'needs_review', 'archived') NOT NULL DEFAULT 'published',
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_islands_code (code),
  KEY idx_islands_status_sort (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE languages (
  code VARCHAR(8) PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  is_source TINYINT(1) NOT NULL DEFAULT 0,
  is_public TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_languages_public_sort (is_public, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audiences (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(32) NOT NULL,
  label_nl VARCHAR(120) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_audiences_code (code),
  KEY idx_audiences_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE themes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  external_key VARCHAR(80) NOT NULL,
  slug VARCHAR(160) NOT NULL,
  color CHAR(7) DEFAULT NULL,
  icon_path VARCHAR(255) DEFAULT NULL,
  status ENUM('draft', 'published', 'needs_review', 'archived') NOT NULL DEFAULT 'published',
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_themes_external_key (external_key),
  UNIQUE KEY uq_themes_slug (slug),
  KEY idx_themes_status_sort (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE theme_translations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  theme_id INT UNSIGNED NOT NULL,
  language_code VARCHAR(8) NOT NULL,
  name VARCHAR(180) NOT NULL DEFAULT '',
  short VARCHAR(255) NOT NULL DEFAULT '',
  translation_status ENUM('missing', 'draft', 'reviewed', 'published') NOT NULL DEFAULT 'missing',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_theme_translations_theme_language (theme_id, language_code),
  KEY idx_theme_translations_language_status (language_code, translation_status),
  CONSTRAINT fk_theme_translations_theme
    FOREIGN KEY (theme_id) REFERENCES themes(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_theme_translations_language
    FOREIGN KEY (language_code) REFERENCES languages(code)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE organizations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  external_key VARCHAR(120) NOT NULL,
  slug VARCHAR(180) NOT NULL,
  status ENUM('draft', 'published', 'needs_review', 'archived') NOT NULL DEFAULT 'draft',
  source_status ENUM('demo', 'submitted', 'verified', 'needs_check', 'expired') NOT NULL DEFAULT 'demo',
  entity_type VARCHAR(80) NOT NULL DEFAULT 'organisation',
  visibility_public TINYINT(1) NOT NULL DEFAULT 0,
  source_locked TINYINT(1) NOT NULL DEFAULT 1,
  review_flags_json JSON DEFAULT NULL,
  source_format_json JSON DEFAULT NULL,
  legacy_sections_json JSON DEFAULT NULL,
  last_checked_at DATE DEFAULT NULL,
  published_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_organizations_external_key (external_key),
  UNIQUE KEY uq_organizations_slug (slug),
  KEY idx_organizations_status_visibility (status, visibility_public),
  KEY idx_organizations_source_status (source_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE organization_islands (
  organization_id INT UNSIGNED NOT NULL,
  island_id INT UNSIGNED NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (organization_id, island_id),
  KEY idx_organization_islands_island (island_id, is_primary),
  CONSTRAINT fk_organization_islands_organization
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_organization_islands_island
    FOREIGN KEY (island_id) REFERENCES islands(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE organization_translations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  organization_id INT UNSIGNED NOT NULL,
  language_code VARCHAR(8) NOT NULL,
  name VARCHAR(255) NOT NULL DEFAULT '',
  youth_title VARCHAR(255) NOT NULL DEFAULT '',
  youth_short TEXT,
  youth_where_can_you_go TEXT,
  youth_how_it_works TEXT,
  professional_summary TEXT,
  professional_referral_or_access TEXT,
  professional_notes TEXT,
  type_label VARCHAR(255) NOT NULL DEFAULT '',
  age_range VARCHAR(160) NOT NULL DEFAULT '',
  translation_status ENUM('missing', 'draft', 'reviewed', 'published') NOT NULL DEFAULT 'missing',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_organization_translations_org_language (organization_id, language_code),
  KEY idx_organization_translations_language_status (language_code, translation_status),
  FULLTEXT KEY ft_organization_translations_search (
    name,
    youth_title,
    youth_short,
    youth_where_can_you_go,
    professional_summary,
    professional_referral_or_access,
    type_label
  ),
  CONSTRAINT fk_organization_translations_organization
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_organization_translations_language
    FOREIGN KEY (language_code) REFERENCES languages(code)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE organization_contacts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  organization_id INT UNSIGNED NOT NULL,
  phone VARCHAR(80) NOT NULL DEFAULT '',
  whatsapp VARCHAR(80) NOT NULL DEFAULT '',
  email VARCHAR(190) NOT NULL DEFAULT '',
  website VARCHAR(255) NOT NULL DEFAULT '',
  address_nl TEXT,
  contact_json JSON DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_organization_contacts_organization (organization_id),
  KEY idx_organization_contacts_email (email),
  CONSTRAINT fk_organization_contacts_organization
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE organization_theme (
  organization_id INT UNSIGNED NOT NULL,
  theme_id INT UNSIGNED NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (organization_id, theme_id),
  KEY idx_organization_theme_theme (theme_id, is_primary, sort_order),
  CONSTRAINT fk_organization_theme_organization
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_organization_theme_theme
    FOREIGN KEY (theme_id) REFERENCES themes(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE organization_audience (
  organization_id INT UNSIGNED NOT NULL,
  audience_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (organization_id, audience_id),
  KEY idx_organization_audience_audience (audience_id),
  CONSTRAINT fk_organization_audience_organization
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_organization_audience_audience
    FOREIGN KEY (audience_id) REFERENCES audiences(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE organization_profile_answers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  organization_id INT UNSIGNED NOT NULL,
  audience_code VARCHAR(32) NOT NULL,
  group_key VARCHAR(80) NOT NULL DEFAULT '',
  field_key VARCHAR(120) NOT NULL,
  language_code VARCHAR(8) NOT NULL,
  answer_text LONGTEXT,
  answer_format ENUM('plain_text', 'markdown') NOT NULL DEFAULT 'plain_text',
  translation_status ENUM('missing', 'draft', 'reviewed', 'published') NOT NULL DEFAULT 'missing',
  source_locked TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_profile_answer (organization_id, audience_code, group_key, field_key, language_code),
  KEY idx_profile_answers_language_status (language_code, translation_status),
  KEY idx_profile_answers_audience_field (audience_code, group_key, field_key),
  CONSTRAINT fk_profile_answers_organization
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_profile_answers_language
    FOREIGN KEY (language_code) REFERENCES languages(code)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE organization_keywords (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  organization_id INT UNSIGNED NOT NULL,
  language_code VARCHAR(8) NOT NULL DEFAULT 'nl',
  keyword VARCHAR(190) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_organization_keyword (organization_id, language_code, keyword),
  KEY idx_organization_keywords_keyword (keyword),
  CONSTRAINT fk_organization_keywords_organization
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_organization_keywords_language
    FOREIGN KEY (language_code) REFERENCES languages(code)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE submissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kind ENUM('feedback', 'organization_signup') NOT NULL,
  status ENUM('new', 'in_review', 'resolved', 'spam', 'archived') NOT NULL DEFAULT 'new',
  organization_id INT UNSIGNED DEFAULT NULL,
  island_id INT UNSIGNED DEFAULT NULL,
  language_code VARCHAR(8) DEFAULT NULL,
  audience_code VARCHAR(32) DEFAULT NULL,
  submitter_name VARCHAR(190) NOT NULL DEFAULT '',
  submitter_email VARCHAR(190) NOT NULL DEFAULT '',
  subject VARCHAR(255) NOT NULL DEFAULT '',
  message TEXT,
  payload_json JSON DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_submissions_kind_status (kind, status, created_at),
  KEY idx_submissions_organization (organization_id),
  KEY idx_submissions_island (island_id),
  CONSTRAINT fk_submissions_organization
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_submissions_island
    FOREIGN KEY (island_id) REFERENCES islands(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_submissions_language
    FOREIGN KEY (language_code) REFERENCES languages(code)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  status ENUM('active', 'invited', 'disabled') NOT NULL DEFAULT 'active',
  last_login_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(80) NOT NULL,
  name VARCHAR(120) NOT NULL,
  description VARCHAR(255) NOT NULL DEFAULT '',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_roles_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_roles (
  user_id INT UNSIGNED NOT NULL,
  role_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, role_id),
  KEY idx_user_roles_role (role_id),
  CONSTRAINT fk_user_roles_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_user_roles_role
    FOREIGN KEY (role_id) REFERENCES roles(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,
  action VARCHAR(120) NOT NULL,
  entity_type VARCHAR(80) NOT NULL,
  entity_id BIGINT UNSIGNED DEFAULT NULL,
  before_json JSON DEFAULT NULL,
  after_json JSON DEFAULT NULL,
  ip_address VARCHAR(45) NOT NULL DEFAULT '',
  user_agent VARCHAR(255) NOT NULL DEFAULT '',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_audit_log_entity (entity_type, entity_id, created_at),
  KEY idx_audit_log_user (user_id, created_at),
  CONSTRAINT fk_audit_log_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
