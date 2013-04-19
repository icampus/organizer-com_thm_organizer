-- Drop Dependent Tables
DROP TABLE IF EXISTS `#__thm_organizer_assets_semesters`;
DROP TABLE IF EXISTS `#__thm_organizer_semesters_majors`;
DROP TABLE IF EXISTS `#__thm_organizer_majors`;
DROP TABLE IF EXISTS `#__thm_organizer_curriculum_semesters`;
DROP TABLE IF EXISTS `#__thm_organizer_semesters`;
DROP TABLE IF EXISTS `#__thm_organizer_assets_tree`;
DROP TABLE IF EXISTS `#__thm_organizer_lecturers_assets`;
DROP TABLE IF EXISTS `#__thm_organizer_teacher_assets`;
DROP TABLE IF EXISTS `#__thm_organizer_assets`;
DROP TABLE IF EXISTS `#__thm_organizer_lecturers`;
DROP TABLE IF EXISTS `#__thm_organizer_asset_types`;

-- Colors
-- Standardize the index column
ALTER TABLE  `#__thm_organizer_colors`
CHANGE `id` `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT;

-- Degrees
-- Empty the yet unused table.
TRUNCATE TABLE `#__thm_organizer_degrees`;

-- Copy data from curriculum to organizer
INSERT INTO `#__thm_organizer_degrees` (`id`, `name`)
SELECT *
FROM `#__thm_curriculum_degrees`;

-- Standardize the index column
ALTER TABLE  `#__thm_organizer_degrees`
CHANGE `id` `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT;

-- Add abbreviations for the connection to Untis data
ALTER TABLE `#__thm_organizer_degrees`
ADD `lsfDegree` varchar ( 10 ) DEFAULT NULL;

-- Fill abbreviation data.
UPDATE `#__thm_organizer_degrees`
SET `abbreviation` = 'B.Sc.', `lsfDegree` = 'BS'
WHERE `name` = 'Bachelor of Science';

UPDATE `#__thm_organizer_degrees`
SET `abbreviation` = 'M.Sc.', `lsfDegree` = 'MS'
WHERE `name` = 'Master of Science';

UPDATE `#__thm_organizer_degrees`
SET `abbreviation` = 'B.A.', `lsfDegree` = 'BA'
WHERE `name` = 'Bachelor of Arts';

UPDATE `#__thm_organizer_degrees`
SET `abbreviation` = 'M.A.', `lsfDegree` = 'MA'
WHERE `name` = 'Master of Arts';

UPDATE `#__thm_organizer_degrees`
SET `abbreviation` = 'B.Eng.', `lsfDegree` = 'BE'
WHERE `name` = 'Bachelor of Engineering';

UPDATE `#__thm_organizer_degrees`
SET `abbreviation` = 'M.Eng.', `lsfDegree` = 'ME'
WHERE `name` = 'Master of Engineering';

UPDATE `#__thm_organizer_degrees`
SET `abbreviation` = 'M.B.A.', `lsfDegree` = 'MB'
WHERE `name` = 'Master of Business Administration and Engineering';

UPDATE `#__thm_organizer_degrees`
SET `abbreviation` = 'Dipl.', `lsfDegree` = 'BW'
WHERE `name` = 'Diplom';

-- Fields
-- Rename table to make contents clearer as regards subject and context.
RENAME TABLE `#__thm_organizer_teacher_fields` TO `#__thm_organizer_fields`;

ALTER TABLE `#__thm_organizer_fields`
ADD `colorID` INT(11) unsigned DEFAULT NULL,
ADD FOREIGN KEY (`colorID`) REFERENCES `#__thm_organizer_colors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE `#__thm_organizer_fields`
SET `colorID` = (SELECT `id`FROM `#__thm_organizer_colors` WHERE `color` = '80ba24')
WHERE `field` = 'Informatik';

UPDATE `#__thm_organizer_fields`
SET `colorID` = (SELECT `id`FROM `#__thm_organizer_colors` WHERE `color` = '71a126')
WHERE `field` = 'Ingenieurwesen / Informatik';

UPDATE `#__thm_organizer_fields`
SET `colorID` = (SELECT `id`FROM `#__thm_organizer_colors` WHERE `color` = '638929')
WHERE `field` = 'Mathematik / Informatik';

UPDATE `#__thm_organizer_fields`
SET `colorID` = (SELECT `id`FROM `#__thm_organizer_colors` WHERE `color` = '032140')
WHERE `field` = 'Mathematik';

UPDATE `#__thm_organizer_fields`
SET `colorID` = (SELECT `id`FROM `#__thm_organizer_colors` WHERE `color` = '063d76')
WHERE `field` = 'Naturwissenschaft';

UPDATE `#__thm_organizer_fields`
SET `colorID` = (SELECT `id`FROM `#__thm_organizer_colors` WHERE `color` = '3d494f')
WHERE `field` = 'Wirtschaft';

UPDATE `#__thm_organizer_fields`
SET `colorID` = (SELECT `id`FROM `#__thm_organizer_colors` WHERE `color` = '6b7e88')
WHERE `field` = 'Sozialwissenschaften' OR `field` = 'Jura';

UPDATE `#__thm_organizer_fields`
SET `colorID` = (SELECT `id`FROM `#__thm_organizer_colors` WHERE `color` = 'cce3a7')
WHERE `field` = 'Diverse';

UPDATE `#__thm_organizer_fields`
SET `colorID` = (SELECT `id`FROM `#__thm_organizer_colors` WHERE `color` = 'ffffff')
WHERE `field` = 'Medizin';

UPDATE `#__thm_organizer_fields`
SET `colorID` = (SELECT `id`FROM `#__thm_organizer_colors` WHERE `color` = 'b7bec2')
WHERE `field` = 'Medizin / Informatik';

UPDATE `#__thm_organizer_fields`
SET `colorID` = (SELECT `id`FROM `#__thm_organizer_colors` WHERE `color` = '00B5DD')
WHERE `colorID` IS NULL;

-- Degree Programs
CREATE TABLE IF NOT EXISTS `#__thm_organizer_degree_programs` (
  `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `version` year (4) DEFAULT NULL,
  `lsfFieldID` varchar(255) DEFAULT NULL,
  `degreeID` INT(11) unsigned DEFAULT NULL,
  `fieldID` INT(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`degreeID`) REFERENCES `#__thm_organizer_degrees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- Module Pools
CREATE TABLE IF NOT EXISTS `#__thm_organizer_pools` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentID` INT(11) UNSIGNED NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `lft` INT(11) UNSIGNED DEFAULT NULL,
  `rgt` INT(11) UNSIGNED DEFAULT NULL,
  `level` INT(11) UNSIGNED DEFAULT NULL,
  `ordering` INT(11) UNSIGNED DEFAULT NULL,
  `short_title_de` varchar(45) DEFAULT NULL,
  `short_title_en` varchar(45) DEFAULT NULL,
  `direction` TINYINT(1) UNSIGNED DEFAULT 1,
  `fieldID` INT(11) unsigned DEFAULT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (`parentID`) REFERENCES `#__thm_organizer_pools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- Subjects
CREATE TABLE IF NOT EXISTS `#__thm_organizer_subjects` (
  `id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `beschreibung` varchar(255) DEFAULT NULL,
  `min_creditpoints` TINYINT(4) DEFAULT NULL,
  `max_creditpoints` TINYINT(4) DEFAULT NULL,
  `lsf_course_id` INT(11) DEFAULT NULL,
  `lsf_course_code` varchar(45) DEFAULT NULL,
  `his_course_code` INT(11) DEFAULT NULL,
  `title_de` varchar(255) DEFAULT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `short_title_de` varchar(45) DEFAULT NULL,
  `short_title_en` varchar(45) DEFAULT NULL,
  `abbreviation` varchar(45) DEFAULT NULL,
  `prerequisite` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `fieldID` INT(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- Subjects Module Pools
-- Recreate the majors table with modifications, the id gets altered later to
-- avoid problems with fk type checks.
CREATE TABLE IF NOT EXISTS `#__thm_organizer_subjects_pools` (
  `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `subjectID` INT(11) unsigned NOT NULL,
  `poolID` INT(11) unsigned NOT NULL,
  PRIMARY KEY (`subjectID`, `poolID`),
  UNIQUE KEY (`id`),
  FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_pools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- Teachers (Lecturers)
-- Alter teachers table to accept lecturers data. MNI has no overlapping IDs.
-- W needs to be emptied first. KMUB de-/reinstalled.
ALTER TABLE `#__thm_organizer_teachers`
CHANGE  `gpuntisID`  `gpuntisID` varchar( 10 ) DEFAULT NULL,
CHANGE  `surname`  `surname` varchar( 255 ) DEFAULT NULL,
CHANGE  `firstname`  `forename` varchar( 255 ) DEFAULT NULL,
ADD `title` varchar ( 45 ) DEFAULT NULL;

-- Copy data from curriculum to organizer
INSERT INTO `#__thm_organizer_teachers` (`id`, `surname`, `forename`, `username`, `title`)
SELECT `id`, `surname`, `forename`, `userid`, `academic_title`
FROM `#__thm_curriculum_lecturers`;

-- Teacher Responsibilites
-- Rename table to make contents clearer as regards subject and context.
RENAME TABLE `#__thm_organizer_lecturers_types` TO `#__thm_organizer_teacher_responsibilities`;

-- Standardize the index column
ALTER TABLE  `#__thm_organizer_teacher_responsibilities`
CHANGE `id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT;

-- Empty the yet unused table.
TRUNCATE TABLE `#__thm_organizer_teacher_responsibilities`;

-- Copy data from curriculum to organizer
INSERT INTO `#__thm_organizer_teacher_responsibilities`
SELECT *
FROM `#__thm_curriculum_lecturers_types`;

-- Subject Teachers
CREATE TABLE IF NOT EXISTS `#__thm_organizer_subject_teachers` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjectID` INT(11) UNSIGNED NOT NULL,
  `teacherID` INT(11) UNSIGNED NOT NULL,
  `teacherResp` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`subjectID`, `teacherID`, `teacherResp`),
  UNIQUE KEY (`id`),
  FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`teacherResp`) REFERENCES `#__thm_organizer_teacher_responsibilities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


-- SOAP Queries
TRUNCATE TABLE `#__thm_organizer_soap_queries`;

INSERT INTO `#__thm_organizer_soap_queries`
SELECT *
FROM `#__thm_curriculum_soap_queries`;