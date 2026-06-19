<?php

namespace App\Model;

class User
{
    private int $id;
    private string $username;
    private string $passwordHash;
    private string $displayName;
    private ?string $email;
    private string $createdAt;

    public function __construct(array $data = [])
    {
        $this->id           = (int) ($data['id'] ?? 0);
        $this->username     = $data['username'] ?? '';
        $this->passwordHash = $data['password_hash'] ?? '';
        $this->displayName  = $data['display_name'] ?? '';
        $this->email        = $data['email'] ?? null;
        $this->createdAt    = $data['created_at'] ?? date('Y-m-d H:i:s');
    }

    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }
    public function getUsername(): string { return $this->username; }
    public function setUsername(string $username): void { $this->username = $username; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function setPasswordHash(string $hash): void { $this->passwordHash = $hash; }
    public function getDisplayName(): string { return $this->displayName; }
    public function setDisplayName(string $name): void { $this->displayName = $name; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): void { $this->email = $email; }
    public function getCreatedAt(): string { return $this->createdAt; }
}
