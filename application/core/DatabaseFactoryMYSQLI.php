<?php

/**
 * Class DatabaseFactoryMysqli
 *
 * Use it like this:
 * $database = DatabaseFactoryMySQLi::getFactory()->getConnection();
 */
class DatabaseFactoryMySQLi
{
    private static $factory;
    private $database;

    public static function getFactory()
    {
        if (!self::$factory) {
            self::$factory = new DatabaseFactoryMySQLi();
        }
        return self::$factory;
    }

    public function getConnection()
    {
        if (!$this->database) {
            try {
                $conn = new mysqli(
                    Config::get('DB_HOST'),
                    Config::get('DB_USER'),
                    Config::get('DB_PASS'),
                    Config::get('DB_NAME')
                );

                if ($conn->connect_error) {
                    die('Connection failed: ' . $conn->connect_error);
                }

                $conn->set_charset(Config::get('DB_CHARSET'));
                $this->database = $conn;

            } catch (Exception $e) {
                echo 'Database connection can not be established. Please try again later.
';
                echo 'Error code: ' . $e->getCode();
                exit;
            }
        }

        return $this->database;
    }
}
 