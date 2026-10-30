# AI Skills Mirror — Backend API

RESTful PHP API and MySQL database backend for the **AI Skills Mirror** Android application.

## 🚀 Features

- **Authentication**: Secure bcrypt-hashed signup, login, and password recovery.
- **Profile Management**: Profile fetching, field updates (bio, career goals, education, skills), and multipart profile picture uploads.
- **Dashboard**: User performance metrics, daily training streak, study minutes, and active lesson tracking.
- **Assessments**: Skill categories, multi-step scenario questions, option scoring, and automated skill score recalculation.
- **Progress Tracking & Analytics**: Skill Mirror tier summaries, baseline vs. current comparisons, and growth report breakdowns.
- **Roadmap & Daily Training**: Personalized 4-week growth steps and daily practice tasks.
- **Speech Studio & Analysis**: Speaking prompts and AI speech analysis (pronunciation, fluency, clarity, transcripts, and tips).
- **Milestones & Achievements**: Streak progress, milestone targets, and XP tracking.
- **Notifications**: Updates and daily training reminders.
- **AI Coach Mira**: Interactive chat with conversational interview and skill coaching.
- **Settings**: Persistent user preferences, notification toggles, and appearance settings.

---

## 🛠️ Tech Stack & Requirements

- **PHP**: 8.0+
- **Database**: MySQL 5.7+ / MariaDB 10.4+
- **Server**: Apache (via XAMPP / WAMP / LAMP)
- **Architecture**: REST API with MySQLi prepared statements & JSON responses

---

## ⚙️ Setup & Installation

### 1. Place in Apache Web Root
Copy or clone this repository into your XAMPP web root directory:
```
C:\xampp\htdocs\skillmirrorbackend
```

### 2. Start Services
Open the **XAMPP Control Panel** and start **Apache** and **MySQL**.

### 3. Import Database
Open [phpMyAdmin](http://localhost/phpmyadmin) in your browser:
1. Create a database named `skill mirror` (or let the schema file create it).
2. Import `database/schema.sql` (fresh installation) or run `database/migrations/01_initial_schema.sql` and `database/seed.sql`.

Alternatively, import via MySQL CLI:
```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql
```

### 4. Database Configuration
Verify database connection details in `config/config.php`:
```php
$host = "localhost";
$username = "root";
$password = "";
$database = "skill mirror";
```

---

## 🧪 Postman Collection

A complete, ready-to-import Postman collection (v2.1) is included in the root directory:
📄 **`postman_collection.json`**

Import this file into Postman to test all 24 API endpoints with pre-configured requests, headers, and test bodies.

---

## 📱 Mobile App Connection

- **Android Emulator**: Uses `http://10.0.2.2/skillmirrorbackend` to access the host machine's `localhost`.
- **Physical Device**: Connect to the same Wi-Fi network and replace `10.0.2.2` with your computer's local IPv4 address (e.g. `http://192.168.x.x/skillmirrorbackend`).
