USE usuarios;

-- El script original ya incluye la columna password,
-- la clave única de username y AUTO_INCREMENT.
-- Solo ampliamos password para guardar hashes con seguridad.
ALTER TABLE usuarios
    MODIFY COLUMN password VARCHAR(255) NOT NULL;
