CREATE DATABASE IF NOT EXISTS supercharged_db
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE supercharged_db;

CREATE TABLE categories (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  description TEXT,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE suppliers (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(150) NOT NULL,
  contact_person VARCHAR(100),
  email          VARCHAR(255),
  phone          VARCHAR(50),
  address        TEXT,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  supplier_id INT DEFAULT NULL,
  name        VARCHAR(200) NOT NULL,
  description TEXT,
  price       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  stock       INT NOT NULL DEFAULT 0,
  min_stock   INT NOT NULL DEFAULT 10,
  barcode     VARCHAR(50) DEFAULT NULL,
  image_url   VARCHAR(500) DEFAULT NULL,
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
  FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE orders (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  supplier_id INT DEFAULT NULL,
  order_date  DATE NOT NULL,
  status      ENUM('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  notes       TEXT,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  order_id   INT NOT NULL,
  product_id INT NOT NULL,
  quantity   INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE users (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  username       VARCHAR(50) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  display_name   VARCHAR(100) NOT NULL,
  email          VARCHAR(255),
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO users (username, password_hash, display_name, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'admin@supercharged.nl');

INSERT INTO categories (name, description) VALUES
('Zuivel', 'Melk, kaas, yoghurt en andere zuivelproducten'),
('Brood & Banket', 'Vers brood, gebak en ontbijtproducten'),
('Groente & Fruit', 'Verse groenten en fruit'),
('Vlees & Vis', 'Vers vlees, gevogelte en vis'),
('Dranken', 'Frisdrank, sappen, water en alcoholische dranken'),
('Diepvries', 'Diepvriesproducten en ijs'),
('Ontbijtgranen & Spreads', 'Cornflakes, hagelslag, pindakaas'),
('Huishouden', 'Schoonmaakmiddelen en huishoudelijke artikelen');

INSERT INTO suppliers (name, contact_person, email, phone, address) VALUES
('FreshFoods B.V.', 'Jan de Vries', 'jan@freshfoods.nl', '010-1234567', 'Industrieweg 12, 3014 AB Rotterdam'),
('Zuivelhoeve Groep', 'Maria Jansen', 'maria@zuivelhoeve.nl', '020-7654321', 'Melkweg 8, 1015 AB Amsterdam'),
('Vers & Rechtstreeks', 'Pieter Bakker', 'pieter@versrechtstreeks.nl', '030-9876543', 'Oogststraat 22, 3512 JK Utrecht');

INSERT INTO products (category_id, supplier_id, name, description, price, stock, min_stock) VALUES
(1, 2, 'Volle Melk 1L', 'Verse volle melk, 1 liter', 1.89, 120, 30),
(1, 2, 'Goudse Kaas 48+', 'Gerijpte Goudse kaas, 500g', 4.99, 45, 10),
(1, 2, 'Magere Yoghurt 500g', 'Magere yoghurt, natuur', 1.29, 60, 20),
(2, 1, 'Wit Brood', 'Vers witbrood, heel', 2.49, 30, 10),
(2, 1, 'Volkoren Brood', 'Gezond volkorenbrood', 2.79, 25, 10),
(3, 3, 'Appels Goudreinet', 'Nederlandse goudreinetten, 1kg', 2.99, 80, 20),
(3, 3, 'Bananen', 'Cavendish bananen, 1kg', 1.69, 100, 30),
(4, 1, 'Kipfilet 500g', 'Verse kipfilet, 500 gram', 6.99, 35, 10),
(4, 1, 'Rundergehakt 500g', 'Half-om-half gehakt', 5.49, 40, 15),
(5, NULL, 'Sinaasappelsap 1L', 'Versgeperste sinaasappelsap', 2.49, 50, 15),
(5, NULL, 'Cola 1.5L', 'Frisdrank, 1.5 liter fles', 1.99, 200, 50),
(6, 1, 'Diepvries Doperwten 1kg', 'Diepgevroren doperwten', 2.19, 60, 20),
(6, NULL, 'Vanille Ijs 1L', 'Romig vanille ijs', 3.99, 40, 10),
(7, NULL, 'Cornflakes 500g', 'Klassieke cornflakes', 3.49, 55, 15),
(7, 3, 'Hagelslag Puur 250g', 'Pure chocoladehagelslag', 2.29, 70, 20),
(8, NULL, 'Allesreiniger 750ml', 'All-purpose schoonmaakmiddel', 1.99, 90, 25);

INSERT INTO orders (supplier_id, order_date, status, notes) VALUES
(1, '2026-01-10', 'delivered', 'Weekelijkse bevoorrading verswaren'),
(1, '2026-01-24', 'delivered', NULL),
(2, '2026-02-01', 'confirmed', 'Extra kaas bestelling ivm feestdagen'),
(3, '2026-02-05', 'pending', NULL),
(1, '2026-02-08', 'shipped', 'Spoedbestelling kipfilet');

INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 1, 24, 1.75),
(1, 2, 12, 4.50),
(1, 3, 20, 1.15),
(2, 6, 40, 2.50),
(2, 7, 30, 1.45),
(3, 2, 24, 4.50),
(3, 4, 10, 2.29),
(4, 8, 15, 6.50),
(4, 9, 20, 5.00),
(5, 10, 12, 2.29);
