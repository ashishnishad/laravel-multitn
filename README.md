## Laravel-8 Calling digisigner.com APIs Using cURL

-----

### How to use

Clone this project to your local computer.

```ps
git clone https://github.com/ashishnishad/laravel8-digisigner.git
```

Navigate to the project folder.

```ps
cd laravel8-digisigner
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
php artisan passport:install
```

```ps
php artisan serve
```

```ps
open url http://127.0.0.1:8000/
```

```ps
Run Postman Collection To Run Rest Api
```
