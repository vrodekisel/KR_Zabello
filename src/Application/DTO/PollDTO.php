<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\Poll;

final class PollDTO
{
    private int $id;
    private string $titleKey;
    private ?string $descriptionKey;

    /**
     * В прикладном слое это называется "contextType",
     * но по факту сюда попадает доменный contentType (map|mod).
     */
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
        $id = $poll->getId();
        if ($id === null) {
            // В нормальной работе id должен быть выставлен репозиторием
            // (в тестах это делает in-memory репозиторий до вызова fromEntity()).
            throw new \RuntimeException('poll.dto.error.id_is_null');
        }

        // На текущем этапе сущность Poll не хранит в себе список опций,
        // они живут в репозиториях. Поэтому отдаём пустой список.
        $optionDTOs = [];

        return new self(
            $id,
            $poll->getTitleKey(),
            $poll->getDescriptionKey(),
            $poll->getContentType(),   // <-- маппим contentType домена в contextType DTO
            $poll->getStatus(),
            $poll->getEndsAt(),        // <-- интерпретируем endsAt как expiresAt
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
