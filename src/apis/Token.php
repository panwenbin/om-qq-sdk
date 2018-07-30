<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

namespace panwenbin\omqq\apis;


use panwenbin\helper\Curl;
use panwenbin\helper\CurlResponse;
use panwenbin\omqq\Config;
use panwenbin\omqq\exceptions\NotYetAuthorizedException;

class Token
{
    const API_AUTHORIZE = 'https://auth.om.qq.com/omoauth2/authorize?response_type=code&client_id={CLIENT_ID}&redirect_uri={REDIRECT_URI}&state={STATE}';
    const API_ACCESS_TOKEN = 'https://auth.om.qq.com/omoauth2/accesstoken?grant_type=authorization_code&client_id={CLIENT_ID}&client_secret={CLIENT_SECRET}&code={CODE}';
    const API_REFRESH_TOKEN = 'https://auth.om.qq.com/omoauth2/refreshtoken?grant_type=refreshtoken&client_id={CLIENT_ID}&refresh_token={REFRESH_TOKEN}';
    const API_CHECK_TOKEN = 'https://auth.om.qq.com/omoauth2/checktoken?access_token={ACCESS_TOKEN}&openid={OPENID}';

    /**
     * @var Config
     */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public $accessToken;
    public $expiresAt;
    public $refreshToken;
    public $openid;
    public $scope;

    /**
     * @return bool
     * @throws NotYetAuthorizedException
     */
    public function fetch()
    {
        if (empty($this->openid)) {
            throw new NotYetAuthorizedException('尚未授权');
        }
        $this->read();
        if ($this->isAvailable()) {
            return true;
        } else {
            $this->refreshToken($this->refreshToken);
            if ($this->isAvailable()) {
                return true;
            }
        }
        throw new NotYetAuthorizedException('需要重新授权');
    }

    protected function isAvailable()
    {
        return time() < $this->expiresAt;
    }

    protected function toJson()
    {
        return json_encode([
            'accessToken' => $this->accessToken,
            'expiresAt' => $this->expiresAt,
            'refreshToken' => $this->refreshToken,
            'openid' => $this->openid,
            'scope' => $this->scope,
        ]);
    }

    protected function fromJson(string $json)
    {
        $tokenArr = json_decode($json, true);
        foreach ((array)$tokenArr as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function fromResponse(CurlResponse $response)
    {
        $jsonArr = $response->jsonBodyArray();
        if (isset($jsonArr['data'])) {
            $data = $jsonArr['data'];
            if (isset($data['access_token'], $data['expires_in'], $data['refresh_token'], $data['openid'])) {
                $this->accessToken = $data['access_token'];
                $this->expiresAt = $data['expires_in'] + time();
                $this->refreshToken = $data['refresh_token'];
                $this->openid = $data['openid'];
                $this->scope = $data['scope'] ?? '';
                return true;
            }
        }
        return false;
    }

    protected function tokenFile()
    {
        return sys_get_temp_dir() . '/om_access_token_for_' . $this->openid;
    }

    /**
     * 重写此方法持久化$this->accessToken和$this->expiresAt
     * @return bool
     */
    protected function write()
    {
        $tokenFile = $this->tokenFile();
        file_put_contents($tokenFile, $this->toJson());
        return true;
    }

    /**
     * 重写此方法读取持久化的accessToken到$this->accessToken和$this->expiresAt
     */
    protected function read()
    {
        $tokenFile = $this->tokenFile();
        if (file_exists($tokenFile)) {
            $json = file_get_contents($tokenFile);
            $this->fromJson($json);
        }
    }

    public function authorizeUrl(string $redirectUri, string $state)
    {
        $apiUrl = $this->config->apiUrl(self::API_AUTHORIZE, ['{REDIRECT_URI}' => $redirectUri, '{STATE}' => $state]);
        return $apiUrl;
    }

    /**
     * @param $code
     * @return array|null
     *
     * 正常返回
     * {
     *   "code": "0",
     *   "msg": "success",
     *   "data": {
     *     "access_token": "ACCESS_TOKEN",
     *     "expires_in": 7200,
     *     "refresh_token": "REFRESH_TOKEN",
     *     "openid": "OPENID",
     *     "scope": "SCOPE"
     *   }
     * }
     */
    public function accessToken($code)
    {
        $apiUrl = $this->config->apiUrl(self::API_ACCESS_TOKEN, ['{CODE}' => $code]);
        $response = Curl::to($apiUrl)->post();
        if ($this->fromResponse($response)) {
            $this->write();
        }
        return $response->jsonBodyArray();
    }

    /**
     * @param $refreshToken
     * @return array|null
     *
     * 正常返回
     * {
     *   "code": "0",
     *   "msg": "success",
     *   "data": {
     *     "access_token": "ACCESS_TOKEN",
     *     "expires_in": 7200,
     *     "refresh_token": "REFRESH_TOKEN",
     *     "openid": "OPENID",
     *     "scope": "SCOPE"
     *   }
     * }
     */
    public function refreshToken($refreshToken)
    {
        $apiUrl = $this->config->apiUrl(self::API_REFRESH_TOKEN, ['{REFRESH_TOKEN}' => $refreshToken]);
        $response = Curl::to($apiUrl)->post();
        if ($this->fromResponse($response)) {
            $this->write();
        }
        return $response->jsonBodyArray();
    }

    /**
     * @param $accessToken
     * @param $openId
     * @return array|null
     *
     * 正常返回
     * {
     *   "code": "0",
     *   "msg": "success",
     *   "data": {
     *     "openid": "OPENID",
     *     "validity": true
     *   }
     * }
     */
    public function checkToken($accessToken, $openId)
    {
        $apiUrl = $this->config->apiUrl(self::API_CHECK_TOKEN, ['{ACCESS_TOKEN}' => $accessToken, '{OPENID}' => $openId]);
        $response = Curl::to($apiUrl)->get();
        return $response->jsonBodyArray();
    }

    /**
     * 给其他接口使用，用于替换Token值
     * @param string $url
     * @param array $replaces
     * @return string
     * @throws NotYetAuthorizedException
     */
    public function apiUrl(string $url, array $replaces = [])
    {
        $this->fetch();
        $replaces['{ACCESS_TOKEN}'] = $this->accessToken;
        $replaces['{OPENID}'] = $this->openid;
        return strtr($url, $replaces);
    }
}