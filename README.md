# BookHaven — Online Book Store (OBS)
**WELAB Web Engineering Project | 2026**
Authors: Omaima Naseer (22MDSWE253) | Sobia Iqbal (22MDSWE265)

---

## 📁 Project Structure

```
obs/
├── config.php                  # DB credentials + helper functions
├── index.php                   # Homepage / Book Catalog
├── book.php                    # Book Detail Page
├── cart.php                    # Shopping Cart
├── checkout.php                # Checkout Form
├── order-confirmation.php      # Order Success Page
├── database.sql                # MySQL schema + seed data
├── css/
│   └── style.css               # All styles
├── js/
│   └── main.js                 # Client-side scripts
├── images/                     # Uploaded book cover images (writable)
├── partials/
│   ├── header.php              # Shared header/nav
│   └── footer.php              # Shared footer
└── admin/
    ├── login.php               # Admin login
    ├── logout.php              # Admin logout
    ├── index.php               # Admin dashboard
    ├── books.php               # Add/delete books
    └── orders.php              # View/update orders
```

---

## ⚙️ Setup Instructions (XAMPP)

### Step 1 — Copy Project
Place the `obs/` folder inside your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\obs\
```

### Step 2 — Create Database
1. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Click **Import** tab
3. Choose `obs/database.sql` and click **Go**

This creates the `obs_db` database with all tables and sample data.

### Step 3 — Configure (if needed)
Open `obs/config.php` and adjust:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'obs_db');
define('DB_USER', 'root');   // your MySQL username
define('DB_PASS', '');       // your MySQL password
```

### Step 4 — Set Permissions
Make the `images/` directory writable so PHP can upload covers:
- On Windows: right-click → Properties → Security → Full Control for IIS_IUSRS

### Step 5 — Launch
Open your browser:
- **Store**: http://localhost/obs/
- **Admin**: http://localhost/obs/admin/login.php

---

## 🔑 Default Admin Credentials

| Username | Password  |
|----------|-----------|
| `admin`  | `admin123` |

> ⚠️ **Change the password immediately** after first login in production!
>
> To generate a new bcrypt hash in PHP:
> ```php
> echo password_hash('your_new_password', PASSWORD_BCRYPT);
> ```
> Then update the `admins` table via phpMyAdmin.

---

## 🗺️ Pages & Features

### Customer-Facing
| Page | URL | Description |
|------|-----|-------------|
| Catalog | `/obs/` | Browse all books; search by title/author |
| Book Detail | `/obs/book.php?id=N` | Full info + Add to Cart |
| Shopping Cart | `/obs/cart.php` | Update quantities, remove items |
| Checkout | `/obs/checkout.php` | Enter name & address; validated |
| Confirmation | `/obs/order-confirmation.php` | Order ID + summary |

### Admin Panel
| Page | URL | Description |
|------|-----|-------------|
| Login | `/obs/admin/login.php` | Credential-gated entry |
| Dashboard | `/obs/admin/` | Stats + recent orders |
| Manage Books | `/obs/admin/books.php` | Add (with image upload) + delete |
| View Orders | `/obs/admin/orders.php` | All orders + status update |

---

## 🔒 Security Measures

- All SQL via **PDO prepared statements** (no SQL injection)
- Passwords hashed with **bcrypt** (`password_hash / password_verify`)
- Admin routes protected by **session check** (`requireAdmin()`)
- **Session timeout** after 30 minutes of inactivity
- All output escaped with `htmlspecialchars()` via `e()` helper
- Session ID regenerated on login (`session_regenerate_id`)
- Error messages **never** expose internal DB details

---

## 🗄️ Database Schema

```
books          → id, title, author, price, image, description, created_at
orders         → id, customer_name, address, total_price, status, order_date
order_items    → id, order_id (FK), book_id (FK), quantity, unit_price
admins         → id, username, password (bcrypt), created_at
```

---

## 📋 Requirements Covered

| # | Requirement | Status |
|---|------------|--------|
| FR-01–05 | Book catalog + search | ✅ |
| FR-06–11 | Session-based shopping cart | ✅ |
| FR-12–17 | Checkout + confirmation | ✅ |
| FR-18–23 | Admin login + book/order management | ✅ |
| NFR-S1–S6 | Security (PDO, bcrypt, sessions) | ✅ |
| NFR-U1–U5 | Usability + validation feedback | ✅ |
| NFR-R1–R3 | Reliability + error handling | ✅ |
| NFR-M1–M3 | Maintainability + modular code | ✅ |

---

*— End of README —*
