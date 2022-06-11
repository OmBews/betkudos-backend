CREATE DATABASE IF NOT EXISTS crypto_sportsbook;

USE crypto_sportsbook;

DROP TABLE IF EXISTS `leagues`;

CREATE TABLE `leagues` (
   `id` bigint unsigned NOT NULL AUTO_INCREMENT,
   `bet365_id` bigint NOT NULL,
   `bets_api_id` bigint DEFAULT NULL,
   `sport_id` bigint NOT NULL,
   `sport_category_id` bigint unsigned DEFAULT NULL,
   `cc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
   `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
   `popular` tinyint(1) NOT NULL DEFAULT '0',
   `active` tinyint(1) NOT NULL DEFAULT '1',
   `time_frame` tinyint NOT NULL DEFAULT '7',
   `created_at` timestamp NULL DEFAULT NULL,
   `updated_at` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`,  `bet365_id`, `name`),
   UNIQUE KEY `leagues_bet365_id_name_unique` (`bet365_id`, `name`),
   SHARD KEY `leagues_shard_key` (`bet365_id`, `name`)
);

DROP TABLE IF EXISTS `market_groups`;

CREATE TABLE `market_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`, `key`, `name`),
  UNIQUE KEY `market_groups_key_name_unique` (`key`,`name`),
  SHARD KEY `market_groups_shard_key` (`key`,`name`)
);

DROP TABLE IF EXISTS `markets`;

CREATE reference TABLE `markets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sport_id` bigint NOT NULL,
  `market_groups` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `live_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` tinyint NOT NULL DEFAULT '0',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `headers` text COLLATE utf8mb4_unicode_ci,
  `featured_header` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `layout` tinyint NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `popular` tinyint(1) NOT NULL DEFAULT '0',
  `on_live_betting` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`, `sport_id`,`name`,`key`),
  UNIQUE KEY `markets_sport_id_name_key_unique` (`sport_id`,`name`,`key`),
);

DROP TABLE IF EXISTS `market_groups`;

CREATE TABLE `market_groups` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `active` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`, `key`, `name`),
    UNIQUE KEY `market_groups_key_name_unique` (`key`,`name`),
    SHARD KEY `market_groups_shard_key` (`key`,`name`)
);

DROP TABLE IF EXISTS `match_markets`;

CREATE TABLE `match_markets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `match_id` bigint NOT NULL,
  `market_id` bigint NOT NULL,
  `order` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`, `match_id`, `market_id`),
  UNIQUE KEY `match_markets_match_id_market_id_unique` (`match_id`, `market_id`),
  SHARD KEY `match_markets_shard_key` (`match_id`, `market_id`)
);

DROP TABLE IF EXISTS `match_stats`;

CREATE TABLE `match_stats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `match_id` bigint NOT NULL,
  `stats` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `events` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`, `match_id`),
  UNIQUE KEY `match_results_match_id_unique` (`match_id`),
  SHARD KEY `match_stats_shard_key` (`match_id`)
);

DROP TABLE IF EXISTS `match_results`;

CREATE TABLE `match_results` (
     `id` bigint unsigned NOT NULL AUTO_INCREMENT,
     `match_id` bigint NOT NULL,
     `single_score` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
     `scores` text COLLATE utf8mb4_unicode_ci NOT NULL,
     `points` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
     `quarter` tinyint(1) DEFAULT 0,
     `created_at` timestamp NULL DEFAULT NULL,
     `updated_at` timestamp NULL DEFAULT NULL,
     `is_playing` tinyint(1) NOT NULL DEFAULT '0',
     `kick_of_time` bigint NOT NULL DEFAULT '0',
     `passed_minutes` bigint NOT NULL DEFAULT '0',
     `passed_seconds` bigint NOT NULL DEFAULT '0',
     `current_time` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
     PRIMARY KEY (`id`, `match_id`),
     UNIQUE KEY `match_results_match_id_unique` (`match_id`),
     SHARD KEY `match_results_shard_key` (`match_id`)
)

DROP TABLE IF EXISTS `matches`;

CREATE TABLE `matches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bet365_id` bigint NOT NULL,
  `bets_api_id` bigint DEFAULT NULL,
  `sport_id` bigint NOT NULL,
  `home_team_id` bigint NOT NULL,
  `away_team_id` bigint NOT NULL,
  `league_id` bigint NOT NULL,
  `cc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `starts_at` int NOT NULL,
  `time_status` tinyint NOT NULL,
  `last_bets_api_update` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`, `bet365_id`),
  UNIQUE KEY `matches_bet365_id_unique` (`bet365_id`),
  KEY `matches_sport_id_index` (`sport_id`),
  SHARD KEY `matches_shard_key` (`bet365_id`)
);

DROP TABLE IF EXISTS `odds`;

CREATE TABLE `odds` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `market_id` bigint NOT NULL,
  `match_id` bigint NOT NULL,
  `bet365_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `match_market_id` bigint unsigned NOT NULL,
  `odds` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `header` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `handicap` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_live` tinyint(1) NOT NULL DEFAULT '0',
  `is_suspended` tinyint(1) NOT NULL DEFAULT '0',
  `order` int DEFAULT NULL,
  PRIMARY KEY (`id`,`market_id`,`match_id`,`bet365_id`),
  UNIQUE KEY `odds_market_id_match_id_bet365_id_unique` (`market_id`,`match_id`,`bet365_id`),
  KEY `odds_match_market_id_index` (`match_market_id`),
  SHARD KEY `odds_shard_key` (`market_id`,`match_id`,`bet365_id`)
);

DROP TABLE IF EXISTS `sport_categories`;

CREATE reference TABLE `sport_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sport_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sport_categories_sport_id_foreign` (`sport_id`),
  CONSTRAINT `sport_categories_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`)
);

DROP TABLE IF EXISTS `sports`;

CREATE reference TABLE `sports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bet365_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `time_frame` tinyint NOT NULL DEFAULT '7',
  `upcoming_preview_limit` tinyint NOT NULL DEFAULT '3',
  `live_preview_limit` tinyint NOT NULL DEFAULT '3',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `on_live_betting` tinyint(1) NOT NULL DEFAULT '0';
  PRIMARY KEY (`id`),
  UNIQUE KEY `sports_name_unique` (`name`)
);

DROP TABLE IF EXISTS `teams`;

CREATE TABLE `teams` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bet365_id` bigint NOT NULL,
  `bets_api_id` bigint DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_id` bigint DEFAULT NULL,
  `cc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`, `name`,`bet365_id`),
  UNIQUE KEY `teams_name_bet365_id_unique` (`name`,`bet365_id`),
  SHARD KEY `teams_shard_key` (`name`,`bet365_id`)
);

DROP TABLE IF EXISTS `countries`;

CREATE TABLE `countries` (
   `id` bigint unsigned NOT NULL AUTO_INCREMENT,
   `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
   `code` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
   `created_at` timestamp NULL DEFAULT NULL,
   `updated_at` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`, 'name', 'code'),
   UNIQUE KEY `countries_name_code_unique` (`name`, `code`),
   SHARD KEY `countries_shard_key` (`name`, `code`)
)
