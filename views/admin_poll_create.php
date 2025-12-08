<?php

use App\Localization\Translator;

/** @var Translator $translator */
/** @var string $pageTitle */
/** @var string $heading */
/** @var string $titleLabel */
/** @var string $descriptionLabel */
/** @var string $contextTypeLabel */
/** @var string $contextKeyLabel */
/** @var string $optionsLabel */
/** @var string $optionLabelBase */
/** @var string $buttonText */
/** @var string $helperText */
/** @var string|null $errorText */
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
            <?= htmlspecialchars($heading, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>

        <?php if ($errorText !== null): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($errorText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/web/admin/polls/create">
            <div class="form-group">
                <label for="title_key"><?= htmlspecialchars($titleLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></label>
                <input type="text" id="title_key" name="title_key" placeholder="poll.custom.title" required>
            </div>

            <div class="form-group">
                <label for="description_key"><?= htmlspecialchars($descriptionLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></label>
                <input type="text" id="description_key" name="description_key" placeholder="poll.custom.description">
            </div>

            <div class="form-group">
                <label for="context_type"><?= htmlspecialchars($contextTypeLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></label>
                <select id="context_type" name="context_type" required>
                    <option value="MAP">MAP</option>
                    <option value="MOD">MOD</option>
                    <option value="OTHER">OTHER</option>
                </select>
            </div>

            <div class="form-group">
                <label for="context_key"><?= htmlspecialchars($contextKeyLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></label>
                <input type="text" id="context_key" name="context_key" placeholder="next_map" required>
            </div>

            <div class="form-group">
                <label><?= htmlspecialchars($optionsLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></label>
                <div class="options-grid">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <div>
                            <label for="option_<?= $i ?>">
                                <?= htmlspecialchars($optionLabelBase, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                <?= $i ?>
                            </label>
                            <input type="text" id="option_<?= $i ?>" name="option_<?= $i ?>" placeholder="option.map_<?= $i ?>">
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <?= htmlspecialchars($buttonText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </button>

            <div class="helper">
                <?= htmlspecialchars($helperText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>
        </form>
    </div>
</div>
</body>
</html>
