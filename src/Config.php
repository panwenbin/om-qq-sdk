<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

namespace panwenbin\omqq;


/**
 * 配置
 * @package panwenbin\omqq
 */
class Config
{
    protected $clientId;
    protected $clientSecret;

    public function __construct(string $clientId, string $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * 给Token接口使用，用于替换接口中的参数
     * @param string $url
     * @param array $replaces
     * @return string
     */
    public function apiUrl(string $url, array $replaces = [])
    {
        $replaces['{CLIENT_ID}'] = $this->clientId;
        $replaces['{CLIENT_SECRET}'] = $this->clientSecret;
        return strtr($url, $replaces);
    }
}