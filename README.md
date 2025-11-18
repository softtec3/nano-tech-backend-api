# NanoTech Backend API

Comprehensive REST-like PHP backend that powers the NanoTech e-commerce platform (admin + public/front APIs). This repository contains server-side endpoints for managing products, categories, sales points, warehouses, orders, users, banners, barcodes, and payment integration (bKash).

**Project Type:** PHP API (file-based endpoints)

**Primary Use:** Serve JSON responses for mobile/web frontends and manage admin operations.

--

**Quick Links**

- **Main folder:** root contains admin/API endpoints
- **Public API (frontend):** `front/`
- **Uploads:** `uploads/` (subfolders: `banners/`, `categories/`, `products/`, `salespoints/`, `users/`)
- **Payment integration:** `front/payment/`

--

**Requirements**

- PHP 7.4+ (or PHP 8.x recommended)
- MySQL
- Web server (Apache, Nginx, or use PHP built-in server for development)
- Recommended: Composer (if you add dependencies later)

--

**Repository Layout (important files)**

- `db_connect.php` and `front/db_connect.php`: Database connection helpers used across endpoints.
- `authentication.php`, `auth_admin_only.php`: Admin authentication helpers.
- `index.php`, `login.php`, `logout.php`: Admin-facing entry and authentication.
- `add_product.php`, `update_product.php`, `delete_product_by_id.php`: Product management.
- `create_category.php`, `create_sub_category.php`, `all_categories.php`, `all_sub_categories.php`: Category management.
- `create_sales_point.php`, `all_sales_points.php`, `change_sales_points_status.php`: Sales point management.
- `all_orders.php`, `update_order_status.php`, `get_order_customer_details.php`: Order management and details.
- `all_products_barcodes_by_id.php`, `create_barcodes.php`: Barcode utilities.
- `create_warehouse.php`, `create_warehouse_section.php`, `create_warehouse_sub_section.php`, `all_warehouses.php`: Warehouse management.
- `try_catch_template.php`: pattern used for endpoint error handling.
- `uploads/`: Stores uploaded media (ensure correct writable permissions).
- `front/`: Public API endpoints used by mobile/web customers.
  - `front/user_signup.php`, `front/user_login.php`, `front/user_logout.php`, `front/get_user_information.php`
  - `front/create_customer_order.php`, `front/create_sales_point_order.php` — ordering endpoints
  - `front/payment/` — bKash integration files (`get_token.php`, `create_payment.php`, `callback.php`)

--

Extended folder structure front

```bash
└── front
    ├── all_banners.php
    ├── all_categories.php
    ├── all_products_by_lang.php
    ├── all_products_ids_by_sales_point.php
    ├── all_products_of_sales_point.php
    ├── all_products_summary_of_sales_point.php
    ├── all_sub_categories_by_category.php
    ├── all_sub_categories.php
    ├── auth_sales_point.php
    ├── auth_user.php
    ├── create_customer_order.php
    ├── create_sales_point_order.php
    ├── db_connect.php
    ├── get_customer_details_of_sales_point_order.php
    ├── get_orders_by_sales_point_id.php
    ├── get_orders_by_user_id.php
    ├── get_orders_items_details.php
    ├── get_product_by_id.php
    ├── get_sales_point_order_items_details.php
    ├── get_user_information.php
    ├── payment
    │   ├── bkash_config.php
    │   ├── callback.php
    │   ├── create_payment.php
    │   ├── db_config.php
    │   ├── get_token.php
    │   └── token.json
    ├── product_specification_by_id.php
    ├── update_user_address_information.php
    ├── update_user_profile_info.php
    ├── user_authentication.php
    ├── user_login.php
    ├── user_logout.php
    └── user_signup.php

```

**Environment & Configuration**

- Database: edit `db_connect.php` and `front/db_connect.php` to set your DB host, username, password, and database name. These files contain the MySQLi connection code used by endpoints.
- Payment: `front/payment/db_config.php` and `front/payment/bkash_config.php` store bKash credentials. Keep those secure and do not commit production secrets.
- File uploads: ensure the `uploads/` subfolders exist and are writable by the web server user.

--

**Running Locally (development)**

1. Install PHP and MySQL (e.g., XAMPP, WampServer, or separate PHP+MySQL services).
2. Place the repository folder into your web root (e.g., `C:\xampp\htdocs\nano-tech-backend-api`), or run PHP built-in server from the `api` folder:

```
php -S localhost:8000
```

3. Import your database schema (user should create the required tables). This repo doesn't include a schema file; inspect endpoints to determine required tables and columns.
4. Update `db_connect.php` connection constants/variables with your local DB credentials.
5. Ensure `uploads/` has write permissions.

--

**API Overview & Examples**

This project uses simple PHP scripts as REST-like endpoints. Most endpoints accept GET or POST parameters and return JSON.

General `curl` example (GET):

```
curl "http://localhost:8000/all_products.php"
```

POST example (create product — adapt parameter names to what's expected by `add_product.php`):

```
curl -X POST "http://localhost:8000/add_product.php" \
	-F "name=Example" \
	-F "price=100" \
	-F "image=@/path/to/image.jpg"
```

Authentication

- Admin endpoints often rely on `authentication.php` and `auth_admin_only.php` checks; pass required tokens or session cookies as implemented.
- Front-facing user endpoints in `front/` provide `user_signup.php`, `user_login.php` and token/session handling.

Payments (bKash)

- `front/payment/get_token.php` obtains an API token from bKash.
- `front/payment/create_payment.php` initiates a payment; `callback.php` handles asynchronous callbacks.
- `token.json` stores temporary tokens and is updated by `get_token.php` — ensure this file is writable and protected.

Uploads

- Uploaded files are stored in `uploads/` subfolders; URLs may be returned by endpoints as relative paths. Configure your webserver to serve `uploads/` securely.

--

**Database Notes**

This project expects several tables (products, categories, sub_categories, sales_points, orders, order_items, users, warehouses, sections, banners, barcodes, transactions). There is no central schema file in the repo; examine endpoint SQL queries to infer exact column names and types before creating the database. Get the database sql file from admin.

--

**Security & Production Checklist**

- Move sensitive config values (DB, payment keys) out of the repo and into environment variables or a secure config file.
- Disable verbose error display in production (use logging instead).
- Sanitize and validate all user inputs to avoid SQL injection and XSS.
- Protect `uploads/` against arbitrary script execution (e.g., deny PHP execution in that folder).
- Secure `token.json` and other secrets with strict filesystem permissions.

--

**Troubleshooting**

- If endpoints return DB connection errors: verify `db_connect.php` credentials and that MySQL is running.
- If file uploads fail: check `uploads/` folder permissions and `post_max_size` / `upload_max_filesize` in `php.ini`.
- If payment fails: check bKash credentials in `front/payment/bkash_config.php` and inspect `get_token.php` outputs.

--

**Contributing**

- Open an issue describing the change and create a pull request with focused modifications.
- Keep public secrets out of commits.

--

**Contact / Maintainers**

- Repository owner: `softtec3` (NanoTech backend maintained by Soft-Tech Technology team)
