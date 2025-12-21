<?php

declare(strict_types=1);

return [
    'ui.app.title' => 'Внутриигровое голосование за контент',

    'ui.page.poll.title' => 'Голосование в опросе',

    'ui.poll.message.vote_success' => 'Ваш голос учтён.',
    'ui.poll.button.vote' => 'Проголосовать',
    'ui.poll.button.already_voted' => 'Вы уже голосовали',

    'ui.poll.results.title' => 'Результаты опроса',
    'ui.poll.results.votes' => 'Голоса',
    'ui.poll.results.total_votes' => 'Всего голосов',

    'ui.poll.error.invalid_id' => 'Некорректный идентификатор опроса.',
    'ui.poll.error.not_found' => 'Опрос не найден.',

    'ui.web.header.language'      => 'Язык',
    'ui.web.header.login'         => 'Войти',
    'ui.web.header.logout'        => 'Выйти',
    'ui.web.header.logged_in_as'  => 'Вы вошли как',

    'ui.web.polls.title'          => 'Опросы по внутриигровому контенту',
    'ui.web.polls.empty'          => 'Сейчас нет активных демонстрационных опросов.',
    'ui.web.polls.status.active'  => 'Активный опрос',
    'ui.web.polls.status.closed'  => 'Закрытый опрос',
    'ui.web.polls.button.open'    => 'Открыть опрос',

    'ui.web.login.title'          => 'Вход в демо-сайт голосования ArenaX',
    'ui.web.login.username'       => 'Имя пользователя',
    'ui.web.login.password'       => 'Пароль',
    'ui.web.login.button'         => 'Войти',
    'ui.web.login.error_invalid'  => 'Неверное имя пользователя или пароль.',
    'ui.web.login.error_banned'   => 'Ваш аккаунт заблокирован.',
    'ui.web.login.helper_demousers' => 'Используйте демонстрационные аккаунты из seed-данных (например, admin, player1, player2).',

    'poll.next_map.title'            => 'Следующая карта в ArenaX',
    'poll.next_map.description'      => 'Выберите карту, которая должна быть сыграна в следующем матче.',

    'poll.better_grass_rating.title'       => 'Оценка мода «Better Grass»',
    'poll.better_grass_rating.description' => 'Насколько полезен и удобен для вас этот графический мод?',

    'poll.popular_mods.title'            => 'Какой мод вывести в топ?',
    'poll.popular_mods.description'      => 'Выберите мод, который стоит выделить и продвигать внутри игры.',

    'ui.web.poll.back_to_list'   => '← К списку опросов',
    'ui.web.login.back_to_polls' => '← К списку опросов',

    'ui.web.admin.polls.create.title'            => 'Создание опроса (админ)',
    'ui.web.admin.polls.create.heading'          => 'Создать новый опрос по внутриигровому контенту',
    'ui.web.admin.polls.create.field.title_key'  => 'Ключ заголовка',
    'ui.web.admin.polls.create.field.description_key' => 'Ключ описания',
    'ui.web.admin.polls.create.field.context_type'    => 'Тип контента',
    'ui.web.admin.polls.create.field.context_key'     => 'Ключ контента',
    'ui.web.admin.polls.create.field.options'    => 'Варианты (ключи локализации)',
    'ui.web.admin.polls.create.field.option_label'    => 'Вариант',
    'ui.web.admin.polls.create.button.save'      => 'Создать опрос',
    'ui.web.admin.polls.create.helper'           => 'Используйте ключи вроде poll.next_map.title, option.map_1, option.map_2…',
    'ui.web.admin.polls.create.error_validation' => 'Заполните заголовок, тип, ключ контента и как минимум два варианта.',
    'ui.web.admin.polls.create.error_generic'    => 'Не удалось создать опрос. Попробуйте позже.',
    'ui.web.admin.polls.create.link'             => 'Создать опрос',
    'ui.web.admin.error.forbidden'               => 'Доступ запрещён: только администратор.',

    'ui.web.admin.polls.list.title'               => 'Админ · Опросы по контенту',
    'ui.web.admin.polls.list.heading'             => 'Опросы по внутриигровому контенту (админ)',
    'ui.web.admin.polls.list.link'                => 'Админ: список опросов',
    'ui.web.admin.polls.list.empty'               => 'Для демо-контекстов ещё нет опросов.',
    'ui.web.admin.polls.list.col.title_key'       => 'Ключ заголовка',
    'ui.web.admin.polls.list.col.context'         => 'Контент',
    'ui.web.admin.polls.list.col.status'          => 'Статус',
    'ui.web.admin.polls.list.col.created_at'      => 'Создан',
    'ui.web.admin.polls.list.col.actions'         => 'Действия',
    'ui.web.admin.polls.list.status.active'       => 'Активен',
    'ui.web.admin.polls.list.status.inactive'     => 'Неактивен',
    'ui.web.admin.polls.list.action.open_as_player' => 'Открыть как игрок',


];
