<?php
/**
 * Description of shalbot_curl
 *
 * @author bakyt
 */
class shalbot_curl
{
    protected $ch;
    protected $cookie_path;
    protected $cookie_file;
    protected $cookie_prefix = '001-cookie-';

    const USER_AGENT = 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.5) Gecko/20091109 Ubuntu/9.10 (karmic) Firefox/3.5.5';

    public function  __construct($default_refferer = NULL, $user_agent = NULL, $cookie_path = NULL, $cookie_prefix = NULL)
    {
        if(!is_null($cookie_path))
        {
            $this->cookie_path = $cookie_path;
        }
        if(!is_null($cookie_prefix))
        {
            $this->cookie_prefix = $cookie_prefix;
        }

        $this->ch = curl_init();

        curl_setopt($this->ch, CURLOPT_USERAGENT,
            (!is_null($user_agent))
                ?$user_agent
                :self::USER_AGENT
        );

        if(isset($this->cookie_path))
        {
            $this->cookie_file = tempnam($this->cookie_path, $this->cookie_prefix);

            curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookie_file);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->cookie_file);
        }

        if(!is_null($default_refferer))
        {
            curl_setopt($this->ch, CURLOPT_REFERER, $default_refferer);
        }

        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    }

    /**
     * POST to a URL
     * @param string $url
     * @param array $data
     * @param string $refferer
     * @return mixed server response
     */
    public function post($url, $data = array(), $refferer = NULL)
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);

        if(!is_null($refferer))
        {
            curl_setopt($this->ch, CURLOPT_REFERER, $refferer);
        }

        curl_setopt($this->ch, CURLOPT_POST, 1);

        if(count($data))
        {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        return curl_exec($this->ch);
    }

    /**
     * GET to a URL
     * @param string $url
     * @param array $data
     * @param string $refferer
     * @return mixed server response
     */
    public function get($url, $data = array(), $refferer = NULL)
    {
        //print_r(__METHOD__.'('.print_r(func_get_args(),1).')');

        if(count($data))
        {
            $url .= '?' .http_build_query($data);
        }

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_HTTPGET, 1);

        if(!is_null($refferer))
        {
            curl_setopt($this->ch, CURLOPT_REFERER, $refferer);
        }

        return curl_exec($this->ch);
    }

    /**
     * Check http return code for 200
     * @return boolean
     */
    public function is_200()
    {
        return (int)$this->get_info(CURLINFO_HTTP_CODE) === 200;
    }

    /**
     * Get information regarding a specific transfer
     * @param int $opt
     * @return mixed
     */
    public function get_info($opt = 0)
    {
        return curl_getinfo($this->ch, $opt);
    }

    public function  __destruct()
    {
        curl_close($this->ch);
        
        if(isset($this->cookie_file))
        {
            @unlink($this->cookie_file);
        }
    }
}