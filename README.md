# Patna-Car-Rental

Online car rental management system built with PHP and MySQL.

## Requirements

- PHP with the MySQLi extension enabled
- MySQL or MariaDB
- Apache (for example, through XAMPP)
- phpMyAdmin or another MySQL client

## Setup

1. Clone or download this repository into your web server's document root.
2. Create a database named `carrental`.
3. Import `database/carrental.sql` into that database using phpMyAdmin or your preferred MySQL client.
4. Copy `config/config.local.example.php` to `config/config.local.php`.
5. Update the values in `config/config.local.php` for your local database:

   ```php
   <?php

   return [
       'host' => 'localhost',
       'user' => 'root',
       'password' => '',
       'name' => 'carrental',
   ];
   ```

6. Open the project through your local Apache server.

The local configuration file is intentionally excluded from Git. Do not commit real database credentials.

## Environment-based configuration

Instead of creating `config/config.local.php`, you can provide these environment variables:

| Variable | Default | Description |
| --- | --- | --- |
| `DB_HOST` | `localhost` | MySQL server host |
| `DB_USER` | Required | Database username |
| `DB_PASSWORD` | Required (an empty value is allowed) | Database password |
| `DB_NAME` | `carrental` | Database name |

Environment variables take precedence over values in the local configuration file. The application fails with a clear configuration error when the database username or password is not configured, rather than silently attempting to connect with hardcoded root credentials.

The `config/` directory denies direct HTTP access through Apache. Keep `config.local.php` private and use your hosting provider's environment-variable or secret-management facility for deployed environments.

## Screenshots

- **Landing Page**

  ![Landing page](Screenshots/index.jpg)

- **Available Cars**

  ![Available cars](Screenshots/available_cars.png)

- **Add Cars**

  ![Add cars](Screenshots/add_car.png)

- **Booking Confirmation**

  ![Booking confirmation](Screenshots/booking_confirmation.png)

- **Return Car**

  ![Return car](Screenshots/return_car.png)

- **Booking Summary**

  ![Booking summary](Screenshots/bookings.png)
