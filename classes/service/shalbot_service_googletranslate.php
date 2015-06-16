<?php
include_once 'shalbot_service_interface.php';
include_once 'abstract/shalbot_service_abstract_private.php';

/**
 * Description of shalbot_service_googletranslate
 *
 * @author bakyt
 */
class shalbot_service_googletranslate
    extends    shalbot_service_abstract_private
    implements shalbot_service_interface
{
    /**
     * @var string
     */
    public $cmd_code = '!tran';
    
    /**
     * @var string
     */
    protected $title = 'Google Переводчик. Пример: "{CMD_CODE} ru/en привет"';

    /**
     * @var string
     */
    protected $version = '1.0b';

    /**
     * @var string
     */
    protected $help_more = 'Пример: "{CMD_CODE} ru/en привет", если не указать языки (from/to) то по умолчанию en/ru. Доступные для перевода языки: ru-русский, en-английский, fr-французский, es-испанский, it-итальянский, ja-японский, tr-турецкий, uk-украинский, sq-албанский, ar-арабский, af-африкаанс, be-белорусский, bg-болгарский, cy-валлийский, hu-венгерский, vi-вьетнамский, gl-галисийский, nl-голландский, el-греческий, da-датский, iw-иврит, yi-идиш, id-индонезийский, ga-ирландский, is-исландский, ca-каталанский, zh-TW-китайский (традиционный), zh-CN-китайский (упрощенный), ko-корейский, lv-латышский, lt-литовский, mk-македонский, ms-малайский, mt-мальтийский, de-немецкий, no-норвежский, fa-персидский, pl-польский, pt-португальский, ro-румынский, sr-сербский, sk-словацкий, sl-словенский, sw-суахили, tl-тагальский, th-тайский, fi-финский, hi-хинди, hr-хорватский, cs-чешский, sv-шведский, et-эстонский';

    /**
     * @var URI
     */
    protected $url = 'http://ajax.googleapis.com/ajax/services/language/translate';

    /**
     * @var string
     */
    protected $api_version = '1.0';

    /**
     * @var string
     */
    protected $api_key;
    
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
        $gtrans_query = explode(' ', $message);

        // with lang
        if(strpos($gtrans_query[1], '/') && strlen($gtrans_query[1]) > 4)
        {
            $langpair = explode('/', $gtrans_query[1]);
            $query = implode(' ',array_slice($gtrans_query, 2));

            return $this->query($query, $langpair[0], $langpair[1]);
        }
        // without lang
        else
        {
            $query = implode(' ',array_slice($gtrans_query, 1));

            return $this->query($query);
        }
    }

    /**
     * Translate query
     * @param string $query
     * @param string $from
     * @param string $to
     * @return string
     */
    protected function query($query, $from = 'EN', $to = 'RU')
    {
        $get = array(
            'v' => $this->api_version,
            'q' => $query,
            'langpair' => $from.'|'.$to
        );

        if(isset($this->api_key))
        {
            $get['key'] = $this->api_key;
        }

        $res = $this->http->get($this->url, $get);
        
        $res = $this->parse_query_result($res);

        return ($res === FALSE)?'not found':$res;
    }

    /**
     * Parse $this->query result
     * @param string $result
     * @return mixed boolean or translated text
     */
    protected function parse_query_result($result)
    {
        $result_arr = json_decode($result);

        if($result_arr->responseStatus != 200)
        {
            return FALSE;
        }
        
        return $result_arr->responseData->translatedText;
    }

    /**
     * cURL init
     */
    protected function http_init()
    {
        include_once 'classes/shalbot_curl.php';

        $this->http = new ShalBot_Curl('http://translate.google.com');
    }
}