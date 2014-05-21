<?php
$sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."tttevents` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`lang` CHAR(5) NULL,
	`post_id` INT(11) NOT NULL,
	`start_at` TIMESTAMP NOT NULL,
	`end_at` TIMESTAMP NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `start_at` (`start_at`),
	INDEX `end_at` (`end_at`)
);";
dbDelta($sql);
?>
