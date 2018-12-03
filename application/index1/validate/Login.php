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

class Login extends Validate
{
    protected $rule = [
        'user_name'     =>      'length:2,60',#用户名在4-60个字节
        'password'      =>      'length:4,12',#密码必须,要求4-12个字节（md5加密）
        'organization_id'       =>      'require|alphaDash',#组织机构必填,并且只能为字母和数字，下划线_及破折号-的组合（md5加密）
        'role_id'       =>      'alphaDash',#角色id值只能为字母和数字，下划线_及破折号-的组合（md5加密）
        'mobile'        =>      'require|number|length:11',#电话号码只能是11位数据
        'email'         =>      'email',#email验证
        'expried_time'  =>      'date',#检查是否为有效日期
    ];

    protected $message = [
        'user_name.length'      =>      '用户名要求在2-60个字之间',
        'password.length'       =>      '密码要求在4-12个字节之间',
        'organization_id.require'     =>      '组织机构必填',
        'organization_id.alphaDash'     =>      '组织机构异常',
        'role_id.alphaDash'     =>      '所属角色异常',
        'mobile.require'        =>      '请输入电话号码',
        'mobile.number'         =>      '电话号码只能是11位数字',
        'mobile.length'         =>      '电话号码只能是11位数字',
        'email.email'           =>      '邮箱格式错误',
        'expried_time'          =>      '请输入有效的时间'
    ];

    protected $scene = [
        #创建用户账号
        'createUser'       =>      'user_name,password,organization_id,mobile,email,expried_time',

        #登录
        'login'            =>       'mobile,password'
    ];
}