<?php
/**
 * Description of shalbot_service_abstract_basic
 *
 * @author bakyt
 */
abstract class shalbot_service_abstract_basic
{
    /**
     * Service work with public or private
     * @var boolean
     */
    protected $is_public;

    /**
     * Service version
     * @var string
     */
    protected $version = '1.0a';

    /**
     * Service title/name
     * @var string
     */
    protected $title = 'Service Title';

    /**
     * Service detailed help
     * @var string
     */
    protected $help_more = '';

    /**
     * Service cmd code
     * @var string
     */
    protected $cmd_code = '!test';

    /**
     * Get service help
     * @return string
     */
    public function get_help()
    {
        $help = "{$this->cmd_code} - {$this->title}.";// (v{$this->version}).";
        return str_replace('{CMD_CODE}', $this->cmd_code, $help);
    }

    /**
     * Get service detailed help
     * @return string
     */
    public function get_help_more()
    {
        $help = str_replace('{CMD_CODE}', $this->cmd_code, $this->help_more);
        return $this->get_help()." {$help}";
    }
    
    /**
     * Get service public or private status
     * @return boolean
     */
    public function is_public()
    {
        return $this->is_public;
    }
}