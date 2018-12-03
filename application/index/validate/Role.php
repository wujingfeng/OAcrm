<?php
/**
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/9/21
 * Time: 15:37
 */

namespace app\index\validate;


use think\Validate;

class Role extends Validate
{
    protected $rule = [
        'organization_id'       =>      'alphaDash',
        'role_name'     =>      'require'
    ];

    protected $message = [
        'organization_id.alphaaDash'=>  '所属组织id参数类型错误',
        'role_name.require'         =>  '角色名称必填'
    ];

    protected $scene = [
        'saveRole'      =>  'organization_id,role_name'
    ];

}