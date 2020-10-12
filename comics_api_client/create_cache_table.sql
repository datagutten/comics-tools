CREATE TABLE `comics_api_cache`
(
    `site`     varchar(100) NOT NULL,
    `comic`    varchar(100) NOT NULL,
    `pub_date` varchar(20)  NOT NULL,
    `file`     varchar(200) NOT NULL,
    `checksum` varchar(64)  NOT NULL,
    `fetched`  varchar(20)  NOT NULL,
    `title`    varchar(255) DEFAULT NULL,
    `text`     text         DEFAULT NULL,
    UNIQUE KEY `comic_checksum` (`comic`, `checksum`),
    KEY `comic_pub_date` (`comic`, `pub_date`)
) DEFAULT CHARSET = utf8;
