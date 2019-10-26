<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/10/10
 * Time: 15:21
 */
namespace app\index\controller;

use think\Config;
use think\Session;
use think\Controller;
use app\common\exception\BaseException;
// require_once 'vendor/autoload.php';
use Guzzle\Http\Client;

 class Gateway extends Controller
{
    /**
     * 配置参数
     * @var array
     */
    protected $config;

    /**
     * 当前时间戳
     * @var int
     */
    protected $timestamp;

    /**
     * 默认第三方授权页面样式
     * @var string
     */
    protected $display = 'default';

    /**
     * 第三方Token信息
     * @var array
     */
    protected $token = null;

    /**
     * 是否验证回跳地址中的state参数
     * @var boolean
     */
    protected $checkState = false;

    public function __construct()
    {
        //默认参数
        $config = [
            // 'app_id'        => 'wx8995d54b8f1289d9',
            // 'app_secret'    => '3512aad341e45b9957ff082dced823bc',
            'app_id'        => 'wxf120ba19ce55a392',
            'app_secret'    => '06c0107cff1e3f5fe6c2eb039ac2d0b7',
            'callback'      => 'http://www.zhihuichacang.com/WeiChatScanCodeReturnUrl',
            'response_type' => 'code',
            'grant_type'    => 'authorization_code',
            'proxy'         => '',
            'state'         => '',
        ];
        $this->config  = $config;
        $this->timestamp = time();
    }

    /**
     * 设置授权页面样式
     *
     * @param string $display
     * @return self
     */
    public function setDisplay($display)
    {
        $this->display = $display;
        return $this;
    }

    /**
     * 强制验证回跳地址中的state参数
     *
     * @return self
     */
    public function mustCheckState()
    {
        $this->checkState = true;
        return $this;
    }

    /**
     * 执行GET请求操作
     *
     * @param string $url
     * @param array $params
     * @param array $headers
     * @return string
     */
    protected function GET($url, $params = [], $headers = [])
    {
        $client   = new \GuzzleHttp\Client();
        $response = $client->request('GET', $url, ['proxy' => $this->config['proxy'], 'headers' => $headers, 'query' => $params]);
        return $response->getBody()->getContents();
    }

    /**
     * 执行POST请求操作
     *
     * @param string $url
     * @param array $params
     * @param array $headers
     * @return string
     */
    protected function POST($url, $params = [], $headers = [])
    {
        $client   = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url, ['proxy' => $this->config['proxy'], 'headers' => $headers, 'form_params' => $params, 'http_errors' => false]);
        return $response->getBody()->getContents();
    }

    /**
     * 默认的AccessToken请求参数
     * @return array
     */
    protected function accessTokenParams()
    {
        $params = [
            'client_id'     => $this->config['app_id'],
            'client_secret' => $this->config['app_secret'],
            'grant_type'    => $this->config['grant_type'],
            'code'          => isset($_REQUEST['code']) ? $_REQUEST['code'] : '',
            'redirect_uri'  => $this->config['callback'],
        ];
        return $params;
    }

    /**
     * 获取AccessToken
     *
     * @return string
     */
    protected function getAccessToken()
    {
        if ($this->checkState === true) {
            if (!isset($_GET['state']) || $_GET['state'] != $this->config['state']) {
                throw new \Exception('传递的STATE参数不匹配！');
            }
        }
        $params = $this->accessTokenParams();
        return $this->POST($this->AccessTokenURL, $params);
    }

    /**
     * 获取token信息
     *
     * @return void
     */
    protected function getToken()
    {
        if (empty($this->token)) {
            $token = $this->getAccessToken();
            /** @scrutinizer ignore-call */
            $this->token = $this->parseToken($token);
        }
    }
}
