CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(150) NOT NULL,
    name_pt VARCHAR(150),
    card_game VARCHAR(50) NOT NULL,
    edition_id VARCHAR(50) NOT NULL,
    edition_name VARCHAR(100) NOT NULL,
    rarity VARCHAR(30) NOT NULL,
    image_url TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Seed: admin / admin123
INSERT IGNORE INTO users (username, password_hash, role)
VALUES ('admin', '$2y$12$FFADTK6vQHyS7NpWo0/wNOk7cd1SOf5ccDGwWzfc.qlg13y8VxRgm', 'admin');
