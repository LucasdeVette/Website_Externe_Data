<?php

namespace App\Model;

class Supplier
{
    private int $id;
    private string $name;
    private ?string $contactPerson;
    private ?string $email;
    private ?string $phone;
    private ?string $address;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(array $data = [])
    {
        $this->id            = (int) ($data['id'] ?? 0);
        $this->name          = $data['name'] ?? '';
        $this->contactPerson = $data['contact_person'] ?? null;
        $this->email         = $data['email'] ?? null;
        $this->phone         = $data['phone'] ?? null;
        $this->address       = $data['address'] ?? null;
        $this->createdAt     = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedAt     = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }

    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }
    public function getContactPerson(): ?string { return $this->contactPerson; }
    public function setContactPerson(?string $cp): void { $this->contactPerson = $cp; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): void { $this->email = $email; }
    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): void { $this->phone = $phone; }
    public function getAddress(): ?string { return $this->address; }
    public function setAddress(?string $address): void { $this->address = $address; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }
}
