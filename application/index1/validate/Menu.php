<?php
/**
 * 登录相关的规则验证
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/9/20
 * Time: 16:23
 */

namespace app\index\validate;


use think\Validate;

class Menu extends Validate
{
    protected $rule = [
        'title'         =>          'require|length:2,30',
        'route'         =>          'require|length:2,100'
    ];

    protected $message = [
        'title.require'         =>          '导航名称不能为空',
        'title.length'          =>          '导航名称过长',
        'route.require'         =>          '路由不能为空',
        'route.length'          =>          '路由地址过长'
    ];

    protected $scene = [
        'saveMenu'          =>          'title,route'
    ];
}