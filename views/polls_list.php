<?php

use App\Localization\Translator;

/** @var Translator $translator */
/** @var string $baseStyles */
/** @var string $topBarHtml */
/** @var string $pageTitle */
/** @var string $emptyText */
/** @var string $openButtonText */
/** @var array<int,array<string,mixed>> $cards */
?>
<!doctype html>
<html lang="<?= htmlspecialchars($translator->getLocale(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>
    <?= $baseStyles ?>
</head>
<body>
<div class="container">
    <?= $topBarHtml ?>

    <?php if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] === 1): ?>
    <div class="back-link" style="margin-bottom: 12px; display: flex; gap: 8px;">
        <a href="/web/admin/polls" class="btn btn-secondary btn-small">
            <?= htmlspecialchars($translator->trans('ui.web.admin.polls.list.link'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </a>
        <a href="/web/admin/polls/create" class="btn btn-secondary btn-small">
            <?= htmlspecialchars($translator->trans('ui.web.admin.polls.create.link'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </a>
    </div>
<?php endif; ?>

    <h1 class="page-title">
        <?= htmlspecialchars($pageTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </h1>

    <?php if ($cards === []): ?>
        <div class="card">
            <p><?= htmlspecialchars($emptyText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        </div>
    <?php else: ?>
        <?php foreach ($cards as $card): ?>
            <div class="card">
                <div class="card-title">
                    <?= htmlspecialchars((string) $card['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </div>

                <div class="card-subtitle">
                    <?= htmlspecialchars((string) $card['statusLabel'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    <?php if (!empty($card['created'])): ?>
                        · <?= htmlspecialchars((string) $card['created'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    <?php endif; ?>
                </div>

                <form method="get" action="/web/poll">
                    <input type="hidden" name="poll_id" value="<?= (int) $card['id'] ?>">
                    <button type="submit" class="btn btn-primary">
                        <?= htmlspecialchars($openButtonText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
