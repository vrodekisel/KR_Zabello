CREATE DATABASE IF NOT EXISTS ingame_content_voting
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ingame_content_voting;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_banned TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS polls (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    type VARCHAR(32) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    content_type VARCHAR(32) NULL,
    content_key VARCHAR(64) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NULL,
    CONSTRAINT fk_polls_created_by
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE INDEX idx_polls_type_active
    ON polls (type, is_active);

CREATE TABLE IF NOT EXISTS options (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poll_id BIGINT UNSIGNED NOT NULL,
    label VARCHAR(255) NOT NULL,
    value VARCHAR(64) NULL,
    position INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_options_poll
        FOREIGN KEY (poll_id) REFERENCES polls(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE INDEX idx_options_poll
    ON options (poll_id, position);

CREATE TABLE IF NOT EXISTS votes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poll_id BIGINT UNSIGNED NOT NULL,
    option_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uniq_votes_user_poll UNIQUE (poll_id, user_id),
    CONSTRAINT fk_votes_poll
        FOREIGN KEY (poll_id) REFERENCES polls(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_votes_option
        FOREIGN KEY (option_id) REFERENCES options(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_votes_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE INDEX idx_votes_poll_option
    ON votes (poll_id, option_id);

CREATE INDEX idx_votes_user
    ON votes (user_id);

CREATE TABLE IF NOT EXISTS vote_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poll_id BIGINT UNSIGNED NULL,
    option_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    status VARCHAR(32) NOT NULL,
    reason VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vote_logs_poll
        FOREIGN KEY (poll_id) REFERENCES polls(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    CONSTRAINT fk_vote_logs_option
        FOREIGN KEY (option_id) REFERENCES options(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    CONSTRAINT fk_vote_logs_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE INDEX idx_vote_logs_created_at
    ON vote_logs (created_at);

CREATE INDEX idx_vote_logs_poll_created_at
    ON vote_logs (poll_id, created_at);

CREATE INDEX idx_vote_logs_ip_created_at
    ON vote_logs (ip_address, created_at);
