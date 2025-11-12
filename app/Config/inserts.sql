-- Insertar roles
INSERT INTO roles (id, name) VALUES 
(1, 'Administrador'),
(2, 'Pasajero'),
(3, 'Chofer');

-- Insertar usuario administrador
-- Usuario: admin, Contraseña: admin123
INSERT INTO users (first_name, last_name, cedula, birth_date, email, phone, username, password) 
VALUES ('Admin', 'Sistema', '000000000', '1990-01-01', 'admin@aventones.com', '88888888', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Asignar rol de administrador al usuario admin
INSERT INTO user_roles (user_id, role_id) VALUES (1, 1);

-- Insertar usuario de prueba (Pasajero)
-- Usuario: juan, Contraseña: pass123
INSERT INTO users (first_name, last_name, cedula, birth_date, email, phone, username, password) 
VALUES ('Juan', 'Pérez', '111111111', '1995-05-15', 'juan@email.com', '87654321', 'juan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Asignar rol de pasajero
INSERT INTO user_roles (user_id, role_id) VALUES (2, 2);

-- Insertar usuario de prueba (Chofer)
-- Usuario: maria, Contraseña: pass123
INSERT INTO users (first_name, last_name, cedula, birth_date, email, phone, username, password) 
VALUES ('María', 'González', '222222222', '1992-08-20', 'maria@email.com', '88889999', 'maria', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Asignar roles de pasajero y chofer
INSERT INTO user_roles (user_id, role_id) VALUES 
(3, 2),
(3, 3);

-- Insertar vehículo de prueba (aprobado)
INSERT INTO vehicles (user_id, brand, model, year, color, plate, status, approved_by, approved_at) 
VALUES (3, 'Toyota', 'Corolla', 2020, 'Blanco', 'ABC123', 'approved', 1, NOW());

-- Insertar un viaje de prueba
INSERT INTO rides (driver_id, vehicle_id, ride_name, departure_location, departure_time, arrival_location, arrival_time, weekdays, price_per_seat, total_seats, available_seats) 
VALUES (3, 1, 'Ruta San José - Heredia', 'San José Centro', '07:00:00', 'Heredia Centro', '08:00:00', 'monday,tuesday,wednesday,thursday,friday', 1500.00, 4, 4);