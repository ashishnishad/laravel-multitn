## Laravel-8 Multi-tenancy with multi DB

-----

### How to use

Clone this project to your local computer.

```ps
git clone https://github.com/ashishnishad/laravel-multitn.git
```

Navigate to the project folder.

```ps
cd laravel-multitn
```

create new .env file and edit database credentials there.

```ps
cp .env.example .env
```

Install required packages.

```ps
composer install
```

Generate new app key.

```ps
php artisan key:generate
```

```ps
php artisan migrate
```

```ps
php artisan serve
```

```ps
open url http://localhost:8000/
```

```ps
For other steps refer to attached video
```
