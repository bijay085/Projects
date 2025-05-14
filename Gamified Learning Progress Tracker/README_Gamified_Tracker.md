# 🎓 Gamified Learning Progress Tracker

A web-based academic performance monitoring system that gamifies student progress through badges, assignments, and visual feedback. This project was built as a formal college submission to combine educational tracking with motivational gaming elements.

---

## 📌 Features

- 🎯 **Progress Visualization** – Track students' academic growth using badges and overall performance indicators.
- 👨‍🏫 **Teacher Panel** – Role-based login system with tools to assign, edit, and review student assignments.
- 👩‍🎓 **Student Dashboard** – Students can view their progress, submitted assignments, and earned badges.
- 🛡️ **Authentication System** – Secure login and session management with role-based access.
- 🏅 **Gamification System** – Teachers assign badges; students can collect and track them over time.

---

## 🗂️ Project Structure

```
glptwor/
├── db and document/
│   ├── *.sql          # Database schema and sample data
│   ├── *.docx/pdf     # Documentation and project report
│   └── *.pptx         # Project presentation
├── php/
│   ├── admin/         # Admin-specific operations
│   ├── ...            # Student/teacher functions
│   ├── session.php    # Login/session management
│   ├── hash_password.php
│   └── logout.php
└── extra/
    └── hash_password.php
```

---

## 🛠️ Tech Stack

- **Frontend:** HTML, CSS, Bootstrap (optional)
- **Backend:** PHP
- **Database:** MySQL
- **Other Tools:** XAMPP, phpMyAdmin

---

## 🧩 Database Setup

1. Import the SQL file `db_gltpdb_with all data.sql` into phpMyAdmin.
2. Adjust database connection settings inside `co.php`.
3. Create roles: `admin`, `teacher`, `student`.

---

## 📄 Documentation

- 📘 `college Gamified Learning Progress Tracker.pdf` – Final report
- 🎞️ `Gamified Learning Progress Tracker.pptx` – Presentation slides
- 📦 `AllMyProjectbackup.sql` – Complete backup with optional schemas

---

## 🔐 Roles & Permissions

| Role     | Permissions                                 |
|----------|---------------------------------------------|
| Admin    | Manage users, roles, and system settings    |
| Teacher  | Assign badges, evaluate students            |
| Student  | Submit assignments, view own progress       |

---

## 🚀 Getting Started

1. Clone the repository and place it in your local server (e.g., `htdocs` in XAMPP).
2. Import the database and configure credentials in `co.php`.
3. Run the project by accessing `localhost/glptwor/php/login.php`.

---

## 📫 Contact

- **Portfolio:** [bijaykoirala0.com.np](https://bijaykoirala0.com.np)
- **Telegram:** [@flamemodparadise](https://t.me/flamemodparadise)
- **Email:** bijaykoirala003@gmail.com

---

> 🎓 *This is my first formal college project and a major milestone in my development journey. Feedback and suggestions are always welcome!*
