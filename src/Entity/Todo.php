<?php

namespace App\Entity;

use App\Dto\TodoDto;
use App\Repository\TodoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: TodoRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[OA\Schema(required: ['title', 'description', 'status'])]
class Todo {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[OA\Property(format: 'int64')]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[OA\Property(nullable: false)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[OA\Property(nullable: false)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[OA\Property(nullable: false)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    #[Context([DateTimeNormalizer::FORMAT_KEY => \DateTimeInterface::RFC3339])]
    #[OA\Property(nullable: false)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    #[Context([DateTimeNormalizer::FORMAT_KEY => \DateTimeInterface::RFC3339])]
    #[OA\Property(nullable: false)]
    private ?\DateTimeInterface $updatedAt = null;

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(int $id): static {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(string $title): static {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(string $description): static {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string {
        return $this->status;
    }

    public function setStatus(string $status): static {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface {
        return $this->updatedAt;
    }


    #[ORM\PrePersist]
    public function prePersist(): void {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void {
        $this->updatedAt = new \DateTime();
    }

    public function updateFrom(TodoDto $other): void {
        $this->title = $other->getTitle();
        $this->description = $other->getDescription();
        $this->status = $other->getStatus();
    }
}
