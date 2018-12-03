<?php
/**
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/9/30
 * Time: 13:33
 */

namespace app\index\controller;


use app\index\model\AreaMap;
use think\Db;
use think\Loader;
use think\Request;

class Dictionary extends Common
{
    /**
     * 添加/修改字典
     * @return \think\response\Json
     */
    public function saveDictionary(){
        $request = Request::instance();

        $dictionary_id  =       $request->param('dictionary_id','','trim');
        $parent_id      =       $request->param('parent_id','0','trim');
        $name           =       $request->param('name','','trim');
        $alias          =       $request->param('alias','','trim');
        $valid          =       $request->param('valid',1,'intval');
        $order_num      =       $request->param('order_num',9999,'intval');
        $description    =       $request->param('description','','trim');

        $data = [
            'parent_id'         =>      $parent_id,
            'name'              =>      $name,

            'alias'             =>      $alias,
            'valid'             =>      $valid,
            'order_num'         =>      $order_num,
            'description'       =>      $description,
        ];
//        $result = $this->validate($data,'Dictionary.saveDictionary');
//
//        if($result !== true){
//            return $this->error_msg($result);
//        }

        if(!$name){
            return $this->error_msg('字典名称不能为空');
        }

        $dictionary = Loader::model('Dictionary');
        if(!$dictionary_id){
            #更新
            $data['dictionary_id'] = $this->md5_str_rand();
            $result = $dictionary->save($data);
        }else{
            $result = $dictionary->save($data,['dictionary_id'=>$dictionary_id]);
        }

        if($result){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }

    }

    /**
     *获取字典信息
     * @return \think\response\Json
     */
    public function getDictionary(){
        $dictionary_id = Request::instance()->param('dictionary_id','44a00fd1903aeb8863c9ad230d5a6af2','trim');
        if($dictionary_id == 'all'){
            $dictionary_id = 0;
            $result = Db::table('dictionary')->field('dictionary_id,parent_id,name,alias,description,valid,order_num')->order('order_num')->select();
        }else{
            $where = [
                'dictionary_id' =>  $dictionary_id
            ];
            $orWhere = [
                'parent_id'     =>  $dictionary_id
            ];
            $result = Db::table('dictionary')->field('dictionary_id,parent_id,name,alias,description,valid,order_num')->where($where)->whereOr($orWhere)->order('order_num')->select();
        }
        $treeResult = $this->generate_tree($result,'dictionary_id','parent_id',$dictionary_id);
        return $this->success_msg($treeResult);

    }


    /**
     * 获取地区级联
     * @return \think\response\Json
     */
    public function getAreaMap(){
        $level = Request::instance()->param('level',3,'intval');
        $where = [
            'level_type'    =>  ['<=',$level]
        ];
        $areamapResult = Db('area_map')->field('area_id,parent_id,short_name')->where($where)->select();
        if($areamapResult){
            $areamapTree = $this->generate_tree($areamapResult,'area_id','parent_id');
            return $this->success_msg($areamapTree);
        }else{
            return $this->error_msg(2);
        }

    }


    public function getProver(){
//        $area = Db('area_map')->field('area_id,name')->where(['level_type'=>1])->select();
        $area = [['name'=>'无'],['name'=>'可转让社保']];
        foreach($area as $item){
            $data = [
                'parent_id'         =>      'e476f42feea7331bf175e3d203dfa627',
                'name'              =>      $item['name'],
                'alias'             =>      '',
                'valid'           =>      1,
                'description'       =>      '',
                'dictionary_id'     =>  $this->md5_str_rand()
            ];
            $data1[] = $data;
        }

        $dictionary = Loader::model('Dictionary');
        $res = $dictionary->saveAll($data1,false);
        if($res){
            echo 1;
        }else{
            echo 2;
        }


    }

}