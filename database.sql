-- ============================================================
-- Online Book Store (OBS) — Database Schema
-- WELAB | 2026
-- Authors: Omaima Naseer (22MDSWE253) | Sobia Iqbal (22MDSWE265)
-- ============================================================

CREATE DATABASE IF NOT EXISTS obs_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE obs_db;

-- ------------------------------------------------------------
-- Table: books
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS books (
    id         INT            NOT NULL AUTO_INCREMENT,
    title      VARCHAR(255)   NOT NULL,
    author     VARCHAR(150)   NOT NULL,
    price      DECIMAL(8,2)   NOT NULL CHECK (price > 0),
    image      VARCHAR(255)   DEFAULT NULL,
    description TEXT          DEFAULT NULL,
    created_at TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: orders
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id            INT            NOT NULL AUTO_INCREMENT,
    customer_name VARCHAR(150)   NOT NULL,
    address       TEXT           NOT NULL,
    total_price   DECIMAL(10,2)  NOT NULL CHECK (total_price >= 0),
    status        ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
    order_date    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: order_items
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id         INT           NOT NULL AUTO_INCREMENT,
    order_id   INT           NOT NULL,
    book_id    INT           NOT NULL,
    quantity   INT           NOT NULL CHECK (quantity > 0),
    unit_price DECIMAL(8,2)  NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_order  FOREIGN KEY (order_id) REFERENCES orders(id)  ON DELETE CASCADE,
    CONSTRAINT fk_book   FOREIGN KEY (book_id)  REFERENCES books(id)   ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: admins  (hashed passwords — bcrypt)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    id           INT          NOT NULL AUTO_INCREMENT,
    username     VARCHAR(80)  NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,  -- bcrypt hash
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Seed Data — Sample Books
-- ------------------------------------------------------------
INSERT INTO books (title, author, price, description) VALUES
('The Great Gatsby',       'F. Scott Fitzgerald', 12.99, 'A novel of the Jazz Age, wealth, and the American Dream.'),
('To Kill a Mockingbird',  'Harper Lee',           14.99, 'A story of racial injustice and moral growth in the American South.'),
('1984',                   'George Orwell',        11.99, 'A dystopian novel about totalitarianism and surveillance.'),
('Pride and Prejudice',    'Jane Austen',           9.99, 'A classic romance exploring class, marriage, and societal expectations.'),
('The Alchemist',          'Paulo Coelho',          13.99, 'A philosophical novel following a shepherd boy on a journey of self-discovery.'),
('Brave New World',        'Aldous Huxley',         12.49, 'A dystopian vision of a genetically engineered future.'),
('The Catcher in the Rye','J.D. Salinger',         10.99, 'A teenager navigates disillusionment and identity in New York.'),
('Harry Potter and the Philosopher\'s Stone', 'J.K. Rowling', 15.99, 'A young wizard discovers his destiny at Hogwarts School of Witchcraft and Wizardry.');

-- ------------------------------------------------------------
-- Seed Data — Default Admin  (password: admin123)
-- Run this in PHP to regenerate hash:
--   echo password_hash('admin123', PASSWORD_BCRYPT);
-- ------------------------------------------------------------
INSERT INTO admins (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- ^^^ This bcrypt hash = "admin123" — change immediately in production!
