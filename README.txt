PROYECTO LOGIN + CRUD COMPATIBLE CON EL SCRIPT PROPORCIONADO

1. Copia estos archivos dentro de:
   C:\xampp\htdocs\ecommerce

2. El proyecto está configurado para conectarse a la base:
   usuarios

3. En phpMyAdmin, selecciona la base usuarios y ejecuta:
   actualizar_tabla.sql

   El script original ya tiene:
   - password
   - username UNIQUE
   - id AUTO_INCREMENT

4. Inicia Apache y MySQL en XAMPP.

5. Abre:
   http://localhost/ecommerce/crear_admin.php

6. Datos iniciales:
   Usuario: admin
   Contraseña: Admin123*

7. Elimina crear_admin.php después de crear el administrador.

8. Abre:
   http://localhost/ecommerce/

NOTA:
La tabla usa los nombres apellidopaterno y apellidomaterno,
sin guion bajo. El código ya fue adaptado.
La columna medio es NOT NULL, por eso se guarda vacía al crear usuarios.
