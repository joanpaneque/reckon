# reckon

a professional time tracking and project management application built with Laravel and Vue.js. designed for freelancers, consultants, and small teams who need to track work time and calculate project costs efficiently.

## features

- **work order management**: create and organize projects with custom hourly rates
- **time tracking**: start/stop timers for individual work sessions with real-time updates
- **cost calculation**: automatic cost computation based on tracked time and hourly rates
- **entry management**: detailed work entries with descriptions and timestamps
- **responsive interface**: modern, clean UI built with Tailwind CSS and Vue.js
- **user authentication**: secure login system with session management
- **real-time updates**: live timer displays and instant data synchronization

## tech stack

- **backend**: Laravel 12 with PHP 8.2+
- **frontend**: Vue.js 3 with Inertia.js for seamless SPA experience
- **styling**: Tailwind CSS with custom components
- **database**: MySQL with Laravel Eloquent ORM
- **authentication**: Laravel Sanctum
- **development**: Vite for fast builds and hot reloading
- **deployment**: Docker support with Laravel Sail

## requirements

- Docker and Docker Compose
- Git

## installation

1. clone the repository:
```bash
git clone https://github.com/joanpaneque/reckon
cd reckon
```

2. create environment file:
```bash
cp .env.example .env
```

3. start the Docker containers:
```bash
./vendor/bin/sail up -d
```

4. install PHP dependencies:
```bash
./vendor/bin/sail composer install
```

5. generate application key:
```bash
./vendor/bin/sail artisan key:generate
```

6. run database migrations:
```bash
./vendor/bin/sail artisan migrate
```

7. install JavaScript dependencies:
```bash
./vendor/bin/sail npm install
```

8. build frontend assets for production:
```bash
./vendor/bin/sail npm run build
```

## development

the project uses Laravel Sail for a consistent Docker-based development environment:

```bash
# start all services (Laravel, MySQL, Vite)
./vendor/bin/sail up -d

# start development with hot reloading
./vendor/bin/sail npm run dev
```

alternatively, use the built-in composer script for concurrent services:

```bash
# start all services (Laravel, queue, logs, Vite) simultaneously
./vendor/bin/sail composer run dev
```

### useful sail commands

```bash
# access the application container
./vendor/bin/sail shell

# run artisan commands
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan tinker

# run tests
./vendor/bin/sail test

# view logs
./vendor/bin/sail logs

# stop all services
./vendor/bin/sail down
```

## usage

1. **create work orders**: set up projects with names and hourly rates
2. **track time**: create entries within work orders and use the timer functionality
3. **monitor progress**: view real-time timers and accumulated time/costs
4. **manage entries**: edit, delete, and organize work sessions
5. **calculate costs**: automatic cost computation based on time and rates

## project structure

```
reckon/
├── app/
│   ├── Http/Controllers/WorkOrders/    # work order and entry controllers
│   └── Models/                         # Eloquent models
├── resources/
│   ├── js/
│   │   ├── Components/                 # Vue.js components
│   │   ├── Layouts/                    # application layouts
│   │   └── Pages/                      # Inertia.js pages
│   └── css/                           # stylesheets
├── database/migrations/               # database schema
└── routes/web.php                     # application routes
```

## key components

- **work orders**: main project containers with hourly rates
- **work order entries**: individual time tracking sessions
- **live timer**: real-time countdown display for active sessions
- **time display**: formatted time and cost calculations
- **authentication**: secure user management system

## license

this project is open-sourced software licensed under the MIT license.
