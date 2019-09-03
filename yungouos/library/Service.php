<?php

namespace addons\yungouos\library;

use Exception;
use think\Response;
use Yungouos\Pay\WxPay;

/**
 * 订单服务类
 *
 * @package addons\yungouos\library
 */
class Service
{

    public static function submitOrder($amount, $orderid = null, $type = null, $title = null, $notifyurl = null, $returnurl = null, $method = null)
    {
        if (!is_array($amount)) {
            $params = [
                'amount' => $amount,
                'orderid' => $orderid,
                'type' => $type,
                'title' => $title,
                'notifyurl' => $notifyurl,
                'returnurl' => $returnurl,
                'method' => $method,
            ];
        } else {
            $params = $amount;
        }
        $type = isset($params['type']) && in_array($params['type'], ['alipay', 'wechat']) ? $params['type'] : 'wechat';
        $method = isset($params['method']) ? $params['method'] : 'web';
        $orderid = isset($params['orderid']) ? $params['orderid'] : date("YmdHis") . mt_rand(100000, 999999);
        $amount = isset($params['amount']) ? $params['amount'] : 1;
        $title = isset($params['title']) ? $params['title'] : "支付";
        $openid = isset($params['openid']) ? $params['openid'] : '';
        $request = request();
        $notifyurl = isset($params['notifyurl']) ? $params['notifyurl'] : $request->root(true) . '/addons/yungouos/index/' . $type . 'notify';
        $returnurl = isset($params['returnurl']) ? $params['returnurl'] : $request->root(true) . '/addons/yungouos/index/' . $type . 'return/out_trade_no/' . $orderid;
        $html = '';
        $config = Service::getConfig($type);
        $config[$type]['notify_url'] = $notifyurl;
        $config[$type]['return_url'] = $returnurl;

        try {

            //使用微信支付付款
            if ("wechat" == $type) {
                $mch_id = $config[$type]['mch_id'];
                $key = $config[$type]['key'];
                $wxpay = new WxPay();
                switch ($method) {
                    //PC扫码支付
                    case 'web':
                        $result = $wxpay->nativePay($orderid, $amount, $mch_id, $title, '1', null, $notifyurl, null, $key);
                        $params = [
                            'body' => $title,
                            'code_url' => $result,
                            'out_trade_no' => $orderid,
                            'return_url' => $returnurl,
                            'total_fee' => $amount,
                        ];
                        $params['sign'] = md5(implode('', $params) . $key);
                        $endpoint = addon_url("yungouos/api/wechat");
                        $html = Service::buildPayHtml($endpoint, $params);
                        Response::create($html)->send();
                        break;
                    case "scan":
                        //扫码支付
                        $result = $wxpay->nativePay($orderid, $amount, $mch_id, $title, '1', null, $notifyurl, null, $key);
                        $params = [
                            'body' => $title,
                            'code_url' => $result,
                            'out_trade_no' => $orderid,
                            'return_url' => $returnurl,
                            'total_fee' => $amount,
                        ];
                        $params['sign'] = md5(implode('', $params) . $key);
                        $endpoint = addon_url("yungouos/api/wechat");
                        $html = Service::buildPayHtml($endpoint, $params);
                        Response::create($html)->send();
                        break;
                    case 'wap':
                        //个人资质无法开通H5支付权限 如果使用WAP访问 走收银台接口
                        $result = $wxpay->cashierPay($orderid, $amount, $mch_id, $title, null, $notifyurl, $returnurl, $key);
                        //收银台返回的是收银台的支付连接，此处直接重定向到收银台即可
                        header("location:{$result}");
                        exit;
                        break;
                    case 'app':
                        //个人资质无法开通APP支付权限 如果使用APP访问 走收银台接口
                        $result = $wxpay->cashierPay($orderid, $amount, $mch_id, $title, null, $notifyurl, $returnurl, $key);
                        //收银台返回的是收银台的支付连接，此处直接重定向到收银台即可
                        header("location:{$result}");
                        exit;
                        break;
                    case 'mp':
                        if (empty($openid)) {
                            $params = array(
                                'out_trade_no' => $orderid,
                                'total_fee' => $amount,
                                'mch_id' => $mch_id,
                                'body' => $title,
                                'notify_url' => $notifyurl,
                                'return_url' => $returnurl,
                                'key' => $key
                            );
                            $callbackUrl = $request->root(true) . addon_url("yungouos/api/wechat");
                            $result = $wxpay->getOauthUrl($params, $callbackUrl);
                            //此处返回的是微信授权链接，直接重定向
                            header("location:{$result}");
                            exit;
                        } else {
                            $html = $wxpay->jsapiPay($orderid, $amount, $mch_id, $title, $openid, null, $notifyurl, $returnurl, $key);
                        }
                        break;
                    case 'miniapp':
                        //小程序支付 小程序端参考：https://open.pay.yungouos.com/#/api/api/pay/wxpay/minPay 集成
                        //微信政策原因，不需要走后端请求，小程序段跳转到“支付收银” 支付收银完成了后台API对接
                        //$html = $wxpay->minAppPay($orderid, $amount, $mch_id, $title, null, null, $notifyurl, $key);
                        break;
                    default:
                        //不知道啥支付方式走收银台接口
                        $result = $wxpay->cashierPay($orderid, $amount, $mch_id, $title, null, $notifyurl, $returnurl, $key);
                        //收银台返回的是收银台的支付连接，此处直接重定向到收银台即可
                        header("location:{$result}");
                        exit;
                        break;
                }
            }
        } catch (Exception $e) {
            throw new OrderException($e->getMessage());
        }
        //返回字符串
        $html = is_array($html) ? json_encode($html) : $html;
        return $html;
    }


    /**
     * 验证回调是否成功
     * @param string $type 支付类型
     * @param array $config 配置信息
     * @return bool|Pay
     */
    public static function checkNotify($type, $config = [])
    {
        $type = strtolower($type);
        if (!in_array($type, ['wechat', 'alipay'])) {
            return false;
        }
        try {
            $config = Service::getConfig($type);
            $key = $config[$type]['key'];
            $wxpay = new WxPay();
            $data = request()->post('', null, 'trim');
            $result = $wxpay->checkNotify($data, $key);
            return $result;
        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * 验证返回是否成功
     * @param string $type 支付类型
     * @param array $config 配置信息
     * @return bool
     */
    public static function checkReturn($type, $config = [])
    {
        $type = strtolower($type);
        if (!in_array($type, ['wechat', 'alipay'])) {
            return false;
        }
        try {
            $data = request()->get('', null, 'trim');
            $config = Service::getConfig($type);
            $key = $config[$type]['key'];
            $wxpay = new WxPay();
            $result = $wxpay->checkNotify($data, $key);
            return $result;
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * 获取配置
     * @param string $type 支付类型
     * @return array|mixed
     */
    public static function getConfig($type = 'wechat')
    {
        $config = get_addon_config('yungouos');
        $config = isset($config[$type]) ? $config[$type] : $config['wechat'];
        if ($config['log']) {
            $config['log'] = [
                'file' => LOG_PATH . '/yungouoslogs/' . $type . '-' . date("Y-m-d") . '.log',
                'level' => 'debug'
            ];
        }
        if (isset($config['cert_client']) && substr($config['cert_client'], 0, 6) == '/yungouos/') {
            $config['cert_client'] = ADDON_PATH . $config['cert_client'];
        }
        if (isset($config['cert_key']) && substr($config['cert_key'], 0, 6) == '/yungouos/') {
            $config['cert_key'] = ADDON_PATH . $config['cert_key'];
        }

        $config['notify_url'] = empty($config['notify_url']) ? addon_url('yungouos/api/notifyx', [], false) . '/type/' . $type : $config['notify_url'];
        $config['notify_url'] = !preg_match("/^(http:\/\/|https:\/\/)/i", $config['notify_url']) ? request()->root(true) . $config['notify_url'] : $config['notify_url'];
        $config['return_url'] = empty($config['return_url']) ? addon_url('yungouos/api/returnx', [], false) . '/type/' . $type : $config['return_url'];
        $config['return_url'] = !preg_match("/^(http:\/\/|https:\/\/)/i", $config['return_url']) ? request()->root(true) . $config['return_url'] : $config['return_url'];
        return [$type => $config];
    }

    /**
     * 构建支付hmtl
     */
    public static function buildPayHtml($endpoint, $params)
    {
        $sHtml = "<form id='alipaysubmit' name='wechatsubmit' action='" . $endpoint . "' method='POST'>";
        foreach ($params as $key => $val) {
            $val = str_replace("'", '&apos;', $val);
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }
        $sHtml .= "<input type='submit' value='ok' style='display:none;'></form>";
        $sHtml .= "<script>document.forms['wechatsubmit'].submit();</script>";

        return $sHtml;
    }

}