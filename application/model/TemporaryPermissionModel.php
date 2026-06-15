<?php

/**
 * Class TemporaryPermissionModel
 *
 * DB-Layer für temporäre Feature-Rechte.
 * Ermöglicht es, einzelne Features zeitlich begrenzt an User zu vergeben
 * und nach Ablauf automatisch wieder zu entziehen.
 */
class TemporaryPermissionModel
{
    /**
     * Erteilt einem User ein temporäres Recht für ein Feature.
     *
     * @param int  $user_id         Der User, der das Recht erhält
     * @param string $feature_key   Eindeutiger Feature-Key (z. B. 'gallery', 'chat', 'notes')
     * @param int  $duration_seconds Gültigkeitsdauer in Sekunden ab jetzt
     * @param int|null $granted_by  User-ID desjenigen, der das Recht erteilt (optional)
     *
     * @return int|false Die neu erzeugte permission_id oder false bei Fehler
     */
    public static function grantPermission($user_id, $feature_key, $duration_seconds, $granted_by = null)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $now = time();
        $expires = $now + (int) $duration_seconds;

        $sql = "INSERT INTO temporary_permissions
                    (user_id, feature_key, granted_at, expires_at, granted_by, is_active)
                VALUES
                    (:user_id, :feature_key, :granted_at, :expires_at, :granted_by, 1)";

        $query = $database->prepare($sql);
        $query->execute(array(
            ':user_id'     => $user_id,
            ':feature_key' => $feature_key,
            ':granted_at'  => $now,
            ':expires_at'  => $expires,
            ':granted_by'  => $granted_by
        ));

        return $query->rowCount() == 1 ? (int) $database->lastInsertId() : false;
    }

    /**
     * Deaktiviert ein einzelnes temporäres Recht (Soft-Revoke).
     *
     * @param int $permission_id
     *
     * @return bool
     */
    public static function revokePermission($permission_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "UPDATE temporary_permissions
                SET is_active = 0
                WHERE permission_id = :permission_id
                LIMIT 1";

        $query = $database->prepare($sql);
        $query->execute(array(':permission_id' => $permission_id));

        return $query->rowCount() == 1;
    }

    /**
     * Deaktiviert alle aktiven Rechte für einen User und ein bestimmtes Feature.
     *
     * @param int    $user_id
     * @param string $feature_key
     *
     * @return bool
     */
    public static function revokeAllForUserFeature($user_id, $feature_key)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "UPDATE temporary_permissions
                SET is_active = 0
                WHERE user_id = :user_id
                  AND feature_key = :feature_key
                  AND is_active = 1";

        $query = $database->prepare($sql);
        $query->execute(array(
            ':user_id'     => $user_id,
            ':feature_key' => $feature_key
        ));

        return $query->rowCount() >= 0;
    }

    /**
     * Prüft, ob ein User ein aktives, noch nicht abgelaufenes Recht für ein Feature hat.
     *
     * @param int    $user_id
     * @param string $feature_key
     *
     * @return bool
     */
    public static function hasPermission($user_id, $feature_key)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT permission_id
                FROM temporary_permissions
                WHERE user_id     = :user_id
                  AND feature_key = :feature_key
                  AND is_active   = 1
                  AND expires_at  > :now
                LIMIT 1";

        $query = $database->prepare($sql);
        $query->execute(array(
            ':user_id'     => $user_id,
            ':feature_key' => $feature_key,
            ':now'         => time()
        ));

        return $query->rowCount() == 1;
    }

    /**
     * Holt alle Rechte eines Users (optional nur aktive).
     *
     * @param int  $user_id
     * @param bool $active_only true = nur aktive, false = alle
     *
     * @return array
     */
    public static function getUserPermissions($user_id, $active_only = true)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT tp.*,
                       u.user_name as granted_by_name
                FROM temporary_permissions tp
                LEFT JOIN users u ON tp.granted_by = u.user_id
                WHERE tp.user_id = :user_id";

        if ($active_only) {
            $sql .= " AND tp.is_active = 1 AND tp.expires_at > :now";
        }

        $sql .= " ORDER BY tp.granted_at DESC";

        $query = $database->prepare($sql);
        $params = array(':user_id' => $user_id);

        if ($active_only) {
            $params[':now'] = time();
        }

        $query->execute($params);

        return $query->fetchAll();
    }

    /**
     * Holt alle aktiven Rechte für ein bestimmtes Feature.
     *
     * @param string $feature_key
     *
     * @return array
     */
    public static function getPermissionsByFeature($feature_key)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT tp.*, u.user_name
                FROM temporary_permissions tp
                JOIN users u ON tp.user_id = u.user_id
                WHERE tp.feature_key = :feature_key
                  AND tp.is_active = 1
                  AND tp.expires_at > :now
                ORDER BY tp.expires_at ASC";

        $query = $database->prepare($sql);
        $query->execute(array(
            ':feature_key' => $feature_key,
            ':now'         => time()
        ));

        return $query->fetchAll();
    }

    /**
     * Holt alle abgelaufenen, noch aktiven Rechte (z. B. für Cleanup).
     *
     * @return array
     */
    public static function getExpiredPermissions()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT *
                FROM temporary_permissions
                WHERE is_active = 1
                  AND expires_at <= :now";

        $query = $database->prepare($sql);
        $query->execute(array(':now' => time()));

        return $query->fetchAll();
    }

    /**
     * Löscht alle abgelaufenen Rechte physisch aus der Datenbank.
     *
     * @return int Anzahl der gelöschten Datensätze
     */
    public static function cleanupExpiredPermissions()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "DELETE FROM temporary_permissions
                WHERE expires_at <= :now";

        $query = $database->prepare($sql);
        $query->execute(array(':now' => time()));

        return $query->rowCount();
    }

    /**
     * Holt alle Rechte in der Datenbank (Admin-Übersicht).
     *
     * @return array
     */
    public static function getAllPermissions()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT tp.*,
                       u.user_name,
                       gb.user_name as granted_by_name
                FROM temporary_permissions tp
                JOIN users u ON tp.user_id = u.user_id
                LEFT JOIN users gb ON tp.granted_by = gb.user_id
                ORDER BY tp.granted_at DESC";

        $query = $database->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }
}
