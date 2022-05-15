CREATE TABLE `admins` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `login` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
    `password` varchar(255) NOT NULL,
    `session` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    UNIQUE KEY `login` (`login`),
    KEY `session` (`session`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=0