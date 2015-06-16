<?php
/**
 * Description of shalbot_weather
 *
 * @author bakyt
 */
class ShalBot_Weather
{
    protected $table_name = 'shalbot_weather_codes';
    
    /**
     * @var ShalBot_Db
     */
    protected $db;

    public function  __construct()
    {
        $this->db_init();
    }

    public function get_random()
    {
        $stmt = $this->db->prepare('SELECT * FROM '.$this->table_name.' WHERE id > ? LIMIT 0,1');
        $stmt->execute(array(rand(1,$this->total_count)));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['value'];
    }

    protected function db_init()
    {
        include_once 'shalbot_db.php';

        $this->db = ShalBot_Db::getInstance();
    }
}
