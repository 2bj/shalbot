<?php
include_once 'shalbot_service_interface.php';
include_once 'abstract/shalbot_service_abstract_private.php';

/**
 * Description of shalbot_service_forismatic
 *
 * @author bakyt
 */
class shalbot_service_forismatic
    extends    shalbot_service_abstract_private
    implements shalbot_service_interface
{
    /**
     * @var string
     */
    public $cmd_code = '!quot';
    
    /**
     * @var string
     */
    protected $title = 'Цитаты крутых философов и высказывания мудрых чуваков';

    /**
     * @var string
     */
    protected $version = '1.0b';

    /**
     * @var URI
     */
    protected $url = 'http://www.forismatic.com/api';

    /**
     * @var string
     */
    protected $api_version = '1.0';

    /**
     * @var ShalBot_Curl
     */
    protected $http;

    public function  __construct()
    {
        $this->url .= '/'.$this->api_version.'/';
        $this->http_init();
    }

    /**
     * Get service result
     * @param string $message
     * @return string
     */
    public function get_result($message)
    {
        return $this->query();
    }

    /**
     * Do query
     * @return string
     */
    protected function query()
    {
        /**
         *
         * Метод getQuote
         * Выбирает случайную цитату по переданому цифровому ключу, если ключ
         * не указан, то на сервере генерируется случайный ключ. Ключ влияет на
         * выбор цитаты. Параметры запроса:
         *   - method=getQuote — имя метода, который необходимо вызвать
         *   - format=<format> — один из поддерживаемых сервером форматов ответа
         *      - xml
         *      - json
         *      - html
         *      - text
         *   - key=<integer> — числовой ключ, который влияет на выбор цитаты,
         *      максимальная длина 6 символов
         * 
         * @link http://www.forismatic.com/api/
         */
        $get = array(
            'method' => 'getQuote',
            'format' => 'json',
            'key' => NULL
        );

        $res = $this->http->post($this->url, $get);
        
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