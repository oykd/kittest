CREATE TABLE `tree` (
    `id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id` bigint(11) UNSIGNED NULL,
    `name` varchar(255) NOT NULL,
    `content` TEXT COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `parent_id` (`parent_id`),
    CONSTRAINT `tree_1` FOREIGN KEY (`parent_id`) REFERENCES `tree` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=0
