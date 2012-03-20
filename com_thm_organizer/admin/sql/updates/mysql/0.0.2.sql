ALTER TABLE `#__thm_organizer_rooms` ADD `campus`  varchar(128) NOT NULL DEFAULT '' AFTER `manager`;
ALTER TABLE `#__thm_organizer_rooms` ADD `building` varchar(64) NOT NULL DEFAULT '' AFTER `campus`;

ALTER TABLE `#__thm_organizer_rooms` DROP `capacity`;
ALTER TABLE `#__thm_organizer_rooms` DROP INDEX `manager`;
ALTER TABLE `#__thm_organizer_rooms` DROP `manager`;

ALTER TABLE `#__thm_organizer_classes` ADD `manager` varchar(50) DEFAULT '' AFTER `alias`;

ALTER TABLE `#__thm_organizer_classes` DROP INDEX `teacherID`;
ALTER TABLE `#__thm_organizer_classes` DROP `teacherID`;

RENAME TABLE `#__thm_organizer_application_settings` TO  `#__thm_organizer_settings` ;

ALTER TABLE `#__thm_organizer_monitors`
ADD `display` INT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'the display behaviour of the monitor',
ADD `interval` INT(1) UNSIGNED NOT NULL DEFAULT'1' COMMENT 'the time interval in minutes between context switches',
ADD `content` VARCHAR(256) DEFAULT NULL COMMENT 'the filename of the resource to the optional resource to be displayed',
ADD`content_meta` TEXT DEFAULT NULL COMMENT'a json string containing optional file extension specific parameters',
CHANGE `roomID` `roomID` INT(11) UNSIGNED NOT NULL COMMENT 'references id of rooms table',
ADD INDEX (`display`);