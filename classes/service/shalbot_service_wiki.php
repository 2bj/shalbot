<?php
include_once 'shalbot_service_interface.php';
include_once 'abstract/shalbot_service_abstract_private.php';

/**
 * Description of shalbot_service_wiki
 *
 * @author bakyt
 */
class shalbot_service_wiki
    extends    shalbot_service_abstract_private
    implements shalbot_service_interface
{
    /**
     * @var string
     */
    public $cmd_code = '!wiki';

    /**
     * @var string
     */
    protected $title = 'Википедиа. Пример: "{CMD_CODE} Ктулху"';

    /**
     * @var string
     */
    protected $help_more = 'Пример: "{CMD_CODE} Ктулху"';

    /**
     * @var string
     */
    protected $version = '0.1a';

    /**
     * @var URI
     */
    protected $url_domain = 'http://{LANG}.wikipedia.org/w/api.php';
    
    /**
     * @var ShalBot_Curl
     */
    protected $http;

    public function  __construct()
    {
        // TODO: do it..
        $this->url_domain = str_replace('{LANG}', 'ru', $this->url_domain);
        
        $this->http_init();
    }

    /**
     * Get service result
     * @param string $message
     * @return mixed
     */
    public function get_result($message)
    {
        $wiki_query = explode(' ', $message);
        $wiki_query[1] = trim($wiki_query[1]);

        if($wiki_query[1] != '')
        {
            return $this->query($wiki_query[1]);
        }
        
        return FALSE;
    }

    /**
     * Get wiki defination
     * @param string $query
     * @return string
     */
    protected function query($query)
    {
        $get = array(
            'action' => 'query',
            'list' => 'search',
            'format' => 'json',
            'srprop' => 'snippet',
            //'sroffset' => '1',
            //'srlimit' => '50',
            'srsearch' => $this->prepare_query($query)
        );

        
        $res = $this->http->get($this->url_domain, $get);
        $res = $this->parse_query_result($res);

        return ($res === FALSE)?'not found':$res;
    }

    /**
     * Prepare $query string
     * @param string $query
     * @return string
     */
    protected function prepare_query($query)
    {
        return trim(mb_strtolower($query, 'UTF-8'));
    }

    /**
     * Parse $this->query() result
     * @param string $result
     * @return boolean
     */
    protected function parse_query_result($result)
    {
        $result_obj = json_decode($result);

        if(isset($result_obj->query->search[0]))
        {
            $title = $result_obj->query->search[0]->title;
            $snippet = $result_obj->query->search[0]->snippet;
            
            $snippet = strip_tags($snippet);

            return $snippet;
        }

        return FALSE;
    }

    /**
     * cURL init
     */
    protected function http_init()
    {
        include_once 'classes/shalbot_curl.php';

        $this->http = new shalbot_curl();
    }
}