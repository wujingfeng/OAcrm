<?php
/**
 * Created by PhpStorm.
 * User: 11633
 * Date: 2018/10/15
 * Time: 23:16
 */

return [

    # 人员信息中需要对字典的字段名
    'staff_field_cfg' => [
        'three_category',
        'education',
        'bid_type',
        'customer_type',
        'year',
        'social_security',
        'level',
        'profession',
        'other_card'
    ],
    # 企业需求信息中需要对字典的字段名
    'demand_field_cfg' => [
        'quality',
        'company_place',
        'customer_type',
        'company_type',
        'level',
        'profession',
        'three_category',
        'education',
        'duty',
        'other_card',
    ],

    # 顶级字典对应关系
    'top_dict_map' => [
        'card'              =>  '01955d0008f2437538f4dfca7c3f2664', # 证书
        'year'              =>  '3215cb92ac0883519011fd148f103126', # 年限
        'bid_type'          =>  '5af8e6daf1e7b065f6fb23f65e52d3ab', # 招标出场类型
        'company_quality'   =>  '6fefd22656b844cb29d77cc0ed6a642a', # 公司资质
        'customer_type'     =>  '833191b335b35fd9c87cab79a1115921', # 客户类型
        'register'          =>  '920da67602df9606156e8c68b61b25f5', # 初始转注
        'quality'           =>  '9cb8b222f4e757c17d11fb7fda4dc71a', # 资质
        'duty'              =>  'b9baf11d7e77e83ef11a5b64355aa352', # 职称
        'three_category'    =>  'bad32008095ec426c825c5da333f0af2', # 三类人员
        'social_security'   =>  'e476f42feea7331bf175e3d203dfa627', # 社保
        'education'         =>  'ebbc6da5d7ca624592904d9a41786cbb' # 学历
    ],

    # 指定不能被删除的字典值
    'can_not_del_dict'  =>[
        'f95c1a880f5ad672ccf76516a620e811', # 潜在客户
        '5ebf1d7b31ded660cc201b500db053c9', # 签约客户
    ]



];