<?php
/**
 * 登录操作相关接口
 * Created by PhpStorm.
 * User: wjfeng
 * Date: 2018/9/20
 * Time: 15:08
 */

namespace app\index\controller;




use think\Db;
use think\Request;
use think\Session;

class Login extends  Common
{
    public function index(){

    }

    /**
     * 登录
     * @return \think\response\Json
     */
    public function login(){
        $request = Request::instance();
        $mobile = $request->param('mobile','','trim');
        $password = $request->param('password','','trim');

        $result = $this->validate([
            'mobile'        =>  $mobile,
            'password'      =>  $password
        ],'Login.login');

        if($result !== true){
            return $this->error_msg($result);
        }

        $where = [
            'mobile'        =>      $mobile,
            'password'      =>      md5($password)
        ];
//        $result = Db::view('user','user_id,user_name')
//            ->view('role','role_permission','user.role_id =  role.role_id')
//            ->where($where)
//            ->find();

        $result = Db('user')->field('user_id,user_name')->where($where)->find();
        if($result){
//            session('user_id',$result['user_id']);
//            Session::set('');
            $data = [
                'user_id'       =>      $result['user_id'],
                'user_name'     =>      $result['user_name'],
                'token'         =>      $result['user_id']
            ];
            return $this->success_msg($data);
        }else{
            return $this->error_msg(5);
        }

    }

    /**
     * 获取用户菜单权限(列表)
     * @return \think\response\Json
     */
    public function getMenu1(){
        $user_id = Request::instance()->param('user_id','','trim');

        $where = [
            'user_id'       =>      $user_id
        ];
        $result = Db::view('user','user_id')
            ->view('role','role_permission','user.role_id =  role.role_id')
            ->where($where)
            ->find();

        if($result){
            $permission = $result['role_permission'];
            $finalResult = [''];
            if($permission){
                $where = [
                    'resource_id'        =>      ['IN',$permission]
                ];
                $resource = Db::table('resource')->field('resource_id,route,title,parent_id')->where($where)->select();
                $treeResult = $this->generate_tree($resource,'resource_id','parent_id');
//                dump($treeResult);
                $finalResult = $treeResult;
            }

            return $this->success_msg($finalResult);
        }else{
            return $this->error_msg(6);
        }

    }






}