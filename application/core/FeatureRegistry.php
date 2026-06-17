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
 */
class FeatureRegistry
{
    /**
     * Feature-Definitionen.
     * 'protected' => true: Feature erfordert ein temporäres Recht
     * 'protected' => false: Feature ist für alle eingeloggten User verfügbar
     */
    private static $features = array(
        'notes'     => array('protected' => false, 'label' => 'Notizen'),
        'gallery'   => array('protected' => false, 'label' => 'Galerie'),
        'chat'      => array('protected' => false, 'label' => 'Chat'),
        'dashboard' => array('protected' => false, 'label' => 'Dashboard'),
    );

    /**
     * Prüft, ob ein Feature temporäre Rechte erfordert.
     *
     * @param string $feature_key
     *
     * @return bool
     */
    public static function isProtected($feature_key)
    {
        return isset(self::$features[$feature_key])
               && self::$features[$feature_key]['protected'];
    }

    /**
     * Gibt alle registrierten Features zurück.
     *
     * @return array
     */
    public static function getAllFeatures()
    {
        return self::$features;
    }

    /**
     * Schaltet den Schutz eines Features ein oder aus.
     *
     * @param string $feature_key
     * @param bool   $protected
     *
     * @return bool
     */
    public static function setProtected($feature_key, $protected)
    {
        if (isset(self::$features[$feature_key])) {
            self::$features[$feature_key]['protected'] = (bool) $protected;
            return true;
        }
        return false;
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
        return isset(self::$features[$feature_key]);
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
        return isset(self::$features[$feature_key])
               ? self::$features[$feature_key]['label']
               : null;
    }
}
