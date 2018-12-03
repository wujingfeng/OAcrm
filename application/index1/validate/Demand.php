<?php
/**
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/10/8
 * Time: 16:23
 */

namespace app\index\validate;


use think\Validate;

class Demand extends Validate
{
    protected $rule = [
        'company_name'              =>  'require',
        'linkman'              =>  'require',
        'sex'               =>  'in:0,1',
        'link_number'       =>  'max:11',
        'landline_phone'    =>  'max:11',
        'return_visit'      =>  'in:0,1',
        'time_visit'        =>  'requireIf:return_visit,1',
        'customer_type'              =>  'require',
        'consult_cost'              =>  'require',
        'user_id'           =>  'require'
    ];

    protected $message = [
        'company_name.require'              =>  '企业名称必填',
        'linkman.require'              =>  '联系人必填',
        'sex.in'                    =>  '性别只能用0或1表示',
        'link_number.max'             =>  '电话号码格式错误',
        'landline_phone.max'             =>  '座机号码格式错误',
        'customer_type.require'     =>  '客户类型必填',
        'return_visit.in'           =>  '回访类型只能用0或1表示',
        'time_visit.requireIf'      =>  '如需回访,则回访时间必填',
        'consult_cost.require'      =>  '咨询费必填',
        'user_id.require'           =>  '录入人必填',

    ];

    protected $scene = [

        #保存/更新人员信息
        'saveDemand'         =>      'company_name,linkman,sex,link_number,landline_phone,customer_type,return_visit,time_visit,consult_cost,user_id'
    ];

}