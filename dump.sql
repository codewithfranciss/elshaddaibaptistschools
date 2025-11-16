-- PostgreSQL database dump fixed for general use

-- Enable plpgsql extension
CREATE EXTENSION IF NOT EXISTS plpgsql;

-- ===========================
-- TABLES
-- ===========================

CREATE TABLE public.users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role VARCHAR(20) NOT NULL CHECK (role IN ('teacher','student','admin')),
    created_at TIMESTAMP DEFAULT now()
);

CREATE TABLE public.classes (
    id SERIAL PRIMARY KEY,
    classid VARCHAR(10) NOT NULL UNIQUE,
    classname VARCHAR(50) NOT NULL
);

CREATE TABLE public.teachers (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES public.users(id) ON DELETE CASCADE,
    teacherid VARCHAR(10) NOT NULL UNIQUE,
    fname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(15)
);

CREATE TABLE public.students (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES public.users(id) ON DELETE CASCADE,
    stuid VARCHAR(20) NOT NULL UNIQUE,
    fname VARCHAR(50) NOT NULL,
    lname VARCHAR(50) NOT NULL,
    gender VARCHAR(10),
    dob DATE
);

CREATE TABLE public.subjects (
    id SERIAL PRIMARY KEY,
    subid VARCHAR(20) NOT NULL UNIQUE,
    subname VARCHAR(100) NOT NULL,
    class_id INT REFERENCES public.classes(id)
);

CREATE TABLE public.academic_records (
    id SERIAL PRIMARY KEY,
    acadid VARCHAR(50) NOT NULL UNIQUE,
    student_id INT REFERENCES public.students(id),
    subject_id INT REFERENCES public.subjects(id),
    score NUMERIC(5,2),
    term VARCHAR(20),
    session VARCHAR(20),
    teacher_id INT REFERENCES public.teachers(id),
    UNIQUE(student_id, subject_id, term, session)
);

CREATE TABLE public.admin (
    adminid SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE public.createuserstatus (
    statusid SERIAL PRIMARY KEY,
    statusname VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE public.userstatustask (
    taskid SERIAL PRIMARY KEY,
    taskname VARCHAR(100) NOT NULL,
    statusid INT REFERENCES createuserstatus(statusid) ON DELETE SET NULL
);

CREATE TABLE public.adminlogin (
    loginid SERIAL PRIMARY KEY,
    adminid INT NOT NULL REFERENCES admin(adminid) ON DELETE CASCADE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE public.assignments (
    id SERIAL PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    filepath TEXT NOT NULL,
    class_id INT REFERENCES public.classes(id),
    teacher_id INT REFERENCES public.teachers(id),
    uploaded_at TIMESTAMP DEFAULT now()
);

CREATE TABLE public.attendance (
    id SERIAL PRIMARY KEY,
    arid VARCHAR(50) NOT NULL UNIQUE,
    student_id INT REFERENCES public.students(id),
    class_id INT REFERENCES public.classes(id),
    date DATE NOT NULL,
    status SMALLINT CHECK(status IN (0,1)),
    teacher_id INT REFERENCES public.teachers(id),
    UNIQUE(student_id, date)
);

CREATE TABLE public.login_log (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES public.users(id),
    login_time TIMESTAMP DEFAULT now()
);

CREATE TABLE public.tasks (
    id SERIAL PRIMARY KEY,
    taskid INT NOT NULL UNIQUE,
    taskname VARCHAR(100) NOT NULL,
    route VARCHAR(50) NOT NULL
);

CREATE TABLE public.role_tasks (
    id SERIAL PRIMARY KEY,
    role VARCHAR(30) NOT NULL,
    task_id INT REFERENCES public.tasks(id),
    UNIQUE(role, task_id)
);

CREATE TABLE public.student_assignments (
    id SERIAL PRIMARY KEY,
    student_id INT REFERENCES public.students(id),
    class_id INT REFERENCES public.classes(id),
    session VARCHAR(20) NOT NULL,
    term VARCHAR(20) NOT NULL,
    UNIQUE(student_id, class_id, session, term)
);

CREATE TABLE public.teacher_assignments (
    id SERIAL PRIMARY KEY,
    teacher_id INT REFERENCES public.teachers(id),
    class_id INT REFERENCES public.classes(id),
    session VARCHAR(20) NOT NULL,
    term VARCHAR(20) NOT NULL,
    UNIQUE(class_id, session, term),
    UNIQUE(teacher_id, class_id, session, term)
);

-- ===========================
-- INITIAL DATA
-- ===========================

-- Insert users first (needed for teachers and students)
INSERT INTO public.users (id, username, password, role, created_at) VALUES
(1, 'tomiwa', 'tom12tom12', 'teacher', '2025-11-13 16:17:17'),
(2, 'ire', 'tom12tom12', 'student', '2025-11-13 16:17:17'),
(3, 'admin', 'admin123', 'admin', '2025-11-13 16:17:17');

-- Classes before subjects or assignments
INSERT INTO public.classes (id, classid, classname) VALUES
(1, 'JSS1A', 'JSS1 A'),
(2, 'JSS1B', 'JSS1 B');

-- Teachers depend on users
INSERT INTO public.teachers (id, user_id, teacherid, fname, lname, email, phone) VALUES
(1, 1, 'T001', 'Tomiwa', 'Akande', 'tomiwa@ebs.com', '08012345678');

-- Students depend on users
INSERT INTO public.students (id, user_id, stuid, fname, lname, gender, dob) VALUES
(1, 2, 'S001', 'Ire', 'Akande', 'Female', '2009-02-10');

-- Subjects depend on classes
INSERT INTO public.subjects (id, subid, subname, class_id) VALUES
(1, 'MATH-J1', 'Mathematics', 1);

-- Academic records depend on students, subjects, teachers
-- (currently no initial academic record, so skipping)

-- Admin data
INSERT INTO public.admin (adminid, username) VALUES
(1, 'admin');

-- Create user statuses
INSERT INTO public.createuserstatus (statusid, statusname) VALUES
(1, 'Active'),
(2, 'Inactive');

-- User status tasks
INSERT INTO public.userstatustask (taskid, taskname, statusid) VALUES
(1, 'Approve User', 1),
(2, 'Reject User', 2);

-- Admin login depends on admin
INSERT INTO public.adminlogin (loginid, adminid, username, password) VALUES
(1, 1, 'admin', 'admin123');

-- Assignments (depends on classes, teachers)
INSERT INTO public.assignments (id, title, description, filepath, class_id, teacher_id, uploaded_at) VALUES
(1, 'Math Homework', 'Complete exercises 1-10', '/assignments/math1.pdf', 1, 1, now());

-- Attendance (depends on students, classes, teachers)
INSERT INTO public.attendance (id, arid, student_id, class_id, date, status, teacher_id) VALUES
(1, 'A001', 1, 1, '2025-11-15', 1, 1);

-- Login log depends on users
INSERT INTO public.login_log (id, user_id, login_time) VALUES
(1, 1, now());

-- Tasks
INSERT INTO public.tasks (id, taskid, taskname, route) VALUES
(1, 1, 'View My Class', 'view_class'),
(2, 2, 'Take Attendance', 'take_attendance'),
(3, 3, 'Upload Result', 'upload_result'),
(4, 4, 'Upload Assignment', 'upload_assignment'),
(5, 5, 'View My Results', 'view_results'),
(6, 6, 'Create User', 'create_user');

-- Role tasks depend on tasks
INSERT INTO public.role_tasks (id, role, task_id) VALUES
(1, 'teacher', 1),
(2, 'teacher', 2),
(3, 'teacher', 3),
(4, 'teacher', 4),
(5, 'student', 5),
(6, 'admin', 6);

-- Student assignments depend on students and classes
INSERT INTO public.student_assignments (id, student_id, class_id, session, term) VALUES
(1, 1, 1, '2025/2026', 'First Term');

-- Teacher assignments depend on teachers and classes
INSERT INTO public.teacher_assignments (id, teacher_id, class_id, session, term) VALUES
(1, 1, 1, '2025/2026', 'First Term');