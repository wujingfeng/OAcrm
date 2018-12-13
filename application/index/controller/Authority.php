<?php
/**
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/12/13
 * Time: 17:36
 */

namespace app\index\controller;


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
        $type = $type=='interface'?'interface':'script';
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


    /**
     * 获取所有的权限列表
     * @return \think\response\Json
     */
    public function getAllMenu(){
        $user_id = Request::instance()->param('user_id','','trim');


        $result = Db('menu')->select();
        $result = $this->generate_tree_with_child($result,'menu_id');
        if($result){
            return $this->success_msg($result);
        }else{
            return $this->success_msg(3);
        }
    }


}