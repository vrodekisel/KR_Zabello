<?php

use App\Domain\Entity\Poll;
use App\Localization\Translator;

/** @var Translator $translator */
/** @var string $pageTitle */
/** @var string $heading */
/** @var Poll $poll */
/** @var array<int,array<string,mixed>> $options */

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
        .meta { font-size: 13px; color: #9CA3AF; margin-bottom: 8px; }
        .tag { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 11px; margin-right: 6px; }
        .tag-active { background: #065F46; color: #A7F3D0; }
        .tag-inactive { background: #4B5563; color: #E5E7EB; }
        .btn { display: inline-block; padding: 8px 16px; border-radius: 999px; border: none; cursor: pointer; font-size: 14px; text-decoration: none; }
        .btn-secondary { background: transparent; color: #9CA3AF; border: 1px solid #4B5563; }
        .btn-secondary:hover { background: #111827; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 16px; }
        th, td { padding: 8px 6px; text-align: left; border-bottom: 1px solid #374151; }
        th { font-weight: 600; color: #D1D5DB; }
        tr:last-child td { border-bottom: none; }
    </style>
</head>
<body>
<div class="container">
    <div style="margin-bottom: 12px;">
        <a href="/web/admin/polls" class="btn btn-secondary">
            <?= htmlspecialchars($translator->trans('ui.web.admin.poll.show.back_to_admin_list'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </a>
    </div>

    <div class="card">
        <div class="card-title">
            <span><?= htmlspecialchars($heading, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
        </div>

        <div class="meta">
            ID: <?= (int) $poll->getId() ?><br>
            <?= htmlspecialchars($translator->trans('ui.web.admin.poll.show.context'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>:
            <?= htmlspecialchars($poll->getContextType() . ' / ' . (string)($poll->getContextKey() ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><br>
            <?= htmlspecialchars($translator->trans('ui.web.admin.poll.show.title_key'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>:
            <?= htmlspecialchars($poll->getTitleKey(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><br>
            <?= htmlspecialchars($translator->trans('ui.web.admin.poll.show.description_key'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>:
            <?= htmlspecialchars((string)($poll->getDescriptionKey() ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><br>
            <?= htmlspecialchars($translator->trans('ui.web.admin.poll.show.status'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>:
            <?php if ($poll->getStatus() === \App\Domain\Entity\Poll::STATUS_ACTIVE): ?>
                <span class="tag tag-active"><?= htmlspecialchars($translator->trans('ui.web.admin.polls.list.status.active'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
            <?php else: ?>
                <span class="tag tag-inactive"><?= htmlspecialchars($translator->trans('ui.web.admin.polls.list.status.inactive'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <h3 style="margin-top: 20px; margin-bottom: 8px;">
            <?= htmlspecialchars($translator->trans('ui.web.admin.poll.show.options_heading'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </h3>

        <?php if (empty($options)): ?>
            <p><?= htmlspecialchars($translator->trans('ui.web.admin.poll.show.options_empty'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th><?= htmlspecialchars($translator->trans('ui.web.admin.poll.show.col.label_key'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></th>
                    <th><?= htmlspecialchars($translator->trans('ui.web.admin.poll.show.col.value'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></th>
                    <th><?= htmlspecialchars($translator->trans('ui.web.admin.poll.show.col.position'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($options as $row): ?>
                    <tr>
                        <td><?= (int)($row['id'] ?? 0) ?></td>
                        <td><?= htmlspecialchars((string)($row['label'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string)($row['value'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string)($row['position'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
