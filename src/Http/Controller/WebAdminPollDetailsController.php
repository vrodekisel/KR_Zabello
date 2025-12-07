<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Domain\Repository\PollRepository;
use App\Localization\Translator;
use App\View\View;

final class WebAdminPollDetailsController
{
    private Translator $translator;
    private View $view;
    private PollRepository $polls;

    public function __construct(
        Translator $translator,
        View $view,
        PollRepository $pollRepository
    ) {
        $this->translator = $translator;
        $this->view       = $view;
        $this->polls      = $pollRepository;
    }

    /**
     * GET /web/admin/poll?poll_id=...
     */
    public function show(): void
    {
        $pollId = isset($_GET['poll_id']) ? (int) $_GET['poll_id'] : 0;

        if ($pollId <= 0) {
            http_response_code(400);
            echo 'Poll id is invalid.';
            return;
        }

        $poll = $this->polls->findById($pollId);
        if ($poll === null) {
            http_response_code(404);
            echo 'Poll not found.';
            return;
        }

        // Берём варианты опроса и превращаем в массивы для шаблона
        $optionEntities = $this->polls->findOptionsByPollId($pollId);
        $options        = [];

        foreach ($optionEntities as $option) {
            if (method_exists($option, 'toArray')) {
                /** @var array<string,mixed> $data */
                $data      = $option->toArray();
                $options[] = $data;
            }
        }

        $this->view->render('admin_poll_show', [
            'translator' => $this->translator,
            'pageTitle'  => $this->translator->trans('ui.web.admin.poll.show.page_title'),
            'heading'    => $this->translator->trans('ui.web.admin.poll.show.heading'),
            'poll'       => $poll,
            'options'    => $options,
        ]);
    }
}
