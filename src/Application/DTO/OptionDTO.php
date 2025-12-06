<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\Option;

final class OptionDTO
{
    private int $id;
    private int $pollId;
    private string $labelKey;
    private int $position;

    public function __construct(
        int $id,
        int $pollId,
        string $labelKey,
        int $position
    ) {
        $this->id = $id;
        $this->pollId = $pollId;
        $this->labelKey = $labelKey;
        $this->position = $position;
    }

    public static function fromEntity(Option $option): self
    {
        return new self(
            $option->getId(),
            $option->getPollId(),
            $option->getLabelKey(),
            $option->getPosition()
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPollId(): int
    {
        return $this->pollId;
    }

    public function getLabelKey(): string
    {
        return $this->labelKey;
    }

    public function getPosition(): int
    {
        return $this->position;
    }
}
