<?php
/**
 * Description of shalbot_service_interface
 *
 * @author bakyt
 */
interface shalbot_service_interface
{
    /**
     * Get result from service
     * @param mixed boolean or $message/query/command from user
     */
    public function get_result($message);

    /**
     * Get service help
     */
    public function get_help();

    /**
     * Get service help detailed
     */
    public function get_help_more();

    /**
     * Service work on public window?
     */
    public function is_public();
}