-- REVISIÓN MANUAL en Agroflorsa. No ejecutado. Requiere columnas existentes.
SELECT portal_issuer, portal_subject, COUNT(*) AS cantidad
FROM usuarios
WHERE portal_issuer IS NOT NULL AND portal_subject IS NOT NULL
GROUP BY portal_issuer, portal_subject HAVING COUNT(*) > 1;
-- Si hay duplicados, revisar sus vínculos antes de continuar; no borrar usuarios.
SELECT COUNT(*) INTO @agro_identity_unique
FROM (
    SELECT INDEX_NAME FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND NON_UNIQUE = 0
    GROUP BY INDEX_NAME
    HAVING COUNT(*) = 2
       AND SUM(COLUMN_NAME IN ('portal_issuer', 'portal_subject')) = 2
       AND SUM(SUB_PART IS NOT NULL) = 0
) AS indices;
SET @agro_identity_sql = IF(@agro_identity_unique > 0,
    'SELECT ''Ya existe índice único de identidad'' AS resultado',
    'ALTER TABLE usuarios ADD UNIQUE KEY uq_portal_identity (portal_issuer, portal_subject)');
PREPARE agro_identity_statement FROM @agro_identity_sql;
EXECUTE agro_identity_statement;
DEALLOCATE PREPARE agro_identity_statement;
