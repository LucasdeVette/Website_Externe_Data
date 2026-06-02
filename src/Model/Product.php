<?php

namespace App\Model;

class Product
{
    private int $id;
    private int $categoryId;
    private ?int $supplierId;
    private string $name;
    private ?string $description;
    private float $price;
    private int $stock;
    private int $minStock;
    private ?string $barcode;
    private ?string $imageUrl;
    private bool $isActive;
    private string $createdAt;
    private string $updatedAt;

    private ?Category $category = null;
    private ?Supplier $supplier = null;

    public function __construct(array $data = [])
    {
        $this->id          = (int) ($data['id'] ?? 0);
        $this->categoryId  = (int) ($data['category_id'] ?? 0);
        $this->supplierId  = isset($data['supplier_id']) ? (int) $data['supplier_id'] : null;
        $this->name        = $data['name'] ?? '';
        $this->description = $data['description'] ?? null;
        $this->price       = (float) ($data['price'] ?? 0);
        $this->stock       = (int) ($data['stock'] ?? 0);
        $this->minStock    = (int) ($data['min_stock'] ?? 10);
        $this->barcode     = $data['barcode'] ?? null;
        $this->imageUrl    = $data['image_url'] ?? null;
        $this->isActive    = isset($data['is_active']) ? (bool) $data['is_active'] : true;
        $this->createdAt   = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedAt   = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }

    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }
    public function getCategoryId(): int { return $this->categoryId; }
    public function setCategoryId(int $id): void { $this->categoryId = $id; }
    public function getSupplierId(): ?int { return $this->supplierId; }
    public function setSupplierId(?int $id): void { $this->supplierId = $id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $desc): void { $this->description = $desc; }
    public function getPrice(): float { return $this->price; }
    public function setPrice(float $price): void { $this->price = $price; }
    public function getStock(): int { return $this->stock; }
    public function setStock(int $stock): void { $this->stock = $stock; }
    public function getMinStock(): int { return $this->minStock; }
    public function setMinStock(int $min): void { $this->minStock = $min; }
    public function getBarcode(): ?string { return $this->barcode; }
    public function setBarcode(?string $code): void { $this->barcode = $code; }
    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function setImageUrl(?string $url): void { $this->imageUrl = $url; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $active): void { $this->isActive = $active; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }

    public function getCategory(): ?Category { return $this->category; }
    public function setCategory(?Category $cat): void { $this->category = $cat; }
    public function getSupplier(): ?Supplier { return $this->supplier; }
    public function setSupplier(?Supplier $sup): void { $this->supplier = $sup; }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->minStock;
    }
}
