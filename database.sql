/* VERSION 0.2 */

CREATE DATABASE taskflow;
USE taskflow;

-- Users Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pomodoro_sessions INT DEFAULT 0,
    pomodoro_time INT DEFAULT 25,
    shortbreak_time INT DEFAULT 5,
    longbreak_time INT DEFAULT 15,
    short_breaks INT DEFAULT 0,
    long_breaks INT DEFAULT 0,
    pomodoro_goal INT DEFAULT 8,
    is_verified TINYINT(1) DEFAULT 0,
    verification_code VARCHAR(255) DEFAULT NULL,
    forgot_code VARCHAR(255) DEFAULT NULL
);
-- Tags Table
CREATE TABLE tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    archived TINYINT(1) DEFAULT 0,
    UNIQUE KEY unique_tag_per_user (name, user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tasks Table
-- Tasks Table (updated with due_date)
CREATE TABLE tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status ENUM('pending', 'completed') NOT NULL DEFAULT 'pending',
    difficulty_level ENUM('Easy', 'Medium', 'Hard') NOT NULL DEFAULT 'Easy',
    difficulty_numeric FLOAT DEFAULT 0.0,
    parent_task_id INT NULL,
    position INT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date DATETIME DEFAULT NULL,
    completed_at DATETIME NULL,
    archived TINYINT(1) DEFAULT 0,
    FOREIGN KEY (parent_task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


-- Task Tags (Many-to-Many Relationship)
CREATE TABLE task_tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    task_id INT NOT NULL,
    tag_id INT NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Trigger: Set difficulty level based on difficulty_numeric before INSERT
DELIMITER $$

CREATE TRIGGER set_difficulty_level
BEFORE INSERT ON tasks
FOR EACH ROW
BEGIN
    IF NEW.difficulty_numeric BETWEEN 0.1 AND 4.0 THEN
        SET NEW.difficulty_level = UPPER('Easy'); 
    ELSEIF NEW.difficulty_numeric BETWEEN 4.1 AND 7.5 THEN
        SET NEW.difficulty_level = UPPER('Medium');
    ELSEIF NEW.difficulty_numeric BETWEEN 7.6 AND 10.0 THEN
        SET NEW.difficulty_level = UPPER('Hard');
    ELSE
        SET NEW.difficulty_level = UPPER('Easy'); 
    END IF;
END$$

DELIMITER ;

-- Trigger: Update difficulty level on difficulty_numeric update
DELIMITER $$

CREATE TRIGGER update_difficulty_level
BEFORE UPDATE ON tasks
FOR EACH ROW
BEGIN
    IF NEW.difficulty_numeric BETWEEN 0.1 AND 4.0 THEN
        SET NEW.difficulty_level = UPPER('Easy');
    ELSEIF NEW.difficulty_numeric BETWEEN 4.1 AND 7.5 THEN
        SET NEW.difficulty_level = UPPER('Medium');
    ELSEIF NEW.difficulty_numeric BETWEEN 7.6 AND 10.0 THEN
        SET NEW.difficulty_level = UPPER('Hard');
    ELSE
        SET NEW.difficulty_level = UPPER('Easy'); 
    END IF;
END$$

DELIMITER ;

-- Trigger: Add default tags after user registration
DELIMITER $$

CREATE TRIGGER after_user_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO tags (name, user_id) VALUES
        ('Life', NEW.id),
        ('Health', NEW.id),
        ('Finance', NEW.id),
        ('Learning', NEW.id),
        ('Family', NEW.id),
        ('Work', NEW.id),
        ('Productivity', NEW.id),
        ('Errands', NEW.id),
        ('Creativity', NEW.id),
        ('Social', NEW.id);
END$$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER set_due_date_default
BEFORE INSERT ON tasks
FOR EACH ROW
BEGIN
    IF NEW.due_date IS NULL THEN
        SET NEW.due_date = DATE_ADD(DATE(NEW.created_at), INTERVAL 7 DAY) + INTERVAL 18 HOUR;
    END IF;
END$$

DELIMITER ;