<?php

namespace addons\yungouos;

use app\common\library\Menu;
use think\Addons;
use think\Config;
use think\Loader;

/**
 * 微信支付宝整合插件
 */
class Yungouos extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {

        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {

        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {

        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {

        return true;
    }

    /**
     * 添加命名空间
     */
    public function appInit()
    {
        //添加支付包的命名空间
        Loader::addNamespace('Yungouos', ADDON_PATH . 'yungouos' . DS . 'library' . DS . 'Yungouos' . DS);
    }

}
