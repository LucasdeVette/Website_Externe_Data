<?php

namespace App\Model;

class Category
{
    private int $id;
    private string $name;
    private ?string $description;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(array $data = [])
    {
        $this->id          = (int) ($data['id'] ?? 0);
        $this->name        = $data['name'] ?? '';
        $this->description = $data['description'] ?? null;
        $this->createdAt   = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedAt   = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }
}
