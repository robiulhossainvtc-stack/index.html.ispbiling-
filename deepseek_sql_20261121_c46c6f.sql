CREATE DATABASE IF NOT EXISTS isp_billing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE isp_billing;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  role ENUM('super_admin','admin','manager','accountant','collector','support') DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password, full_name, role) VALUES
('admin', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ0V8J3n2x1yT5k6m7N8o9P2', 'Super Admin', 'super_admin');

CREATE TABLE packages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  speed_mbps INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  duration_days INT DEFAULT 30,
  status ENUM('Active','Inactive') DEFAULT 'Active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_code VARCHAR(20) UNIQUE NOT NULL,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  alt_phone VARCHAR(20),
  address TEXT,
  area VARCHAR(100),
  package_id INT,
  status ENUM('Active','Inactive') DEFAULT 'Active',
  due_amount DECIMAL(10,2) DEFAULT 0,
  connection_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL
);

CREATE TABLE bills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bill_no VARCHAR(30) UNIQUE NOT NULL,
  customer_id INT NOT NULL,
  bill_month VARCHAR(7) NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  paid_amount DECIMAL(10,2) DEFAULT 0,
  status ENUM('Unpaid','Partial','Paid') DEFAULT 'Unpaid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  receipt_no VARCHAR(30) UNIQUE NOT NULL,
  customer_id INT NOT NULL,
  bill_id INT,
  amount DECIMAL(10,2) NOT NULL,
  method ENUM('Cash','bKash','Nagad','Rocket','Bank','Online') DEFAULT 'Cash',
  collector VARCHAR(100),
  payment_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

INSERT INTO packages (name, speed_mbps, price, duration_days, status) VALUES
('Home Basic', 10, 600, 30, 'Active'),
('Home Plus', 20, 900, 30, 'Active'),
('Premium', 40, 1400, 30, 'Active'),
('Business Pro', 100, 3000, 30, 'Inactive');

INSERT INTO customers (customer_code, name, phone, area, package_id, status, due_amount, connection_date) VALUES
('ISP-1001', 'রফিকুল ইসলাম', '01711000001', 'ঢাকা - মিরপুর', 2, 'Active', 500, '2024-01-15'),
('ISP-1002', 'সাবরিনা আক্তার', '01822000002', 'ঢাকা - উত্তরা', 1, 'Active', 0, '2024-02-10'),
('ISP-1003', 'মাহবুব হোসেন', '01933000003', 'গাজীপুর - টঙ্গী', 3, 'Inactive', 1400, '2024-01-05'),
('ISP-1004', 'নাসরিন সুলতানা', '01644000004', 'নারায়ণগঞ্জ - সদর', 2, 'Active', 900, '2024-03-20');