--
-- PostgreSQL database dump
--

\restrict oXdvzTtoV7XMZX8yqdrF4f7hdrLlqeVciqiJ6YRIk9zA2eLP0XuT1aw7TS3jtcz

-- Dumped from database version 16.15 (Ubuntu 16.15-0ubuntu0.24.04.1)
-- Dumped by pg_dump version 16.15 (Ubuntu 16.15-0ubuntu0.24.04.1)

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

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: cache; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache OWNER TO davidcode;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO davidcode;

--
-- Name: documentaciones; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.documentaciones (
    doc_id integer NOT NULL,
    doc_sol_id integer NOT NULL,
    doc_nombre_original_archivo character varying(255) NOT NULL,
    doc_tipo_documento character varying(50),
    doc_formato_archivo character varying(20),
    doc_tamano_bytes bigint,
    doc_ruta_almacenamiento_url text NOT NULL,
    doc_fecha_subida timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    doc_estado_validacion character varying(20)
);


ALTER TABLE public.documentaciones OWNER TO davidcode;

--
-- Name: documentaciones_doc_id_seq; Type: SEQUENCE; Schema: public; Owner: davidcode
--

CREATE SEQUENCE public.documentaciones_doc_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.documentaciones_doc_id_seq OWNER TO davidcode;

--
-- Name: documentaciones_doc_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: davidcode
--

ALTER SEQUENCE public.documentaciones_doc_id_seq OWNED BY public.documentaciones.doc_id;


--
-- Name: estado_solicitudes; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.estado_solicitudes (
    eso_id integer NOT NULL,
    eso_nombre_estado character varying(50) NOT NULL,
    eso_descripcion text
);


ALTER TABLE public.estado_solicitudes OWNER TO davidcode;

--
-- Name: estado_solicitudes_eso_id_seq; Type: SEQUENCE; Schema: public; Owner: davidcode
--

CREATE SEQUENCE public.estado_solicitudes_eso_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.estado_solicitudes_eso_id_seq OWNER TO davidcode;

--
-- Name: estado_solicitudes_eso_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: davidcode
--

ALTER SEQUENCE public.estado_solicitudes_eso_id_seq OWNED BY public.estado_solicitudes.eso_id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection character varying(255) NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO davidcode;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: davidcode
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO davidcode;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: davidcode
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: hilos_chat; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.hilos_chat (
    hch_id bigint NOT NULL,
    hch_id_usuario integer NOT NULL,
    hch_id_admin integer,
    hch_estado character varying(255) DEFAULT 'pendiente'::character varying NOT NULL,
    hch_etiqueta_tema character varying(255),
    hch_fecha_solicitud_cierre timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.hilos_chat OWNER TO davidcode;

--
-- Name: hilos_chat_hch_id_seq; Type: SEQUENCE; Schema: public; Owner: davidcode
--

CREATE SEQUENCE public.hilos_chat_hch_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.hilos_chat_hch_id_seq OWNER TO davidcode;

--
-- Name: hilos_chat_hch_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: davidcode
--

ALTER SEQUENCE public.hilos_chat_hch_id_seq OWNED BY public.hilos_chat.hch_id;


--
-- Name: historial_estado_solicitudes; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.historial_estado_solicitudes (
    hes_id integer NOT NULL,
    hes_sol_id integer NOT NULL,
    hes_usu_id_responsable integer NOT NULL,
    hes_eso_id_anterior integer,
    hes_eso_id_nuevo integer NOT NULL,
    hes_observaciones_comentarios text,
    hes_fecha_cambio timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.historial_estado_solicitudes OWNER TO davidcode;

--
-- Name: historial_estado_solicitudes_hes_id_seq; Type: SEQUENCE; Schema: public; Owner: davidcode
--

CREATE SEQUENCE public.historial_estado_solicitudes_hes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.historial_estado_solicitudes_hes_id_seq OWNER TO davidcode;

--
-- Name: historial_estado_solicitudes_hes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: davidcode
--

ALTER SEQUENCE public.historial_estado_solicitudes_hes_id_seq OWNED BY public.historial_estado_solicitudes.hes_id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO davidcode;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO davidcode;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: davidcode
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO davidcode;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: davidcode
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: lapso_academicos; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.lapso_academicos (
    lac_id integer NOT NULL,
    lac_id_lapso character varying(20) NOT NULL,
    lac_fecha_inicio date NOT NULL,
    lac_fecha_cierre date NOT NULL,
    lac_estado_lapso character varying(20) NOT NULL
);


ALTER TABLE public.lapso_academicos OWNER TO davidcode;

--
-- Name: lapso_academicos_lac_id_seq; Type: SEQUENCE; Schema: public; Owner: davidcode
--

CREATE SEQUENCE public.lapso_academicos_lac_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lapso_academicos_lac_id_seq OWNER TO davidcode;

--
-- Name: lapso_academicos_lac_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: davidcode
--

ALTER SEQUENCE public.lapso_academicos_lac_id_seq OWNED BY public.lapso_academicos.lac_id;


--
-- Name: mensajes_chat; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.mensajes_chat (
    mch_id bigint NOT NULL,
    mch_id_hilo bigint NOT NULL,
    mch_id_remitente integer NOT NULL,
    mch_cuerpo text,
    mch_ruta_imagen character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.mensajes_chat OWNER TO davidcode;

--
-- Name: mensajes_chat_mch_id_seq; Type: SEQUENCE; Schema: public; Owner: davidcode
--

CREATE SEQUENCE public.mensajes_chat_mch_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.mensajes_chat_mch_id_seq OWNER TO davidcode;

--
-- Name: mensajes_chat_mch_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: davidcode
--

ALTER SEQUENCE public.mensajes_chat_mch_id_seq OWNED BY public.mensajes_chat.mch_id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO davidcode;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: davidcode
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO davidcode;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: davidcode
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO davidcode;

--
-- Name: preguntas_frecuentes; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.preguntas_frecuentes (
    id bigint NOT NULL,
    pregunta character varying(255) NOT NULL,
    respuesta text NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.preguntas_frecuentes OWNER TO davidcode;

--
-- Name: preguntas_frecuentes_id_seq; Type: SEQUENCE; Schema: public; Owner: davidcode
--

CREATE SEQUENCE public.preguntas_frecuentes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.preguntas_frecuentes_id_seq OWNER TO davidcode;

--
-- Name: preguntas_frecuentes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: davidcode
--

ALTER SEQUENCE public.preguntas_frecuentes_id_seq OWNED BY public.preguntas_frecuentes.id;


--
-- Name: requisitos; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.requisitos (
    req_id integer NOT NULL,
    req_nombre_requisito character varying(100) NOT NULL,
    req_descripcion text,
    req_formato_esperado character varying(50)
);


ALTER TABLE public.requisitos OWNER TO davidcode;

--
-- Name: requisitos_req_id_seq; Type: SEQUENCE; Schema: public; Owner: davidcode
--

CREATE SEQUENCE public.requisitos_req_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.requisitos_req_id_seq OWNER TO davidcode;

--
-- Name: requisitos_req_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: davidcode
--

ALTER SEQUENCE public.requisitos_req_id_seq OWNED BY public.requisitos.req_id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO davidcode;

--
-- Name: solicitudes; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.solicitudes (
    sol_id integer NOT NULL,
    sol_usu_id integer NOT NULL,
    sol_tsi_id integer NOT NULL,
    sol_lac_id integer NOT NULL,
    sol_eso_id integer NOT NULL,
    sol_id_seguimiento character varying(50) NOT NULL,
    sol_motivo_detallado text,
    sol_prioridad character varying(20),
    sol_fecha_creacion timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    sol_fecha_ultima_actualizacion timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    sol_fecha_resolucion timestamp(0) without time zone
);


ALTER TABLE public.solicitudes OWNER TO davidcode;

--
-- Name: solicitudes_sol_id_seq; Type: SEQUENCE; Schema: public; Owner: davidcode
--

CREATE SEQUENCE public.solicitudes_sol_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.solicitudes_sol_id_seq OWNER TO davidcode;

--
-- Name: solicitudes_sol_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: davidcode
--

ALTER SEQUENCE public.solicitudes_sol_id_seq OWNED BY public.solicitudes.sol_id;


--
-- Name: tipo_documentos; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.tipo_documentos (
    tdo_id integer NOT NULL,
    tdo_nombre_documento character varying(100) NOT NULL,
    tdo_abreviatura character varying(10) NOT NULL
);


ALTER TABLE public.tipo_documentos OWNER TO davidcode;

--
-- Name: tipo_documentos_tdo_id_seq; Type: SEQUENCE; Schema: public; Owner: davidcode
--

CREATE SEQUENCE public.tipo_documentos_tdo_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tipo_documentos_tdo_id_seq OWNER TO davidcode;

--
-- Name: tipo_documentos_tdo_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: davidcode
--

ALTER SEQUENCE public.tipo_documentos_tdo_id_seq OWNED BY public.tipo_documentos.tdo_id;


--
-- Name: tipo_solicitud_requisitos; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.tipo_solicitud_requisitos (
    tsr_tsi_id integer NOT NULL,
    tsr_req_id integer NOT NULL,
    tsr_es_obligatorio boolean DEFAULT true NOT NULL
);


ALTER TABLE public.tipo_solicitud_requisitos OWNER TO davidcode;

--
-- Name: tipo_solicitudes; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.tipo_solicitudes (
    tsi_id integer NOT NULL,
    tsi_nombre_tipo character varying(100) NOT NULL,
    tsi_descripcion text,
    tsi_tiempo_estimado_dias integer,
    tsi_requiere_aprobacion_especial boolean DEFAULT false NOT NULL,
    tsi_estado_tipo character varying(20) NOT NULL,
    tsi_fecha_inicio date,
    tsi_fecha_fin date
);


ALTER TABLE public.tipo_solicitudes OWNER TO davidcode;

--
-- Name: tipo_solicitudes_tsi_id_seq; Type: SEQUENCE; Schema: public; Owner: davidcode
--

CREATE SEQUENCE public.tipo_solicitudes_tsi_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tipo_solicitudes_tsi_id_seq OWNER TO davidcode;

--
-- Name: tipo_solicitudes_tsi_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: davidcode
--

ALTER SEQUENCE public.tipo_solicitudes_tsi_id_seq OWNED BY public.tipo_solicitudes.tsi_id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.users OWNER TO davidcode;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: davidcode
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO davidcode;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: davidcode
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: usuarios; Type: TABLE; Schema: public; Owner: davidcode
--

CREATE TABLE public.usuarios (
    usu_id integer NOT NULL,
    usu_rol character varying(255) NOT NULL,
    usu_tdo_id integer NOT NULL,
    usu_primer_nombre character varying(50) NOT NULL,
    usu_segundo_nombre character varying(50),
    usu_primer_apellido character varying(50) NOT NULL,
    usu_segundo_apellido character varying(50),
    usu_numero_documento character varying(20) NOT NULL,
    usu_correo_electronico character varying(100) NOT NULL,
    usu_numero_telefono character varying(20),
    usu_contrasena_hash character varying(255) NOT NULL,
    usu_estado_cuenta character varying(20) NOT NULL,
    usu_fecha_registro timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    usu_ultimo_acceso timestamp(0) without time zone,
    CONSTRAINT usuarios_usu_rol_check CHECK (((usu_rol)::text = ANY ((ARRAY['estudiante'::character varying, 'admin'::character varying])::text[])))
);


ALTER TABLE public.usuarios OWNER TO davidcode;

--
-- Name: usuarios_usu_id_seq; Type: SEQUENCE; Schema: public; Owner: davidcode
--

CREATE SEQUENCE public.usuarios_usu_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuarios_usu_id_seq OWNER TO davidcode;

--
-- Name: usuarios_usu_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: davidcode
--

ALTER SEQUENCE public.usuarios_usu_id_seq OWNED BY public.usuarios.usu_id;


--
-- Name: documentaciones doc_id; Type: DEFAULT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.documentaciones ALTER COLUMN doc_id SET DEFAULT nextval('public.documentaciones_doc_id_seq'::regclass);


--
-- Name: estado_solicitudes eso_id; Type: DEFAULT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.estado_solicitudes ALTER COLUMN eso_id SET DEFAULT nextval('public.estado_solicitudes_eso_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: hilos_chat hch_id; Type: DEFAULT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.hilos_chat ALTER COLUMN hch_id SET DEFAULT nextval('public.hilos_chat_hch_id_seq'::regclass);


--
-- Name: historial_estado_solicitudes hes_id; Type: DEFAULT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.historial_estado_solicitudes ALTER COLUMN hes_id SET DEFAULT nextval('public.historial_estado_solicitudes_hes_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: lapso_academicos lac_id; Type: DEFAULT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.lapso_academicos ALTER COLUMN lac_id SET DEFAULT nextval('public.lapso_academicos_lac_id_seq'::regclass);


--
-- Name: mensajes_chat mch_id; Type: DEFAULT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.mensajes_chat ALTER COLUMN mch_id SET DEFAULT nextval('public.mensajes_chat_mch_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: preguntas_frecuentes id; Type: DEFAULT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.preguntas_frecuentes ALTER COLUMN id SET DEFAULT nextval('public.preguntas_frecuentes_id_seq'::regclass);


--
-- Name: requisitos req_id; Type: DEFAULT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.requisitos ALTER COLUMN req_id SET DEFAULT nextval('public.requisitos_req_id_seq'::regclass);


--
-- Name: solicitudes sol_id; Type: DEFAULT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.solicitudes ALTER COLUMN sol_id SET DEFAULT nextval('public.solicitudes_sol_id_seq'::regclass);


--
-- Name: tipo_documentos tdo_id; Type: DEFAULT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.tipo_documentos ALTER COLUMN tdo_id SET DEFAULT nextval('public.tipo_documentos_tdo_id_seq'::regclass);


--
-- Name: tipo_solicitudes tsi_id; Type: DEFAULT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.tipo_solicitudes ALTER COLUMN tsi_id SET DEFAULT nextval('public.tipo_solicitudes_tsi_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: usuarios usu_id; Type: DEFAULT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.usuarios ALTER COLUMN usu_id SET DEFAULT nextval('public.usuarios_usu_id_seq'::regclass);


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: documentaciones; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.documentaciones (doc_id, doc_sol_id, doc_nombre_original_archivo, doc_tipo_documento, doc_formato_archivo, doc_tamano_bytes, doc_ruta_almacenamiento_url, doc_fecha_subida, doc_estado_validacion) FROM stdin;
\.


--
-- Data for Name: estado_solicitudes; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.estado_solicitudes (eso_id, eso_nombre_estado, eso_descripcion) FROM stdin;
1	pendiente	\N
2	aprobada	\N
3	rechazada	\N
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: hilos_chat; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.hilos_chat (hch_id, hch_id_usuario, hch_id_admin, hch_estado, hch_etiqueta_tema, hch_fecha_solicitud_cierre, created_at, updated_at) FROM stdin;
3	6	7	cerrado	dfgfgh	2026-09-26 23:22:47	2026-09-26 23:21:12	2026-09-26 23:22:54
4	6	7	cerrado	dfgh	2026-09-26 23:29:58	2026-09-26 23:28:21	2026-09-26 23:30:07
\.


--
-- Data for Name: historial_estado_solicitudes; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.historial_estado_solicitudes (hes_id, hes_sol_id, hes_usu_id_responsable, hes_eso_id_anterior, hes_eso_id_nuevo, hes_observaciones_comentarios, hes_fecha_cambio) FROM stdin;
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: lapso_academicos; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.lapso_academicos (lac_id, lac_id_lapso, lac_fecha_inicio, lac_fecha_cierre, lac_estado_lapso) FROM stdin;
1	2026-2	2026-07-01	2026-12-15	activo
\.


--
-- Data for Name: mensajes_chat; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.mensajes_chat (mch_id, mch_id_hilo, mch_id_remitente, mch_cuerpo, mch_ruta_imagen, created_at, updated_at) FROM stdin;
1	3	6	kjg	\N	2026-09-26 23:21:12	2026-09-26 23:21:12
2	3	7	asfbnm	\N	2026-09-26 23:21:54	2026-09-26 23:21:54
3	4	6	kkk	\N	2026-09-26 23:28:21	2026-09-26 23:28:21
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
5	2026_08_24_230331_create_tipo_documentos_table	1
6	2026_08_24_230334_create_requisitos_table	1
7	2026_08_24_230336_create_tipo_solicitudes_table	1
8	2026_08_24_230339_create_estado_solicitudes_table	1
9	2026_08_24_230341_create_lapso_academicos_table	1
10	2026_08_24_230344_create_tipo_solicitud_requisitos_table	1
11	2026_08_24_230345_create_usuarios_table	1
12	2026_08_24_230346_create_hilo_chats_table	1
13	2026_08_24_230347_create_mensaje_chats_table	1
14	2026_08_24_230350_create_solicitudes_table	1
15	2026_08_24_230352_create_historial_estado_solicitudes_table	1
16	2026_08_24_230355_create_documentaciones_table	1
17	2026_08_27_221348_add_fechas_disponibilidad_a_tipo_solicitudes	1
18	2026_09_26_212212_cambiar_tipo_respuesta_en_preguntas_frecuentes	2
19	2026_09_26_212756_cambiar_tipo_respuesta_en_preguntas_frecuentes	3
20	2026_08_18_223846_create_preguntas_frecuentes_table	4
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: preguntas_frecuentes; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.preguntas_frecuentes (id, pregunta, respuesta, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: requisitos; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.requisitos (req_id, req_nombre_requisito, req_descripcion, req_formato_esperado) FROM stdin;
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
1pBt3cI65NnvojxaV2zRPrfgQui7DNSW9qCYDCs2	6	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:155.0) Gecko/20100101 Firefox/155.0	eyJfdG9rZW4iOiJob29DVko2dzVyNEF2TFV6WVFjb0VqaFByZ292OTdUSGE5R3V5WjJmIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC91c2VyXC9zb3BvcnRlXC9jaGF0XC80Iiwicm91dGUiOiJ1c2VyLmNoYXQubW9zdHJhciJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjo2fQ==	1790561895
71HItGqPsfe2IxnBN8h5D7XvahEUa0VKp8Db05A0	\N	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:155.0) Gecko/20100101 Firefox/155.0	eyJfdG9rZW4iOiJ5TXFldlBPTmd0REhnVnozbjA3OU5zM2FPb3h3T0pIMnRzZ1V0QmZzIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2xvZ2luIiwicm91dGUiOiJsb2dpbiJ9fQ==	1790466577
atEDlXTncqARf6VonnQuK15OGa2O9rnXacwpJ8Co	6	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:155.0) Gecko/20100101 Firefox/155.0	eyJfdG9rZW4iOiJ5TGhDTWVrOGVhQVhUQWdJV21oclZjNmFMcXRtaTJ1SVhWektFS21QIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3VzZXJcL2Rhc2hib2FyZCIsInJvdXRlIjoiZGFzaGJvYXJkIn0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjo2fQ==	1790983919
9NvikfGoE1OewJBqR5p7Sve02Sg0kGYXalsN0Y6t	7	127.0.0.1	Mozilla/5.0 (X11; Linux x86_64; rv:155.0) Gecko/20100101 Firefox/155.0	eyJfdG9rZW4iOiIxVEZKaWpRRklDSnpMVGgyeVJjY2U3aGp5UTJ0WkFrUVpEaFRmaWd1IiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3VzZXJcL3NvcG9ydGVcL2NoYXQifSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hZG1pblwvZGFzaGJvYXJkIiwicm91dGUiOiJhZG1pbi5kYXNoYm9hcmQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6N30=	1790466443
\.


--
-- Data for Name: solicitudes; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.solicitudes (sol_id, sol_usu_id, sol_tsi_id, sol_lac_id, sol_eso_id, sol_id_seguimiento, sol_motivo_detallado, sol_prioridad, sol_fecha_creacion, sol_fecha_ultima_actualizacion, sol_fecha_resolucion) FROM stdin;
1	1	1	1	1	SOL-BSFYHIR6	Necesito constancia para trámite de beca.	normal	2026-09-10 21:21:27	2026-09-10 21:21:27	\N
2	2	2	1	1	SOL-EQ17EW2F	Deseo cambiarme de Informática a Administración.	normal	2026-09-10 21:21:27	2026-09-10 21:21:27	\N
3	3	3	1	2	SOL-OMUHF9EG	Solicito revisión de la nota de Matemática II.	normal	2026-09-10 21:21:27	2026-09-10 21:21:27	\N
4	4	1	1	3	SOL-YDQNCUXM	Constancia de notas certificadas.	normal	2026-09-10 21:21:27	2026-09-10 21:21:27	\N
5	1	3	1	1	SOL-M62HBSDF	Revisión de nota de Programación I.	normal	2026-09-10 21:21:27	2026-09-10 21:21:27	\N
\.


--
-- Data for Name: tipo_documentos; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.tipo_documentos (tdo_id, tdo_nombre_documento, tdo_abreviatura) FROM stdin;
1	Cédula de Identidad	V
\.


--
-- Data for Name: tipo_solicitud_requisitos; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.tipo_solicitud_requisitos (tsr_tsi_id, tsr_req_id, tsr_es_obligatorio) FROM stdin;
\.


--
-- Data for Name: tipo_solicitudes; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.tipo_solicitudes (tsi_id, tsi_nombre_tipo, tsi_descripcion, tsi_tiempo_estimado_dias, tsi_requiere_aprobacion_especial, tsi_estado_tipo, tsi_fecha_inicio, tsi_fecha_fin) FROM stdin;
1	Constancia de Estudio	\N	3	f	activo	\N	\N
2	Cambio de Carrera	\N	15	f	activo	\N	\N
3	Revisión de Nota	\N	7	f	activo	\N	\N
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at) FROM stdin;
1	Test User	test@example.com	2026-09-10 21:21:24	$2y$12$Z7iiZtnOsBTvfG8i3PkCzOc1PdwJyhIJPJ1MHRE2gmDIQnV8TTzDW	utV86Tbhb9	2026-09-10 21:21:24	2026-09-10 21:21:24
\.


--
-- Data for Name: usuarios; Type: TABLE DATA; Schema: public; Owner: davidcode
--

COPY public.usuarios (usu_id, usu_rol, usu_tdo_id, usu_primer_nombre, usu_segundo_nombre, usu_primer_apellido, usu_segundo_apellido, usu_numero_documento, usu_correo_electronico, usu_numero_telefono, usu_contrasena_hash, usu_estado_cuenta, usu_fecha_registro, usu_ultimo_acceso) FROM stdin;
1	estudiante	1	María	\N	Pérez	\N	V-30123456	maria.perez@ejemplo.com	\N	$2y$12$HHd9qLynF8Od7S1gm49S3.vcvNO6bKtf29LWCyzT.fvKL9bMeKyAO	activo	2026-09-10 21:21:25	\N
2	estudiante	1	Carlos	\N	Gómez	\N	V-28456789	carlos.gomez@ejemplo.com	\N	$2y$12$4jdxMrnvNGvPXQ39tA73wu1Km3DfBz/YERqXgFAsl4bqd0knvCdji	activo	2026-09-10 21:21:26	\N
3	estudiante	1	Andreina	\N	Rojas	\N	V-27998877	andreina.rojas@ejemplo.com	\N	$2y$12$Cl/ndxE83VpVNyvmMn.Nwe7GlHzVO2blJzxFb.sqK2uLvsF95liry	activo	2026-09-10 21:21:26	\N
4	estudiante	1	Luis	\N	Fernández	\N	V-31234567	luis.fernandez@ejemplo.com	\N	$2y$12$00ohRuYOp.FyqCcTULydOOp8uQscCcOQS8tEo1x.ZAOHJs7X0.R7C	activo	2026-09-10 21:21:27	\N
6	estudiante	1	David	\N	Vergara	\N	32846798	aless23ver@gmail.com	\N	$2y$12$99HbYnh6e5Ghp.Aj8Cs4ku1WVVckxT6ej6EEpgpChk842p8bA7tYq	activo	2026-09-26 15:46:19	\N
7	admin	1	Luis	\N	Silva	\N	31757257	lui123@gmail.com	\N	$2y$12$h9A8sX.0DSgohyWBtnwmGuafQZB55gS7XRKv6u0SInVGwA1pnQZMO	activo	2026-09-26 17:05:11	\N
\.


--
-- Name: documentaciones_doc_id_seq; Type: SEQUENCE SET; Schema: public; Owner: davidcode
--

SELECT pg_catalog.setval('public.documentaciones_doc_id_seq', 1, false);


--
-- Name: estado_solicitudes_eso_id_seq; Type: SEQUENCE SET; Schema: public; Owner: davidcode
--

SELECT pg_catalog.setval('public.estado_solicitudes_eso_id_seq', 3, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: davidcode
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: hilos_chat_hch_id_seq; Type: SEQUENCE SET; Schema: public; Owner: davidcode
--

SELECT pg_catalog.setval('public.hilos_chat_hch_id_seq', 4, true);


--
-- Name: historial_estado_solicitudes_hes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: davidcode
--

SELECT pg_catalog.setval('public.historial_estado_solicitudes_hes_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: davidcode
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: lapso_academicos_lac_id_seq; Type: SEQUENCE SET; Schema: public; Owner: davidcode
--

SELECT pg_catalog.setval('public.lapso_academicos_lac_id_seq', 1, true);


--
-- Name: mensajes_chat_mch_id_seq; Type: SEQUENCE SET; Schema: public; Owner: davidcode
--

SELECT pg_catalog.setval('public.mensajes_chat_mch_id_seq', 3, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: davidcode
--

SELECT pg_catalog.setval('public.migrations_id_seq', 20, true);


--
-- Name: preguntas_frecuentes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: davidcode
--

SELECT pg_catalog.setval('public.preguntas_frecuentes_id_seq', 1, false);


--
-- Name: requisitos_req_id_seq; Type: SEQUENCE SET; Schema: public; Owner: davidcode
--

SELECT pg_catalog.setval('public.requisitos_req_id_seq', 1, false);


--
-- Name: solicitudes_sol_id_seq; Type: SEQUENCE SET; Schema: public; Owner: davidcode
--

SELECT pg_catalog.setval('public.solicitudes_sol_id_seq', 5, true);


--
-- Name: tipo_documentos_tdo_id_seq; Type: SEQUENCE SET; Schema: public; Owner: davidcode
--

SELECT pg_catalog.setval('public.tipo_documentos_tdo_id_seq', 1, true);


--
-- Name: tipo_solicitudes_tsi_id_seq; Type: SEQUENCE SET; Schema: public; Owner: davidcode
--

SELECT pg_catalog.setval('public.tipo_solicitudes_tsi_id_seq', 3, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: davidcode
--

SELECT pg_catalog.setval('public.users_id_seq', 1, true);


--
-- Name: usuarios_usu_id_seq; Type: SEQUENCE SET; Schema: public; Owner: davidcode
--

SELECT pg_catalog.setval('public.usuarios_usu_id_seq', 7, true);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: documentaciones documentaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.documentaciones
    ADD CONSTRAINT documentaciones_pkey PRIMARY KEY (doc_id);


--
-- Name: estado_solicitudes estado_solicitudes_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.estado_solicitudes
    ADD CONSTRAINT estado_solicitudes_pkey PRIMARY KEY (eso_id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: hilos_chat hilos_chat_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.hilos_chat
    ADD CONSTRAINT hilos_chat_pkey PRIMARY KEY (hch_id);


--
-- Name: historial_estado_solicitudes historial_estado_solicitudes_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.historial_estado_solicitudes
    ADD CONSTRAINT historial_estado_solicitudes_pkey PRIMARY KEY (hes_id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: lapso_academicos lapso_academicos_lac_id_lapso_unique; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.lapso_academicos
    ADD CONSTRAINT lapso_academicos_lac_id_lapso_unique UNIQUE (lac_id_lapso);


--
-- Name: lapso_academicos lapso_academicos_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.lapso_academicos
    ADD CONSTRAINT lapso_academicos_pkey PRIMARY KEY (lac_id);


--
-- Name: mensajes_chat mensajes_chat_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.mensajes_chat
    ADD CONSTRAINT mensajes_chat_pkey PRIMARY KEY (mch_id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: preguntas_frecuentes preguntas_frecuentes_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.preguntas_frecuentes
    ADD CONSTRAINT preguntas_frecuentes_pkey PRIMARY KEY (id);


--
-- Name: requisitos requisitos_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.requisitos
    ADD CONSTRAINT requisitos_pkey PRIMARY KEY (req_id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: solicitudes solicitudes_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.solicitudes
    ADD CONSTRAINT solicitudes_pkey PRIMARY KEY (sol_id);


--
-- Name: solicitudes solicitudes_sol_id_seguimiento_unique; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.solicitudes
    ADD CONSTRAINT solicitudes_sol_id_seguimiento_unique UNIQUE (sol_id_seguimiento);


--
-- Name: tipo_documentos tipo_documentos_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.tipo_documentos
    ADD CONSTRAINT tipo_documentos_pkey PRIMARY KEY (tdo_id);


--
-- Name: tipo_solicitud_requisitos tipo_solicitud_requisitos_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.tipo_solicitud_requisitos
    ADD CONSTRAINT tipo_solicitud_requisitos_pkey PRIMARY KEY (tsr_tsi_id, tsr_req_id);


--
-- Name: tipo_solicitudes tipo_solicitudes_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.tipo_solicitudes
    ADD CONSTRAINT tipo_solicitudes_pkey PRIMARY KEY (tsi_id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (usu_id);


--
-- Name: usuarios usuarios_usu_correo_electronico_unique; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_usu_correo_electronico_unique UNIQUE (usu_correo_electronico);


--
-- Name: usuarios usuarios_usu_numero_documento_unique; Type: CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_usu_numero_documento_unique UNIQUE (usu_numero_documento);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: davidcode
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: davidcode
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: failed_jobs_connection_queue_failed_at_index; Type: INDEX; Schema: public; Owner: davidcode
--

CREATE INDEX failed_jobs_connection_queue_failed_at_index ON public.failed_jobs USING btree (connection, queue, failed_at);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: davidcode
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: davidcode
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: davidcode
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: documentaciones fk_doc_sol; Type: FK CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.documentaciones
    ADD CONSTRAINT fk_doc_sol FOREIGN KEY (doc_sol_id) REFERENCES public.solicitudes(sol_id) ON DELETE CASCADE;


--
-- Name: historial_estado_solicitudes fk_hes_eso_ant; Type: FK CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.historial_estado_solicitudes
    ADD CONSTRAINT fk_hes_eso_ant FOREIGN KEY (hes_eso_id_anterior) REFERENCES public.estado_solicitudes(eso_id);


--
-- Name: historial_estado_solicitudes fk_hes_eso_nue; Type: FK CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.historial_estado_solicitudes
    ADD CONSTRAINT fk_hes_eso_nue FOREIGN KEY (hes_eso_id_nuevo) REFERENCES public.estado_solicitudes(eso_id);


--
-- Name: historial_estado_solicitudes fk_hes_sol; Type: FK CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.historial_estado_solicitudes
    ADD CONSTRAINT fk_hes_sol FOREIGN KEY (hes_sol_id) REFERENCES public.solicitudes(sol_id) ON DELETE CASCADE;


--
-- Name: historial_estado_solicitudes fk_hes_usu; Type: FK CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.historial_estado_solicitudes
    ADD CONSTRAINT fk_hes_usu FOREIGN KEY (hes_usu_id_responsable) REFERENCES public.usuarios(usu_id);


--
-- Name: solicitudes fk_sol_eso; Type: FK CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.solicitudes
    ADD CONSTRAINT fk_sol_eso FOREIGN KEY (sol_eso_id) REFERENCES public.estado_solicitudes(eso_id);


--
-- Name: solicitudes fk_sol_lac; Type: FK CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.solicitudes
    ADD CONSTRAINT fk_sol_lac FOREIGN KEY (sol_lac_id) REFERENCES public.lapso_academicos(lac_id);


--
-- Name: solicitudes fk_sol_tsi; Type: FK CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.solicitudes
    ADD CONSTRAINT fk_sol_tsi FOREIGN KEY (sol_tsi_id) REFERENCES public.tipo_solicitudes(tsi_id);


--
-- Name: solicitudes fk_sol_usu; Type: FK CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.solicitudes
    ADD CONSTRAINT fk_sol_usu FOREIGN KEY (sol_usu_id) REFERENCES public.usuarios(usu_id);


--
-- Name: tipo_solicitud_requisitos fk_tsr_req; Type: FK CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.tipo_solicitud_requisitos
    ADD CONSTRAINT fk_tsr_req FOREIGN KEY (tsr_req_id) REFERENCES public.requisitos(req_id) ON DELETE CASCADE;


--
-- Name: tipo_solicitud_requisitos fk_tsr_tsi; Type: FK CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.tipo_solicitud_requisitos
    ADD CONSTRAINT fk_tsr_tsi FOREIGN KEY (tsr_tsi_id) REFERENCES public.tipo_solicitudes(tsi_id) ON DELETE CASCADE;


--
-- Name: usuarios fk_usu_tdo; Type: FK CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT fk_usu_tdo FOREIGN KEY (usu_tdo_id) REFERENCES public.tipo_documentos(tdo_id);


--
-- Name: hilos_chat hilos_chat_hch_id_admin_foreign; Type: FK CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.hilos_chat
    ADD CONSTRAINT hilos_chat_hch_id_admin_foreign FOREIGN KEY (hch_id_admin) REFERENCES public.usuarios(usu_id);


--
-- Name: hilos_chat hilos_chat_hch_id_usuario_foreign; Type: FK CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.hilos_chat
    ADD CONSTRAINT hilos_chat_hch_id_usuario_foreign FOREIGN KEY (hch_id_usuario) REFERENCES public.usuarios(usu_id);


--
-- Name: mensajes_chat mensajes_chat_mch_id_hilo_foreign; Type: FK CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.mensajes_chat
    ADD CONSTRAINT mensajes_chat_mch_id_hilo_foreign FOREIGN KEY (mch_id_hilo) REFERENCES public.hilos_chat(hch_id);


--
-- Name: mensajes_chat mensajes_chat_mch_id_remitente_foreign; Type: FK CONSTRAINT; Schema: public; Owner: davidcode
--

ALTER TABLE ONLY public.mensajes_chat
    ADD CONSTRAINT mensajes_chat_mch_id_remitente_foreign FOREIGN KEY (mch_id_remitente) REFERENCES public.usuarios(usu_id);


--
-- PostgreSQL database dump complete
--

\unrestrict oXdvzTtoV7XMZX8yqdrF4f7hdrLlqeVciqiJ6YRIk9zA2eLP0XuT1aw7TS3jtcz

