CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone_number VARCHAR(13) NOT NULL UNIQUE,
    visit_count INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    table_number VARCHAR(10) NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    duration VARCHAR(10) NOT NULL,
    status ENUM('Menunggu', 'Aktif', 'Selesai') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    table_number VARCHAR(10) NOT NULL,
    transaction_date DATE NOT NULL,
    duration VARCHAR(10) NOT NULL,
    total_amount INT NOT NULL,
    payment_method ENUM('Cash', 'QRIS', 'Transfer') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert admin user with hashed password (password: admin123)
INSERT INTO users (name, email, username, password, role) VALUES (
    'Admin Billiard',
    'admin@example.com',
    'admin123',
    '$2y$10$jB.qUvbLjH9N04UeTVagNO2ISX5q2aa5TjBlVqOf9DWMvhq61tPFu',
    'admin'
);

-- Insert initial customer data
INSERT INTO customers (name, phone_number, visit_count) VALUES
    ('Andi', '081234567890', 5),
    ('Siti', '082198765432', 3),
    ('Budi', '083123456789', 2);

-- Insert initial booking data
INSERT INTO bookings (customer_name, table_number, booking_date, start_time, duration, status) VALUES
    ('Andi', 'Meja 3', '2025-05-24', '17:00:00', '2 jam', 'Aktif'),
    ('Siti', 'Meja 5', '2025-05-24', '18:30:00', '1 jam', 'Menunggu'),
    ('Budi', 'Meja 2', '2025-05-24', '19:00:00', '1 jam', 'Menunggu');

-- Insert initial transaction data
INSERT INTO transactions (customer_name, table_number, transaction_date, duration, total_amount, payment_method) VALUES
    ('Andi', 'Meja 3', '2025-05-05', '2 jam', 60000, 'Cash'),
    ('Siti', 'Meja 5', '2025-05-05', '1 jam', 30000, 'QRIS');