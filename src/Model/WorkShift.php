<?php

namespace App\Model;

class WorkShift
{
    private int $id;
    private int $userId;
    private string $shiftDate;
    private string $startTime;
    private string $endTime;
    private ?string $notes;
    private ?string $displayName;
    private string $createdAt;

    public function __construct(array $data = [])
    {
        $this->id          = (int)($data['id'] ?? 0);
        $this->userId      = (int)($data['user_id'] ?? 0);
        $this->shiftDate   = $data['shift_date'] ?? '';
        $this->startTime   = $data['start_time'] ?? '';
        $this->endTime     = $data['end_time'] ?? '';
        $this->notes       = $data['notes'] ?? null;
        $this->displayName = $data['display_name'] ?? null;
        $this->createdAt   = $data['created_at'] ?? date('Y-m-d H:i:s');
    }

    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }
    public function getUserId(): int { return $this->userId; }
    public function setUserId(int $userId): void { $this->userId = $userId; }
    public function getShiftDate(): string { return $this->shiftDate; }
    public function setShiftDate(string $date): void { $this->shiftDate = $date; }
    public function getStartTime(): string { return $this->startTime; }
    public function setStartTime(string $time): void { $this->startTime = $time; }
    public function getEndTime(): string { return $this->endTime; }
    public function setEndTime(string $time): void { $this->endTime = $time; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): void { $this->notes = $notes; }
    public function getDisplayName(): ?string { return $this->displayName; }
    public function setDisplayName(?string $name): void { $this->displayName = $name; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function getDurationHours(): float
    {
        $start = new \DateTime($this->shiftDate . ' ' . $this->startTime);
        $end   = new \DateTime($this->shiftDate . ' ' . $this->endTime);
        $diff  = $start->diff($end);
        return $diff->h + round($diff->i / 60, 2);
    }

    public function getDurationFormatted(): string
    {
        $start = new \DateTime($this->shiftDate . ' ' . $this->startTime);
        $end   = new \DateTime($this->shiftDate . ' ' . $this->endTime);
        $diff  = $start->diff($end);
        return $diff->h . 'u' . ($diff->i ? $diff->i . 'm' : '');
    }
}
