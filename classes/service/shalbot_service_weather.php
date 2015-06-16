<?php
include_once 'shalbot_service_interface.php';
include_once 'abstract/shalbot_service_abstract_private.php';

/**
 * Description of shalbot_service_weather
 *
 * @author bakyt
 */
class shalbot_service_weather
    extends    shalbot_service_abstract_private
    implements shalbot_service_interface
{
    /**
     * @var string
     */
    public $cmd_code = '!weath';
    
    /**
     * @var string
     */
    protected $title = 'Прогноз погоды [weather.com]';

    /**
     * @var string
     */
    protected $version = '1.0b';

    /**
     * @var URI
     */
    protected $url = 'http://xoap.weather.com/weather/local/';

    /**
     * @var URI
     */
    protected $url_search = 'http://xoap.weather.com/search/search';

    /**
     * @var string
     */
    protected $partner_id = '1152397988';

    /**
     * @var string
     */
    protected $license_key = '5cf13b464a2657ed';

    /**
     * @var ShalBot_Curl
     */
    protected $http;

    public function  __construct()
    {
        $this->http_init();
    }

    /**
     * Get service result
     * @param string $message
     * @return string
     */
    public function get_result($message)
    {

        // @see http://informer.gismeteo.ru/getcode/xml.php?id=27612

        $this->get_locid($message);

        //return $this->query();
    }
    
    /**
     * LocID search function
     * @param string $location 
     */
    protected function get_locid($location)
    {
        $get = array(
            'where' => $location
        );

        $res = $this->http->get($this->url_search, $get);

        $this->parse_locid_result($res);
    }

    protected function parse_locid_result($response_xml)
    {

        print_r($response_xml);
        
        $xml = simplexml_load_string($response_xml);

        print_r((array)$xml);
    }

    /**
     * Do query
     * @return string
     */
    protected function query()
    {

//30339?cc=*&dayf=5&link=xoap&prod=xoap&par=[PartnerID]&key=[LicenseKey]

        $get = array(
            'cc' => '*',
            'dayf' => '3',
            'link' => 'xoap',
            'prod' => 'xoap',
            'par' => $this->partner_id,
            'key' => $this->license_key,
        );

        $res = $this->http->get($this->url, $get);
        
        $res = $this->parse_query_result($res);

        return ($res === FALSE)?'oops, try again ;)':$res;
    }

    /**
     * Parse $this->query result
     * @param string $result
     * @return mixed boolean or translated text
     */
    protected function parse_query_result($result)
    {
        $result_obj = json_decode($result);

        /**
         * {"quoteText":"Подумай, как трудно изменить себя самого, и ты поймешь,
         *  сколь ничтожны твои возможности изменить других. ",
         * "quoteAuthor":"Вольтер", "senderName":"", "senderLink":""}
         */

        if($result_obj->quoteText == '')
        {
            return FALSE;
        }
        
        $quote = $result_obj->quoteText;
        if($result_obj->quoteAuthor != '')
        {
            $quote .= "-- {$result_obj->quoteAuthor}";
        }

        return $quote;
    }

    /**
     * cURL init
     */
    protected function http_init()
    {
        include_once 'classes/shalbot_curl.php';

        $this->http = new ShalBot_Curl('http://www.forismatic.com/');
    }
}