<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\Poll;

final class PollDTO
{
    private int $id;
    private string $titleKey;
    private ?string $descriptionKey;
    private string $contextType;
    private string $status;
    private ?\DateTimeImmutable $expiresAt;
    /** @var OptionDTO[] */
    private array $options;

    /**
     * @param OptionDTO[] $options
     */
    public function __construct(
        int $id,
        string $titleKey,
        ?string $descriptionKey,
        string $contextType,
        string $status,
        ?\DateTimeImmutable $expiresAt,
        array $options
    ) {
        $this->id = $id;
        $this->titleKey = $titleKey;
        $this->descriptionKey = $descriptionKey;
        $this->contextType = $contextType;
        $this->status = $status;
        $this->expiresAt = $expiresAt;
        $this->options = $options;
    }

    public static function fromEntity(Poll $poll): self
    {
        $optionDTOs = [];

        foreach ($poll->getOptions() as $option) {
            $optionDTOs[] = OptionDTO::fromEntity($option);
        }

        return new self(
            $poll->getId(),
            $poll->getTitleKey(),
            $poll->getDescriptionKey(),
            $poll->getContextType(),
            $poll->getStatus(),
            $poll->getExpiresAt(),
            $optionDTOs
        );
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /**
     * @return OptionDTO[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
