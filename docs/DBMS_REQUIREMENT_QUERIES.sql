-- Sports Club DBMS requirement examples

-- Aggregate + JOIN
SELECT s.sport_id, s.sport_name, COUNT(ss.student_id) AS total_students
FROM sports AS s
LEFT JOIN student_sports AS ss ON s.sport_id = ss.sport_id
GROUP BY s.sport_id, s.sport_name;

-- Subquery
SELECT st.student_id, st.student_code, st.name
FROM students AS st
WHERE st.student_id IN (
    -- Retrieves rows from the database.
    SELECT ss.student_id FROM student_sports AS ss WHERE ss.sport_id = 1
);

-- Subquery using EXISTS
SELECT st.student_id, st.student_code, st.name
FROM students AS st
WHERE EXISTS (
    -- Retrieves rows from the database.
    SELECT 1 FROM student_sports AS ss WHERE ss.student_id = st.student_id
);
