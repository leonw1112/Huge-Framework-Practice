<?php

/**
 * Class FeatureRegistry
 *
 * Zentrale Registry für alle Features der Anwendung.
 * Definiert, welche Features temporäre Rechte erfordern
 * und damit über TemporaryPermissionModel gesteuert werden können.
 *
 * Alle Features sind per Default NICHT geschützt (backward-compatible).
 * Ein Admin kann einzelne Features als 'protected' markieren,
 * sodass sie temporäre Rechte voraussetzen.
 *
 * Der Schutz-Status wird in der Tabelle `protected_features` persistiert.
 */
class FeatureRegistry
{
    /**
     * Feature-Definitionen (nur statische Metadaten wie Label).
     * Der Schutz-Status kommt aus der Datenbank.
     */
    private static $featureDefinitions = array(
        'notes'     => array('label' => 'Notizen'),
        'gallery'   => array('label' => 'Galerie'),
        'chat'      => array('label' => 'Chat'),
        'dashboard' => array('label' => 'Dashboard'),
    );

    /**
     * Prüft, ob ein Feature temporäre Rechte erfordert.
     * Liest den Status aus der Datenbank.
     *
     * @param string $feature_key
     *
     * @return bool
     */
    public static function isProtected($feature_key)
    {
        if (!isset(self::$featureDefinitions[$feature_key])) {
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT is_protected FROM protected_features WHERE feature_key = :feature_key LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(':feature_key' => $feature_key));

        $result = $query->fetch();

        // Wenn kein Eintrag in der DB, default = nicht geschützt
        return $result ? (bool) $result->is_protected : false;
    }

    /**
     * Gibt alle registrierten Features zurück, inklusive aktuellem Schutz-Status.
     *
     * @return array
     */
    public static function getAllFeatures()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT feature_key, is_protected FROM protected_features";
        $query = $database->prepare($sql);
        $query->execute();

        $dbFlags = array();
        foreach ($query->fetchAll() as $row) {
            $dbFlags[$row->feature_key] = (bool) $row->is_protected;
        }

        $features = array();
        foreach (self::$featureDefinitions as $key => $def) {
            $features[$key] = array(
                'label'     => $def['label'],
                'protected' => isset($dbFlags[$key]) ? $dbFlags[$key] : false
            );
        }

        return $features;
    }

    /**
     * Schaltet den Schutz eines Features ein oder aus.
     * Persistiert den Status in der Datenbank.
     *
     * @param string $feature_key
     * @param bool   $protected
     *
     * @return bool
     */
    public static function setProtected($feature_key, $protected)
    {
        if (!isset(self::$featureDefinitions[$feature_key])) {
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "INSERT INTO protected_features (feature_key, is_protected, updated_at)
                VALUES (:feature_key, :is_protected, :now)
                ON DUPLICATE KEY UPDATE
                    is_protected = VALUES(is_protected),
                    updated_at = VALUES(updated_at)";

        $query = $database->prepare($sql);
        $query->execute(array(
            ':feature_key'   => $feature_key,
            ':is_protected'  => $protected ? 1 : 0,
            ':now'           => time()
        ));

        return $query->rowCount() >= 0;
    }

    /**
     * Prüft, ob ein Feature-Key überhaupt existiert.
     *
     * @param string $feature_key
     *
     * @return bool
     */
    public static function featureExists($feature_key)
    {
        return isset(self::$featureDefinitions[$feature_key]);
    }

    /**
     * Label eines Features zurückgeben.
     *
     * @param string $feature_key
     *
     * @return string|null
     */
    public static function getLabel($feature_key)
    {
        return isset(self::$featureDefinitions[$feature_key])
               ? self::$featureDefinitions[$feature_key]['label']
               : null;
    }
}
