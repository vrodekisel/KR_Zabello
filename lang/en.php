<?php

declare(strict_types=1);

return [
    // Общие
    'ui.app.title' => 'Ingame content voting',

    // Страница опроса
    'ui.page.poll.title' => 'Vote in poll',

    'ui.poll.message.vote_success' => 'Your vote has been saved.',
    'ui.poll.button.vote' => 'Vote',
    'ui.poll.button.already_voted' => 'You have already voted',

    'ui.poll.results.title' => 'Poll results',
    'ui.poll.results.votes' => 'Votes',
    'ui.poll.results.total_votes' => 'Total votes',

    'ui.poll.error.invalid_id' => 'Poll id is invalid.',
    'ui.poll.error.not_found' => 'Poll not found.',

        // --- Header / navigation ---
    'ui.web.header.language'      => 'Language',
    'ui.web.header.login'         => 'Log in',
    'ui.web.header.logout'        => 'Log out',
    'ui.web.header.logged_in_as'  => 'Logged in as',

    // --- Polls list page ---
    'ui.web.polls.title'          => 'Content polls',
    'ui.web.polls.empty'          => 'There are no active demo polls right now.',
    'ui.web.polls.status.active'  => 'Active poll',
    'ui.web.polls.status.closed'  => 'Closed poll',
    'ui.web.polls.button.open'    => 'Open poll',

    // --- Web login ---
    'ui.web.login.title'          => 'Sign in to ArenaX voting demo',
    'ui.web.login.username'       => 'Username',
    'ui.web.login.password'       => 'Password',
    'ui.web.login.button'         => 'Sign in',
    'ui.web.login.error_invalid'  => 'Invalid username or password.',
    'ui.web.login.error_banned'   => 'Your account is banned.',
    'ui.web.login.helper_demousers' => 'Use demo accounts from seed data (e.g. admin, player1, player2).',

        // --- Demo polls: titles & descriptions ---
    'poll.next_map.title'            => 'Next map in ArenaX',
    'poll.next_map.description'      => 'Choose which map should be played in the next match.',

    'poll.better_grass_rating.title'       => 'Rate the "Better Grass" mod',
    'poll.better_grass_rating.description' => 'How useful is this graphics mod for you?',

    'poll.popular_mods.title'            => 'Which mod should we feature?',
    'poll.popular_mods.description'      => 'Vote for the mod that should be highlighted in the in-game store.',

    'ui.web.poll.back_to_list'   => '← Back to polls',
    'ui.web.login.back_to_polls' => '← Back to polls list',

    'ui.web.admin.polls.create.title'            => 'Create poll (admin)',
    'ui.web.admin.polls.create.heading'          => 'Create new in-game content poll',
    'ui.web.admin.polls.create.field.title_key'  => 'Title key',
    'ui.web.admin.polls.create.field.description_key' => 'Description key',
    'ui.web.admin.polls.create.field.context_type'    => 'Content type',
    'ui.web.admin.polls.create.field.context_key'     => 'Content key',
    'ui.web.admin.polls.create.field.options'    => 'Options (localization keys)',
    'ui.web.admin.polls.create.field.option_label'    => 'Option',
    'ui.web.admin.polls.create.button.save'      => 'Create poll',
    'ui.web.admin.polls.create.helper'           => 'Use localization keys like poll.next_map.title, option.map_1, option.map_2…',
    'ui.web.admin.polls.create.error_validation' => 'Please fill title, content type, content key and at least two options.',
    'ui.web.admin.polls.create.error_generic'    => 'Unable to create poll. Try again later.',
    'ui.web.admin.polls.create.link'             => 'Create new poll',
    'ui.web.admin.error.forbidden'               => 'Access denied: admin only.',

        'ui.web.admin.polls.list.title'               => 'Admin · Content polls',
    'ui.web.admin.polls.list.heading'             => 'In-game content polls (admin view)',
    'ui.web.admin.polls.list.link'                => 'Admin: polls list',
    'ui.web.admin.polls.list.empty'               => 'There are no polls for demo contexts yet.',
    'ui.web.admin.polls.list.col.title_key'       => 'Title key',
    'ui.web.admin.polls.list.col.context'         => 'Content',
    'ui.web.admin.polls.list.col.status'          => 'Status',
    'ui.web.admin.polls.list.col.created_at'      => 'Created at',
    'ui.web.admin.polls.list.col.actions'         => 'Actions',
    'ui.web.admin.polls.list.status.active'       => 'Active',
    'ui.web.admin.polls.list.status.inactive'     => 'Inactive',
    'ui.web.admin.polls.list.action.open_as_player' => 'Open as player',



];
