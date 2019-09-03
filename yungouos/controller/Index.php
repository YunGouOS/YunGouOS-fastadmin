<?php

namespace addons\yungouos\controller;

use addons\yungouos\library\Service;
use fast\Random;
use think\addons\Controller;
use Yungouos\Pay\Log;
use Yungouos\Pay\Pay;
use Exception;

/**
 * 微信支付宝插件首页
 *
 * 此控制器仅用于开发展示说明和体验，建议自行添加一个新的控制器进行处理返回和回调事件，同时删除此控制器文件
 *
 * Class Index
 * @package addons\yungouos\controller
 */
class Index extends Controller
{

    protected $layout = 'default';

    protected $config = [];

    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        $this->view->assign("title", "YunGouOS个人微信支付宝支付接口");
        return $this->view->fetch();
    }

    /**
     * 体验，仅供开发测试
     */
    public function experience()
    {
        $amount = $this->request->request('amount');
        $type = $this->request->request('type');
        $method = $this->request->request('method');

        if (!$amount || $amount < 0) {
            $this->error("支付金额必须大于0");
        }

        if (!$type || !in_array($type, ['alipay', 'wechat'])) {
            $this->error("支付类型不能为空");
        }

        //订单号
        $out_trade_no = date("YmdHis") . mt_rand(100000, 999999);

        //订单标题
        $title = 'FastAdmin测试订单';

        //回调链接
        $notifyurl = $this->request->root(true) . '/addons/yungouos/index/notifyx/paytype/' . $type;
        $returnurl = $this->request->root(true) . '/addons/yungouos/index/returnx/paytype/' . $type . '/out_trade_no/' . $out_trade_no;

        return Service::submitOrder($amount, $out_trade_no, $type, $title, $notifyurl, $returnurl, $method);
    }

    /**
     * 支付成功，仅供开发测试
     */
    public function notifyx()
    {
        $paytype = $this->request->param('paytype');
        $pay = \addons\yungouos\library\Service::checkNotify($paytype);
        if (!$pay) {
            echo '签名错误';
            return;
        }
        $data =request()->post('', null, 'trim');
        try {
            $payamount =$data['money'];
            $out_trade_no = $data['outTradeNo'];
            //你可以在此编写订单逻辑
        } catch (Exception $e) {
        }
        echo 'SUCCESS';
    }

    /**
     * 支付返回，仅供开发测试
     */
    public function returnx()
    {
        $paytype = $this->request->param('paytype');
        $out_trade_no = $this->request->param('outTradeNo');


        //你可以在这里通过out_trade_no去验证订单状态
        //但是不可以在此编写订单逻辑！！！

        $this->success("请返回网站查看支付结果", addon_url("yungouos/index/index"));
    }

}
