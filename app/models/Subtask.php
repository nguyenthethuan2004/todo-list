<?php
class Subtask
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    public function create(int $taskId, string $title): bool
    {
        $stmt = $this->db->prepare('INSERT INTO subtasks (task_id, title) VALUES (:task_id, :title)');
        return $stmt->execute([':task_id' => $taskId, ':title' => $title]);
    }
    public function getByTask(int $taskId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM subtasks WHERE task_id = :task_id ORDER BY id ASC');
        $stmt->execute([':task_id' => $taskId]);
        return $stmt->fetchAll();
    }
    public function toggle(int $id, int $taskId): bool
    {
        $stmt = $this->db->prepare('UPDATE subtasks SET is_done = 1 - is_done WHERE id = :id AND task_id = :task_id');
        return $stmt->execute([':id' => $id, ':task_id' => $taskId]);
    }
    public function delete(int $id, int $taskId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM subtasks WHERE id = :id AND task_id = :task_id');
        return $stmt->execute([':id' => $id, ':task_id' => $taskId]);
    }
    public function statsForTask(int $taskId): array
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total, SUM(is_done) AS done FROM subtasks WHERE task_id = :task_id');
        $stmt->execute([':task_id' => $taskId]);
        $row = $stmt->fetch();
        return [
            'total' => (int)($row['total'] ?? 0),
            'done' => (int)($row['done'] ?? 0),
        ];
    }
    public function statsForTasks(array $taskIds): array
    {
        if (empty($taskIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
        $stmt = $this->db->prepare("SELECT task_id, COUNT(*) AS total, SUM(is_done) AS done FROM subtasks WHERE task_id IN ($placeholders) GROUP BY task_id");
        $stmt->execute($taskIds);
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[(int)$row['task_id']] = [
                'total' => (int)$row['total'],
                'done' => (int)($row['done'] ?? 0),
            ];
        }
        return $result;
    }
}
