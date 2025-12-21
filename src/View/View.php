<?php

declare(strict_types=1);

namespace App\View;

use App\Localization\Translator;

final class View
{
    private Translator $translator;

    /** @var string[] */
    private array $availableLocales;

    /**
     * @param string[] $availableLocales
     */
    public function __construct(Translator $translator, array $availableLocales)
    {
        $this->translator      = $translator;
        $this->availableLocales = $availableLocales;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function render(string $template, array $data = []): void
    {
        $viewFile = __DIR__ . '/../../views/' . $template . '.php';

        if (!is_file($viewFile)) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'View template not found: ' . $template;
            return;
        }

        $translator       = $this->translator;
        $availableLocales = $this->availableLocales;

        extract($data, EXTR_SKIP);

        require $viewFile;
    }
}
