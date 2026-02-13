CREATE DATABASE IF NOT EXISTS milon_vet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE milon_vet;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'Owner',
    email VARCHAR(100),
    phone VARCHAR(30),
    address VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE business_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_name VARCHAR(150) NOT NULL,
    logo_url VARCHAR(255),
    phone VARCHAR(30),
    email VARCHAR(100),
    address VARCHAR(255),
    currency VARCHAR(10) DEFAULT '৳'
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255)
);

CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    address VARCHAR(255),
    balance DECIMAL(10, 2) DEFAULT 0
);

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    address VARCHAR(255),
    due_balance DECIMAL(10, 2) DEFAULT 0
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category_id INT,
    supplier_id INT,
    purchase_price DECIMAL(10, 2) NOT NULL,
    selling_price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

CREATE TABLE purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    discount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    net_amount DECIMAL(10, 2) NOT NULL,
    paid_amount DECIMAL(10, 2) NOT NULL,
    due_amount DECIMAL(10, 2) NOT NULL,
    payment_type VARCHAR(20) NOT NULL,
    purchase_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

CREATE TABLE purchase_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_cost DECIMAL(10, 2) NOT NULL,
    line_total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (purchase_id) REFERENCES purchases(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    memo_no VARCHAR(50) NOT NULL,
    customer_id INT,
    subtotal DECIMAL(10, 2) NOT NULL,
    discount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    rounding DECIMAL(10, 2) NOT NULL DEFAULT 0,
    total DECIMAL(10, 2) NOT NULL,
    paid DECIMAL(10, 2) DEFAULT 0,
    payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

CREATE TABLE sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE expense_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_category_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    expense_date DATE NOT NULL,
    note VARCHAR(255),
    FOREIGN KEY (expense_category_id) REFERENCES expense_categories(id)
);

CREATE TABLE incomes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    income_date DATE NOT NULL,
    note VARCHAR(255)
);

CREATE TABLE accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(30) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE transaction_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(20) NOT NULL
);

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(20) NOT NULL,
    category_id INT NOT NULL,
    account_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    txn_date DATE NOT NULL,
    note VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES transaction_categories(id),
    FOREIGN KEY (account_id) REFERENCES accounts(id)
);

CREATE TABLE account_transfers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_account_id INT NOT NULL,
    to_account_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    transfer_date DATE NOT NULL,
    note VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_account_id) REFERENCES accounts(id),
    FOREIGN KEY (to_account_id) REFERENCES accounts(id)
);

CREATE TABLE assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL
);

CREATE TABLE liabilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL
);

INSERT INTO users (name, username, password_hash, role, email, phone, address)
VALUES ('Milon Admin', 'admin', '$2y$10$ZfWGYLfi9aYxHmyo7LIsIu0V4Um5mO4c4l9kH6cYg8e2PH6mEwO4y', 'Owner', 'owner@milonvet.com', '+8801XXXXXXXXX', 'ঢাকা, বাংলাদেশ');

INSERT INTO business_info (business_name, logo_url, phone, email, address, currency)
VALUES ('Milon Veterinary', '', '+8801XXXXXXXXX', 'info@milonvet.com', 'ফার্মগেট, ঢাকা', '৳');

INSERT INTO categories (name, description) VALUES
('ভেট মেডিসিন', 'ভেটেরিনারি মেডিসিন'),
('ফিড', 'ফিড ও পশুখাদ্য'),
('সাপ্লিমেন্ট', 'ভিটামিন ও সাপ্লিমেন্ট');

INSERT INTO expense_categories (name) VALUES
('স্টাফ বেতন'),
('ডেলিভারি চার্জ'),
('ইউটিলিটি বিল'),
('সাপ্লায়ার পেমেন্ট');

INSERT INTO suppliers (name, phone, address, balance) VALUES
('Vet Pharma Ltd.', '+88016XXXXXXX', 'ঢাকা', 5000.00),
('Green Feed', '+88015XXXXXXX', 'মানিকগঞ্জ', 3500.00);

INSERT INTO customers (name, phone, address, due_balance) VALUES
('রহিম এন্টারপ্রাইজ', '+88017XXXXXXX', 'সাভার', 8200.00),
('সাথী ফিড', '+88019XXXXXXX', 'মানিকগঞ্জ', 4500.00),
('কৃষ্ণা ভেট ক্লিনিক', '+88018XXXXXXX', 'গাজীপুর', 6300.00);

INSERT INTO products (name, category_id, supplier_id, purchase_price, selling_price, stock) VALUES
('ভিটামিন ফিড প্রিমিক্স', 2, 2, 1200.00, 1450.00, 120),
('এন্টি বায়োটিক ভেট', 1, 1, 350.00, 450.00, 25),
('ক্যালসিয়াম সাপ্লিমেন্ট', 3, 1, 220.00, 300.00, 8);

INSERT INTO sales (memo_no, customer_id, total, paid, payment_method) VALUES
('MV-202402-0001', 1, 3200.00, 3200.00, 'ক্যাশ'),
('MV-202402-0002', 2, 4850.00, 2000.00, 'ব্যাংক');

INSERT INTO sale_items (sale_id, product_id, quantity, price) VALUES
(1, 2, 4, 450.00),
(2, 1, 1, 1450.00);

INSERT INTO purchases (supplier_id, total_amount, discount, net_amount, paid_amount, due_amount, payment_type, purchase_date)
VALUES (1, 5000.00, 200.00, 4800.00, 3000.00, 1800.00, 'partial', '2024-02-12');

INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_cost, line_total) VALUES
(1, 2, 10, 350.00, 3500.00),
(1, 3, 10, 150.00, 1500.00);

INSERT INTO expenses (expense_category_id, amount, expense_date, note) VALUES
(3, 1500.00, '2024-02-12', 'ইউটিলিটি বিল'),
(2, 750.00, '2024-02-12', 'ডেলিভারি চার্জ');

INSERT INTO incomes (source, amount, income_date, note) VALUES
('সেলস ক্যাশ', 9800.00, '2024-02-12', 'দৈনিক বিক্রয়'),
('ব্যাংক ট্রান্সফার', 2600.00, '2024-02-12', 'দৈনিক বিক্রয়');

INSERT INTO accounts (name, type) VALUES
('Cash', 'cash'),
('Bank', 'bank'),
('bKash', 'bkash');

INSERT INTO transaction_categories (name, type) VALUES
('Sales', 'income'),
('Service', 'income'),
('Other Income', 'income'),
('Purchase', 'expense'),
('Rent', 'expense'),
('Salary', 'expense'),
('Utilities', 'expense'),
('Other Expense', 'expense');

INSERT INTO assets (name, amount) VALUES
('ইনভেন্টরি স্টক', 120000.00),
('ক্যাশ ইন হ্যান্ড', 25000.00),
('ব্যাংক ব্যালেন্স', 80000.00);


CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO liabilities (name, amount) VALUES
('সাপ্লায়ার বকেয়া', 45000.00),
('স্টাফ বেতন', 15000.00),
('ইউটিলিটি বিল', 6500.00);
