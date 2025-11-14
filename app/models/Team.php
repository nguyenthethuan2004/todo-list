<?php
class Team
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    public function create(int $ownerId, string $name): bool
    {
        $stmt = $this->db->prepare('INSERT INTO teams (owner_id, name) VALUES (:owner_id, :name)');
        $ok = $stmt->execute([':owner_id' => $ownerId, ':name' => $name]);
        if ($ok) {
            $teamId = (int)$this->db->lastInsertId();
            $this->addMember($teamId, $ownerId, 'owner');
        }
        return $ok;
    }
    public function addMember(int $teamId, int $userId, string $role = 'viewer'): bool
    {
        $stmt = $this->db->prepare('INSERT INTO team_members (team_id, user_id, role) VALUES (:team_id, :user_id, :role)
            ON DUPLICATE KEY UPDATE role = VALUES(role)');
        return $stmt->execute([':team_id' => $teamId, ':user_id' => $userId, ':role' => $role]);
    }
    public function getTeamsForUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT t.*, tm.role FROM teams t JOIN team_members tm ON t.id = tm.team_id WHERE tm.user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
