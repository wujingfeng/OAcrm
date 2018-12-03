<?php
/**
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/9/29
 * Time: 18:14
 */

namespace app\index\controller;


use think\Loader;
use think\Request;

class Menu  extends Common
{
    /**
     * 创建/修改菜单
     * @return \think\response\Json
     */
    public  function saveMenu(){
        $request = Request::instance();
        $menu_id = $request->param('menu_id','','trim');
        $parent_id = $request->param('parent_id','44e6d633f45f11edd409a97efdabcfc1','trim');
        $title = $request->param('title','用户管理','trim');
        $route = $request->param('route',"/index/User/index",'trim');
        $display = $request->param('display',1,'intval');
        $valid = $request->param('valid',1,'intval');

        $data = [
            'title'     =>      $title,
            'route'     =>      $route
        ];
        $result = $this->validate($data,'Menu.saveMenu');
        if($result !== true){
            return $this->error_msg($result);
        }

        $data['parent_id']   = $parent_id;
        $data['display']   = $display;
        $data['valid']   = $valid;

        $menu = Loader::model('Menu');
        if(!$menu_id){
            $isExists = $menu->where(['title'=>$title])->find();
            if($isExists){
                return $this->error_msg(8);
            }
            #插入
            $data['menu_id'] = $this->md5_str_rand();
            $result = $menu->save($data);
        }else{
            $result = $menu->save($data,['menu_id'=>$menu_id]);
        }

        if($result){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }

    }
}