<?php

namespace App\Repository;

use App\Model\WorkShift;

class WorkShiftRepository extends BaseRepository
{
    public function findByMonth(int $year, int $month): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT ws.*, u.display_name
             FROM work_shifts ws
             JOIN users u ON u.id = ws.user_id
             WHERE YEAR(ws.shift_date) = ? AND MONTH(ws.shift_date) = ?
             ORDER BY ws.shift_date, ws.start_time"
        );
        $stmt->execute([$year, $month]);
        $list = [];
        foreach ($stmt->fetchAll() as $row) {
            $list[] = new WorkShift($row);
        }
        return $list;
    }

    public function findById(int $id): ?WorkShift
    {
        $stmt = $this->pdo->prepare('SELECT * FROM work_shifts WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new WorkShift($row) : null;
    }

    public function create(WorkShift $shift): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO work_shifts (user_id, shift_date, start_time, end_time, notes) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $shift->getUserId(),
            $shift->getShiftDate(),
            $shift->getStartTime(),
            $shift->getEndTime(),
            $shift->getNotes(),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(WorkShift $shift): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE work_shifts SET user_id=?, shift_date=?, start_time=?, end_time=?, notes=? WHERE id=?'
        );
        return $stmt->execute([
            $shift->getUserId(),
            $shift->getShiftDate(),
            $shift->getStartTime(),
            $shift->getEndTime(),
            $shift->getNotes(),
            $shift->getId(),
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM work_shifts WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function findByDateRange(string $startDate, string $endDate): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT ws.*, u.display_name
             FROM work_shifts ws
             JOIN users u ON u.id = ws.user_id
             WHERE ws.shift_date BETWEEN ? AND ?
             ORDER BY ws.shift_date, ws.start_time"
        );
        $stmt->execute([$startDate, $endDate]);
        $list = [];
        foreach ($stmt->fetchAll() as $row) {
            $list[] = new WorkShift($row);
        }
        return $list;
    }

    public function deleteByDate(string $date): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM work_shifts WHERE shift_date = ?');
        return $stmt->execute([$date]);
    }
}
