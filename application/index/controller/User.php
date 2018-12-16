<?php
/**
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/9/21
 * Time: 11:56
 */

namespace app\index\controller;

use think\Loader;
use think\Request;
class User extends  Common
{
    /**
     * 创建用户账号
     */
    public function saveUser(){
        $request = Request::instance();
        $user_id = $request->param('uid','','trim');
        $user_name = $request->param('user_name','','trim');
        $password = $request->param('password','123456','trim');# 密码默认123456
        $role_id = $request->param('role_id','','trim');
        $organization_id = $request->param('dept_id','','trim');
        $mobile = $request->param('mobile','');
        $email  = $request->param('email','','trim');
        $qq  = $request->param('qq','','trim');

        $data = [
            'user_name'     =>  $user_name,
            'password'      =>  $password,
            'role_id'       =>  $role_id,
            'organization_id'       =>  $organization_id,
            'mobile'        =>  $mobile,
            'email'         =>  $email,
        ];

        #验证规则
        $result = $this->validate($data,'Login.createUser');
        if(true !== $result){
            return $this->error_msg($result);
        }

        # 判断手机号是否已经存在
        if($user_id){
            # 更新时 不修改密码
            unset($data['password']);
            $mobileWhere = [
                'mobile'=>$mobile,
                'user_id'=>['<>',$user_id]
            ];
        }else{
            # 如果是创建新用户,则不允许创建超级管理员的用户
            if($organization_id == 'd41d8cd98f00b204e9800998ecf8427e'){
                $isexist = Db('user')->where(['organization_id'=>$organization_id])->find();
                if($isexist){
                    return $this->error_msg('操作失败,已存在超级管理员');
                }
            }


            $mobileWhere = [
                'mobile'=>$mobile,
            ];
        }



        $data['password'] = md5($password);
        $data['role_id'] = $role_id;
        $data['qq'] = $qq;


        $model = Loader::model('User');

        $login_name_result = $model->where($mobileWhere)->find();
        if($login_name_result){
            return $this->error_msg(4);#注册失败,手机号已存在
        }

        if(!$user_id){
            #注册流程
            $data['user_id'] = md5(mt_rand());
            $result = $model->save($data);
        }else{
            #更新流程
            $result = $model->save($data,['user_id'=>$user_id]);
        }

        if($result){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }
    }

    /**
     * 获取角色下的用户列表
     * @return \think\response\Json
     */
    public function getUserList(){
        $role_id = Request::instance()->param('role_id','','trim');


        $where = [
            'role_id'   =>  $role_id
        ];

        $role_result = Db('user')->field('user_id,user_name,mobile,email,qq')->where($where)->select();

        if($role_result){
            return $this->success_msg($role_result,count($role_result));
        }else{
            return $this->success_msg(3);
        }

    }

    /**
     * 删除用户
     * @return \think\response\Json
     */
    public function delUser(){
        $user_id = Request::instance()->param('uid','','trim');
        if(!$user_id){
            return $this->error_msg('参数错误');
        }
        $result = Db('user')->where(['user_id'=>$user_id])->delete();
        if($result){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }

    }


}