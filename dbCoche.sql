-- Ejemplo de script de implementación de BBDD (por ejemplo, 'bbdd.sql')
-- Creamos y empezamos a usar la BBDD

DROP DATABASE IF EXISTS bbdd_reservas;
CREATE DATABASE IF NOT EXISTS bbdd_reservas;
USE bbdd_reservas;

DROP TABLE IF EXISTS reservas;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS coches;

-- Implementación en SQL del modelo de base de datos


CREATE TABLE usuarios (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100),
	fecha_nacimiento DATE,
	dni VARCHAR(9) UNIQUE,
	email VARCHAR(100),
    password VARCHAR(255),
	rol VARCHAR(50)

);


CREATE TABLE coches (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    marca VARCHAR(50),
    modelo VARCHAR(50),
	descripcion VARCHAR(255),
	tipo_combustible VARCHAR(50),
	tipo_transmision VARCHAR(50),
	num_puertas VARCHAR(50),
    precio FLOAT,
	imagen VARCHAR(150),
    estado BOOLEAN
);

CREATE TABLE reservas (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    fecha_recogida DATE,
    fecha_devolucion DATE,
	lugar_recogida VARCHAR (60),
	lugar_devolucion VARCHAR (60),
    id_usuario INT,
    id_coche INT,
    CONSTRAINT id_user2_fk FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT id_coche_fk FOREIGN KEY (id_coche) REFERENCES coches (id) ON DELETE CASCADE
);

INSERT INTO usuarios (username, password, fecha_nacimiento, dni, email, rol) VALUES
('admin', '$2y$10$J0p3zn8xMQ/Hhb4lkuhr9e58/3Jl8YvqC.q6GlWJQ8/rF4qRcbop6', '1989-12-15', '81802971K','admin@gmail.com','admin'),
('maria', '$2y$10$J0p3zn8xMQ/Hhb4lkuhr9e58/3Jl8YvqC.q6GlWJQ8/rF4qRcbop6', '1995-11-10', '43960723H','maria@gmail.com','user'),
('juan', '$2y$10$J0p3zn8xMQ/Hhb4lkuhr9e58/3Jl8YvqC.q6GlWJQ8/rF4qRcbop6', '2000-04-05', '08091098C','juan@gmail.com','user'),
('pepe', '$2y$10$J0p3zn8xMQ/Hhb4lkuhr9e58/3Jl8YvqC.q6GlWJQ8/rF4qRcbop6', '1987-07-15', '23777015Y', 'pepe@gmail.com','user');


INSERT INTO coches ( marca, modelo, descripcion, tipo_combustible, tipo_transmision, num_puertas, precio, imagen, estado) VALUES
('Peugeot', '3008', 'SUV', 'G', 'M', '5', 25,'./img/peugeot_3008.jpg', TRUE),
('Opel', 'Mokka', 'SUV', 'G', 'M', '5', 22,'./img/opel_mokka.jpg', TRUE),
('Nissan', 'Qashqai', 'SUV', 'G', 'A', '5', 20,'./img/nissan_qashqai.jpg', TRUE),
('Volkswagen', 'T-cross', 'SUV', 'G', 'M', '5', 25,'./img/volkswagen_t_cross.jpg', TRUE),
('Ford', 'Focus', 'Deportivo', 'G', 'M', '5', 21,'./img/ford_focus.jpg', TRUE),
( 'Seat', 'Ibiza', 'Urbano', 'D', 'A', '5', 18,'./img/seat_ibiza.jpg', TRUE),
( 'Seat', 'León', 'Deportivo', 'G', 'M', '5', 19,'./img/seat_leon.jpg', TRUE),
( 'Fiat', '500', 'Mini', 'G', 'A', '3', 15,'./img/fiat_500.jpg', TRUE),
( 'Fiat', 'Grande', 'Urbano', 'G', 'M', '3', 17,'./img/fiat_grande.jpg', TRUE),
( 'Citroen', 'C3', 'Urbano', 'D', 'M', '5', 15,'./img/citroen_c3.jpg', TRUE);

-- Formato fecha MySQL: yyyy-mm-dd
INSERT INTO reservas (fecha_recogida, fecha_devolucion, lugar_recogida, lugar_devolucion, id_usuario, id_coche) VALUES
('2022-11-01', '2022-11-15','Málaga', 'Barcelona', 2, 1),
('2022-12-01', '2022-12-05','Madrid', 'Burgos', 3, 2),
('2022-12-10', '2022-12-15','Valencia', 'Málaga', 4, 3),
('2022-12-20', '2022-12-25','Madrid', 'Málaga', 2, 4),
('2023-01-05', '2023-01-10','Alicante', 'Córdoba', 3, 5),
('2023-01-15', '2023-01-30','Lleida', 'Málaga', 4, 1),
('2023-01-15', '2023-01-20','Jaén', 'Cádiz', 2, 2);

