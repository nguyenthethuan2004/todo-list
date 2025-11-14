<?php

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table");
    $stmt->execute([':table' => $table]);
    return (bool) $stmt->fetchColumn();
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column");
    $stmt->execute([':table' => $table, ':column' => $column]);
    return (bool) $stmt->fetchColumn();
}

function addColumnIfMissing(PDO $pdo, string $table, string $column, string $alterSql): void
{
    if (!columnExists($pdo, $table, $column)) {
        $pdo->exec($alterSql);
    }
}

function createTableIfNotExists(PDO $pdo, string $table, string $sql): void
{
    if (!tableExists($pdo, $table)) {
        $pdo->exec($sql);
    }
}

function ensureSchema(PDO $pdo): void
{
    addColumnIfMissing(
        $pdo,
        'users',
        'theme',
        "ALTER TABLE users ADD COLUMN theme ENUM('light','dark') NOT NULL DEFAULT 'light'"
    );

    addColumnIfMissing(
        $pdo,
        'tasks',
        'team_id',
        "ALTER TABLE tasks ADD COLUMN team_id INT UNSIGNED NULL"
    );

    addColumnIfMissing(
        $pdo,
        'tasks',
        'priority',
        "ALTER TABLE tasks ADD COLUMN priority ENUM('high','medium','low') NOT NULL DEFAULT 'medium'"
    );

    addColumnIfMissing(
        $pdo,
        'tasks',
        'auto_priority',
        "ALTER TABLE tasks ADD COLUMN auto_priority TINYINT(1) NOT NULL DEFAULT 1"
    );

    createTableIfNotExists($pdo, 'teams', "
        CREATE TABLE teams (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            owner_id INT UNSIGNED NOT NULL,
            name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_teams_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    createTableIfNotExists($pdo, 'team_members', "
        CREATE TABLE team_members (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            team_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            role ENUM('owner','editor','viewer') NOT NULL DEFAULT 'viewer',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_team_user (team_id, user_id),
            CONSTRAINT fk_team_members_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
            CONSTRAINT fk_team_members_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    createTableIfNotExists($pdo, 'task_collaborators', "
        CREATE TABLE task_collaborators (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            task_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            role ENUM('owner','editor','viewer') NOT NULL DEFAULT 'viewer',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_task_user (task_id, user_id),
            CONSTRAINT fk_task_collab_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
            CONSTRAINT fk_task_collab_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    createTableIfNotExists($pdo, 'subtasks', "
        CREATE TABLE subtasks (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            task_id INT UNSIGNED NOT NULL,
            title VARCHAR(150) NOT NULL,
            is_done TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_subtasks_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    createTableIfNotExists($pdo, 'reminders', "
        CREATE TABLE reminders (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            task_id INT UNSIGNED NOT NULL,
            channel ENUM('email','browser') NOT NULL,
            remind_at DATETIME NOT NULL,
            status ENUM('pending','sent','cancelled') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_reminders_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    createTableIfNotExists($pdo, 'reminder_logs', "
        CREATE TABLE reminder_logs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            reminder_id INT UNSIGNED NOT NULL,
            sent_at DATETIME NULL,
            message TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_reminder_logs FOREIGN KEY (reminder_id) REFERENCES reminders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    createTableIfNotExists($pdo, 'task_comments', "
        CREATE TABLE task_comments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            task_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_comments_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
            CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    createTableIfNotExists($pdo, 'task_attachments', "
        CREATE TABLE task_attachments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            task_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_attachments_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
            CONSTRAINT fk_attachments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    createTableIfNotExists($pdo, 'tags', "
        CREATE TABLE tags (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    createTableIfNotExists($pdo, 'task_tags', "
        CREATE TABLE task_tags (
            task_id INT UNSIGNED NOT NULL,
            tag_id INT UNSIGNED NOT NULL,
            PRIMARY KEY (task_id, tag_id),
            CONSTRAINT fk_task_tags_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
            CONSTRAINT fk_task_tags_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}

