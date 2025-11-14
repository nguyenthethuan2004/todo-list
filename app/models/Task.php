<?php
class Task
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    public function create(array $data): int
    {
        $sql = "INSERT INTO tasks (user_id, team_id, title, description, due_date, priority, auto_priority, status)
                VALUES (:user_id, :team_id, :title, :description, :due_date, :priority, :auto_priority, :status)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            ':user_id' => $data['user_id'],
            ':team_id' => $data['team_id'],
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':due_date' => $data['due_date'],
            ':priority' => $data['priority'],
            ':auto_priority' => $data['auto_priority'],
            ':status' => $data['status'],
        ]);
        return $ok ? (int)$this->db->lastInsertId() : 0;
    }
    public function getAllByUser(int $userId, array $filters = [], ?int $limit = null, int $offset = 0): array
    {
        $query = "SELECT DISTINCT t.* FROM tasks t
            LEFT JOIN task_collaborators tc ON t.id = tc.task_id AND tc.user_id = :user_id
            WHERE (t.user_id = :user_id OR tc.user_id IS NOT NULL)";
        $params = [':user_id' => $userId];
        if (!empty($filters['status']) && in_array($filters['status'], ['pending','in_progress','completed'], true)) {
            $query .= " AND t.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['priority']) && in_array($filters['priority'], ['high','medium','low'], true)) {
            $query .= " AND t.priority = :priority";
            $params[':priority'] = $filters['priority'];
        }
        if (!empty($filters['search'])) {
            $query .= " AND (t.title LIKE :search OR t.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['tag_ids'])) {
            $tagPlaceholders = [];
            foreach (array_values($filters['tag_ids']) as $index => $tagId) {
                $key = ':tag_' . $index;
                $tagPlaceholders[] = $key;
                $params[$key] = $tagId;
            }
            $query .= " AND t.id IN (SELECT task_id FROM task_tags WHERE tag_id IN (" . implode(',', $tagPlaceholders) . "))";
        }
        $sort = strtolower($filters['sort'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
        $query .= " ORDER BY t.due_date $sort, t.created_at DESC";
        if ($limit !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function countAllByUser(int $userId, array $filters = []): int
    {
        $query = "SELECT COUNT(DISTINCT t.id) AS total FROM tasks t
            LEFT JOIN task_collaborators tc ON t.id = tc.task_id AND tc.user_id = :user_id
            WHERE (t.user_id = :user_id OR tc.user_id IS NOT NULL)";
        $params = [':user_id' => $userId];
        if (!empty($filters['status']) && in_array($filters['status'], ['pending','in_progress','completed'], true)) {
            $query .= " AND t.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['priority']) && in_array($filters['priority'], ['high','medium','low'], true)) {
            $query .= " AND t.priority = :priority";
            $params[':priority'] = $filters['priority'];
        }
        if (!empty($filters['search'])) {
            $query .= " AND (t.title LIKE :search OR t.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['tag_ids'])) {
            $tagPlaceholders = [];
            foreach (array_values($filters['tag_ids']) as $index => $tagId) {
                $key = ':tag_' . $index;
                $tagPlaceholders[] = $key;
                $params[$key] = $tagId;
            }
            $query .= " AND t.id IN (SELECT task_id FROM task_tags WHERE tag_id IN (" . implode(',', $tagPlaceholders) . "))";
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
    public function getByIdWithAccess(int $id, int $userId): ?array
    {
        $sql = "SELECT DISTINCT t.* FROM tasks t
            LEFT JOIN task_collaborators tc ON t.id = tc.task_id AND tc.user_id = :user_id
            WHERE t.id = :id AND (t.user_id = :user_id OR tc.user_id IS NOT NULL) LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $task = $stmt->fetch();
        return $task ?: null;
    }
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE tasks SET title = :title, description = :description, due_date = :due_date,
                status = :status, priority = :priority, auto_priority = :auto_priority, team_id = :team_id
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':due_date' => $data['due_date'],
            ':status' => $data['status'],
            ':priority' => $data['priority'],
            ':auto_priority' => $data['auto_priority'],
            ':team_id' => $data['team_id'],
            ':id' => $id,
        ]);
    }
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM tasks WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
    public function getKanban(int $userId): array
    {
        $tasks = $this->getAllByUser($userId);
        $columns = [
            'pending' => [],
            'in_progress' => [],
            'completed' => [],
        ];
        foreach ($tasks as $task) {
            $columns[$task['status']][] = $task;
        }
        return $columns;
    }
    public function getAnalytics(int $userId): array
    {
        $sql = "SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
            SUM(CASE WHEN status != 'completed' AND due_date < CURDATE() THEN 1 ELSE 0 END) AS overdue,
            AVG(DATEDIFF(due_date, created_at)) AS avg_duration
            FROM tasks
            WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch() ?: [];
    }
    public function getStatusBreakdown(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT status, COUNT(*) AS total FROM tasks WHERE user_id = :user_id GROUP BY status");
        $stmt->execute([':user_id' => $userId]);
        $result = ['pending' => 0, 'in_progress' => 0, 'completed' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['status']] = (int)$row['total'];
        }
        return $result;
    }
    public function getPriorityBreakdown(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT priority, COUNT(*) AS total FROM tasks WHERE user_id = :user_id GROUP BY priority");
        $stmt->execute([':user_id' => $userId]);
        $result = ['high' => 0, 'medium' => 0, 'low' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['priority']] = (int)$row['total'];
        }
        return $result;
    }
    public function getMonthlyCompletion(int $userId, int $months = 6): array
    {
        $since = (new \DateTimeImmutable('first day of this month'))->modify("-{$months} months")->format('Y-m-d');
        $stmt = $this->db->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS total
            FROM tasks
            WHERE user_id = :user_id AND status = 'completed' AND created_at >= :since
            GROUP BY ym ORDER BY ym");
        $stmt->execute([':user_id' => $userId, ':since' => $since]);
        return $stmt->fetchAll();
    }
}
