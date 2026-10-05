-- Creates the project database if needed.
CREATE DATABASE IF NOT EXISTS sports_club_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- Selects the database for subsequent statements.
USE sports_club_db;

-- Changes a MySQL session setting.
SET FOREIGN_KEY_CHECKS = 0;

-- Removes an existing table before rebuilding the schema.
DROP TABLE IF EXISTS team_members;
-- Removes an existing table before rebuilding the schema.
DROP TABLE IF EXISTS teams;
-- Removes an existing table before rebuilding the schema.
DROP TABLE IF EXISTS selections;
-- Removes an existing table before rebuilding the schema.
DROP TABLE IF EXISTS football_performance;
-- Removes an existing table before rebuilding the schema.
DROP TABLE IF EXISTS volleyball_performance;
-- Removes an existing table before rebuilding the schema.
DROP TABLE IF EXISTS cricket_performance;
-- Removes an existing table before rebuilding the schema.
DROP TABLE IF EXISTS session_participants;
-- Removes an existing table before rebuilding the schema.
DROP TABLE IF EXISTS sessions;
-- Removes an existing table before rebuilding the schema.
DROP TABLE IF EXISTS student_sport_choices;
-- Removes an existing table before rebuilding the schema.
DROP TABLE IF EXISTS positions;
-- Removes an existing table before rebuilding the schema.
DROP TABLE IF EXISTS student_sports;
-- Removes an existing table before rebuilding the schema.
DROP TABLE IF EXISTS coach_sports;
-- Removes an existing table before rebuilding the schema.
DROP TABLE IF EXISTS coaches;
-- Removes an existing table before rebuilding the schema.
DROP TABLE IF EXISTS students;
-- Removes an existing table before rebuilding the schema.
DROP TABLE IF EXISTS sports;

-- Changes a MySQL session setting.
SET FOREIGN_KEY_CHECKS = 1;


-- Creates a table and defines its columns/constraints.
CREATE TABLE sports (
    -- Defines the unique identifier for rows.
    sport_id INT AUTO_INCREMENT PRIMARY KEY,
    -- Prevents duplicate values.
    sport_name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;


-- Creates a table and defines its columns/constraints.
CREATE TABLE students (
    -- Defines the unique identifier for rows.
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    -- Prevents duplicate values.
    student_code VARCHAR(20) NOT NULL UNIQUE,
    -- Requires a value.
    name VARCHAR(100) NOT NULL,
    -- Prevents duplicate values.
    phone VARCHAR(20) NOT NULL UNIQUE,
    -- Prevents duplicate values.
    email VARCHAR(120) NOT NULL UNIQUE,
    -- Requires a value.
    password_hash VARCHAR(255) NOT NULL,
    -- Requires a value.
    age TINYINT UNSIGNED NOT NULL,
    -- Requires a value.
    blood_group VARCHAR(5) NOT NULL,
    weight_kg DECIMAL(5,2),
    height_cm DECIMAL(5,2),
    physical_attributes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- Creates a table and defines its columns/constraints.
CREATE TABLE coaches (
    -- Defines the unique identifier for rows.
    coach_id INT AUTO_INCREMENT PRIMARY KEY,
    -- Prevents duplicate values.
    coach_code VARCHAR(20) NOT NULL UNIQUE,
    -- Requires a value.
    name VARCHAR(100) NOT NULL,
    -- Prevents duplicate values.
    phone VARCHAR(20) NOT NULL UNIQUE,
    -- Prevents duplicate values.
    email VARCHAR(120) NOT NULL UNIQUE,
    -- Requires a value.
    password_hash VARCHAR(255) NOT NULL,
    -- Requires a value.
    age TINYINT UNSIGNED NOT NULL,
    -- Requires a value.
    blood_group VARCHAR(5) NOT NULL,
    weight_kg DECIMAL(5,2),
    height_cm DECIMAL(5,2),
    physical_attributes TEXT,
    -- Requires a value.
    experience_years TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- Creates a table and defines its columns/constraints.
CREATE TABLE coach_sports (
    -- Requires a value.
    coach_id INT NOT NULL,
    -- Requires a value.
    sport_id INT NOT NULL,
    -- Defines the unique identifier for rows.
    PRIMARY KEY (coach_id, sport_id),
    -- Defines a relationship to another table.
    FOREIGN KEY (coach_id) REFERENCES coaches(coach_id) ON DELETE CASCADE,
    -- Defines a relationship to another table.
    FOREIGN KEY (sport_id) REFERENCES sports(sport_id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- Creates a table and defines its columns/constraints.
CREATE TABLE student_sports (
    -- Requires a value.
    student_id INT NOT NULL,
    -- Requires a value.
    sport_id INT NOT NULL,
    selected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Defines the unique identifier for rows.
    PRIMARY KEY (student_id, sport_id),
    -- Defines a relationship to another table.
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    -- Defines a relationship to another table.
    FOREIGN KEY (sport_id) REFERENCES sports(sport_id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- Creates a table and defines its columns/constraints.
CREATE TABLE positions (
    -- Defines the unique identifier for rows.
    position_id INT AUTO_INCREMENT PRIMARY KEY,
    -- Requires a value.
    sport_id INT NOT NULL,
    -- Requires a value.
    position_name VARCHAR(80) NOT NULL,
    description VARCHAR(255),
    -- Prevents duplicate values.
    UNIQUE (sport_id, position_name),
    -- Defines a relationship to another table.
    FOREIGN KEY (sport_id) REFERENCES sports(sport_id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- Creates a table and defines its columns/constraints.
CREATE TABLE student_sport_choices (
    -- Defines the unique identifier for rows.
    choice_id INT AUTO_INCREMENT PRIMARY KEY,
    -- Requires a value.
    student_id INT NOT NULL,
    -- Requires a value.
    sport_id INT NOT NULL,
    -- Requires a value.
    position_id INT NOT NULL,
    handedness ENUM('Right-Handed','Left-Handed') NULL,
    serve_type ENUM('Underhanded','Normal','Jump','Jump Float','Curved') NULL,
    previous_role ENUM('Team Captain','Ace','Head Coach','Assistant Coach','Team Manager') NULL,
    batting_style ENUM('Right-Hand Batter','Left-Hand Batter') NULL,
    bowling_style ENUM(
        'Right-Arm Fast','Right-Arm Fast-Medium','Right-Arm Medium',
        'Left-Arm Fast','Left-Arm Fast-Medium','Left-Arm Medium',
        'Off-Spin','Leg-Spin','Left-Arm Orthodox'
    ) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Prevents duplicate values.
    UNIQUE (student_id, sport_id),
    -- Defines a relationship to another table.
    FOREIGN KEY (student_id, sport_id) REFERENCES student_sports(student_id, sport_id) ON DELETE CASCADE,
    -- Defines a relationship to another table.
    FOREIGN KEY (position_id) REFERENCES positions(position_id) ON DELETE RESTRICT
) ENGINE=InnoDB;


-- Creates a table and defines its columns/constraints.
CREATE TABLE sessions (
    -- Defines the unique identifier for rows.
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    -- Requires a value.
    sport_id INT NOT NULL,
    -- Requires a value.
    coach_id INT NOT NULL,
    -- Requires a value.
    session_type ENUM('Practice','Testing') NOT NULL,
    session_number TINYINT UNSIGNED NULL,
    -- Requires a value.
    title VARCHAR(150) NOT NULL,
    -- Requires a value.
    session_date DATE NOT NULL,
    -- Requires a value.
    start_time TIME NOT NULL,
    -- Requires a value.
    end_time TIME NOT NULL,
    -- Requires a value.
    location VARCHAR(150) NOT NULL,
    description TEXT,
    -- Requires a value.
    status ENUM('Scheduled','Completed','Cancelled') NOT NULL DEFAULT 'Scheduled',
    -- Defines a relationship to another table.
    FOREIGN KEY (sport_id) REFERENCES sports(sport_id) ON DELETE CASCADE,
    -- Defines a relationship to another table.
    FOREIGN KEY (coach_id) REFERENCES coaches(coach_id) ON DELETE RESTRICT
) ENGINE=InnoDB;


-- Creates a table and defines its columns/constraints.
CREATE TABLE session_participants (
    -- Requires a value.
    session_id INT NOT NULL,
    -- Requires a value.
    student_id INT NOT NULL,
    -- Requires a value.
    attendance ENUM('Present','Absent','Pending') NOT NULL DEFAULT 'Pending',
    -- Defines the unique identifier for rows.
    PRIMARY KEY (session_id, student_id),
    -- Defines a relationship to another table.
    FOREIGN KEY (session_id) REFERENCES sessions(session_id) ON DELETE CASCADE,
    -- Defines a relationship to another table.
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- Creates a table and defines its columns/constraints.
CREATE TABLE football_performance (
    -- Defines the unique identifier for rows.
    performance_id INT AUTO_INCREMENT PRIMARY KEY,
    -- Requires a value.
    session_id INT NOT NULL,
    -- Requires a value.
    student_id INT NOT NULL,
    -- Requires a value.
    coach_id INT NOT NULL,
    passing_accuracy DECIMAL(5,2) DEFAULT 0,
    shot_accuracy DECIMAL(5,2) DEFAULT 0,
    speed_kmh DECIMAL(5,2) DEFAULT 0,
    goals INT UNSIGNED DEFAULT 0,
    assists INT UNSIGNED DEFAULT 0,
    tackles INT UNSIGNED DEFAULT 0,
    interceptions INT UNSIGNED DEFAULT 0,
    fouls INT UNSIGNED DEFAULT 0,
    yellow_cards INT UNSIGNED DEFAULT 0,
    red_cards INT UNSIGNED DEFAULT 0,
    saves INT UNSIGNED DEFAULT 0,
    goals_conceded INT UNSIGNED DEFAULT 0,
    save_accuracy DECIMAL(5,2) DEFAULT NULL,
    -- Requires a value.
    overall_score DECIMAL(5,2) NOT NULL,
    coach_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Prevents duplicate values.
    UNIQUE (session_id, student_id),
    -- Defines a relationship to another table.
    FOREIGN KEY (session_id) REFERENCES sessions(session_id) ON DELETE CASCADE,
    -- Defines a relationship to another table.
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    -- Defines a relationship to another table.
    FOREIGN KEY (coach_id) REFERENCES coaches(coach_id) ON DELETE RESTRICT
) ENGINE=InnoDB;


-- Creates a table and defines its columns/constraints.
CREATE TABLE volleyball_performance (
    -- Defines the unique identifier for rows.
    performance_id INT AUTO_INCREMENT PRIMARY KEY,
    -- Requires a value.
    session_id INT NOT NULL,
    -- Requires a value.
    student_id INT NOT NULL,
    -- Requires a value.
    coach_id INT NOT NULL,
    service_aces INT UNSIGNED DEFAULT 0,
    service_errors INT UNSIGNED DEFAULT 0,
    attack_attempts INT UNSIGNED DEFAULT 0,
    successful_attacks INT UNSIGNED DEFAULT 0,
    attack_errors INT UNSIGNED DEFAULT 0,
    blocks INT UNSIGNED DEFAULT 0,
    digs INT UNSIGNED DEFAULT 0,
    assists INT UNSIGNED DEFAULT 0,
    reception_success_rate DECIMAL(5,2) DEFAULT 0,
    net_violations INT UNSIGNED DEFAULT 0,
    rotation_faults INT UNSIGNED DEFAULT 0,
    foot_faults INT UNSIGNED DEFAULT 0,
    double_contacts INT UNSIGNED DEFAULT 0,
    other_faults INT UNSIGNED DEFAULT 0,
    -- Requires a value.
    overall_score DECIMAL(5,2) NOT NULL,
    coach_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Prevents duplicate values.
    UNIQUE (session_id, student_id),
    -- Defines a relationship to another table.
    FOREIGN KEY (session_id) REFERENCES sessions(session_id) ON DELETE CASCADE,
    -- Defines a relationship to another table.
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    -- Defines a relationship to another table.
    FOREIGN KEY (coach_id) REFERENCES coaches(coach_id) ON DELETE RESTRICT
) ENGINE=InnoDB;


-- Creates a table and defines its columns/constraints.
CREATE TABLE cricket_performance (
    -- Defines the unique identifier for rows.
    performance_id INT AUTO_INCREMENT PRIMARY KEY,
    -- Requires a value.
    session_id INT NOT NULL,
    -- Requires a value.
    student_id INT NOT NULL,
    -- Requires a value.
    coach_id INT NOT NULL,
    runs INT UNSIGNED DEFAULT 0,
    balls_faced INT UNSIGNED DEFAULT 0,
    fours INT UNSIGNED DEFAULT 0,
    sixes INT UNSIGNED DEFAULT 0,
    dismissals INT UNSIGNED DEFAULT 0,
    overs DECIMAL(4,1) DEFAULT 0,
    runs_conceded INT UNSIGNED DEFAULT 0,
    wickets INT UNSIGNED DEFAULT 0,
    maidens INT UNSIGNED DEFAULT 0,
    wides INT UNSIGNED DEFAULT 0,
    no_balls INT UNSIGNED DEFAULT 0,
    catches INT UNSIGNED DEFAULT 0,
    run_outs INT UNSIGNED DEFAULT 0,
    stumpings INT UNSIGNED DEFAULT 0,
    fielding_errors INT UNSIGNED DEFAULT 0,
    fitness_score DECIMAL(5,2) DEFAULT 0,
    experience_score DECIMAL(5,2) DEFAULT 0,
    rule_violations INT UNSIGNED DEFAULT 0,
    -- Requires a value.
    overall_score DECIMAL(5,2) NOT NULL,
    coach_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Prevents duplicate values.
    UNIQUE (session_id, student_id),
    -- Defines a relationship to another table.
    FOREIGN KEY (session_id) REFERENCES sessions(session_id) ON DELETE CASCADE,
    -- Defines a relationship to another table.
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    -- Defines a relationship to another table.
    FOREIGN KEY (coach_id) REFERENCES coaches(coach_id) ON DELETE RESTRICT
) ENGINE=InnoDB;


-- Creates a table and defines its columns/constraints.
CREATE TABLE selections (
    -- Defines the unique identifier for rows.
    selection_id INT AUTO_INCREMENT PRIMARY KEY,
    -- Requires a value.
    student_id INT NOT NULL,
    -- Requires a value.
    sport_id INT NOT NULL,
    -- Requires a value.
    testing_session_id INT NOT NULL,
    -- Requires a value.
    overall_score DECIMAL(5,2) NOT NULL,
    -- Requires a value.
    selection_status ENUM('Selected','Not Selected','Under Review') NOT NULL,
    coach_notes TEXT,
    selected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Prevents duplicate values.
    UNIQUE (student_id, sport_id),
    -- Defines a relationship to another table.
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    -- Defines a relationship to another table.
    FOREIGN KEY (sport_id) REFERENCES sports(sport_id) ON DELETE CASCADE,
    -- Defines a relationship to another table.
    FOREIGN KEY (testing_session_id) REFERENCES sessions(session_id) ON DELETE RESTRICT
) ENGINE=InnoDB;


-- Creates a table and defines its columns/constraints.
CREATE TABLE teams (
    -- Defines the unique identifier for rows.
    team_id INT AUTO_INCREMENT PRIMARY KEY,
    -- Prevents duplicate values.
    sport_id INT NOT NULL UNIQUE,
    -- Requires a value.
    team_name VARCHAR(100) NOT NULL,
    -- Requires a value.
    coach_id INT NOT NULL,
    formation VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Defines a relationship to another table.
    FOREIGN KEY (sport_id) REFERENCES sports(sport_id) ON DELETE CASCADE,
    -- Defines a relationship to another table.
    FOREIGN KEY (coach_id) REFERENCES coaches(coach_id) ON DELETE RESTRICT
) ENGINE=InnoDB;


-- Creates a table and defines its columns/constraints.
CREATE TABLE team_members (
    -- Defines the unique identifier for rows.
    team_member_id INT AUTO_INCREMENT PRIMARY KEY,
    -- Requires a value.
    team_id INT NOT NULL,
    -- Requires a value.
    student_id INT NOT NULL,
    -- Requires a value.
    position_id INT NOT NULL,
    -- Requires a value.
    lineup_status ENUM('Starter','Substitute','Reserve') NOT NULL DEFAULT 'Starter',
    jersey_number TINYINT UNSIGNED NULL,
    formation_slot VARCHAR(80) NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Prevents duplicate values.
    UNIQUE (team_id, student_id),
    -- Prevents duplicate values.
    UNIQUE (team_id, jersey_number),
    -- Defines a relationship to another table.
    FOREIGN KEY (team_id) REFERENCES teams(team_id) ON DELETE CASCADE,
    -- Defines a relationship to another table.
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    -- Defines a relationship to another table.
    FOREIGN KEY (position_id) REFERENCES positions(position_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Inserts rows into a table.
INSERT INTO sports (sport_id, sport_name) VALUES (1,'Football'), (2,'Volleyball'), (3,'Cricket');

-- Inserts rows into a table.
INSERT INTO positions (position_id, sport_id, position_name, description) VALUES (1,1,'Goalkeeper','Standard Goalkeeper position/role'), (2,1,'Centre-Back','Standard Centre-Back position/role'), (3,1,'Left-Back','Standard Left-Back position/role'), (4,1,'Right-Back','Standard Right-Back position/role'), (5,1,'Defensive Midfielder','Standard Defensive Midfielder position/role'), (6,1,'Central Midfielder','Standard Central Midfielder position/role'), (7,1,'Attacking Midfielder','Standard Attacking Midfielder position/role'), (8,1,'Left Winger','Standard Left Winger position/role'), (9,1,'Right Winger','Standard Right Winger position/role'), (10,1,'Striker','Standard Striker position/role');
-- Inserts rows into a table.
INSERT INTO positions (position_id, sport_id, position_name, description) VALUES (11,2,'Setter','Standard Setter position/role'), (12,2,'Left Wing Spiker','Standard Left Wing Spiker position/role'), (13,2,'Right Wing Spiker','Standard Right Wing Spiker position/role'), (14,2,'Middle Blocker','Standard Middle Blocker position/role'), (15,2,'Libero','Standard Libero position/role'), (16,2,'Defensive Specialist','Standard Defensive Specialist position/role'), (17,2,'Pinch Server','Standard Pinch Server position/role');
-- Inserts rows into a table.
INSERT INTO positions (position_id, sport_id, position_name, description) VALUES (18,3,'Batter','Standard Batter position/role'), (19,3,'Bowler','Standard Bowler position/role'), (20,3,'All-Rounder','Standard All-Rounder position/role'), (21,3,'Wicketkeeper','Standard Wicketkeeper position/role'), (22,3,'Wicketkeeper-Batter','Standard Wicketkeeper-Batter position/role');
















-- Helpful views for the dashboards.
CREATE OR REPLACE VIEW v_student_sport_status AS
SELECT
    s.student_id,
    s.student_code,
    s.name,
    sp.sport_id,
    sp.sport_name,
    ss.selected_at,
    p.position_name
FROM students s
JOIN student_sports ss ON ss.student_id = s.student_id
JOIN sports sp ON sp.sport_id = ss.sport_id
JOIN student_sport_choices c ON c.student_id = ss.student_id AND c.sport_id = ss.sport_id
JOIN positions p ON p.position_id = c.position_id;

CREATE OR REPLACE VIEW v_selection_summary AS
SELECT
    se.selection_id,
    se.student_id,
    s.student_code,
    s.name,
    se.sport_id,
    sp.sport_name,
    se.testing_session_id,
    se.overall_score,
    se.selection_status,
    se.selected_at
FROM selections se
JOIN students s ON s.student_id = se.student_id
JOIN sports sp ON sp.sport_id = se.sport_id;


-- Demo login note:
-- All seeded accounts use the same demo password: Password123!
-- The stored value above is a SHA-256 demo hash so the SQL remains portable.
-- For the final PHP application, use password_hash() and password_verify()
-- rather than storing or comparing plain-text passwords.

/* -------------------------------------------------------------------------
   Data-integrity checks (MySQL 8 / MariaDB 10.4+)
   ------------------------------------------------------------------------- */
ALTER TABLE sessions
    ADD CONSTRAINT chk_sessions_time_order CHECK (end_time > start_time),
    ADD CONSTRAINT chk_sessions_number CHECK (session_number IS NULL OR session_number BETWEEN 1 AND 255);

-- Changes the structure of an existing table.
ALTER TABLE football_performance
    ADD CONSTRAINT chk_football_pass_accuracy CHECK (passing_accuracy BETWEEN 0 AND 100),
    ADD CONSTRAINT chk_football_shot_accuracy CHECK (shot_accuracy BETWEEN 0 AND 100),
    ADD CONSTRAINT chk_football_speed CHECK (speed_kmh BETWEEN 0 AND 200),
    ADD CONSTRAINT chk_football_save_accuracy CHECK (save_accuracy IS NULL OR save_accuracy BETWEEN 0 AND 100),
    ADD CONSTRAINT chk_football_score CHECK (overall_score BETWEEN 0 AND 100);

-- Changes the structure of an existing table.
ALTER TABLE volleyball_performance
    ADD CONSTRAINT chk_volleyball_reception CHECK (reception_success_rate BETWEEN 0 AND 100),
    ADD CONSTRAINT chk_volleyball_score CHECK (overall_score BETWEEN 0 AND 100);

-- Changes the structure of an existing table.
ALTER TABLE cricket_performance
    ADD CONSTRAINT chk_cricket_fitness CHECK (fitness_score BETWEEN 0 AND 100),
    ADD CONSTRAINT chk_cricket_experience CHECK (experience_score BETWEEN 0 AND 100),
    ADD CONSTRAINT chk_cricket_score CHECK (overall_score BETWEEN 0 AND 100);

-- Changes the structure of an existing table.
ALTER TABLE selections
    ADD CONSTRAINT chk_selection_score CHECK (overall_score BETWEEN 0 AND 100);

/* ================================================================
   DBMS COURSE REQUIREMENT QUERIES
   These are examples using the project's actual relationships.
   ================================================================ */

-- Aggregate function + JOIN: count registered students per sport.
SELECT s.sport_id, s.sport_name, COUNT(ss.student_id) AS total_students
FROM sports AS s
LEFT JOIN student_sports AS ss ON s.sport_id = ss.sport_id
GROUP BY s.sport_id, s.sport_name;

-- Subquery: find students registered for Football (sport_id = 1).
SELECT st.student_id, st.student_code, st.name
FROM students AS st
WHERE st.student_id IN (
    -- Retrieves rows from the database.
    SELECT ss.student_id
    FROM student_sports AS ss
    WHERE ss.sport_id = 1
);

-- Subquery using EXISTS: find students who selected at least one sport.
SELECT st.student_id, st.student_code, st.name
FROM students AS st
WHERE EXISTS (
    -- Retrieves rows from the database.
    SELECT 1
    FROM student_sports AS ss
    WHERE ss.student_id = st.student_id
);
