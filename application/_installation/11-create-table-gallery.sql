CREATE TABLE IF NOT EXISTS `huge`.`gallery` (
 `gallery_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing gallery_id',
 `user_id` int(11) NOT NULL COMMENT 'id of the user who uploaded the image',
 `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'filename of the uploaded image',
 `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'title/description of the image',
 `created_at` bigint(20) NOT NULL COMMENT 'timestamp of when the image was uploaded',
 PRIMARY KEY (`gallery_id`),
 KEY `user_id` (`user_id`),
 CONSTRAINT `gallery_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='user gallery images';
