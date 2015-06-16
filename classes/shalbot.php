<?php
/**
 * ShalBot
 */
class shalbot
{
    public $version = '1.1.3b';
    
    protected $url_domain = 'https://chat.kg';
    protected $url_login;
    protected $url_login_reffer;
    protected $url_live_reffer;
    protected $url_grnd;
    protected $url_watch; 
    protected $url_action;
    protected $url_write;    
    
    private $nickname;
    private $password;
    private $room = 3; // 3 == "Unlimited" room for development..

    /**
     * seesion_id from server
     * @var int
     */
    private $grnd;

    /**
     * Users
     * @var array
     */
    private $users = array();
        
    /**
     * Private services
     * @var array
     */
    private $service_private = array();

    /**
     * Public services
     * @var array
     */
    private $service_public = array();

    /**
     * Services help
     * @var array
     */
    private $service_help = array();


    const USER_AGENT = 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.5) Gecko/20091109 Ubuntu/9.10 (karmic) Firefox/3.5.5 ShalBot/1.0.2a (2BJ)';
    
    const CMD_LOGOUT = 'my:logout';
    
    const PARSECODE_USER_JOINED = 'wa';
    const PARSECODE_USER_LEAVE  = 'wd';
    const PARSECODE_PRIVATE_MSG = 'pw';
    const PARSECODE_PUBLIC_MSG  = 'cw';
    const PARSECODE_NEW_TOPIC   = 't';
    const PARSECODE_SERVICE_MSG = 'm';

    public function __construct($nickname, $password = '', $room = NULL)
    {
        $this->log('->__construct');

        $this->nickname = $nickname;
        $this->password = $password;

        if(!is_null($room))
        {
            $this->room = $room;
        }
        
        $this->url_login        = $this->url_domain . '/servlet/login';
        $this->url_login_reffer = $this->url_domain . '/login.jsp';
        $this->url_grnd         = $this->url_domain . '/frames.jsp';
        $this->url_watch        = $this->url_domain . '/watch.jsp';
        $this->url_write        = $this->url_domain . '/write.jsp';
        $this->url_action       = $this->url_domain . '/servlet/action';

        
        $this->http_init();

        $this->add_service('greatwords'); // !gword
        $this->add_service('currency'); // !curr
        $this->add_service('bashorg'); // !bor
        $this->add_service('forismatic'); // !quot
        $this->add_service('anecdote'); // !anec
        $this->add_service('aphorism'); // !apho
        $this->add_service('googletranslate'); // !tran
        //$this->add_service('wiki'); // !wiki
        


        $this->login_res = $this->login();
        $this->grnd = $this->get_grnd();
        
        $this->log('<-__construct');        
    }

    public $re_login_count = 0;

    public function re_login()
    {
        $this->log(__METHOD__);
        
        $this->login_res = $this->login();
        $this->grnd = $this->get_grnd();
        $this->re_login_count++;

        sleep(2);
        return $this->login_res;
    }

    protected function add_service($name)
    {
        $this->log(__METHOD__.': '.$name);

        $file = 'classes/service/shalbot_service_'.$name.'.php';
        $service = 'shalbot_service_'.$name;

        if(!file_exists($file))
        {
            throw new Exception('Service '.$file.' not found');
        }

        include_once $file;
        
        $tmp_service = new $service;
        if($tmp_service->is_public())
        {
            $this->service_public[$name] = $tmp_service;
        }
        else
        {
            $this->service_private[$name] = $tmp_service;
        }

        $this->add_service_help($name, $tmp_service->get_help());

        unset($tmp_service);
    }

    /**
     * Add service help
     * @param string $service_name
     * @param text $help_text
     */
    protected function add_service_help($service_name, $help_text)
    {
        $this->log(__METHOD__.': '.$service_name);
        
        $this->service_help[$service_name] = $help_text;
    }

    /**
     * Get service help
     * @param string $service_name
     * @return string
     */
    protected function get_service_help($service_name)
    {
        $this->log(__METHOD__.': '.$service_name);
        
        return $this->service_help[$service_name];
    }

    /**
     * Get bot help
     * @return string
     */
    protected function get_help()
    {
        $help_welcome = 'Привет, я - бот ('.$this->version.'). Доступные на данный момент сервисы: ';

        $append = ""; // " ---- если Вам есть что сказать по поводу botа - welcome (pager 2BJ) ";
        
        
        return $help_welcome . implode('; ', $this->service_help) . $append;
    }

    /**
     * Get users
     * @return string
     */
    protected function get_users()
    {
        return implode(', ', array_keys($this->users));
    }



    protected $curl_cookie_path = '/tmp';
    protected $curl_cookie_prefix = '001-shalbot-chatkg-';

    public function http_init()
    {
        include_once 'shalbot_curl.php';

        $this->http = new shalbot_curl($this->url_grnd, self::USER_AGENT, $this->curl_cookie_path, $this->curl_cookie_prefix);
    }

    /**
     * Check for logged in
     * @return boolean
     */
    public function is_logged_in()
    {
        if($this->login_res === TRUE)
        {
            if(FALSE !== $this->grnd)
            {
                return TRUE;
            }
        }

        return FALSE;
    }
    
    /**
     * Send message to server
     * @param string $message
     */
    protected function write($message, $is_private = TRUE)
    {
        $this->log(__METHOD__);
        
        $post = array(
            'rmesg' => $message,
            'privateon' => ($is_private)?'true':'false',
            'imageson' => 'false',
            'rnd' => $this->grnd
        );
        
        return $this->http->post($this->url_write, $post);
    }

    /**
     * Send message to private
     * @param string $message
     * @return mixed?
     */
    protected function write_to_private($nickname, $message)
    {
        $this->log(__METHOD__);
        
        return $this->write($nickname.': '.$message, TRUE);
    }

    /**
     * Send message to public
     * @param string $message
     * @return mixed? 
     */
    protected function write_to_public($nickname, $message)
    {
        $this->log(__METHOD__);
        
        return $this->write($nickname.': '.$message, FALSE);
    }

    /**
     * Logout
     */
    public function logout()
    {
        $this->log(__METHOD__);

        $post = array(
            'do' => 'logout',
            'm' => 'msg',
            'rnd' => $this->grnd
        );

        $this->http->post($this->url_action, $post);
    }

    /**
     * Watcher
     * @return boolean
     */
    public function watch()
    {
        $ret = TRUE;

        $get = array(
            'ajax' => rand(1,600000)
        );

        $res = $this->http->get($this->url_watch, $get);
        
        $messages = explode("\n", $res);
        
        foreach($messages as $msg)
        {
            if(strlen($msg) < 2)
            {
                continue;
            }
            
            $this->log('MSG: '.$msg);

            $line = explode("|", $msg);
            
            switch($line[0])
            {
                case self::PARSECODE_PUBLIC_MSG:
                    $from_user = $line[2];
                    $cmd = str_replace('  ', ' ', trim($line[3]));
                    $to_user = substr($cmd, 0, strlen($this->nickname));
                    $cmd = trim(substr($cmd, strlen($this->nickname)+1));
                    
                    if($to_user.':' == $this->nickname.':')
                    {
                        $this->log("TOBOT_PUB: {$from_user} -> {$cmd}");
                        if(mb_strpos($cmd, '!help', NULL, 'UTF-8') !== FALSE)
                        {
                            $this->write_to_private($from_user, $this->get_help());
                            break;
                        }
                        
                        foreach($this->service_private as $service)
                        {
                            if(mb_strpos($cmd, $service->cmd_code, NULL, 'UTF-8') !== FALSE)
                            {
                                $service_result = $service->get_result($cmd);
                                if($service_result !== FALSE)
                                {
                                    $this->write_to_private($from_user, $service_result);
                                }
                            }
                        }
                    }
                break;
                
                case self::PARSECODE_PRIVATE_MSG:
                    $name_cmd = explode('-&gt;', $line[1]);

                    $from_user = $name_cmd[0];
                    $cmd = str_replace('  ', ' ', trim($name_cmd[1]));
                    $cmd = trim(substr($cmd, strlen($this->nickname)+1));

                    if(mb_strpos($cmd, self::CMD_LOGOUT, NULL, 'UTF-8') !== FALSE)
                    {
                        $this->logout();
                    }
                    else
                    {
                        if($from_user != '' && $from_user != $this->nickname)
                        {
                            $this->log("TOBOT_PVT: {$from_user} -> {$cmd}");

                            if(mb_strpos($cmd, '!help', NULL, 'UTF-8') !== FALSE)
                            {
                                $this->write_to_private($from_user, $this->get_help());
                                break;
                            }

                            foreach($this->service_private as $service)
                            {
                                if(mb_strpos($cmd, $service->cmd_code, NULL, 'UTF-8') !== FALSE)
                                {
                                    $service_result = $service->get_result($cmd);
                                    if($service_result !== FALSE)
                                    {
                                        $this->write_to_private($from_user, $service_result);
                                    }
                                }
                            }

                            /* TODO: useful? xz
                            elseif(mb_strpos($cmd, '!users', NULL, 'UTF-8') !== FALSE)
                            {
                                $this->write_to_private($from_user, $this->get_users());
                            }*/
                        }
                    }
                break;

                case self::PARSECODE_USER_JOINED:
                    $this->add_user($line[1]);
                break;

                case self::PARSECODE_USER_LEAVE:
                    $this->remove_user($line[1]);
                break;

                case self::PARSECODE_SERVICE_MSG:
                    // TODO: need analyze
                    if(mb_strpos($line[1], 'You are not in the chat anymore', NULL, 'UTF-8') !== FALSE)
                    {
                        $ret = FALSE;
                        break;
                    }
                break;
            }
            
        }
        
        return $ret;
    }

    protected $fuck_words = array(' похуй ', 'бляд', ' блят', ' бля ', ' блять ', ' плять ', ' хуй', ' ибал', ' ебал', 'нахуй', ' хуй', ' хуи', 'хуител', ' хуя', 'хуя', ' хую', ' хуе', ' ахуе', ' охуе', 'хуев', ' хер ', ' хер', 'хер', ' пох ', ' нах ', 'писд', 'пизд', 'рizd', ' пздц ', ' еб', ' епана ', ' епать ', ' ипать ', ' выепать ', ' ибаш', ' уеб', 'проеб', 'праеб', 'приеб', 'съеб', 'сьеб', 'взъеб', 'взьеб', 'въеб', 'вьеб', 'выебан', 'перееб', 'недоеб', 'долбоеб', 'долбаеб', ' ниибац', ' неебац', ' неебат', ' ниибат', ' пидар', ' рidаr', ' пидар', ' пидор', 'педор', 'пидор', 'пидарас', 'пидараз', ' педар', 'педри', 'пидри', ' заеп', ' заип', ' заеб', 'ебучий', 'ебучка ', 'епучий', 'епучка ', ' заиба', 'заебан', 'заебис', ' выеб', 'выебан', ' поеб', ' наеб', ' наеб', 'сьеб', 'взьеб', 'вьеб', ' гандон', ' гондон', 'пахуи', 'похуис', ' манда ', 'мандав', ' залупа', ' залупог');

    /**
     * Check text for fuck word[s]
     * @param string $text
     * @return boolean
     */
    protected function has_fuck_word($text)
    {
        //$this->log(__METHOD__.':'.$text);

        $text = " {$text} ";
        
        foreach($this->fuck_words as $word)
        {
            if(mb_stripos($text, $word, NULL, 'UTF-8') !== FALSE)
            {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Add user to user list
     * @param string $nickname
     */
    protected function add_user($nickname)
    {
        $this->log(__METHOD__.': '.$nickname);

        $this->users[$nickname] = date('d.m.y H:m:i');

        //$this->log('Users: '. print_r($this->users,1));
    }

    /**
     * Remove user from user list
     * @param string $nickname
     */
    protected function remove_user($nickname)
    {
        $this->log(__METHOD__.': '.$nickname);
        
        unset($this->users[$nickname]);

        //$this->log('Users: '. print_r($this->users,1));
    }

    /**
     * Get GRND number
     * @return mixed boolean or grnd number
     */
    protected function get_grnd()
    {
        $this->log(__METHOD__);
        
        $res = $this->http->get($this->url_grnd, NULL, $this->url_login);
        
        //$this->log('get_grnd server response: '.$res);

        preg_match("/var grnd = ([0-9]+)/", $res, $match);

        preg_match("/new Date\(([0-9,]+)\)/", $res, $match2);

        if(strlen($match2[1]) > 5)
        {
            $_tmp = explode(',', $match2[1]); // format: year, month, day, hours, minutes, seconds

            $this->server_time = date('d.m.Y H:i:s', mktime($_tmp[3], $_tmp[4], $_tmp[5], $_tmp[1]+1, $_tmp[2], $_tmp[0]));

            $this->log('server time: '.$this->server_time);
            
        }

        if(strlen($match[1]) == 6)
        {
            return $match[1];
        }
        
        return FALSE;
    }

    /**
     * Login
     * @return boolean
     */
    protected function login()
    {
        $this->log(__METHOD__);

        $post = array(
	        'nickname' => $this->nickname,
	        'password' => $this->password,
	        'email' => '',
	        'homepage' => '',
	        'skin' => '',
	        'room' => $this->room,
	        'Submit' => 'Submit'
        );

        $this->log('login params: '.print_r($post,1));

        $res = $this->http->post($this->url_login, $post, $this->url_login_reffer);

        $this->log('LOGIN response: '.$res);
        
        $ret = $res == 'ok';
        
        if($ret)
        {
            $this->re_login_count = 0;
        }

        return $ret;
    }
    
    public function __destruct()
    {
        $this->log(__METHOD__);
        $this->logout();
    }

    /**
     * Live logger
     * @param mixed $value
     */
    public function log($value)
    {
        System_Daemon::log(System_Daemon::LOG_INFO, print_r($value,1));
        /*
        if(gettype($value) == 'string')
        {
            fwrite(STDOUT, $value."\n");
        }
        else
        {
            fwrite(STDOUT, print_r($value, 1)."\n");
        }*/
    }
}