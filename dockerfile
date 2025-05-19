FROM php:8.1-apache
# ติดตั้ง mysqli extension สำหรับเชื่อม MySQL/MariaDB
RUN docker-php-ext-install mysqli
# เปิด mod_rewrite ของ Apache (ถ้าต้องใช้)
RUN a2enmod rewrite
# กำหนดโฟลเดอร์งานภายใน container
WORKDIR /var/www/html