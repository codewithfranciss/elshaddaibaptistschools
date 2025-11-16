--
-- PostgreSQL database dump
--

-- Dumped from database version 10.23
-- Dumped by pg_dump version 10.23

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: academic_records; Type: TABLE; Schema: public; Owner: elshrwia
--

CREATE TABLE public.academic_records (
    id integer NOT NULL,
    acadid character varying(50) NOT NULL,
    student_id integer,
    subject_id integer,
    score numeric(5,2),
    term character varying(20),
    session character varying(20),
    teacher_id integer
);

CREATE TABLE public.admin (
    adminid SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE
);

ALTER TABLE public.academic_records OWNER TO elshrwia;

--
-- Name: academic_records_id_seq; Type: SEQUENCE; Schema: public; Owner: elshrwia
--

CREATE SEQUENCE public.academic_records_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.academic_records_id_seq OWNER TO elshrwia;

--
-- Name: academic_records_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: elshrwia
--

ALTER SEQUENCE public.academic_records_id_seq OWNED BY public.academic_records.id;


--
-- Name: assignments; Type: TABLE; Schema: public; Owner: elshrwia
--

CREATE TABLE public.assignments (
    id integer NOT NULL,
    title character varying(100) NOT NULL,
    description text,
    filepath text NOT NULL,
    class_id integer,
    teacher_id integer,
    uploaded_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.assignments OWNER TO elshrwia;

--
-- Name: assignments_id_seq; Type: SEQUENCE; Schema: public; Owner: elshrwia
--

CREATE SEQUENCE public.assignments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.assignments_id_seq OWNER TO elshrwia;

--
-- Name: assignments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: elshrwia
--

ALTER SEQUENCE public.assignments_id_seq OWNED BY public.assignments.id;


--
-- Name: attendance; Type: TABLE; Schema: public; Owner: elshrwia
--

CREATE TABLE public.attendance (
    id integer NOT NULL,
    arid character varying(50) NOT NULL,
    student_id integer,
    class_id integer,
    date date NOT NULL,
    status smallint,
    teacher_id integer,
    CONSTRAINT attendance_status_check CHECK ((status = ANY (ARRAY[0, 1])))
);


ALTER TABLE public.attendance OWNER TO elshrwia;

--
-- Name: attendance_id_seq; Type: SEQUENCE; Schema: public; Owner: elshrwia
--

CREATE SEQUENCE public.attendance_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.attendance_id_seq OWNER TO elshrwia;

--
-- Name: attendance_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: elshrwia
--

ALTER SEQUENCE public.attendance_id_seq OWNED BY public.attendance.id;


--
-- Name: classes; Type: TABLE; Schema: public; Owner: elshrwia
--

CREATE TABLE public.classes (
    id integer NOT NULL,
    classid character varying(10) NOT NULL,
    classname character varying(50) NOT NULL
);


ALTER TABLE public.classes OWNER TO elshrwia;

--
-- Name: classes_id_seq; Type: SEQUENCE; Schema: public; Owner: elshrwia
--

CREATE SEQUENCE public.classes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.classes_id_seq OWNER TO elshrwia;

--
-- Name: classes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: elshrwia
--

ALTER SEQUENCE public.classes_id_seq OWNED BY public.classes.id;


--
-- Name: login_log; Type: TABLE; Schema: public; Owner: elshrwia
--

CREATE TABLE public.login_log (
    id integer NOT NULL,
    user_id integer,
    login_time timestamp without time zone DEFAULT now()
);


ALTER TABLE public.login_log OWNER TO elshrwia;

--
-- Name: login_log_id_seq; Type: SEQUENCE; Schema: public; Owner: elshrwia
--

CREATE SEQUENCE public.login_log_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.login_log_id_seq OWNER TO elshrwia;

--
-- Name: login_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: elshrwia
--

ALTER SEQUENCE public.login_log_id_seq OWNED BY public.login_log.id;


--
-- Name: role_tasks; Type: TABLE; Schema: public; Owner: elshrwia
--

CREATE TABLE public.role_tasks (
    id integer NOT NULL,
    role character varying(20) NOT NULL,
    task_id integer
);


ALTER TABLE public.role_tasks OWNER TO elshrwia;

--
-- Name: role_tasks_id_seq; Type: SEQUENCE; Schema: public; Owner: elshrwia
--

CREATE SEQUENCE public.role_tasks_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.role_tasks_id_seq OWNER TO elshrwia;

--
-- Name: role_tasks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: elshrwia
--

ALTER SEQUENCE public.role_tasks_id_seq OWNED BY public.role_tasks.id;


--
-- Name: student_assignments; Type: TABLE; Schema: public; Owner: elshrwia
--

CREATE TABLE public.student_assignments (
    id integer NOT NULL,
    student_id integer,
    class_id integer,
    session character varying(20) NOT NULL,
    term character varying(20) NOT NULL
);


ALTER TABLE public.student_assignments OWNER TO elshrwia;

--
-- Name: student_assignments_id_seq; Type: SEQUENCE; Schema: public; Owner: elshrwia
--

CREATE SEQUENCE public.student_assignments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.student_assignments_id_seq OWNER TO elshrwia;

--
-- Name: student_assignments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: elshrwia
--

ALTER SEQUENCE public.student_assignments_id_seq OWNED BY public.student_assignments.id;


--
-- Name: students; Type: TABLE; Schema: public; Owner: elshrwia
--

CREATE TABLE public.students (
    id integer NOT NULL,
    user_id integer,
    stuid character varying(20) NOT NULL,
    fname character varying(50) NOT NULL,
    lname character varying(50) NOT NULL,
    gender character varying(10),
    dob date
);


ALTER TABLE public.students OWNER TO elshrwia;

--
-- Name: students_id_seq; Type: SEQUENCE; Schema: public; Owner: elshrwia
--

CREATE SEQUENCE public.students_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.students_id_seq OWNER TO elshrwia;

--
-- Name: students_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: elshrwia
--

ALTER SEQUENCE public.students_id_seq OWNED BY public.students.id;


--
-- Name: subjects; Type: TABLE; Schema: public; Owner: elshrwia
--

CREATE TABLE public.subjects (
    id integer NOT NULL,
    subid character varying(20) NOT NULL,
    subname character varying(100) NOT NULL,
    class_id integer
);


ALTER TABLE public.subjects OWNER TO elshrwia;

--
-- Name: subjects_id_seq; Type: SEQUENCE; Schema: public; Owner: elshrwia
--

CREATE SEQUENCE public.subjects_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.subjects_id_seq OWNER TO elshrwia;

--
-- Name: subjects_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: elshrwia
--

ALTER SEQUENCE public.subjects_id_seq OWNED BY public.subjects.id;


--
-- Name: tasks; Type: TABLE; Schema: public; Owner: elshrwia
--

CREATE TABLE public.tasks (
    id integer NOT NULL,
    taskid integer NOT NULL,
    taskname character varying(100) NOT NULL,
    route character varying(50) NOT NULL
);


ALTER TABLE public.tasks OWNER TO elshrwia;

--
-- Name: tasks_id_seq; Type: SEQUENCE; Schema: public; Owner: elshrwia
--

CREATE SEQUENCE public.tasks_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tasks_id_seq OWNER TO elshrwia;

--
-- Name: tasks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: elshrwia
--

ALTER SEQUENCE public.tasks_id_seq OWNED BY public.tasks.id;


--
-- Name: teacher_assignments; Type: TABLE; Schema: public; Owner: elshrwia
--

CREATE TABLE public.teacher_assignments (
    id integer NOT NULL,
    teacher_id integer,
    class_id integer,
    session character varying(20) NOT NULL,
    term character varying(20) NOT NULL
);


ALTER TABLE public.teacher_assignments OWNER TO elshrwia;

--
-- Name: teacher_assignments_id_seq; Type: SEQUENCE; Schema: public; Owner: elshrwia
--

CREATE SEQUENCE public.teacher_assignments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.teacher_assignments_id_seq OWNER TO elshrwia;

--
-- Name: teacher_assignments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: elshrwia
--

ALTER SEQUENCE public.teacher_assignments_id_seq OWNED BY public.teacher_assignments.id;


--
-- Name: teachers; Type: TABLE; Schema: public; Owner: elshrwia
--

CREATE TABLE public.teachers (
    id integer NOT NULL,
    user_id integer,
    teacherid character varying(10) NOT NULL,
    fname character varying(50) NOT NULL,
    lname character varying(50) NOT NULL,
    email character varying(100) NOT NULL,
    phone character varying(15)
);


ALTER TABLE public.teachers OWNER TO elshrwia;

--
-- Name: teachers_id_seq; Type: SEQUENCE; Schema: public; Owner: elshrwia
--

CREATE SEQUENCE public.teachers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.teachers_id_seq OWNER TO elshrwia;

--
-- Name: teachers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: elshrwia
--

ALTER SEQUENCE public.teachers_id_seq OWNED BY public.teachers.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: elshrwia
--

CREATE TABLE public.users (
    id integer NOT NULL,
    username character varying(50) NOT NULL,
    password text NOT NULL,
    role character varying(20) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    CONSTRAINT users_role_check CHECK (((role)::text = ANY ((ARRAY['teacher'::character varying, 'student'::character varying, 'admin'::character varying])::text[])))
);


ALTER TABLE public.users OWNER TO elshrwia;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: elshrwia
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO elshrwia;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: elshrwia
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: academic_records id; Type: DEFAULT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.academic_records ALTER COLUMN id SET DEFAULT nextval('public.academic_records_id_seq'::regclass);


--
-- Name: assignments id; Type: DEFAULT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.assignments ALTER COLUMN id SET DEFAULT nextval('public.assignments_id_seq'::regclass);


--
-- Name: attendance id; Type: DEFAULT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.attendance ALTER COLUMN id SET DEFAULT nextval('public.attendance_id_seq'::regclass);


--
-- Name: classes id; Type: DEFAULT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.classes ALTER COLUMN id SET DEFAULT nextval('public.classes_id_seq'::regclass);


--
-- Name: login_log id; Type: DEFAULT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.login_log ALTER COLUMN id SET DEFAULT nextval('public.login_log_id_seq'::regclass);


--
-- Name: role_tasks id; Type: DEFAULT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.role_tasks ALTER COLUMN id SET DEFAULT nextval('public.role_tasks_id_seq'::regclass);


--
-- Name: student_assignments id; Type: DEFAULT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.student_assignments ALTER COLUMN id SET DEFAULT nextval('public.student_assignments_id_seq'::regclass);


--
-- Name: students id; Type: DEFAULT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.students ALTER COLUMN id SET DEFAULT nextval('public.students_id_seq'::regclass);


--
-- Name: subjects id; Type: DEFAULT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.subjects ALTER COLUMN id SET DEFAULT nextval('public.subjects_id_seq'::regclass);


--
-- Name: tasks id; Type: DEFAULT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.tasks ALTER COLUMN id SET DEFAULT nextval('public.tasks_id_seq'::regclass);


--
-- Name: teacher_assignments id; Type: DEFAULT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.teacher_assignments ALTER COLUMN id SET DEFAULT nextval('public.teacher_assignments_id_seq'::regclass);


--
-- Name: teachers id; Type: DEFAULT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.teachers ALTER COLUMN id SET DEFAULT nextval('public.teachers_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: academic_records; Type: TABLE DATA; Schema: public; Owner: elshrwia
--



--
-- Data for Name: assignments; Type: TABLE DATA; Schema: public; Owner: elshrwia
--



--
-- Data for Name: attendance; Type: TABLE DATA; Schema: public; Owner: elshrwia
--



--
-- Data for Name: classes; Type: TABLE DATA; Schema: public; Owner: elshrwia
--

INSERT INTO public.classes VALUES (1, 'JSS1A', 'JSS1 A');
INSERT INTO public.classes VALUES (2, 'JSS1B', 'JSS1 B');


--
-- Data for Name: login_log; Type: TABLE DATA; Schema: public; Owner: elshrwia
--



--
-- Data for Name: role_tasks; Type: TABLE DATA; Schema: public; Owner: elshrwia
--

INSERT INTO public.role_tasks VALUES (1, 'teacher', 1);
INSERT INTO public.role_tasks VALUES (2, 'teacher', 2);
INSERT INTO public.role_tasks VALUES (3, 'teacher', 3);
INSERT INTO public.role_tasks VALUES (4, 'teacher', 4);
INSERT INTO public.role_tasks VALUES (5, 'student', 5);
INSERT INTO public.role_tasks VALUES (6, 'admin', 6);


--
-- Data for Name: student_assignments; Type: TABLE DATA; Schema: public; Owner: elshrwia
--

INSERT INTO public.student_assignments VALUES (1, 1, 1, '2025/2026', 'First Term');


--
-- Data for Name: students; Type: TABLE DATA; Schema: public; Owner: elshrwia
--

INSERT INTO public.students VALUES (1, 2, 'S001', 'Ire', 'Akande', 'Female', '2009-02-10');


--
-- Data for Name: subjects; Type: TABLE DATA; Schema: public; Owner: elshrwia
--

INSERT INTO public.subjects VALUES (1, 'MATH-J1', 'Mathematics', 1);


--
-- Data for Name: tasks; Type: TABLE DATA; Schema: public; Owner: elshrwia
--

INSERT INTO public.tasks VALUES (1, 1, 'View My Class', 'view_class');
INSERT INTO public.tasks VALUES (2, 2, 'Take Attendance', 'take_attendance');
INSERT INTO public.tasks VALUES (3, 3, 'Upload Result', 'upload_result');
INSERT INTO public.tasks VALUES (4, 4, 'Upload Assignment', 'upload_assignment');
INSERT INTO public.tasks VALUES (5, 5, 'View My Results', 'view_results');
INSERT INTO public.tasks VALUES (6, 6, 'Create User', 'create_user');


--
-- Data for Name: teacher_assignments; Type: TABLE DATA; Schema: public; Owner: elshrwia
--

INSERT INTO public.teacher_assignments VALUES (1, 1, 1, '2025/2026', 'First Term');


--
-- Data for Name: teachers; Type: TABLE DATA; Schema: public; Owner: elshrwia
--

INSERT INTO public.teachers VALUES (1, 1, 'T001', 'Tomiwa', 'Akande', 'tomiwa@ebs.com', '08012345678');


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: elshrwia
--

INSERT INTO public.users VALUES (1, 'tomiwa', 'tom12tom12', 'teacher', '2025-11-13 16:17:17.189624');
INSERT INTO public.users VALUES (2, 'ire', 'tom12tom12', 'student', '2025-11-13 16:17:17.189624');
INSERT INTO public.users VALUES (3, 'admin', 'admin123', 'admin', '2025-11-13 16:17:17.189624');


--
-- Name: academic_records_id_seq; Type: SEQUENCE SET; Schema: public; Owner: elshrwia
--

SELECT pg_catalog.setval('public.academic_records_id_seq', 1, false);


--
-- Name: assignments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: elshrwia
--

SELECT pg_catalog.setval('public.assignments_id_seq', 1, false);


--
-- Name: attendance_id_seq; Type: SEQUENCE SET; Schema: public; Owner: elshrwia
--

SELECT pg_catalog.setval('public.attendance_id_seq', 1, false);


--
-- Name: classes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: elshrwia
--

SELECT pg_catalog.setval('public.classes_id_seq', 2, true);


--
-- Name: login_log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: elshrwia
--

SELECT pg_catalog.setval('public.login_log_id_seq', 1, false);


--
-- Name: role_tasks_id_seq; Type: SEQUENCE SET; Schema: public; Owner: elshrwia
--

SELECT pg_catalog.setval('public.role_tasks_id_seq', 6, true);


--
-- Name: student_assignments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: elshrwia
--

SELECT pg_catalog.setval('public.student_assignments_id_seq', 1, true);


--
-- Name: students_id_seq; Type: SEQUENCE SET; Schema: public; Owner: elshrwia
--

SELECT pg_catalog.setval('public.students_id_seq', 1, true);


--
-- Name: subjects_id_seq; Type: SEQUENCE SET; Schema: public; Owner: elshrwia
--

SELECT pg_catalog.setval('public.subjects_id_seq', 1, true);


--
-- Name: tasks_id_seq; Type: SEQUENCE SET; Schema: public; Owner: elshrwia
--

SELECT pg_catalog.setval('public.tasks_id_seq', 6, true);


--
-- Name: teacher_assignments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: elshrwia
--

SELECT pg_catalog.setval('public.teacher_assignments_id_seq', 1, true);


--
-- Name: teachers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: elshrwia
--

SELECT pg_catalog.setval('public.teachers_id_seq', 1, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: elshrwia
--

SELECT pg_catalog.setval('public.users_id_seq', 3, true);


--
-- Name: academic_records academic_records_acadid_key; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.academic_records
    ADD CONSTRAINT academic_records_acadid_key UNIQUE (acadid);


--
-- Name: academic_records academic_records_pkey; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.academic_records
    ADD CONSTRAINT academic_records_pkey PRIMARY KEY (id);


--
-- Name: academic_records academic_records_student_id_subject_id_term_session_key; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.academic_records
    ADD CONSTRAINT academic_records_student_id_subject_id_term_session_key UNIQUE (student_id, subject_id, term, session);


--
-- Name: assignments assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.assignments
    ADD CONSTRAINT assignments_pkey PRIMARY KEY (id);


--
-- Name: attendance attendance_arid_key; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.attendance
    ADD CONSTRAINT attendance_arid_key UNIQUE (arid);


--
-- Name: attendance attendance_pkey; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.attendance
    ADD CONSTRAINT attendance_pkey PRIMARY KEY (id);


--
-- Name: attendance attendance_student_id_date_key; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.attendance
    ADD CONSTRAINT attendance_student_id_date_key UNIQUE (student_id, date);


--
-- Name: classes classes_classid_key; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT classes_classid_key UNIQUE (classid);


--
-- Name: classes classes_pkey; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT classes_pkey PRIMARY KEY (id);


--
-- Name: login_log login_log_pkey; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.login_log
    ADD CONSTRAINT login_log_pkey PRIMARY KEY (id);


--
-- Name: role_tasks role_tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.role_tasks
    ADD CONSTRAINT role_tasks_pkey PRIMARY KEY (id);


--
-- Name: role_tasks role_tasks_role_task_id_key; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.role_tasks
    ADD CONSTRAINT role_tasks_role_task_id_key UNIQUE (role, task_id);


--
-- Name: student_assignments student_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.student_assignments
    ADD CONSTRAINT student_assignments_pkey PRIMARY KEY (id);


--
-- Name: student_assignments student_assignments_student_id_class_id_session_term_key; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.student_assignments
    ADD CONSTRAINT student_assignments_student_id_class_id_session_term_key UNIQUE (student_id, class_id, session, term);


--
-- Name: students students_pkey; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.students
    ADD CONSTRAINT students_pkey PRIMARY KEY (id);


--
-- Name: students students_stuid_key; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.students
    ADD CONSTRAINT students_stuid_key UNIQUE (stuid);


--
-- Name: subjects subjects_pkey; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.subjects
    ADD CONSTRAINT subjects_pkey PRIMARY KEY (id);


--
-- Name: subjects subjects_subid_key; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.subjects
    ADD CONSTRAINT subjects_subid_key UNIQUE (subid);


--
-- Name: tasks tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_pkey PRIMARY KEY (id);


--
-- Name: tasks tasks_taskid_key; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_taskid_key UNIQUE (taskid);


--
-- Name: teacher_assignments teacher_assignments_class_id_session_term_key; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.teacher_assignments
    ADD CONSTRAINT teacher_assignments_class_id_session_term_key UNIQUE (class_id, session, term);


--
-- Name: teacher_assignments teacher_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.teacher_assignments
    ADD CONSTRAINT teacher_assignments_pkey PRIMARY KEY (id);


--
-- Name: teacher_assignments teacher_assignments_teacher_id_class_id_session_term_key; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.teacher_assignments
    ADD CONSTRAINT teacher_assignments_teacher_id_class_id_session_term_key UNIQUE (teacher_id, class_id, session, term);


--
-- Name: teachers teachers_email_key; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.teachers
    ADD CONSTRAINT teachers_email_key UNIQUE (email);


--
-- Name: teachers teachers_pkey; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.teachers
    ADD CONSTRAINT teachers_pkey PRIMARY KEY (id);


--
-- Name: teachers teachers_teacherid_key; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.teachers
    ADD CONSTRAINT teachers_teacherid_key UNIQUE (teacherid);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: users users_username_key; Type: CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- Name: academic_records academic_records_student_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.academic_records
    ADD CONSTRAINT academic_records_student_id_fkey FOREIGN KEY (student_id) REFERENCES public.students(id);


--
-- Name: academic_records academic_records_subject_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.academic_records
    ADD CONSTRAINT academic_records_subject_id_fkey FOREIGN KEY (subject_id) REFERENCES public.subjects(id);


--
-- Name: academic_records academic_records_teacher_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.academic_records
    ADD CONSTRAINT academic_records_teacher_id_fkey FOREIGN KEY (teacher_id) REFERENCES public.teachers(id);


--
-- Name: assignments assignments_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.assignments
    ADD CONSTRAINT assignments_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.classes(id);


--
-- Name: assignments assignments_teacher_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.assignments
    ADD CONSTRAINT assignments_teacher_id_fkey FOREIGN KEY (teacher_id) REFERENCES public.teachers(id);


--
-- Name: attendance attendance_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.attendance
    ADD CONSTRAINT attendance_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.classes(id);


--
-- Name: attendance attendance_student_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.attendance
    ADD CONSTRAINT attendance_student_id_fkey FOREIGN KEY (student_id) REFERENCES public.students(id);


--
-- Name: attendance attendance_teacher_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.attendance
    ADD CONSTRAINT attendance_teacher_id_fkey FOREIGN KEY (teacher_id) REFERENCES public.teachers(id);


--
-- Name: login_log login_log_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.login_log
    ADD CONSTRAINT login_log_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: role_tasks role_tasks_task_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.role_tasks
    ADD CONSTRAINT role_tasks_task_id_fkey FOREIGN KEY (task_id) REFERENCES public.tasks(id);


--
-- Name: student_assignments student_assignments_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.student_assignments
    ADD CONSTRAINT student_assignments_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.classes(id);


--
-- Name: student_assignments student_assignments_student_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.student_assignments
    ADD CONSTRAINT student_assignments_student_id_fkey FOREIGN KEY (student_id) REFERENCES public.students(id);


--
-- Name: students students_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.students
    ADD CONSTRAINT students_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: subjects subjects_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.subjects
    ADD CONSTRAINT subjects_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.classes(id);


--
-- Name: teacher_assignments teacher_assignments_class_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.teacher_assignments
    ADD CONSTRAINT teacher_assignments_class_id_fkey FOREIGN KEY (class_id) REFERENCES public.classes(id);


--
-- Name: teacher_assignments teacher_assignments_teacher_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.teacher_assignments
    ADD CONSTRAINT teacher_assignments_teacher_id_fkey FOREIGN KEY (teacher_id) REFERENCES public.teachers(id);


--
-- Name: teachers teachers_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: elshrwia
--

ALTER TABLE ONLY public.teachers
    ADD CONSTRAINT teachers_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: TABLE academic_records; Type: ACL; Schema: public; Owner: elshrwia
--

GRANT ALL ON TABLE public.academic_records TO "elshrwia_EBS_portal_db ";


--
-- Name: TABLE assignments; Type: ACL; Schema: public; Owner: elshrwia
--

GRANT ALL ON TABLE public.assignments TO "elshrwia_EBS_portal_db ";


--
-- Name: TABLE attendance; Type: ACL; Schema: public; Owner: elshrwia
--

GRANT ALL ON TABLE public.attendance TO "elshrwia_EBS_portal_db ";


--
-- Name: TABLE classes; Type: ACL; Schema: public; Owner: elshrwia
--

GRANT ALL ON TABLE public.classes TO "elshrwia_EBS_portal_db ";


--
-- Name: TABLE login_log; Type: ACL; Schema: public; Owner: elshrwia
--

GRANT ALL ON TABLE public.login_log TO "elshrwia_EBS_portal_db ";


--
-- Name: TABLE role_tasks; Type: ACL; Schema: public; Owner: elshrwia
--

GRANT ALL ON TABLE public.role_tasks TO "elshrwia_EBS_portal_db ";


--
-- Name: TABLE student_assignments; Type: ACL; Schema: public; Owner: elshrwia
--

GRANT ALL ON TABLE public.student_assignments TO "elshrwia_EBS_portal_db ";


--
-- Name: TABLE students; Type: ACL; Schema: public; Owner: elshrwia
--

GRANT ALL ON TABLE public.students TO "elshrwia_EBS_portal_db ";


--
-- Name: TABLE subjects; Type: ACL; Schema: public; Owner: elshrwia
--

GRANT ALL ON TABLE public.subjects TO "elshrwia_EBS_portal_db ";


--
-- Name: TABLE tasks; Type: ACL; Schema: public; Owner: elshrwia
--

GRANT ALL ON TABLE public.tasks TO "elshrwia_EBS_portal_db ";


--
-- Name: TABLE teacher_assignments; Type: ACL; Schema: public; Owner: elshrwia
--

GRANT ALL ON TABLE public.teacher_assignments TO "elshrwia_EBS_portal_db ";


--
-- Name: TABLE teachers; Type: ACL; Schema: public; Owner: elshrwia
--

GRANT ALL ON TABLE public.teachers TO "elshrwia_EBS_portal_db ";


--
-- Name: TABLE users; Type: ACL; Schema: public; Owner: elshrwia
--

GRANT ALL ON TABLE public.users TO "elshrwia_EBS_portal_db ";


--
-- PostgreSQL database dump complete
--

