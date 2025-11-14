<?php
class TaskCollaborator
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    public function add(int $taskId, int $userId, string $role = 'viewer'): bool
    {
        $stmt = $this->db->prepare('INSERT INTO task_collaborators (task_id, user_id, role) VALUES (:task_id, :user_id, :role)
            ON DUPLICATE KEY UPDATE role = VALUES(role)');
        return $stmt->execute([':task_id' => $taskId, ':user_id' => $userId, ':role' => $role]);
    }
    public function remove(int $taskId, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM task_collaborators WHERE task_id = :task_id AND user_id = :user_id');
        return $stmt->execute([':task_id' => $taskId, ':user_id' => $userId]);
    }
    public function getCollaborators(int $taskId): array
    {
        $stmt = $this->db->prepare('SELECT tc.*, u.username FROM task_collaborators tc JOIN users u ON tc.user_id = u.id WHERE tc.task_id = :task_id');
        $stmt->execute([':task_id' => $taskId]);
        return $stmt->fetchAll();
    }
    public function userCanEdit(int $taskId, int $userId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM task_collaborators WHERE task_id = :task_id AND user_id = :user_id AND role IN ('owner','editor')");
        $stmt->execute([':task_id' => $taskId, ':user_id' => $userId]);
        return (bool)$stmt->fetchColumn();
    }
}
