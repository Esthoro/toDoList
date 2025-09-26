# ToDo & Co – Task Management Application
## Overview

ToDo & Co is a Symfony-based web application for managing daily tasks. This project improves an existing MVP by fixing bugs, adding new features, and implementing automated tests to ensure quality and reliability.

## Features

User Management: Create/edit users, assign roles (ROLE_USER / ROLE_ADMIN). Admins only can manage users.
Task Management: Create/edit/toggle/delete tasks. Tasks linked to their creator; anonymous tasks managed by admins. Only creators can delete their tasks.
Authentication & Authorization: Role-based access control using Symfony Security.
Automated Testing: PHPUnit unit/functional tests, >70% coverage.
Documentation: Clear instructions for authentication, project architecture, and contribution guidelines.

## Prerequisites

- PHP 8.2
- Composer
- Symfony CLI (recommended)
- A running database (MySQL or PostgreSQL)

## Installation
git clone https://github.com/Esthoro/toDoList.git
cd todolist
composer install
cp .env .env.local   # configure DB and env variables
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load  # optional
symfony server:start

Access the app at http://127.0.0.1:8000

Running Tests
php bin/phpunit
php bin/phpunit --coverage-html var/coverage

## License

For educational purposes only.
