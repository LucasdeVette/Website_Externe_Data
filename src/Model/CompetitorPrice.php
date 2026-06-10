<?php

namespace App\Model;

class CompetitorPrice
{
    private int $id;
    private int $productId;
    private int $storeId;
    private float $price;
    private string $recordedAt;
    private string $createdAt;
    private string $updatedAt;

    private ?CompetitorStore $store = null;
    private ?string $productName = null;

    public function __construct(array $data = [])
    {
        $this->id         = (int) ($data['id'] ?? 0);
        $this->productId  = (int) ($data['product_id'] ?? 0);
        $this->storeId    = (int) ($data['store_id'] ?? 0);
        $this->price      = (float) ($data['price'] ?? 0);
        $this->recordedAt = $data['recorded_at'] ?? date('Y-m-d');
        $this->createdAt  = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedAt  = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }

    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }
    public function getProductId(): int { return $this->productId; }
    public function setProductId(int $id): void { $this->productId = $id; }
    public function getStoreId(): int { return $this->storeId; }
    public function setStoreId(int $id): void { $this->storeId = $id; }
    public function getPrice(): float { return $this->price; }
    public function setPrice(float $price): void { $this->price = $price; }
    public function getRecordedAt(): string { return $this->recordedAt; }
    public function setRecordedAt(string $date): void { $this->recordedAt = $date; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }

    public function getStore(): ?CompetitorStore { return $this->store; }
    public function setStore(?CompetitorStore $s): void { $this->store = $s; }

    public function getProductName(): ?string { return $this->productName; }
    public function setProductName(?string $name): void { $this->productName = $name; }
}
