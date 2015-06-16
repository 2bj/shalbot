<?php
include_once 'shalbot_service_interface.php';
include_once 'abstract/shalbot_service_abstract_private.php';

/**
 * Description of shalbot_service_bashorg
 *
 * @author bakyt
 */
class shalbot_service_bashorg
    extends    shalbot_service_abstract_private
    implements shalbot_service_interface
{
    /**
     * @var string
     */
    public $cmd_code = '!bor';
    
    /**
     * @var string
     */
    protected $title = 'Bash.Org.Ru (NEW)';

    /**
     * @var string
     */
    protected $version = '1.0a';

    /**
     * @var URI
     */
    protected $url = 'http://bash.org.ru/rss/';

    /**
     * @var ShalBot_Curl
     */
    protected $http;

    /**
     * @var DateTime
     */
    protected $last_update = 0;


    /**
     * @var int
     */
    protected $update_interval = 900; // 15 min

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var int
     */
    protected $data_count = 0;


    public function  __construct()
    {
        $this->http_init();
        $this->update_data();
    }

    /**
     * Get service result
     * @param string $message
     * @return string
     */
    public function get_result($message)
    {
        $this->update_data();

        return $this->get_random();
    }
    protected function get_random()
    {
        return $this->data[rand(0,$this->data_count)]['quot'];
    }

    /**
     * Update data
     */
    protected function update_data()
    {
        if(
                $this->last_update == 0 ||
                (microtime()-$this->last_update) > $this->update_interval
          )
        {
            $data = $this->http->get($this->url);

            $xml = simplexml_load_string($data);

            foreach($xml->channel->item as $node)
            {
                $value = html_entity_decode((string)$node->description);
                $value = str_replace('<br>', ' ', $value);

                $result[] = array(
                    'quot' => $value,
                    'guid' => (string)$node->guid
                );
            }

            if(count($result))
            {
                $this->data_count = count($result);
                $this->last_update = microtime();
                $this->data = $result;
            }
        }
    }
    
    /**
     * cURL init
     */
    protected function http_init()
    {
        include_once 'classes/shalbot_curl.php';

        $this->http = new ShalBot_Curl("http://bash.org.ru/");
    }
}