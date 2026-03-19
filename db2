CREATE TABLE special (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    status ENUM('trending', 'upcoming', 'discount') NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);