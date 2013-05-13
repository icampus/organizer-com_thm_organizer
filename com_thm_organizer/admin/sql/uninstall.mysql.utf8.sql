SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

# independent table
DROP TABLE IF EXISTS `#__thm_organizer_soap_queries`;

# curriculum lecturers chain
DROP TABLE IF EXISTS `#__thm_organizer_lecturers_assets`;
DROP TABLE IF EXISTS `#__thm_organizer_lecturers_types`;
DROP TABLE IF EXISTS `#__thm_organizer_lecturers`;

# events/categories chain
DROP TABLE IF EXISTS `#__thm_organizer_event_exclude_dates`;
DROP TABLE IF EXISTS `#__thm_organizer_event_groups`;
DROP TABLE IF EXISTS `#__thm_organizer_event_rooms`;
DROP TABLE IF EXISTS `#__thm_organizer_event_teachers`;
DROP TABLE IF EXISTS `#__thm_organizer_events`;
DROP TABLE IF EXISTS `#__thm_organizer_categories`;

# rooms/room types
DROP TABLE IF EXISTS `#__thm_organizer_monitors`;
DROP TABLE IF EXISTS `#__thm_organizer_rooms`;
DROP TABLE IF EXISTS `#__thm_organizer_room_types`;

# teachers
DROP TABLE IF EXISTS `#__thm_organizer_subject_teachers`;
DROP TABLE IF EXISTS `#__thm_organizer_teacher_responsibilities`;
DROP TABLE IF EXISTS `#__thm_organizer_teachers`;
DROP TABLE IF EXISTS `#__thm_organizer_teacher_fields`;

# curriculum assets chain
DROP TABLE IF EXISTS `#__thm_organizer_assets_semesters`;
DROP TABLE IF EXISTS `#__thm_organizer_assets_tree`;
DROP TABLE IF EXISTS `#__thm_organizer_assets`;
DROP TABLE IF EXISTS `#__thm_organizer_asset_types`;

# curriculum chain
DROP TABLE IF EXISTS `#__thm_organizer_semesters_majors`;
DROP TABLE IF EXISTS `#__thm_organizer_semesters`;
DROP TABLE IF EXISTS `#__thm_organizer_curriculum_semesters`;
DROP TABLE IF EXISTS `#__thm_organizer_majors`;

# mapping tables
DROP TABLE IF EXISTS `#__thm_organizer_mappings`;
DROP TABLE IF EXISTS `#__thm_organizer_subjects`;
DROP TABLE IF EXISTS `#__thm_organizer_pools`;
DROP TABLE IF EXISTS `#__thm_organizer_degree_programs`;
DROP TABLE IF EXISTS `#__thm_organizer_degrees`;

# colors/fields
DROP TABLE IF EXISTS `#__thm_organizer_fields`;
DROP TABLE IF EXISTS `#__thm_organizer_colors`;

# schedules
DROP TABLE IF EXISTS `#__thm_organizer_user_schedules`;
DROP TABLE IF EXISTS `#__thm_organizer_virtual_schedules_elements`;
DROP TABLE IF EXISTS `#__thm_organizer_virtual_schedules`;
DROP TABLE IF EXISTS `#__thm_organizer_schedules`;

