<?php

return array (
  0 => 
  array (
    'name' => 'wechat',
    'title' => '微信支付',
    'type' => 'array',
    'content' => 
    array (
    ),
    'value' => 
    array (
      'mch_id' => '',
      'key' => '',
      'notify_url' => '/addons/yungouos/api/notifyx/type/wechat',
      'log' => '1',
    ),
    'rule' => '',
    'msg' => '',
    'tip' => '微信支付参数配置',
    'ok' => '',
    'extend' => '',
  ),
  1 => 
  array (
    'name' => 'alipay',
    'title' => '支付宝',
    'type' => 'array',
    'content' => 
    array (
    ),
    'value' => 
    array (
      'mch_id' => '',
      'key' => '',
      'notify_url' => '/addons/yungouos/api/notifyx/type/alipay',
      'log' => '1',
    ),
    'rule' => 'required',
    'msg' => '',
    'tip' => '支付宝参数配置',
    'ok' => '',
    'extend' => '',
  ),
  2 => 
  array (
    'name' => '__tips__',
    'title' => 'YunGouOS介绍',
    'type' => 'array',
    'content' => 
    array (
    ),
    'value' => 'YunGouOS是微信、支付宝官方合作伙伴，专业服务于个人、个体、企业用户提供便捷的支付API系统，我们不做支付清算，资金由支付宝/微信官方直接清算，我们只是支付搬运工。<br/>申请流程：1、登录www.yungouos.com提交资料申请->YunGouOS审核->微信/支付宝审核->审核通过开通正规商户<br/>常见问题：https://open.pay.yungouos.com/#/api/index',
    'rule' => '',
    'msg' => '',
    'tip' => 'YunGouOS参数配置',
    'ok' => '',
    'extend' => '',
  ),
);
