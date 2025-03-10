-- Crear base de datos
CREATE DATABASE garantias;

-- Usar base de datos
USE garantias;

-- Crear tablas
/* Tabla de administrador */
CREATE TABLE configuracion_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave_admin VARCHAR(255) NOT NULL -- Clave única del administrador
);

/* Tabla principal de equipos */
CREATE TABLE equipos (
    assetname VARCHAR(50) PRIMARY KEY,
    serial_number VARCHAR(50) UNIQUE,
    purchase_country VARCHAR(50),
    warranty_enddate VARCHAR(50),
    expired VARCHAR(50),
    new_laptop VARCHAR(50),
    fk_id INT,
    HeadSet VARCHAR(255),
    FOREIGN KEY (fk_id) REFERENCES configuracion_sistema(id)
);

/* Tabla de usuarios relacionada a la tabla de equipos */
CREATE TABLE usuarios_equipos (
    fk_assetname VARCHAR(50) NOT NULL,
    user_status VARCHAR(50) NOT NULL,
    last_user VARCHAR(50),
    job_title VARCHAR(50),
    status_change VARCHAR(50),
    cedula INT,
    Dongle BIGINT,
    Carnet VARCHAR(255),
    LLave VARCHAR(255),
    Tipo_ID VARCHAR(40),
    fecha_salida DATE,
    FOREIGN KEY (fk_assetname) REFERENCES equipos(assetname)
);
-- Insertar datos
/* Crear registro único para el administrador */
INSERT INTO configuracion_sistema (clave_admin) VALUES ('Sena@1234');

-- Crear vistas
/* Vista de consulta general */
CREATE VIEW vista_equipos_usuarios AS
SELECT 
    e.assetname,
    e.serial_number,
    e.purchase_country,
    e.warranty_enddate,
    e.expired,
    e.new_laptop,
    e.HeadSet,
    ue.user_status,
    ue.last_user,
    ue.job_title,
    ue.status_change,
    ue.cedula,
    ue.Dongle,
    ue.Carnet,
    ue.LLave,
    ue.Tipo_ID,
    ue.fecha_salida
FROM equipos e
INNER JOIN usuarios_equipos ue
ON e.assetname = ue.fk_assetname;

--Vista de consulta para contraseña
CREATE VIEW vista_claves_admin AS
SELECT id, clave_admin
FROM configuracion_sistema
WITH CHECK OPTION;