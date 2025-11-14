<?php
class TaskComment
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    public function create(int $taskId, int $userId, string $content): bool
    {
        $stmt = $this->db->prepare('INSERT INTO task_comments (task_id, user_id, content) VALUES (:task_id, :user_id, :content)');
        return $stmt->execute([
            ':task_id' => $taskId,
            ':user_id' => $userId,
            ':content' => $content,
        ]);
    }
    public function getByTask(int $taskId): array
    {
        $stmt = $this->db->prepare('SELECT c.*, u.username FROM task_comments c JOIN users u ON c.user_id = u.id WHERE c.task_id = :task_id ORDER BY c.created_at DESC');
        $stmt->execute([':task_id' => $taskId]);
        return $stmt->fetchAll();
    }
}
