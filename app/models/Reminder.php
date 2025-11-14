<?php
class Reminder
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    public function create(int $taskId, string $channel, string $remindAt): bool
    {
        $stmt = $this->db->prepare('INSERT INTO reminders (task_id, channel, remind_at) VALUES (:task_id, :channel, :remind_at)');
        return $stmt->execute([
            ':task_id' => $taskId,
            ':channel' => $channel,
            ':remind_at' => $remindAt,
        ]);
    }
    public function getByTask(int $taskId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM reminders WHERE task_id = :task_id ORDER BY remind_at ASC');
        $stmt->execute([':task_id' => $taskId]);
        return $stmt->fetchAll();
    }
    public function log(int $reminderId, string $message, ?string $status = null): bool
    {
        $stmt = $this->db->prepare('INSERT INTO reminder_logs (reminder_id, sent_at, message) VALUES (:reminder_id, NOW(), :message)');
        $stmt->execute([':reminder_id' => $reminderId, ':message' => $message]);
        if ($status) {
            $update = $this->db->prepare('UPDATE reminders SET status = :status WHERE id = :id');
            $update->execute([':status' => $status, ':id' => $reminderId]);
        }
        return true;
    }
    public function delete(int $id, int $taskId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM reminders WHERE id = :id AND task_id = :task_id');
        return $stmt->execute([':id' => $id, ':task_id' => $taskId]);
    }
}
