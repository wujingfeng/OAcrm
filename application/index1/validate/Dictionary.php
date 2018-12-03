<?php
/**
 * Created by PhpStorm.
 * User: 11633
 * Date: 2018/10/16
 * Time: 22:28
 */

namespace app\index\validate;


use think\Validate;

class Dictionary extends Validate
{
    protected $rule = [
        'order_num'     =>      'require|num',
        'valid'         =>      'require|num|in:0,1'
    ];

    protected $message = [
        'order_num.require'     =>      '序列号必填,可默认9999',
        'order_num.num'         =>      '序列号只能是数字',
        'valid.require'         =>      '有效值必填',
        'valid.num'             =>      '有效值只能是0或1',
        'valid.in'              =>      '有效值只能是0或1',
    ];


    protected $scene = [
        'saveDictionary'    =>      'order_num,valid'
    ];

}