USE ingame_content_voting;

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE vote_logs;
TRUNCATE TABLE votes;
TRUNCATE TABLE options;
TRUNCATE TABLE polls;
TRUNCATE TABLE users;

SET FOREIGN_KEY_CHECKS = 1;

-- Демонстрационные пользователи
INSERT INTO users (id, username, password_hash, is_banned, created_at) VALUES
(1, 'admin',   '$2b$12$92KzeTYcThP/n3SHUb.Cp.xDcVT5irYR9P7G3DLLrX3AbqmdbaeU2', 0, NOW()),
(2, 'player1', '$2b$12$Sv9UZe2NHd3uIdmOtCa1xO6jrhoPuvXZ6yETgv1eFMe9JCuiyTNg.', 0, NOW()),
(3, 'player2', '$2b$12$A2VL3eKKubU1RoUXV5Kx0uoZx8QsMqgHmehRSa3Hpv28n9Yh42/na', 0, NOW());

-- Опрос 1: голосование карту
INSERT INTO polls (
    id, title, description, type, is_active,
    content_type, content_key,
    created_by, created_at, expires_at
) VALUES
(
    1,
    'poll.next_map.title',
    'poll.next_map.description',
    'MAP_VOTE',
    1,
    'MAP',
    'next_map',
    1,
    NOW(),
    NULL
);

-- Опрос 2: рейтинг мода BetterGrass
INSERT INTO polls (
    id, title, description, type, is_active,
    content_type, content_key,
    created_by, created_at, expires_at
) VALUES
(
    2,
    'poll.better_grass_rating.title',
    'poll.better_grass_rating.description',
    'MOD_RATING',
    1,
    'MOD',
    'better_grass',
    1,
    NOW(),
    NULL
);

-- Опрос 3: выбор популярного мода
INSERT INTO polls (
    id, title, description, type, is_active,
    content_type, content_key,
    created_by, created_at, expires_at
) VALUES
(
    3,
    'poll.popular_mods.title',
    'poll.popular_mods.description',
    'MOD_VOTE',
    1,
    'MOD',
    'popular_mod',
    1,
    NOW(),
    NULL
);

-- Варианты для опроса 1: карты
INSERT INTO options (id, poll_id, label, value, position, created_at) VALUES
(1, 1, 'option.map.frozen_temple.label',   'frozen_temple',   1, NOW()),
(2, 1, 'option.map.desert_ruins.label',    'desert_ruins',    2, NOW()),
(3, 1, 'option.map.cyber_arena.label',     'cyber_arena',     3, NOW()),
(4, 1, 'option.map.ice_cavern.label',      'ice_cavern',      4, NOW()),
(5, 1, 'option.map.volcano_crater.label',  'volcano_crater',  5, NOW());

-- Варианты для опроса 2: рейтинг мода BetterGrass (1–5)
INSERT INTO options (id, poll_id, label, value, position, created_at) VALUES
(6,  2, 'option.mod.better_grass.rating.1.label', '1', 1, NOW()),
(7,  2, 'option.mod.better_grass.rating.2.label', '2', 2, NOW()),
(8,  2, 'option.mod.better_grass.rating.3.label', '3', 3, NOW()),
(9,  2, 'option.mod.better_grass.rating.4.label', '4', 4, NOW()),
(10, 2, 'option.mod.better_grass.rating.5.label', '5', 5, NOW());

-- Варианты для опроса 3: список модов
INSERT INTO options (id, poll_id, label, value, position, created_at) VALUES
(11, 3, 'option.mod.better_grass.label',   'better_grass',   1, NOW()),
(12, 3, 'option.mod.hd_textures.label',    'hd_textures',    2, NOW()),
(13, 3, 'option.mod.fast_travel.label',    'fast_travel',    3, NOW()),
(14, 3, 'option.mod.hardcore_mode.label',  'hardcore_mode',  4, NOW());

-- Голоса (успешные)
INSERT INTO votes (id, poll_id, option_id, user_id, created_at) VALUES
(1, 1, 3, 2, NOW() - INTERVAL 2 MINUTE),
(2, 2, 9, 2, NOW() - INTERVAL 90 SECOND),
(3, 1, 1, 3, NOW() - INTERVAL 80 SECOND),
(4, 2, 10, 3, NOW() - INTERVAL 70 SECOND),
(5, 3, 11, 2, NOW() - INTERVAL 65 SECOND),
(6, 3, 12, 3, NOW() - INTERVAL 55 SECOND); 

-- Логи голосования
INSERT INTO vote_logs (
    poll_id, option_id, user_id,
    ip_address, user_agent,
    status, reason, created_at
) VALUES
(1, 3, 2, '192.168.0.10', 'DemoClient/1.0', 'SUCCESS',  'log.reason.vote_accepted',          NOW() - INTERVAL 2 MINUTE),
(2, 9, 2, '192.168.0.10', 'DemoClient/1.0', 'SUCCESS',  'log.reason.vote_accepted',          NOW() - INTERVAL 90 SECOND),
(1, 1, 3, '192.168.0.11', 'DemoClient/1.0', 'SUCCESS',  'log.reason.vote_accepted',          NOW() - INTERVAL 80 SECOND),
(2, 10,3, '192.168.0.11', 'DemoClient/1.0', 'SUCCESS',  'log.reason.vote_accepted',          NOW() - INTERVAL 70 SECOND),
(3, 11,2, '192.168.0.10', 'DemoClient/1.0', 'SUCCESS',  'log.reason.vote_accepted',          NOW() - INTERVAL 65 SECOND),
(3, 12,3, '192.168.0.11', 'DemoClient/1.0', 'SUCCESS',  'log.reason.vote_accepted',          NOW() - INTERVAL 55 SECOND),

(1, 2, 2, '192.168.0.10', 'DemoClient/1.0', 'DUPLICATE','log.reason.already_voted',          NOW() - INTERVAL 50 SECOND),
(1, 3, 2, '192.168.0.10', 'DemoClient/1.0', 'FORBIDDEN','log.reason.rate_limit_exceeded',    NOW() - INTERVAL 30 SECOND);
