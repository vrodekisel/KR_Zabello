<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreatePoll;

final class CreatePollRequest
{
    private int $creatorUserId;
    private string $titleKey;
    private ?string $descriptionKey;
    private string $contextType;
    private string $contextKey;
    /** @var string[] */
    private array $optionLabelKeys;
    private ?\DateTimeImmutable $expiresAt;

    /**
     * @param string[] $optionLabelKeys
     */
    public function __construct(
        int $creatorUserId,
        string $titleKey,
        ?string $descriptionKey,
        string $contextType,
        string $contextKey,
        array $optionLabelKeys,
        ?\DateTimeImmutable $expiresAt
    ) {
        $this->creatorUserId   = $creatorUserId;
        $this->titleKey        = $titleKey;
        $this->descriptionKey  = $descriptionKey;
        $this->contextType     = $contextType;
        $this->contextKey      = $contextKey;
        $this->optionLabelKeys = $optionLabelKeys;
        $this->expiresAt       = $expiresAt;
    }

    public function getCreatorUserId(): int
    {
        return $this->creatorUserId;
    }

    public function getTitleKey(): string
    {
        return $this->titleKey;
    }

    public function getDescriptionKey(): ?string
    {
        return $this->descriptionKey;
    }

    public function getContextType(): string
    {
        return $this->contextType;
    }

    public function getContextKey(): string
    {
        return $this->contextKey;
    }

    /**
     * @return string[]
     */
    public function getOptionLabelKeys(): array
    {
        return $this->optionLabelKeys;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
