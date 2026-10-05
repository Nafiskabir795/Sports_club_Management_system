-- Selects the database for subsequent statements.
USE sports_club_db;

/* Run once against an existing sports_club_db. */
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
