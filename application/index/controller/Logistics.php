<?php
/**
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/12/3
 * Time: 16:07
 */

namespace app\index\controller;


use think\Db;
use think\Loader;
use think\Request;

class Logistics extends Common
{

    /**
     * 保存申请的后勤信息
     * @return \think\response\Json
     */
    public function saveApplyLogistics(){
        $request = Request::instance();

        $logistics_id = $request->param('logistics_id','','trim');
        $user_id = $request->param('user_id','','trim');
        $customer_name = $request->param('customer_name','','trim');
        $customer_type = $request->param('customer_type','','trim');
        $logistics_type = $request->param('logistics_type','','trim');
        $receiver = $request->param('receiver','','trim');
        $address = $request->param('address','','trim');
        $send_company = $request->param('send_company','','trim');
        $phone = $request->param('phone','','trim');
        $goods_detail = $request->param('goods_detail','','trim');
        $apply_remark = $request->param('apply_remark','','trim');

        if(!$user_id){
            return $this->error_msg('参数错误');
        }

        $data = [
            'user_id'  =>  $user_id,
            'customer_name'  =>  $customer_name,
            'customer_type'  =>  $customer_type,
            'logistics_type'  =>  $logistics_type,
            'receiver'  =>  $receiver,
            'address'  =>  $address,
            'send_company'  =>  $send_company,
            'phone'  =>  $phone,
            'goods_detail'  =>  $goods_detail,
            'apply_remark'  =>  $apply_remark,
        ];

        $model = Loader::model('logistics');
        if(!$logistics_id){
           $data['logistics_id'] = $this->md5_str_rand();
            $result = $model->save($data);
        }else{
            $result = $model->save($data,['logistics_id'=>$logistics_id]);
        }

        if($result){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }

    }


    /**
     * 保存审批的后勤信息
     * @return \think\response\Json
     */
    public function saveApproveLogistics(){
        $request = Request::instance();

        $logistics_id = $request->param('logistics_id','','trim');
        $user_id = $request->param('user_id','','trim'); # 审批人id
        $cost = $request->param('cost','','trim');
        $approve_remark = $request->param('approve_remark','','trim');
        $status =  $request->param('status','','trim');
        $express_company =  $request->param('express_company','','trim');
        $express_number =  $request->param('express_number','','trim');

        if(!$logistics_id){
            return $this->error_msg('参数错误');
        }

        $data = [
            'approver'  =>  $user_id,
            'cost'      =>  $cost,
            'approve_remark'=> $approve_remark,
            'status'    =>  $status,
            'express_company'    =>  $express_company,
            'express_number'    =>  $express_number,
            'approve_time'=>date('Y-m-d H:i:s',time())
        ];

        $model = Loader::model('logistics');

        $result = $model->save($data,['logistics_id'=>$logistics_id]);
        if($result){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }

    }


    /**
     * 获取后勤信息列表
     * @return \think\response\Json
     */
    public function getLogisticsList(){
        $request = Request::instance();

//        $logistics_id = $request->param('logistics_id','','trim');
        $status = $request->param('status','','trim');
        $logistics_type = $request->param('logistics_type','','trim');
        $customer_type = $request->param('customer_type','','trim');
        $customer_name = $request->param('customer_name','','trim');
        $minTime = $request->param('min_time','','trim');
        $maxTime = $request->param('max_time','','trim');
        $page = $request->param('page',1,'intval');
        $rows = $request->param('rows',10,'intval');
        $begin_item = ($page-1)*$rows;

        $where = false;
        if($status){
            $where['status'] = $status;
        }
        if($logistics_type){
            $where['logistics_type'] = $logistics_type;
        }
        if($customer_name){
            $where['customer_name'] = $customer_name;
        }
        if($customer_type){
            $where['customer_type'] = $customer_type;
        }
        if($minTime){
            $where['created'] = ['gt',$minTime];
        }
        if($maxTime){
            $where['created'] = ['lt',$maxTime];
        }

        $result = Db::view('logistics',"*")
            ->view('user','user_name as approver','logistics.approver = user.user_id','left')
            ->where($where)->limit($begin_item,$rows)->select();
        $count = Db('logistics')->where($where)->count();
        if($result){
            return $this->success_msg($result,$count);
        }else{
            return $this->success_msg(3);
        }
    }



}