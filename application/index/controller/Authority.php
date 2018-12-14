<?php
/**
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/12/13
 * Time: 17:36
 */

namespace app\index\controller;


use think\Db;
use think\Loader;
use think\Request;

class Authority extends  Common
{
    public function saveNavigateAuth(){
        $request = Request::instance();

        $parent_id = $request->param('parent_id','0','trim');
        $name = $request->param('name','','trim');
        $route = $request->param('route','','trim');
        $remark = $request->param('remark','','trim');
        $order_num = $request->param('order_num',9999,'intval');
        $can_delete = $request->param('can_delete',0,'intval');
        $script_name = $request->param('script_name','','trim'); # 脚本名字
        $valid = $request->param('valid',1,'intval');
        $can_change = $request->param('change',1,'intval');  #是否有编辑权限(1有 0没有)
        $menu_id = $request->param('menu_id','','trim');

        if(!$name){
            return $this->error_msg('参数错误');
        }
        $route = strtolower($route);
        $route_arr = explode('/',trim($route,'?'));
        $control = array_slice($route_arr,-2,1)[0];
        $action = array_slice($route_arr,-1,1)[0];
//        $action = end($route_arr);
//        array_pop($route_arr);
//        $control = end($route_arr);
        $order_num = $order_num>9999?9999:$order_num;

        $data = [
            'parent_id'  =>  $parent_id,
            'name'  =>  $name,
            'controller'   =>  $control,
            'action'   =>  $action,
            'route'   =>  $route,
            'remark'    =>  $remark,
            'order_num' =>  $order_num,
            'can_delete'    =>  $can_delete,
            'script_name'      =>  $script_name,
            'valid'     =>  $valid,
            'can_change'     =>  $can_change,
        ];


        $model = Loader::model('menu');
        # 判断菜单名是否重复
        $isExists = $model->where(['name'=>$name])->find();
        if($isExists){
            return $this->error_msg('菜单名称重复');
        }
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


    /**
     * 获取所有的权限菜单列表
     * @return \think\response\Json
     */
    public function getAllMenu(){
        $user_id = Request::instance()->param('user_id','','trim');

        $result = Db('menu')->field('menu_id,parent_id,name')->select();
        $result = $this->generate_tree_with_child($result,'menu_id');
        if($result){
            return $this->success_msg($result);
        }else{
            return $this->success_msg(3);
        }
    }

    /**
     *
     * 保存角色权限
     * @return \think\response\Json
     */
    public function saveRoleAuth(){
        $request = Request::instance();

        $user_id =  $request->param('user_id','','trim');
        $role_id =  $request->param('role_id','','trim');
        $menu_ids = $request->param('menu_ids','','trim');
        $id = $request->param('id','','trim');

        $data = [
            'role_id'   =>  $role_id,
            'menu_id'   =>  $menu_ids
        ];

        $model = Loader::model('permission');

        if($id){
            $result = $model->save($data,['id'=>$id]);
        }else{
            $result = $model->save($data);
        }

        if($result){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }

    }

    /**
     *
     * 获取用户的菜单权限列表
     * @return \think\response\Json
     */
    public function getUserMenu(){
        $user_id = Request::instance()->param('user_id','','trim');

        $result = Db::view('user','user_id')
            ->view('role','role_id','user.role_id = role.role_id','left')
            ->view('permission','menu_id','permission.role_id = role.role_id','left')
            ->where(['user.user_id'=>$user_id])
            ->select();

        if(!$result){
            return $this->success_msg(3);
        }

        $menu_ids = implode(array_column($result,'menu_id'),',');
        $menus_result = Db('menu')->field('menu_id,parent_id,name,script_name,icon')
            ->where(['menu_id'=>['in',$menu_ids]])
            ->select();
//        if(!$menus_result){
//            return $this->success_msg(3);
//        }
//        dump($menus_result);
        $result = $this->generate_tree_with_child($menus_result,'menu_id');
//        dump($result);
        if($result){
            return $this->success_msg($result);
        }else{
            return $this->error_msg(2);
        }

    }




}