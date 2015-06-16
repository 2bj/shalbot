<?php
include_once 'shalbot_service_interface.php';
include_once 'abstract/shalbot_service_abstract_private.php';


// http://www.rzhunemogu.ru/widzh/anekdot.aspx?wauth=1..11pr.2621-1.1260171966117833.16433411.16433411.6.72642668.418.9af7341d2dde1cf9d2c0db4799a9653b&.=http%3a%2f%2fwww.yandex.ru|yandex.ru


/**
 * Description of shalbot_service_anecdote
 *
 * @author bakyt
 */
class shalbot_service_anecdote
    extends    shalbot_service_abstract_private
    implements shalbot_service_interface
{
    /**
     * @var string
     */
    public $cmd_code = '!anec';

    /**
     * @var string
     */
    protected $title = 'Анекдоты';

    /**
     * @var string
     */
    protected $version = '1.0b';

    /**
     * @var string
     */
    protected $table_name = 'shalbot_anecdote';

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