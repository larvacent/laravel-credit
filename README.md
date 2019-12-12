# laravel-credit

Laravel 积分

## 环境需求

- PHP >= 7.1.3

## Installation

```bash
composer require larva/laravel-credit -vv
```

## for Laravel

This service provider must be registered.

```php
// config/app.php

'providers' => [
    '...',
    Larva\Credit\CreditServiceProvider::class,
];
```
## 数据表
```php
Schema::table('users', function (Blueprint $table) {
    $table->unsignedInteger('credit')->default(0)->nullable()->after('balance');
    $table->unsignedInteger('level_credit')->default(0)->nullable()->after('credit');
});
```




