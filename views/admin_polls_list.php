<?php

use App\Localization\Translator;

/** @var Translator $translator */
/** @var string $pageTitle */
/** @var string $heading */
/** @var string $createLinkText */
/** @var array<int, array<string,mixed>> $polls */
?>
<!doctype html>
<html lang="<?= htmlspecialchars($translator->getLocale(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; padding: 24px; background: #111827; color: #E5E7EB; }
        .container { max-width: 960px; margin: 0 auto; }
        .card { background: #1F2937; border-radius: 12px; padding: 20px; margin-bottom: 16px; box-shadow: 0 10px 20px rgba(0,0,0,0.4); }
        .card-title { font-size: 20px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; }
        .btn { display: inline-block; padding: 8px 16px; border-radius: 999px; border: none; cursor: pointer; font-size: 14px; text-decoration: none; }
        .btn-primary { background: #2563EB; color: #F9FAFB; }
        .btn-primary:hover { background: #1D4ED8; }
        .btn-secondary { background: transparent; color: #9CA3AF; border: 1px solid #4B5563; }
        .btn-secondary:hover { background: #111827; }
        .btn-small { font-size: 13px; padding: 4px 10px; }
        .back-link { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { padding: 8px 6px; text-align: left; border-bottom: 1px solid #374151; }
        th { font-weight: 600; color: #D1D5DB; }
        tr:last-child td { border-bottom: none; }
        .tag { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 11px; }
        .tag-active { background: #065F46; color: #A7F3D0; }
        .tag-inactive { background: #4B5563; color: #E5E7EB; }
        .actions { display: flex; gap: 6px; }
    </style>
</head>
<body>
<div class="container">
    <div class="back-link">
        <a href="/web/polls" class="btn btn-secondary btn-small">
            <?= htmlspecialchars($translator->trans('ui.web.poll.back_to_list'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </a>
    </div>

    <div class="card">
        <div class="card-title">
            <span><?= htmlspecialchars($heading, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
            <a href="/web/admin/polls/create" class="btn btn-primary btn-small">
                <?= htmlspecialchars($createLinkText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </a>
        </div>

        <?php if (empty($polls)): ?>
            <p><?= htmlspecialchars($translator->trans('ui.web.admin.polls.list.empty'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th><?= htmlspecialchars($translator->trans('ui.web.admin.polls.list.col.title_key'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></th>
                    <th><?= htmlspecialchars($translator->trans('ui.web.admin.polls.list.col.context'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></th>
                    <th><?= htmlspecialchars($translator->trans('ui.web.admin.polls.list.col.status'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></th>
                    <th><?= htmlspecialchars($translator->trans('ui.web.admin.polls.list.col.created_at'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></th>
                    <th><?= htmlspecialchars($translator->trans('ui.web.admin.polls.list.col.actions'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($polls as $row): ?>
                    <tr>
                        <td><?= (int) $row['id'] ?></td>
                        <td><?= htmlspecialchars((string)$row['title_key'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string)$row['context_type'] . ' / ' . (string)$row['context_key'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td>
                            <?php if (!empty($row['is_active'])): ?>
                                <span class="tag tag-active"><?= htmlspecialchars($translator->trans('ui.web.admin.polls.list.status.active'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                            <?php else: ?>
                                <span class="tag tag-inactive"><?= htmlspecialchars($translator->trans('ui.web.admin.polls.list.status.inactive'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars((string)($row['created_at'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td>
                            <div class="actions">
                                <a href="/web/admin/poll?poll_id=<?= (int)$row['id'] ?>" class="btn btn-secondary btn-small">
                                    <?= htmlspecialchars($translator->trans('ui.web.admin.polls.list.action.details'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </a>
                                <a href="/web/poll?poll_id=<?= (int)$row['id'] ?>" class="btn btn-secondary btn-small">
                                    <?= htmlspecialchars($translator->trans('ui.web.admin.polls.list.action.open_as_player'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
