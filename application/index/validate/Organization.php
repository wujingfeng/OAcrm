<?php
/**
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/9/21
 * Time: 15:37
 */

namespace app\index\validate;


use think\Validate;

class Organization extends Validate
{
    protected $rule = [
        'parent_id'             =>      'alphaDash',
        'organization_name'     =>      'require'
    ];

    protected $message = [
        'parent_id.alphaaDash'      =>  '上级组织id参数类型错误',
        'organization_name.require' =>  '组织名称必填'
    ];

    protected $scene = [
        'saveOrganization'      =>  'parent_id,organization_name'
    ];

}