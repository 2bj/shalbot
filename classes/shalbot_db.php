<?php
/**
 * Description of shalbot_db
 *
 * @author bakyt
 */
class shalbot_db
{
    /**
     * @var PDO
     */
    static private $instance = null;

    /**
     * @return PDO
     */
    public static function getInstance()
    {
        if( self::$instance == null ){
            try {
                $db = new PDO(
                    "mysql:host=".DATABASE_HOST.";dbname=".DATABASE_NAME,
                    DATABASE_USER,
                    DATABASE_PASS,
                    array(PDO::ATTR_PERSISTENT => TRUE)
                );

                $db->exec("SET CHARACTER_SET_CLIENT=utf8");
                $db->exec("SET CHARACTER_SET_RESULTS=utf8");
                $db->exec("SET COLLATION_CONNECTION=utf8_general_ci");

                self::$instance = $db;
            } catch (Exception $e) {
              die($e->getMessage());
            }
        }
        return self::$instance;
    }
    private function __construct(){}
    private function __clone(){}
}