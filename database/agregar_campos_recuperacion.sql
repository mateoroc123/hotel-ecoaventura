-- =====================================================
-- Script para agregar campos de recuperacion de password
-- Hotel EcoAventura
-- Ejecutar en phpMyAdmin
-- =====================================================

-- Agregar columna para el codigo de recuperacion
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS codigo_recuperacion VARCHAR(6) NULL,
ADD COLUMN IF NOT EXISTS codigo_expira DATETIME NULL;

-- Si el IF NOT EXISTS no funciona en tu version de MySQL, usa esto:
-- ALTER TABLE usuarios ADD COLUMN codigo_recuperacion VARCHAR(6) NULL;
-- ALTER TABLE usuarios ADD COLUMN codigo_expira DATETIME NULL;

-- Verificar que las columnas se agregaron correctamente
DESCRIBE usuarios;
