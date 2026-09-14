<?php
namespace Classes;
use PDO;
use RuntimeException;

final class PortalIdentity
{
    public function __construct(private PDO $db) {}

    public function find(string $issuer, string $subject): ?array
    {
        $q=$this->db->prepare('SELECT id,nombre,rol FROM usuarios WHERE portal_issuer=? AND portal_subject=? AND activo=1');
        $q->execute([$issuer,$subject]);
        return $q->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function link(string $issuer, string $subject, int $localId): void
    {
        if ($issuer==='' || strlen($issuer)>191 || !preg_match('/^[1-9][0-9]{0,63}$/',$subject)) throw new RuntimeException('Identidad central inválida.');
        $this->db->beginTransaction();
        try {
            $sql='SELECT portal_issuer,portal_subject FROM usuarios WHERE id=?';
            if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME)==='mysql') $sql.=' FOR UPDATE';
            $q=$this->db->prepare($sql);$q->execute([$localId]);$current=$q->fetch(PDO::FETCH_ASSOC);
            if (!$current) throw new RuntimeException('Usuario local inexistente.');
            if ($current['portal_subject']!==null && ($current['portal_subject']!==$subject || $current['portal_issuer']!==$issuer)) throw new RuntimeException('El usuario ya tiene otra identidad central.');
            $this->db->prepare('UPDATE usuarios SET portal_issuer=?,portal_subject=? WHERE id=?')->execute([$issuer,$subject,$localId]);
            $this->db->commit();
        } catch (\Throwable $e) {if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }
}
