<?php
/**
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/9/21
 * Time: 13:42
 * author:wjfeng
 */

namespace app\index\controller;


use think\Db;
use think\Loader;
use think\Request;

class Role extends Common
{
    /**
     * 创建/更新角色
     * @return \think\response\Json|void
     */
    public function saveRole(){
        $request            = Request::instance();
        $user_id            = $request->param('user_id','','trim');#当前用户id
        $role_id            = $request->param('role_id','','trim');#角色id
        $organization_id    = $request->param('organization_id','','trim');#所属组织id
        $role_permission    = $request->param('role_permission','','trim');#所属拥有的权限(逗号拼接)
        $role_name          = $request->param('role_name','','trim');#角色名称
        $role_description   = $request->param('description','','trim');#角色描述

        $data = [
            'organization_id'   =>  $organization_id,
            'role_name'         =>  $role_name,
            'role_description'  =>  $role_description,
            'role_permission'   =>  $role_permission
        ];

        #参数验证
        $result = $this->validate($data,'Role.saveRole');
        if($result !== true){
            return $this->error($result);
        }
        # ===== 最高组织下只能创建一个角色---超级管理员
        if($organization_id == 'd41d8cd98f00b204e9800998ecf8427e'){
            $isexist = Db('role')->where(['organization_id'=>$organization_id])->find();
            if($isexist){
                return $this->error_msg('最高机构下只允许存在超级管理员一个角色');
            }
        }


        # =====         鉴权 start                 ====
        # 判断该用户操作的权限是否超过其本身拥有的权限
        $where = [
            'user.user_id'      =>  $user_id
        ];
        $result = Db::view('user','*')
            ->view('role','*','user.role_id = role.role_id','inner')
            ->where($where)
            ->find();
//        dump($result);

        if($result){
            if($role_permission){
                if($result['role_permission']){
                    $getPermission = explode(',',$role_permission);
                    $dbPermission = explode(',',$result['role_permission']);
                    #如果创建的角色权限大于自身所拥有的权限，则创建失败
                    $diff = array_diff($getPermission,$dbPermission);
                    if($diff){
                        return $this->error_msg(3);
                    }
                }else{
                    return $this->error_msg(3);
                }
            }

        }
        # ======            鉴权 end              ==========

        #保存
        $model = Loader::model('Role');

        $isExists = $model->where(['organization_id'=>$organization_id,'role_name'=>$role_name])->find();
        if($isExists){
            return $this->error_msg(7);
        }
        if(!$role_id){
            $role_id = $this->md5_str_rand();
            $data['role_id'] = $role_id;
            $result = $model->save($data);
        }else{
            $result = $model->save($data,['role_id'=>$role_id]);
        }
        if($result){
            return $this->success_msg($role_id);
        }else{
            return $this->error_msg(2);
        }
    }



    /**
     * 删除角色
     * @return \think\response\Json
     */
    public function delRole(){
        $role_id = Request::instance()->param('role_id','','trim');
        if(!$role_id){
            return $this->error_msg('参数错误');
        }
        $isExist = Db('user')->where(['role_id'=>$role_id])->find();

        if($isExist){
            return $this->error_msg('请先删除子节点');
        }

        $result = Db('role')->where(['role_id'=>$role_id])->delete();
        if($result){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }


    }

    public function getMenuList(){
        $user_id = Request::instance()->param('user_id','','trim');





    }


}