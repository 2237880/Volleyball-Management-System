-- Volleyball Management System (MySQL) - Schema
-- Execute this file in your chosen MySQL database.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ----------------------------
-- users
-- ----------------------------
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','coach','player') NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- teams
-- ----------------------------
CREATE TABLE IF NOT EXISTS teams (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  coach_user_id INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_team_name (name),
  KEY idx_teams_coach (coach_user_id),
  CONSTRAINT fk_teams_coach
    FOREIGN KEY (coach_user_id) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- players (team roster)
-- ----------------------------
CREATE TABLE IF NOT EXISTS players (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  team_id INT UNSIGNED NOT NULL,
  player_user_id INT UNSIGNED NOT NULL,
  position VARCHAR(60) NOT NULL DEFAULT 'Unknown',
  is_captain TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_team_player (team_id, player_user_id),
  KEY idx_players_team (team_id),
  KEY idx_players_player (player_user_id),
  CONSTRAINT fk_players_team
    FOREIGN KEY (team_id) REFERENCES teams(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_players_user
    FOREIGN KEY (player_user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- matches (fixtures)
-- ----------------------------
CREATE TABLE IF NOT EXISTS matches (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  home_team_id INT UNSIGNED NOT NULL,
  away_team_id INT UNSIGNED NOT NULL,
  scheduled_at DATETIME NOT NULL,
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_matches_scheduled (scheduled_at),
  KEY idx_matches_home (home_team_id),
  KEY idx_matches_away (away_team_id),
  CONSTRAINT fk_matches_home_team
    FOREIGN KEY (home_team_id) REFERENCES teams(id)
    ON DELETE RESTRICT,
  CONSTRAINT fk_matches_away_team
    FOREIGN KEY (away_team_id) REFERENCES teams(id)
    ON DELETE RESTRICT,
  CONSTRAINT fk_matches_created_by
    FOREIGN KEY (created_by) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- match_scores (final results)
-- ----------------------------
CREATE TABLE IF NOT EXISTS match_scores (
  match_id INT UNSIGNED NOT NULL,
  home_score INT UNSIGNED NOT NULL DEFAULT 0,
  away_score INT UNSIGNED NOT NULL DEFAULT 0,
  winner_team_id INT UNSIGNED NULL,
  entered_by INT UNSIGNED NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (match_id),
  KEY idx_match_scores_winner (winner_team_id),
  CONSTRAINT fk_match_scores_match
    FOREIGN KEY (match_id) REFERENCES matches(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_match_scores_winner
    FOREIGN KEY (winner_team_id) REFERENCES teams(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_match_scores_entered_by
    FOREIGN KEY (entered_by) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

