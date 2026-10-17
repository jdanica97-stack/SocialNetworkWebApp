# SMCC Connect - Social Network Web Application

A full-featured collegiate social networking web application built with pure PHP, MySQL, Bootstrap 5.3, and standard Model-View-Controller (MVC) architecture.

---

## 📁 Project Deliverables & Folder Structure

This project adheres strictly to the required MVC structure:

```text
SocialNetworkWebApp/
├── app/
│   ├── controllers/          # Application controllers handling HTTP requests
│   │   ├── AdminController.php
│   │   ├── AuthController.php
│   │   ├── CommentController.php
│   │   ├── FollowController.php
│   │   ├── InsightsController.php
│   │   ├── MessageController.php
│   │   ├── NotificationController.php
│   │   ├── PostController.php
│   │   ├── ProfileController.php
│   │   ├── ReportController.php
│   │   ├── SearchController.php
│   │   └── SettingsController.php
│   ├── helpers/              # Security and error handling helpers
│   │   ├── ErrorHandler.php
│   │   └── Security.php
│   ├── models/               # Database models and data access logic
│   │   ├── AdminModel.php
│   │   ├── CommentModel.php
│   │   ├── EventModel.php
│   │   ├── FollowModel.php
│   │   ├── MessageModel.php
│   │   ├── NotificationModel.php
│   │   ├── PostModel.php
│   │   ├── ReportModel.php
│   │   └── UserModel.php
│   └── views/                # Presentation templates (HTML / PHP)
│       ├── admin/
│       ├── auth/
│       ├── comments/
│       ├── home/
│       ├── insights/
│       ├── layouts/
│       ├── messages/
│       ├── posts/
│       ├── profile/
│       ├── reports/
│       ├── search/
│       └── settings/
├── config/
│   └── database.php          # Database configuration and PDO connection
├── public/                   # Publicly accessible web root
│   ├── index.php             # Front controller & action router
│   ├── assets/               # CSS stylesheets, JS scripts, icons
│   │   ├── css/
│   │   │   ├── cropper.min.css
│   │   │   └── style.css
│   │   └── js/
│   │       ├── cropper.min.js
│   │       ├── interactions.js
│   │       └── theme.js
│   └── uploads/              # Media upload directories (avatars, covers, posts)
│       ├── avatars/
│       ├── covers/
│       └── posts/
├── sql/
│   └── social_app.sql        # Database schema (CREATE TABLE) and sample seed data
├── tests/                    # PHPUnit automated test suite (Unit & Integration)
├── composer.json             # PHPUnit dependencies
├── phpunit.xml               # Test configuration
└── README.md                 # Project documentation
```

---

## 🗄️ Database Setup (SQL File)

The database schema and sample data are located in [`sql/social_app.sql`](sql/social_app.sql).

### Tables Included:
1. `users` - User credentials, full name, bio, profile & cover photos, role (admin/user), privacy settings.
2. `posts` - Post text content, multi-image support, timestamps.
3. `comments` - Post discussions and nested/chronological responses.
4. `likes` - Post reactions with unique user-post constraint.
5. `follows` - Follower and following relationships between users.
6. `messages` - Direct messages between users (read, edit, unsend flags).
7. `notifications` - In-app activity alerts for likes, comments, and follows.
8. `events` - Campus activities and announcements calendar.

### How to Import via phpMyAdmin / MySQL CLI:
1. Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
2. Click on **Import**.
3. Choose the file `sql/social_app.sql` from this repository.
4. Click **Go** / **Import**. The database `social_app` and all 8 tables with sample seed records will be created automatically.

Alternatively, via MySQL CLI:
```bash
mysql -u root -p < sql/social_app.sql
```

---

## 👥 Default Seed Accounts

The sample database comes pre-seeded with test accounts. All passwords are encrypted with bcrypt (`PASSWORD_BCRYPT`):

| Username | Password | Role | Bio / Description |
| :--- | :--- | :--- | :--- |
| **`alice_wonder`** | `password123` | **Admin** | Web developer and open-source enthusiast |
| **`bob_builder`** | `password123` | **User** | Passionate about coding and technology |
| **`charlie_brown`** | `password123` | **User** | Coffee lover and backend explorer |

---

## 🚀 Installation & Running the Project

### Prerequisites:
- **XAMPP** (PHP 8.0+ and MySQL / MariaDB)
- Apache web server running on port `80`
- MySQL service running on port `3306`

### Steps:
1. Clone or extract this repository into your XAMPP web root:
   ```text
   C:\xampp\htdocs\SocialNetworkWebApp
   ```
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Import `sql/social_app.sql` via phpMyAdmin.
4. Verify database connection credentials in `config/database.php` (defaults: `host=127.0.0.1`, `user=root`, `password=""`, `dbname=social_app`).
5. Open your web browser and navigate to:
   ```text
   http://localhost/SocialNetworkWebApp/
   ```

---

## 🛡️ Key Features & Security Implementation

- **MVC Separation**: Clear division between Models (PDO queries), Views (UI templates), and Controllers (business logic).
- **Security & Protection**:
  - **CSRF Tokens**: Form submissions protected with cryptographic session tokens.
  - **SQL Injection Prevention**: 100% prepared statements across all PDO queries with bound parameters.
  - **XSS Sanitization**: User-submitted content safely escaped with `htmlspecialchars()` and UTF-8 encoding.
  - **Secure Password Hashing**: Passwords stored using `password_hash()` with modern bcrypt algorithms.
  - **File Upload Safeguards**: MIME validation, extension whitelisting, image dimension checks, and random file renaming.
- **Rich User Experience**:
  - **Interactive Reactions**: Like/unlike posts dynamically with instant counter updates and a **"View who reacted"** modal.
  - **Pre-Post Image Cropping**: Cropper tool allowing rotation, pan, flip, and aspect ratio crop before uploading.
  - **Facebook-Style Media Format**: Clean edge-to-edge layout for single and multi-image posts.
  - **Responsive & Dark Mode**: Full desktop and mobile support with instant dark/light theme switching.

---

## 🧪 Automated Testing

The project includes an automated PHPUnit test suite covering unit helpers and database integration:

```bash
# Run PHPUnit tests (from repository root)
vendor/bin/phpunit
```

All 60 tests and 215 assertions pass with zero failures.
