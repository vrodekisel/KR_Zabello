<?php

use App\Localization\Translator;

/** @var Translator $translator */
/** @var string $baseStyles */
/** @var string $topBarHtml */
/** @var string $pageTitle */
/** @var int $pollId */
/** @var string|null $status */
/** @var string|null $successMessage */
/** @var string|null $errorMessage */
/** @var bool $hasVoted */
/** @var string $pollTitle */
/** @var string|null $pollDescription */
/** @var array<int,array{id:int,label:string,count:int,percent:float}> $optionsView */
/** @var int $totalVotes */
/** @var string $voteButtonText */
/** @var string $alreadyVotedText */
/** @var string $resultsTitle */
/** @var string $votesLabel */
/** @var string $totalVotesLabel */
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
    <div class="back-link">
        <a href="/web/polls" class="btn btn-secondary btn-small">
            <?= htmlspecialchars($translator->trans('ui.web.poll.back_to_list'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </a>
    </div>

    <?php if ($successMessage !== null): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($successMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
    <?php elseif ($errorMessage !== null): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($errorMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-title">
            <?= htmlspecialchars($pollTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </div>

        <?php if ($pollDescription !== null && $pollDescription !== ''): ?>
            <div class="card-subtitle">
                <?= htmlspecialchars($pollDescription, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/web/poll/vote">
            <input type="hidden" name="poll_id" value="<?= (int) $pollId ?>">

            <ul class="option-list">
                <?php foreach ($optionsView as $option): ?>
                    <li class="option-item">
                        <label>
                            <?php if ($hasVoted): ?>
                                <input type="radio" disabled>
                            <?php else: ?>
                                <input type="radio" name="option_id" value="<?= (int) $option['id'] ?>">
                            <?php endif; ?>
                            <?= htmlspecialchars($option['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($hasVoted): ?>
                <button type="submit" class="btn btn-primary" disabled>
                    <?= htmlspecialchars($alreadyVotedText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </button>
            <?php else: ?>
                <button type="submit" class="btn btn-primary">
                    <?= htmlspecialchars($voteButtonText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </button>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($hasVoted && $totalVotes > 0): ?>
        <div class="card">
            <div class="results-header">
                <?= htmlspecialchars($resultsTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </div>

            <?php foreach ($optionsView as $option): ?>
                <li class="option-item">
                    <label>
                        <?php if ($hasVoted): ?>
                            <input type="radio" disabled>
                        <?php else: ?>
                            <input
                                type="radio"
                                name="option_id"
                                value="<?= (int) $option['id'] ?>"
                                required
                            >
                        <?php endif; ?>
                        <?= htmlspecialchars($option['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </label>
                </li>
            <?php endforeach; ?>


            <div class="result-meta">
                <?= htmlspecialchars($totalVotesLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                : <?= (int) $totalVotes ?>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
