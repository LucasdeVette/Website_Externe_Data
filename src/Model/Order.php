<?php

namespace App\Model;

class Order
{
    private int $id;
    private ?int $supplierId;
    private string $orderDate;
    private string $status;
    private ?string $notes;
    private string $createdAt;
    private string $updatedAt;

    private ?Supplier $supplier = null;
    private array $items = [];

    public function __construct(array $data = [])
    {
        $this->id           = (int) ($data['id'] ?? 0);
        $this->supplierId   = isset($data['supplier_id']) ? (int) $data['supplier_id'] : null;
        $this->orderDate    = $data['order_date'] ?? date('Y-m-d');
        $this->status     = $data['status'] ?? 'pending';
        $this->notes      = $data['notes'] ?? null;
        $this->createdAt  = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedAt  = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }

    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }
    public function getSupplierId(): ?int { return $this->supplierId; }
    public function setSupplierId(?int $id): void { $this->supplierId = $id; }
    public function getOrderDate(): string { return $this->orderDate; }
    public function setOrderDate(string $date): void { $this->orderDate = $date; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): void { $this->notes = $notes; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }

    public function getSupplier(): ?Supplier { return $this->supplier; }
    public function setSupplier(?Supplier $sup): void { $this->supplier = $sup; }
    public function getItems(): array { return $this->items; }
    public function setItems(array $items): void { $this->items = $items; }
    public function addItem(array $item): void { $this->items[] = $item; }

    public function getItemCount(): int
    {
        return count($this->items);
    }

    public function getTotalAmount(): float
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['unit_price'] * $item['quantity'];
        }
        return $total;
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending'   => 'In afwachting',
            'confirmed' => 'Bevestigd',
            'shipped'   => 'Verzonden',
            'delivered' => 'Geleverd',
            'cancelled' => 'Geannuleerd',
            default     => $this->status,
        };
    }
}
