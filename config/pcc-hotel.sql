-- Room types categorization
CREATE TABLE room_types (
    room_type_id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL,
    description TEXT,
    base_price DECIMAL(10,2) NOT NULL,
    capacity INT NOT NULL,
    amenities TEXT
);

-- Rooms details
CREATE TABLE rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(20) NOT NULL UNIQUE,
    room_type_id INT NOT NULL,
    floor VARCHAR(10),
    description TEXT,
    status ENUM('available', 'occupied', 'maintenance', 'reserved') NOT NULL DEFAULT 'available',
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (room_type_id) REFERENCES room_types(room_type_id)
);