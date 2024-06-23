<?php

namespace App\Dto;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(required: ['title', 'description', 'status'])]
class TodoDto {

    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 100)]
    #[OA\Property(nullable: false)]
    private ?string $title = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 255)]
    #[OA\Property(nullable: false)]
    private ?string $description = null;

    #[Assert\NotBlank]
    #[Assert\Choice(['pending', 'completed'])]
    #[OA\Property(nullable: false)]
    private ?string $status = null;

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(?string $title): void {
        $this->title = $title;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getStatus(): ?string {
        return $this->status;
    }

    public function setStatus(?string $status): void {
        $this->status = $status;
    }
}