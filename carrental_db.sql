CREATE DATABASE IF NOT EXISTS carrental_db;
USE carrental_db;

CREATE TABLE customer (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50),
    licence_no VARCHAR(30),
    phone VARCHAR(15)
);

CREATE TABLE car (
    car_id INT AUTO_INCREMENT PRIMARY KEY,
    model VARCHAR(50),
    reg_no VARCHAR(30),
    status VARCHAR(20) DEFAULT 'Available'
);

CREATE TABLE rental (
    rental_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    car_id INT,
    start_date DATE,
    end_date DATE,
    FOREIGN KEY (customer_id) REFERENCES customer(customer_id),
    FOREIGN KEY (car_id) REFERENCES car(car_id)
);

INSERT INTO customer (customer_id, name, licence_no, phone) VALUES
(1, 'Joseph', 'KL-DL-1001', '9876500001'),
(2, 'Anu', 'KL-DL-1002', '9876500002'),
(3, 'Rahul', 'KL-DL-1003', '9876500003'),
(4, 'Meera', 'KL-DL-1004', '9876500004'),
(5, 'Arun', 'KL-DL-1005', '9876500005');

INSERT INTO car (car_id, model, reg_no, status) VALUES
(1, 'Swift', 'KL-07-AB-1001', 'Available'),
(2, 'Baleno', 'KL-07-AB-1002', 'Available'),
(3, 'City', 'KL-07-AB-1003', 'Available'),
(4, 'Creta', 'KL-07-AB-1004', 'Available'),
(5, 'Innova', 'KL-07-AB-1005', 'Available');

INSERT INTO rental (customer_id, car_id, start_date, end_date) VALUES
(1, 1, '2026-11-01', '2026-11-03'),
(2, 2, '2026-11-05', '2026-11-07'),
(3, 3, '2026-11-10', '2026-11-12');
