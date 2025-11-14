<?php
class TaskAttachment
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    public function create(int $taskId, int $userId, string $fileName, string $filePath, string $mime): bool
    {
        $stmt = $this->db->prepare('INSERT INTO task_attachments (task_id, user_id, file_name, file_path, mime_type) VALUES (:task_id, :user_id, :file_name, :file_path, :mime)');
        return $stmt->execute([
            ':task_id' => $taskId,
            ':user_id' => $userId,
            ':file_name' => $fileName,
            ':file_path' => $filePath,
            ':mime' => $mime,
        ]);
    }
    public function getByTask(int $taskId): array
    {
        $stmt = $this->db->prepare('SELECT a.*, u.username FROM task_attachments a JOIN users u ON a.user_id = u.id WHERE a.task_id = :task_id ORDER BY a.created_at DESC');
        $stmt->execute([':task_id' => $taskId]);
        return $stmt->fetchAll();
    }
    public function delete(int $id, int $taskId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM task_attachments WHERE id = :id AND task_id = :task_id');
        return $stmt->execute([':id' => $id, ':task_id' => $taskId]);
    }
}
