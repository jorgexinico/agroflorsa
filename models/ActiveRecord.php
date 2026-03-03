<?php
namespace Model;

use PDO;
use PDOStatement;

/**
 * ActiveRecord — ORM base para Agroflorsa
 * Provee métodos CRUD genéricos sobre PDO.
 */
class ActiveRecord {

    // Base de datos
    protected static $db;
    protected static $tabla    = '';
    protected static $columnasDB = [];
    protected static $idTabla  = 'id';

    // Alertas y mensajes
    protected static $alertas = [];

    /* ──────────────────────────────────────────────
     *  CONFIGURACIÓN
     * ────────────────────────────────────────────── */

    public static function setDB(PDO $database): void {
        self::$db = $database;
    }

    /* ──────────────────────────────────────────────
     *  ALERTAS
     * ────────────────────────────────────────────── */

    public static function setAlerta(string $tipo, string $mensaje): void {
        static::$alertas[$tipo][] = $mensaje;
    }

    public static function getAlertas(): array {
        return static::$alertas;
    }

    public function validar(): array {
        static::$alertas = [];
        return static::$alertas;
    }

    /* ──────────────────────────────────────────────
     *  CRUD PRINCIPAL
     * ────────────────────────────────────────────── */

    /**
     * Guarda o actualiza el registro según si tiene ID.
     */
    public function guardar(): array {
        $id = static::$idTabla ?? 'id';
        if (!is_null($this->$id)) {
            return $this->actualizar();
        }
        return $this->crear();
    }

    /* ──────────────────────────────────────────────
     *  LECTURAS
     * ────────────────────────────────────────────── */

    public static function all(string $orden = ''): array {
        $query = "SELECT * FROM " . static::$tabla;
        if ($orden) $query .= " ORDER BY $orden";
        return self::consultarSQL($query);
    }

    /**
     * Busca por PK simple o compuesta.
     * @param int|array $id
     */
    public static function find(int|array $id): ?object {
        $idCol = static::$idTabla ?? 'id';
        $query = "SELECT * FROM " . static::$tabla;

        if (is_array($idCol)) {
            $condiciones = [];
            $params      = [];
            foreach ($idCol as $col) {
                $condiciones[] = "$col = :$col";
                $params[":$col"] = $id[$col];
            }
            $query .= " WHERE " . implode(' AND ', $condiciones);
        } else {
            $query  .= " WHERE $idCol = :id LIMIT 1";
            $params  = [':id' => $id];
        }

        $resultado = self::queryPrep($query, $params);
        return array_shift($resultado);
    }

    /**
     * WHERE columna = valor con prepared statement.
     */
    public static function where(string $columna, mixed $valor, string $condicion = '='): array {
        $query  = "SELECT * FROM " . static::$tabla . " WHERE $columna $condicion :valor";
        return self::queryPrep($query, [':valor' => $valor]);
    }

    /**
     * WHERE con múltiples condiciones: ['columna' => valor, ...]
     */
    public static function whereArray(array $condiciones): array {
        $partes  = [];
        $params  = [];
        foreach ($condiciones as $col => $val) {
            $partes[]        = "$col = :$col";
            $params[":$col"] = $val;
        }
        $query = "SELECT * FROM " . static::$tabla . " WHERE " . implode(' AND ', $partes);
        return self::queryPrep($query, $params);
    }

    public static function get(int $limite): ?object {
        $query = "SELECT * FROM " . static::$tabla . " LIMIT $limite";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    /* ──────────────────────────────────────────────
     *  ESCRITURA — PREPARED STATEMENTS
     * ────────────────────────────────────────────── */

    public function crear(): array {
        $atributos = $this->atributos();
        $columnas  = implode(', ', array_keys($atributos));
        $marcas    = implode(', ', array_map(fn($k) => ":$k", array_keys($atributos)));

        $query = "INSERT INTO " . static::$tabla . " ($columnas) VALUES ($marcas)";
        $stmt  = self::$db->prepare($query);

        foreach ($atributos as $col => $val) {
            $stmt->bindValue(":$col", $val);
        }

        $resultado = $stmt->execute();

        return [
            'resultado' => $resultado,
            'id'        => self::$db->lastInsertId(),
        ];
    }

    public function actualizar(): array {
        $atributos = $this->atributos();
        $idCol     = static::$idTabla ?? 'id';

        $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($atributos)));

        if (is_array($idCol)) {
            $conds = implode(' AND ', array_map(fn($c) => "$c = :__$c", $idCol));
            $query = "UPDATE " . static::$tabla . " SET $sets WHERE $conds";
            $stmt  = self::$db->prepare($query);
            foreach ($atributos as $col => $val) {
                $stmt->bindValue(":$col", $val);
            }
            foreach ($idCol as $c) {
                $stmt->bindValue(":__$c", $this->$c);
            }
        } else {
            $query = "UPDATE " . static::$tabla . " SET $sets WHERE $idCol = :__id";
            $stmt  = self::$db->prepare($query);
            foreach ($atributos as $col => $val) {
                $stmt->bindValue(":$col", $val);
            }
            $stmt->bindValue(':__id', $this->$idCol);
        }

        $resultado = $stmt->execute();
        return ['resultado' => $resultado];
    }

    public function eliminar(): bool {
        $idCol = static::$idTabla ?? 'id';
        $query = "DELETE FROM " . static::$tabla . " WHERE $idCol = :id";
        $stmt  = self::$db->prepare($query);
        $stmt->bindValue(':id', $this->$idCol);
        return $stmt->execute();
    }

    /* ──────────────────────────────────────────────
     *  CONSULTAS AVANZADAS
     * ────────────────────────────────────────────── */

    /**
     * Ejecuta SQL con prepared statements y retorna objetos del modelo actual.
     */
    public static function queryPrep(string $sql, array $params = []): array {
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($r) => static::crearObjeto($r), $rows);
    }

    /**
     * Ejecuta SQL libre y retorna array asociativo plano (sin mapear a objeto).
     * Útil para JOINs y reportes.
     */
    public static function fetchRaw(string $sql, array $params = []): array {
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ejecuta SQL y retorna el primer resultado como array asociativo.
     */
    public static function fetchFirstRaw(string $sql, array $params = []): ?array {
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return !empty($resultado) ? $resultado[0] : null;
    }

    /**
     * Ejecuta DML (INSERT/UPDATE/DELETE) con prepared statement.
     * @return bool
     */
    public static function ejecutar(string $sql, array $params = []): bool {
        $stmt = self::$db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * @deprecated Usar fetchRaw(). Mantenido por compatibilidad.
     */
    public static function SQL($consulta) {
        return self::$db->query($consulta);
    }

    public static function consultarSQL(string $query): array {
        $stmt = self::$db->query($query);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return array_map(fn($r) => static::crearObjeto($r), $rows);
    }

    /**
     * @deprecated Usar fetchRaw(). mb_convert_encoding reemplaza utf8_encode deprecado.
     */
    public static function fetchArray(string $query): array {
        $stmt   = self::$db->query($query);
        $rows   = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data   = [];
        foreach ($rows as $row) {
            $data[] = array_change_key_case(
                array_map(fn($v) => is_string($v) ? mb_convert_encoding($v, 'UTF-8', 'UTF-8') : $v, $row)
            );
        }
        $stmt->closeCursor();
        return $data;
    }

    /* ──────────────────────────────────────────────
     *  HELPERS INTERNOS
     * ────────────────────────────────────────────── */

    protected static function crearObjeto(array $registro): static {
        $objeto = new static();
        foreach ($registro as $key => $value) {
            $key = strtolower($key);
            if (property_exists($objeto, $key)) {
                $objeto->$key = $value;
            }
        }
        return $objeto;
    }

    public function atributos(): array {
        $atributos = [];
        $idCol     = is_array(static::$idTabla) ? static::$idTabla : [static::$idTabla ?? 'id'];
        foreach (static::$columnasDB as $columna) {
            $columna = strtolower($columna);
            if (in_array($columna, $idCol)) continue;
            $atributos[$columna] = $this->$columna ?? null;
        }
        return $atributos;
    }

    /**
     * @deprecated Usar prepared statements directamente.
     */
    public function sanitizarAtributos(): array {
        $atributos = $this->atributos();
        $sanitizado = [];
        foreach ($atributos as $key => $value) {
            $sanitizado[$key] = self::$db->quote($value);
        }
        return $sanitizado;
    }

    public function sincronizar(array $args = []): void {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Retorna el PDO para transacciones manuales.
     */
    public static function getDB(): PDO {
        return self::$db;
    }
}
