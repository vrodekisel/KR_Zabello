<?php

declare(strict_types=1);

namespace App\Localization;

/**
 * Простейший переводчик, который грузит lang/{locale}.php
 * и умеет подставлять плейсхолдеры {name}.
 */
final class Translator
{
    private string $locale;
    private string $fallbackLocale;

    /** @var array<string,string> */
    private array $messages = [];

    /** @var array<string,string> */
    private array $fallbackMessages = [];

    public function __construct(string $locale, string $fallbackLocale, string $langPath)
    {
        $this->locale         = $locale;
        $this->fallbackLocale = $fallbackLocale;

        $this->messages        = $this->loadLangFile($langPath, $locale);
        $this->fallbackMessages = $locale === $fallbackLocale
            ? $this->messages
            : $this->loadLangFile($langPath, $fallbackLocale);
    }

    /**
     * @return array<string,string>
     */
    private function loadLangFile(string $langPath, string $locale): array
    {
        $file = rtrim($langPath, '/\\') . DIRECTORY_SEPARATOR . $locale . '.php';
        if (!file_exists($file)) {
            return [];
        }

        $data = require $file;
        if (!is_array($data)) {
            return [];
        }

        $result = [];
        foreach ($data as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param array<string,string|int|float> $replacements
     */
    public function trans(string $key, array $replacements = []): string
    {
        $text = $this->messages[$key]
            ?? $this->fallbackMessages[$key]
            ?? $key;

        if ($replacements === []) {
            return $text;
        }

        $replacePairs = [];
        foreach ($replacements as $name => $value) {
            $replacePairs['{' . $name . '}'] = (string) $value;
        }

        return strtr($text, $replacePairs);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
