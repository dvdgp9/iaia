-- Migración: Agregar campo output_data a gesture_executions
-- Ejecutar en Plesk > Bases de datos > phpMyAdmin

ALTER TABLE gesture_executions 
ADD COLUMN output_data JSON NULL AFTER output_content;
