CREATE DATABASE IF NOT EXISTS camera_rental_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE camera_rental_system;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    deposit DECIMAL(10,2) NOT NULL,
    image_url TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rentals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(30) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    customer_address TEXT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    days INT NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    deposit DECIMAL(12,2) NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    order_code VARCHAR(30) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rental_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS rental_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rental_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price_per_day DECIMAL(10,2) NOT NULL,
    deposit_per_item DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_item_rental FOREIGN KEY (rental_id) REFERENCES rentals(id) ON DELETE CASCADE,
    CONSTRAINT fk_item_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

INSERT INTO products (id,name,category,price,deposit,image_url) VALUES
(1,'Aperture LS 1200x light','Lights',4000,5000,'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=700&q=80'),
(2,'Sigma 105mm f/2.8 DG DN Macro Art Lens (Sony E)','Sony E-Mount Lenses',1500,3000,'https://images.unsplash.com/photo-1606980707986-1d2e1a8a3c9f?auto=format&fit=crop&w=700&q=80'),
(3,'Venus Optics Laowa 10mm f/2.8 Zero-D FF Autofocus Lens','Sony E-Mount Lenses',2000,3500,'https://images.unsplash.com/photo-1617005082139-548c4dd27f35?auto=format&fit=crop&w=700&q=80'),
(4,'Sony A7 IV Mirrorless Camera','Camera',3500,10000,'https://images.unsplash.com/photo-1510127034890-ba27508e9f1c?auto=format&fit=crop&w=700&q=80'),
(5,'Sony 70-200mm f/2.8 GM OSS II','Sony E-Mount Lenses',3000,7000,'https://images.unsplash.com/photo-1617005082139-548c4dd27f35?auto=format&fit=crop&w=700&q=80'),
(6,'Sony 24-70mm f/2.8 GM II','Sony E-Mount Lenses',2500,6000,'https://images.unsplash.com/photo-1606980707986-1d2e1a8a3c9f?auto=format&fit=crop&w=700&q=80'),
(7,'DJI RS 4 Camera Stabilizer','Camera Stabilizer Systems',2500,5000,'https://images.unsplash.com/photo-1492724441997-5dc865305da7?auto=format&fit=crop&w=700&q=80'),
(8,'SmallHD Wireless Monitor','Monitor And Wireless Video System',2200,4500,'https://images.unsplash.com/photo-1512790182412-b19e6d62bc39?auto=format&fit=crop&w=700&q=80'),
(9,'Manfrotto Professional Tripod','Tripods',1200,2500,'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=700&q=80'),
(10,'Wireless Follow Focus System','Wireless Follow Focus Systems',1800,4000,'https://images.unsplash.com/photo-1551818255-e6e10975bc17?auto=format&fit=crop&w=700&q=80'),
(11,'Cine Prime Lens 50mm T1.5','Cine Lenses',2800,6000,'https://images.unsplash.com/photo-1542567455-cd733f23fbb1?auto=format&fit=crop&w=700&q=80'),
(12,'V-Mount Battery System','Battery System',1000,2000,'https://images.unsplash.com/photo-1484704849700-f032a568e944?auto=format&fit=crop&w=700&q=80'),
(13,'Camera + 24-70mm Bundle','Bundle Deals',5000,12000,'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=700&q=80'),
(14,'EF to Sony E Lens Adapter','Lens Adapters',700,1500,'https://images.unsplash.com/photo-1606980707986-1d2e1a8a3c9f?auto=format&fit=crop&w=700&q=80')
ON DUPLICATE KEY UPDATE name=VALUES(name), category=VALUES(category), price=VALUES(price), deposit=VALUES(deposit), image_url=VALUES(image_url);
