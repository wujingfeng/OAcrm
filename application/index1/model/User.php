<?php
/**
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/9/20
 * Time: 16:03
 */

namespace app\index\model;


class User extends  Common
{
    protected $table = 'user';
    protected $pk = 'user_id';
//
//    # 设置关联模型及其数据表
//    protected $relationModel = [
//        'Role'      =>  'Role'
//    ];
//
//    protected $mapFields = [
//        'user_role_id'        =>  'User.role_id',
//        'role_role_id'        =>   'Role.role_id'
//    ];

    public function role($field='*'){
//        return $this->hasOne('Role','role_id','role_id')->field($field)->where($where)->select();
        return $this->hasOne('Role','role_id','role_id')->field($field);
    }

}