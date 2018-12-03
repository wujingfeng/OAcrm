<?php
/**
 * 组织机构
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/9/21
 * Time: 15:25
 */

namespace app\index\controller;


use think\Db;
use think\Loader;
use think\Request;

class Organization extends Common
{
    public function  saveOrganization(){
        $request        = Request::instance();
        $organization_id= $request->param('organization_id','','trim');#组织id
        $parent_id      = $request->param('parent_id','cfcd208495d565ef66e7dff9f98764da','trim');#上级组织id
        $name           = $request->param('name','业务部2','trim');#组织名称
        $description    = $request->param('description',"",'trim');#组织描述/备注

        $data = [
            'parent_id'                 =>  $parent_id,
            'organization_name'         =>  $name,
            'organization_description'  =>  $description
        ];

        #参数验证
        $result = $this->validate($data,'Organization.saveOrganization');
        if($result !== true){
            return $this->error($result);
        }

        #保存
        $model = Loader::model('Organization');
        if(!$organization_id){
            $name = $model->where(['organization_name'=>$name,'parent_id'=>$parent_id])->find();
            if($name){
                return $this->error_msg(7);
            }
            $organization_id = $this->md5_str_rand();
            $data['organization_id'] = $organization_id;
            $result = $model->save($data);
        }else{
            $result = $model->save($data,['organization_id'=>$organization_id]);
        }
        if($result){
            return $this->success_msg($organization_id);
        }else{
            return $this->error_msg(2);
        }

    }


    /**
     * 获取组织机构及对应角色
     * @return \think\response\Json
     */
    public function getOrganization(){

        $where = [
            'valid' => 1
        ];
        $result = Db::table('organization')->field('organization_id,parent_id,organization_name,organization_description')->where($where)->select();


        if($result){

            # 获取到每个部门下的所有角色
            $organization_ids = array_column($result,'organization_id');
            $where = [
                'organization_id'   =>  ['IN',$organization_ids],
                'valid'             =>  1
            ];
            $roleResult = Db('role')->field('role_id,organization_id,role_name,role_description')->where($where)->select();
            if($roleResult){
                foreach ($result as &$value){
                    $value['child'] = isset($value['child'])?$value['child']:[];
                    foreach($roleResult as $item){
                        if($value['organization_id'] == $item['organization_id']){
                            array_push($value['child'],$item);
                        }
                    }
                }
            }
            # 生成树形结构
            $generate_result = $this->generate_tree($result,'organization_id','parent_id');
            return $this->success_msg($generate_result);
        }else{
            return $this->error_msg(2);
        }
    }

}