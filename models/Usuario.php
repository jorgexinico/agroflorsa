<?php
namespace Models;

use Model\ActiveRecord;

/**
 * Modelo Usuario — tabla `usuarios`
 */
class Usuario extends ActiveRecord {

    protected static $tabla     = 'usuarios';
    protected static $idTabla   = 'id';
    protected static $columnasDB = [
        'id','nombre','usuario','password_hash','rol','activo','creado_en'
    ];

    public ?int    $id            = null;
    public string  $nombre        = '';
    public string  $usuario       = '';
    public string  $password_hash = '';
    public string  $rol           = 'vendedor';
    public int     $activo        = 1;
    public ?string $creado_en     = null;

    // ── Campos de formulario (no van a BD) ──────────
    public string $password        = '';
    public string $password_confirm = '';

    /* ──────────────────────────────────────────────
     *  MÉTODOS DE NEGOCIO
     * ────────────────────────────────────────────── */

    /**
     * Busca usuario activo por nombre de usuario.
     */
    public static function findByUsuario(string $usuario): ?self {
        $resultado = self::where('usuario', $usuario);
        return array_shift($resultado) ?: null;
    }

    /**
     * Verifica la contraseña en texto plano contra el hash almacenado.
     */
    public function verificarPassword(string $password): bool {
        return password_verify($password, $this->password_hash);
    }

    /**
     * Hashea y asigna la contraseña.
     */
    public function hashPassword(): void {
        $this->password_hash = password_hash($this->password, PASSWORD_BCRYPT);
    }

    /* ──────────────────────────────────────────────
     *  VALIDACIONES
     * ────────────────────────────────────────────── */

    public function validar(): array {
        static::$alertas = [];

        if (empty($this->nombre)) {
            self::setAlerta('danger', 'El nombre es obligatorio');
        }
        if (empty($this->usuario)) {
            self::setAlerta('danger', 'El usuario es obligatorio');
        }
        if (empty($this->password)) {
            self::setAlerta('danger', 'La contraseña es obligatoria');
        }

        return static::$alertas;
    }

    public function validarLogin(): array {
        static::$alertas = [];

        if (empty($this->usuario)) {
            self::setAlerta('danger', 'El usuario es obligatorio');
        }
        if (empty($this->password)) {
            self::setAlerta('danger', 'La contraseña es obligatoria');
        }

        return static::$alertas;
    }
}
