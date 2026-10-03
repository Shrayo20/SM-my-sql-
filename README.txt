SM RENTALS - Camera Rental System (PHP + MySQL)

Run on XAMPP:
1. Open XAMPP Control Panel and start Apache and MySQL.
2. Copy this folder to C:\xampp\htdocs\camera_rental_system
3. Open http://localhost/camera_rental_system/index.php

The database (camera_rental_system), its tables and the 14 products are created
automatically on the first request, using database.sql. No manual import needed.
(You can still import database.sql in phpMyAdmin yourself if you prefer.)

If your MySQL root password is not empty, edit $pass in config.php.
Product photos are remote URLs, so they need internet access; a placeholder is shown if one fails.
