<?php

namespace App\Model;

class CompetitorStore
{
    private int $id;
    private string $name;
    private ?string $website;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(array $data = [])
    {
        $this->id        = (int) ($data['id'] ?? 0);
        $this->name      = $data['name'] ?? '';
        $this->website   = $data['website'] ?? null;
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedAt = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }

    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }
    public function getWebsite(): ?string { return $this->website; }
    public function setWebsite(?string $website): void { $this->website = $website; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }
}
