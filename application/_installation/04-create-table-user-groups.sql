CREATE TABLE IF NOT EXISTS `user_groups` (
    `group_id` int(11) NOT NULL AUTO_INCREMENT,
    `group_name` varchar(50) NOT NULL,
    `group_description` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `user_groups` (`group_id`, `group_name`, `group_description`) VALUES
(1, 'Gast', 'Nicht registrierte Besucher'),
(2, 'User', 'Standardbenutzer'),
(3, 'Freigabe 1', 'Zukünftige Erweiterung'),
(4, 'Freigabe 2', 'Zukünftige Erweiterung'),
(5, 'Freigabe 3', 'Zukünftige Erweiterung'),
(6, 'Freigabe 4', 'Zukünftige Erweiterung'),
(7, 'Admin', 'Systemadministrator');