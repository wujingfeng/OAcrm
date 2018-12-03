<?php
/**
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/10/8
 * Time: 16:23
 */

namespace app\index\validate;


use think\Validate;

class Staff extends Validate
{
    protected $rule = [
        'name'              =>  'require',
        'sex'               =>  'in:0,1',
        'three_category'    =>  'require',
        'cellphone'         =>  'require|max:11',
        'bid_type'          =>  'require',
        'customer_type'     =>  'require',
        'return_visit'      =>  'in:0,1',
        'time_visit'        =>  'requireIf:return_visit,1',
        'prepay'            =>  'require',
        'social_security'   =>  'require',
        'card_status'       =>  'require|in:0,1',
        'talent_type'       =>  'require|in:0,1',
        'consult_cost'      =>  'require|float',
        'id_card'           =>  ['require','regex'=>'\d{6}(18|19|20)?\d{2}(0[1-9]|1[12])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)'],
        'user_id'           =>  'require'
    ];

    protected $message = [
        'name.require'              =>  '姓名必填',
        'sex.in'                    =>  '性别只能用0或1表示',
        'three_category.require'    =>  '三类人员必填',
        'cellphone.require'         =>  '电话号码必填',
//        'cellphone.num'             =>  '电话号码格式错误',
        'cellphone.max'             =>  '电话号码格式错误',
        'bid_type.require'          =>  '招标出场类型必填',
        'customer_type.require'     =>  '客户类型必填',
        'return_visit.in'           =>  '回访类型只能用0或1表示',
        'time_visit.requireIf'      =>  '如需回访,则回访时间必填',
        'prepay.require'            =>  '人才预付金必填',
        'social_security.require'   =>  '社保类型必填',
        'card_status.require'       =>  '证书状态必填',
        'card_status.in'            =>  '证书状态只能用0或1表示',
        'talent_type.require'       =>  '人才类型必填',
        'talent_type.in'            =>  '人才类型只能用0或1表示',
        'consult_cost.require'      =>  '咨询费必填',
        'id_card.require'           =>  '身份证号必填',
        'id_card.regex'             =>  '身份证号格式错误',
        'user_id.require'           =>  '录入人必填',

    ];

    protected $scene = [

        #保存/更新人员信息
        'saveStaff'         =>      'name,sex,three_category,cellphone,bid_type,customer_type,return_visit,time_visit,prepay,social_security,card_status,talent_type,consult_cost,id_card,user_id'

    ];

}