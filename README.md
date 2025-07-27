# Bank Sejahtera - Simple Banking Application

A simple web-based banking application built with PHP and MySQL. This project is designed for educational purposes to demonstrate basic web development concepts and to highlight common security vulnerabilities.

**Disclaimer:** ⚠️ This application is for learning purposes only and contains deliberate security flaws. **DO NOT use it in a production environment.**

## Features

*   **User Authentication:** Login and registration system.
*   **Role-Based Access Control:**
    *   **Nasabah (Customer):** Can view their balance, manage their profile, and view their transaction history.
    *   **Teller:** Can view all customer transactions.
    *   **Admin:** Can manage user accounts and roles.
*   **Transaction Management:** Users can add, view, edit, and delete their financial transactions (income and expenses).
*   **Profile Management:** Users can update their personal information and profile picture.
*   **Dashboard:** A summary view of the user's current balance.
*   **Dummy Data Generator:** A script to populate the database with sample users and transactions for testing.

## Technology Stack

*   **Backend:** PHP
*   **Database:** MySQL / MariaDB
*   **Frontend:** HTML, CSS (with a simple, clean interface)

## Setup and Installation

### Prerequisites

*   A local web server environment like [XAMPP](https://www.apachefriends.org/), WAMP, or MAMP.
*   PHP 7.2 or higher.
*   MySQL or MariaDB.

### Installation Steps

1.  **Clone the repository or download the source code** into your web server's root directory (e.g., `htdocs` for XAMPP).

2.  **Database Setup:**
    *   Open your database management tool (e.g., phpMyAdmin).
    *   Create a new database named `bank_sejahtera`.
    *   Run the following SQL queries to create the necessary tables:

    ```sql
    -- Table structure for table `users`
    CREATE TABLE `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `nama_lengkap` varchar(255) NOT NULL,
      `email` varchar(255) NOT NULL,
      `password` varchar(255) NOT NULL,
      `role` enum('admin','teller','nasabah') NOT NULL DEFAULT 'nasabah',
      `alamat` text DEFAULT NULL,
      `no_telepon` varchar(20) DEFAULT NULL,
      `tanggal_lahir` date DEFAULT NULL,
      `foto_profil` varchar(255) DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    -- Table structure for table `transactions`
    CREATE TABLE `transactions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `tipe` enum('pemasukan','pengeluaran') NOT NULL,
      `jumlah` decimal(15,2) NOT NULL,
      `deskripsi` varchar(255) NOT NULL,
      `tanggal_transaksi` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`),
      CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ```

3.  **Create a Default Admin User:**
    *   Run this SQL query to create an admin account. The password is `admin123`.
    ```sql
    -- The password 'admin123' is hashed using MD5: e10adc3949ba59abbe56e057f20f883e
    INSERT INTO `users` (`nama_lengkap`, `email`, `password`, `role`) VALUES
    ('Admin Sejahtera', 'admin@example.com', 'e10adc3949ba59abbe56e057f20f883e', 'admin');
    ```

4.  **Database Connection:**
    *   The database connection is configured in `core/init.php`. If your database credentials are different from the default (user: `root`, password: `(empty)`, db: `bank_sejahtera`), please update them in that file.

5.  **Generate Dummy Data (Optional):**
    *   To populate the application with sample data, navigate to `http://localhost/Bank-Sejahtera/generate_dummy_data.php` in your browser.
    *   This will create 100 customer accounts, each with a random number of transactions.
    *   **IMPORTANT:** Delete the `generate_dummy_data.php` file after use.

6.  **Run the Application:**
    *   Navigate to `http://localhost/Bank-Sejahtera/` in your browser.
    *   You can now log in with the admin credentials or any generated customer accounts.

## Default Credentials

*   **Admin:**
    *   **Email:** `admin@example.com`
    *   **Password:** `admin123`
*   **Customer (Generated):**
    *   **Email:** `nasabah1@example.com`, `nasabah2@example.com`, etc.
    *   **Password:** `password123` (for all generated users)

## Identified Security Vulnerabilities (For Educational Purposes)

This project intentionally includes several common security vulnerabilities. The goal is to learn how to identify and fix them.

1.  **SQL Injection (SQLi)**
    *   **Location:** `login.php`
    *   **Description:** The login form directly concatenates user input into the SQL query, allowing an attacker to manipulate the query and bypass authentication.

2.  **Insecure Direct Object Reference (IDOR)**
    *   **Location:** `edit_transaksi.php`, `hapus_transaksi.php`
    *   **Description:** The application checks for the transaction ID but does not verify if the transaction belongs to the currently logged-in user. This allows a user to view, edit, or delete transactions belonging to other users by simply changing the `id` parameter in the URL.

3.  **Unrestricted File Upload**
    *   **Location:** `profil.php`
    *   **Description:** The profile picture upload feature does not properly validate the file type, allowing an attacker to upload malicious files (e.g., a PHP web shell) to the server.

4.  **Insecure Password Storage**
    *   **Location:** `register.php`, `login.php`, `edit_user.php`, `profil.php`
    *   **Description:** Passwords are hashed using the outdated and insecure `MD5` algorithm. MD5 is susceptible to collision attacks and can be easily cracked using rainbow tables. Modern applications should use strong, salted hashing algorithms like `password_hash()` (Bcrypt).

5.  **Cross-Site Scripting (XSS) - Potential**
    *   **Description:** While `htmlspecialchars()` is used in many places, a thorough audit should be conducted to ensure all user-controlled output is properly escaped to prevent XSS attacks.

