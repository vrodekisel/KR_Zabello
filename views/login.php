<?php

use App\Localization\Translator;

/** @var Translator $translator */
/** @var string[] $availableLocales */
/** @var string $locale */
/** @var string $redirect */
/** @var string|null $errorText */
/** @var string $title */
/** @var string $usernameLabel */
/** @var string $passwordLabel */
/** @var string $languageLabel */
/** @var string $buttonText */
/** @var string $helperText */
?>
<!doctype html>
<html lang="<?= htmlspecialchars($locale, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; padding: 24px; background: #111827; color: #E5E7EB; }
        .container { max-width: 420px; margin: 0 auto; }
        .card { background: #1F2937; border-radius: 12px; padding: 20px; margin-bottom: 16px; box-shadow: 0 10px 20px rgba(0,0,0,0.4); }
        .card-title { font-size: 20px; margin-bottom: 16px; }
        .form-group { margin-bottom: 12px; }
        label { font-size: 14px; display: block; margin-bottom: 4px; }
        input, select { width: 100%; padding: 8px; border-radius: 8px; border: 1px solid #374151; background: #111827; color: #E5E7EB; }
        .btn { display: inline-block; padding: 8px 16px; border-radius: 999px; border: none; cursor: pointer; font-size: 14px; }
        .btn-primary { background: #2563EB; color: #F9FAFB; }
        .btn-primary:hover { background: #1D4ED8; }
        .alert { padding: 8px 12px; border-radius: 8px; margin-bottom: 12px; font-size: 14px; }
        .alert-error { background: #7F1D1D; color: #FECACA; }
        .helper { font-size: 12px; color: #9CA3AF; margin-top: 8px; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-title">
            <?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>

        <?php if ($errorText !== null): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($errorText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/web/login">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">

            <div class="form-group">
                <label for="username">
                    <?= htmlspecialchars($usernameLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">
                    <?= htmlspecialchars($passwordLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="lang">
                    <?= htmlspecialchars($languageLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </label>
                <select id="lang" name="lang">
                    <?php foreach ($availableLocales as $lang): ?>
                        <option value="<?= htmlspecialchars($lang, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            <?= $lang === $locale ? ' selected' : '' ?>>
                            <?= strtoupper($lang) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <?= htmlspecialchars($buttonText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </button>

            <div class="helper">
                <?= htmlspecialchars($helperText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>

            <div class="helper">
                <a href="/web/polls">
                    <?= htmlspecialchars($translator->trans('ui.web.login.back_to_polls'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
