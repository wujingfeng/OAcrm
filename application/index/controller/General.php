<?php
/**
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/10/29
 * Time: 17:23
 */

namespace app\index\controller;


use think\Db;
use think\Loader;
use think\Request;

class General extends Common
{

//    public function unlinkFile($path){
//        return file_exists($path) && unlink($path);
//    }

    /**
     * 附件上传
     * @return \think\response\Json
     */
    public  function saveFile()
    {
        $type = Request::instance()->param('type','staff','trim');
        $type_id = Request::instance()->param('type_id','','trim');
        $images = Request::instance()->param('image/a');
        $images_name = Request::instance()->param('image_name/a');

        $date = date('Ymd');
        if(!$type_id){
            return $this->error_msg('缺少参数');
        }
//        $type = $type== 'staff'?'staff':'demand';

        $data = [];

        #===========

        if($images){
            $temp['type_id'] = $type_id;
            $temp['type'] = $type;
            for($i = 0;$i<count($images);$i++){
                $zj = strpos($images[$i], 'base64');
                if ($zj !== false) {
                    $file = $images[$i];
                    $start = strpos($file, ',');
                    $file = substr($file, $start + 1);
                    $file = str_replace(' ', '+', $file);
                    $data_file = base64_decode($file);
//                    $suffix = substr($images_name[$i],strpos($images_name[$i],'.'));
                    $filePath = ROOT_PATH . 'public' . DS . 'uploads'.DS .$date.$type_id;
//                    if (!is_dir($filePath)){
//                        mkdir($filePath);
//                    }
                    $name = trim(trim($images_name[$i],'/'),'\\');
                    $fileName = $filePath.$name;
//                    $ff = explode('/', $fileName);//dump ($ff).'<br>';
                    $success = file_put_contents($fileName, $data_file);
                    $temp['path'] = DS. 'public' . DS . 'uploads'.DS .$date.$type_id.$name;
                }else{
                    $temp['path'] = $images[$i];
                }
                $data[] = $temp;
            }

        }

        if ($data) {
            Db('enclosure')->where(['type_id'=>$type_id])->delete();
            $result = Db('enclosure')->insertAll($data);
            if($result){
                return $this->success_msg('上传成功！');
            }else{
                return $this->error_msg('上传失败！');
            }
        } else {
            return $this->error_msg('上传失败！');
        }
    }

    public function getFile(){
        $request = Request::instance();
        $id = $request->param('type_id','','trim');

        $result = Db('enclosure')->field('path')->where(['type_id'=>$id])->select();

        if($result){
            $finalResult = [];
            foreach($result as $re){
                $finalResult[] = $re['path'];
            }
            return $this->success_msg($finalResult);
        }else{
            return $this->error_msg(1);
        }

    }


    /**
     * 状态更改
     * @return \think\response\Json
     */
    public function changeStatus(){
        $id = Request::instance()->param('id','','trim'); # 字符串1,2,3,4
        $status = Request::instance()->param('status','','trim');
        $user_id = Request::instance()->param('user_id','','trim');
        $type = Request::instance()->param('type','staff','trim');

        $type_map = [
            # 修改人员状态
            'staff' =>  [
                'staff_card', # 模型名
                'id',    # 查询的字段
                'status' # 修改的字段
            ],
            # 修改需求状态
            'demand'=>  [
                'company_demand',
                'demand_id',
                'customer_type'
            ],
            # 修改匹配状态
            'match' =>  [
                'match',
                'match_id',
                'status'
            ]
        ];

        if(!in_array($type,array_keys($type_map))){
            return $this->error_msg('类型参数错误');
        }
        $model = $type_map[$type][0];
        $pk = $type_map[$type][1];
        $status_type = $type_map[$type][2];
//        $type = $type =='staff'?'staff_card':'demand_card';

        if(!$id){
            return $this->error_msg('参数错误');
        }
        $model = Loader::model($model);

        $result = $model->save([$status_type=>$status],[$pk=>['IN',$id]]);


        if($result){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }

    }


}