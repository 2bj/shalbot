<?php
include_once 'shalbot_service_interface.php';
include_once 'abstract/shalbot_service_abstract_private.php';

//http://www.ac-soft.ru/pub/aforisms/aforismjs_wijet.php


/**
 * Description of shalbot_service_aphorism
 *
 * @author bakyt
 */
class shalbot_service_aphorism
    extends    shalbot_service_abstract_private
    implements shalbot_service_interface
{
    /**
     * @var string
     */
    public $cmd_code = '!apho';

    /**
     * @var string
     */
    protected $title = 'Афоризмы';

    /**
     * @var string
     */
    protected $version = '1.0b';

    /**
     * @var string
     */
    protected $table_name = 'shalbot_aphorism';
    
    public function  __construct()
    {
        $this->db_init();
        
        $this->total_count = $this->get_total_count();
    }

    /**
     * @param string $message
     * @return string
     */
    public function get_result($message)
    {
        return $this->get_random();
    }

    /**
     * Get total count
     * @return int
     */
    protected function get_total_count()
    {
        $db = shalbot_db::getInstance();
        $stmt = $db->prepare('SELECT COUNT(*) total_count FROM '.$this->table_name);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total_count']-1;
    }

    /**
     * Get random anecdote
     * @return string
     */
    protected function get_random()
    {
        $db = shalbot_db::getInstance();
        $stmt = $db->prepare('SELECT * FROM '.$this->table_name.' WHERE id > ? LIMIT 0,1');
        $stmt->execute(array(rand(1,$this->total_count)));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['value'];
    }

    /**
     * Init database
     */
    protected function db_init()
    {
        include_once 'classes/shalbot_db.php';
    }
}
