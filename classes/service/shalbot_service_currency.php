<?php
include_once 'shalbot_service_interface.php';
include_once 'abstract/shalbot_service_abstract_private.php';

/**
 * Description of shalbot_service_currency
 *
 * @author bakyt
 */
class shalbot_service_currency
    extends    shalbot_service_abstract_private
    implements shalbot_service_interface
{
    /**
     * @var string
     */
    public $cmd_code = '!curr';
    
    /**
     * @var string
     */
    protected $title = 'Курсы валют (НБКР, ЦБРФ). Пр.: "{CMD_CODE}" - НБКР, "{CMD_CODE} rub" - ЦБРФ (NEW)';// [nbkr.kg, cbr.ru] (new)';

    /**
     * @var string
     */
    protected $version = '1.1a';

    /**
     * www.cbr.ru RUB -> *
     * @var URI
     */
    protected $url_cbr = 'http://www.cbr.ru/scripts/XML_daily.asp';

    /**
     * www.nbkr.kg KGS -> *
     * @var URI
     */
    protected $url_nbkr = 'http://www.nbkr.kg/';

    /**
     * @var ShalBot_Curl
     */
    protected $http;

    /**
     * @var array
     */
    protected $cache = array();

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
        if(strlen($message) > 6 && strpos($message, ' ') !== FALSE)
        {
            $cmd = explode(' ', $message);

            if(strtolower($cmd[1]) == 'rub')
            {
                $week_day = date('w');
                $plus_days = 1;

                if($week_day == 0 || ($week_day > 4))
                {
                    $plus_days = 3;
                }


                if(strtolower($cmd[2]) == 'today')
                {
                    $plus_days = 0;
                }

                $date = date('d/m/Y', mktime(0, 0, 0, date("m"), date("d")+$plus_days, date("Y")));

                $char_code = array('EUR', 'USD');

                return $this->query_cbr($char_code, $date);
            }
        }
        else
        {
            return $this->query_nbkr(array('USD', 'EUR', 'RUB'), date('d/m/Y'));
        }
    }

    protected $char_codes_nbkr = array(
        "Доллар США" 	   => "USD",
        "Российский рубль" => "RUB",
        //"Казахский тенге"  => "KZT",
        "Евро"		   => "EUR",
    );

    protected function query_nbkr($char_code, $date)
    {
        $get = array(
            'date' => $date
        );

        /*
        $cache_id = md5(print_r($get,1));

        if($this->is_cached($cache_id))
        {
            $res = $this->get_cache($cache_id);
        }
        else
        {
            $res = $this->http->get($this->url_nbkr, $get);
            $res = $this->parse_query_nbkr_result($res);
            $this->set_cache($cache_id, $res);
        }
        */
        
        $res = $this->http->get($this->url_nbkr, $get);
        $res = $this->parse_query_nbkr_result($res);
        
        if(is_array($char_code))
        {
            foreach ($char_code as $code)
            {
                $curr = $res['items'][strtoupper($code)];

                if(count($curr))
                {
                    $ret .= $this->build_nbkr_string($curr);
                }
            }

        }
        else
        {
            $curr = $res['items'][strtoupper($char_code)];

            if(count($curr))
            {
                $ret = $this->build_nbkr_string($curr);
            }
        }
        if($ret != '')
        {
            $ret .= '('.$res['date'].', НБ КР)';
        }

        return ($ret != '')?$ret:'not found;';
    }

    protected function parse_query_nbkr_result($res)
    {
        $res = iconv('cp1251', 'utf-8', $res);
        
        $result = array();

        if(preg_match_all("/<td\b[^>]*class=\"informer_header\"[^>]*>(.*)<\/table>/isU", $res, $o, PREG_SET_ORDER))
        {
            $res = $o[0][1];

            if(preg_match('#class="currencyrate_cell"><nobr>(.*) г.</nobr>#isU', $res, $ts))
            {
                $date = $ts[1];
                $result['date'] = $date;
                $date = strtotime($date);

                if(preg_match_all("/<td\b[^>]*class=\"currencyrate_cell\"[^>]*>(.*)<\/td>/isU", $res, $o))
                {
                    foreach($o[1] as $k=>$i)
                    {
                        $i = trim($i);
                        if(preg_match("/KGS\/\d+\s+(.*)\s*<\/nobr>\s*$/i", $i, $cur))
                        {
                            $char_code = strtoupper($cur[1]);

                            if(in_array($char_code, $this->char_codes_nbkr) && floatval($o[1][$k-1]) != 0)
                            {
                                $result['items'][$char_code] = array(
                                    'Nominal' => '1',
                                    'Name' => $this->get_name_by_char_code_nbkr($char_code),
                                    'Value' => floatval(str_replace(",", ".", $o[1][$k-1]))
                                );
                            }
                        }
                    }
                }
            }
        }
        
        return $result;
    }

    /**
     * Retrn return normal name by charcode (nbkr.kg)
     * @param string $char_code
     * @return string
     */
    protected function get_name_by_char_code_nbkr($char_code)
    {
        foreach($this->char_codes_nbkr as $name => $code)
        {
            if($char_code == $code)
            {
                return $name;
            }
        }
    }

    /**
     * Do query for RUB -> *
     * @param string $char_code EUR, USD, ...
     * @param string $date d/m/Y
     * @return string
     */
    protected function query_cbr($char_code, $date)
    {
        $get = array(
            'date_req' => $date
        );

        /*
        $cache_id = md5(print_r($get,1));
        if($this->is_cached($cache_id))
        {
            $res = $this->get_cache($cache_id);
        }
        else
        {
            $res = $this->http->get($this->url_cbr, $get);
            $res = $this->parse_query_cbr_result($res);

            if($res['date'] == $date)
            {
                $this->set_cache($cache_id, $res);
            }
        }*/
        $res = $this->http->get($this->url_cbr, $get);
        $res = $this->parse_query_cbr_result($res);

        if(is_array($char_code))
        {
            foreach ($char_code as $code)
            {
                $curr = $res['items'][strtoupper($code)];

                if(count($curr))
                {
                    $ret .= $this->build_cbr_string($curr);
                }
            }

        }
        else
        {
            $curr = $res['items'][strtoupper($char_code)];

            if(count($curr))
            {
                $ret = $this->build_cbr_string($curr);
            }
        }
        if($ret != '')
        {
            $ret .= ' ('.$res['date'].', ЦБ РФ)';
        }

        return ($ret != '')?$ret:'not found;';
    }

    protected function build_string($curr, $curr_name)
    {
        return $curr['Nominal'].' '.$curr['Name'].' = '.$curr['Value'].' '.$curr_name.' ';
    }

    protected function build_cbr_string($curr)
    {
        return $this->build_string($curr, 'руб.');
    }

    protected function build_nbkr_string($curr)
    {
        return $this->build_string($curr, 'сом.');
    }

    /**
     * Set cbr cache
     * @param string $key
     * @return mixed
     */
    protected function get_cache($key)
    {
        return $this->cache[$key];
    }

    /**
     * Check cbr cache
     * @param string $key
     * @return mixed
     */
    protected function is_cached($key)
    {
        return isset($this->cache[$key]);
    }


    /**
     * Get cbr cache
     * @param string $key
     * @param mixed $value
     */
    protected function set_cache($key, $value)
    {
        $this->cache[$key] = $value;
    }

    /**
     * Parse $this->query_cbr result
     * @param string $result_xml
     * @return mixed
     */
    protected function parse_query_cbr_result($result_xml)
    {
        $xml = simplexml_load_string($result_xml);

        $result = array();

        $result['date'] = (string)$xml->attributes()->Date;

        foreach($xml as $node)
        {
            $result['items'][(string)$node->CharCode] = array(
                'Nominal' => (string)$node->Nominal,
                'Name' => (string)$node->Name,
                'Value' => (string)$node->Value,
            );
        }

        return $result;
    }

    /**
     * cURL init
     */
    protected function http_init()
    {
        include_once 'classes/shalbot_curl.php';

        $this->http = new ShalBot_Curl("http://www.nbkr.kg/");
    }
}