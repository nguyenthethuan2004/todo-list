<?php
class Tag
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    public function getOrCreate(string $name): int
    {
        $name = strtoupper(trim($name));
        if ($name === '') {
            return 0;
        }
        $stmt = $this->db->prepare('SELECT id FROM tags WHERE name = :name');
        $stmt->execute([':name' => $name]);
        $tag = $stmt->fetch();
        if ($tag) {
            return (int)$tag['id'];
        }
        $insert = $this->db->prepare('INSERT INTO tags (name) VALUES (:name)');
        $insert->execute([':name' => $name]);
        return (int)$this->db->lastInsertId();
    }
    public function findIdByName(string $name): ?int
    {
        $stmt = $this->db->prepare('SELECT id FROM tags WHERE name = :name');
        $stmt->execute([':name' => strtoupper(trim($name))]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id'] : null;
    }
    public function syncForTask(int $taskId, array $tagNames): void
    {
        $tagIds = [];
        foreach ($tagNames as $name) {
            $id = $this->getOrCreate($name);
            if ($id) {
                $tagIds[] = $id;
            }
        }
        $this->db->prepare('DELETE FROM task_tags WHERE task_id = :task_id')->execute([':task_id' => $taskId]);
        if (empty($tagIds)) {
            return;
        }
        $stmt = $this->db->prepare('INSERT INTO task_tags (task_id, tag_id) VALUES (:task_id, :tag_id)');
        foreach (array_unique($tagIds) as $tagId) {
            $stmt->execute([':task_id' => $taskId, ':tag_id' => $tagId]);
        }
    }
    public function getForTask(int $taskId): array
    {
        $stmt = $this->db->prepare('SELECT t.* FROM tags t JOIN task_tags tt ON t.id = tt.tag_id WHERE tt.task_id = :task_id ORDER BY t.name');
        $stmt->execute([':task_id' => $taskId]);
        return $stmt->fetchAll();
    }
    public function getForTasks(array $taskIds): array
    {
        if (empty($taskIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
        $stmt = $this->db->prepare("SELECT tt.task_id, t.name FROM task_tags tt JOIN tags t ON tt.tag_id = t.id WHERE tt.task_id IN ($placeholders) ORDER BY t.name");
        $stmt->execute($taskIds);
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[(int)$row['task_id']][] = $row['name'];
        }
        return $result;
    }
}
