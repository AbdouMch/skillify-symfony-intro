# 🎓 Introduction to Symfony Framework

Welcome to the Symfony intro app for the summer internship workshop!  
This project will help you get familiar with Symfony’s core concepts by working on a simple event management app.

---

## ✅ Requirements

You can run the app **using Docker (recommended)** or set it up **locally on your machine**.

### 🔧 Docker Setup (Recommended)

Make sure the following are installed:

- Docker
- Docker Compose
- GNU Make
- WSL (for Windows users)

### 💻 Local Setup (Without Docker)

You’ll need:

- PHP >= 8.1
- Composer
- Symfony CLI
- MySQL 8

---

## 🚀 Installation

### 📦 Docker Setup

Run the following command from the project root:

```bash
make init
```

### 🛠 Local Installation (Without Docker)

* Install dependencies

```bash
composer install
```

* Configure environment variables

* Create a file named .env.local at the root of the project based on the .env-sample file:

* Replace db_user, db_password, and symfony_app with your local MySQL credentials.
```dotenv
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/symfony_app?serverVersion=8.0"
```

* Create the database

```bash
php bin/console doctrine:database:create
```

* Run database migrations

```bash
php bin/console doctrine:migrations:migrate
```

* Load fixtures (optional, if provided)

```bash
php bin/console doctrine:fixtures:load
```

* Start the Symfony local server

```bash
symfony serve -d
```


