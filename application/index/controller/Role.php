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


    public function saveNavigateAuth(){
        $request = Request::instance();

        $parent_id = $request->param('parent_id','0','trim');
        $name = $request->param('name','','trim');
        $route = $request->param('route','','trim');
        $remark = $request->param('remark','','trim');
        $order_num = $request->param('order_num',9999,'intval');
        $can_delete = $request->param('can_delete',0,'intval');
        $type = $request->param('type','script','trim'); # 控制类型(script/interface) 脚本/接口
        $valid = $request->param('valid',1,'intval');
        $can_change = $request->param('change',1,'intval');  #是否有编辑权限(1有 0没有)
        $menu_id = $request->param('menu_id','','trim');

        if(!$name){
            return $this->error_msg('参数错误');
        }
        if($type == 'interface'){
            $control = array_slice(explode('/',trim($route,'?')),-2,1)[0];
            $action = array_slice(explode('/',trim($route,'?')),-1,1)[0];
        }else{
            $control = '';
            $action = '';
        }
        $order_num = $order_num>9999?9999:$order_num;

        $data = [
            'name'  =>  $name,
            'controller'   =>  $control,
            'action'   =>  $action,
            'route'   =>  $route,
            'remark'    =>  $remark,
            'order_num' =>  $order_num,
            'can_delete'    =>  $can_delete,
            'type'      =>  $type,
            'valid'     =>  $valid,
            'can_change'     =>  $can_change,
        ];

        $model = Loader::model('menu');
        if(!$menu_id){
            $data['menu_id'] = $this->md5_str_rand();

            $result = $model->save($data);
        }else{
            $result = $model->save($data,['menu_id'=>$menu_id]);
        }

        if($result){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }

    }


}