<?php
/**
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/12/11
 * Time: 18:06
 */

namespace app\index\controller;


use think\Db;
use think\Loader;
use think\Request;

class Invoice extends Common
{
    /**
     *
     * 保存/修改开票信息
     * @return \think\response\Json
     */
    public function saveInvoice(){
        $request = Request::instance();

        $invoice_id = $request->param('invoice_id','','trim');
        $user_id = $request->param('user_id','','trim');
        $invoice_type = $request->param('invoice_type','','trim'); # 发票类型
        $apply_company = $request->param('apply_company','','trim'); # 申请开票单位
        $invoice_time = $request->param('invoice_time','','trim'); #开票时间
        $invoice_company = $request->param('invoice_company','','trim'); # 开票公司名称
        $bank_account = $request->param('bank_account','','trim'); # 合同约定回款开户行及账户
        $invoice_duty_number = $request->param('invoice_duty_number','','trim'); # 开票单位税号
        $invoice_bank_account = $request->param('invoice_bank_account','','trim'); # 开票单位银行开户行及账号
        $invoice_address = $request->param('invoice_address','','trim'); # 开票单位地址
        $invoice_phone = $request->param('invoice_phone','','trim'); # 开票单位电话
        $remark = $request->param('remark','','trim'); # 备注
        $receive_detail = $request->param('receive_detail','','trim'); # 收款明细
        $apply_time = $request->param('apply_time','','trim'); # 申请开票时间
        $estimate_time = $request->param('estimate_time','','trim'); # 大概(预计)开票时间
        $invoice_money = $request->param('invoice_money','','trim'); # 开票金额
        $contract_price = $request->param('contract_price','','trim'); # 合同本金
        $taxes_price = $request->param('taxes_price','','trim'); # 税金金额
        $return_account = $request->param('return_account','','trim'); # 回款账号+账号(简写)
        $return_time = $request->param('return_time','','trim'); # 回款时间
        $return_price = $request->param('return_price','','trim'); # 回款金额

        $data = [
            'user_id'          =>      $user_id,
            'invoice_type'          =>      $invoice_type,
            'apply_company'          =>      $apply_company,
            'invoice_time'          =>      $invoice_time,
            'invoice_company'          =>      $invoice_company,
            'bank_account'          =>      $bank_account,
            'invoice_duty_number'          =>      $invoice_duty_number,
            'invoice_bank_account'          =>      $invoice_bank_account,
            'invoice_address'          =>      $invoice_address,
            'invoice_phone'          =>      $invoice_phone,
            'remark'          =>      $remark,
            'receive_detail'          =>      $receive_detail,
            'apply_time'          =>      $apply_time,
            'estimate_time'          =>      $estimate_time,
            'invoice_money'          =>      $invoice_money,
            'contract_price'          =>      $contract_price,
            'taxes_price'          =>      $taxes_price,
            'return_account'          =>      $return_account,
            'return_time'          =>      $return_time,
            'return_price'          =>      $return_price,
        ];

        $model = Loader::model('invoice');
        if(!$invoice_id){
            $data['invoice_id'] = $this->md5_str_rand();

            $result = $model->save($data);

        }else{
            $result = $model->save($data,['invoice_id'=>$invoice_id]);
        }

        if($result){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }

    }


    /**
     * 获取开票列表
     * @return \think\response\Json
     */
    public function getInvoice(){
        $request = Request::instance();

        $user_id = $request->param('user_id','','trim'); #
        $apply_company = $request->param('apply_company','','trim'); # 申请开票公司
        $min_time = $request->param('min_time','','trim'); # 预计开票时间 下限
        $max_time = $request->param('max_time','','trim'); # 预计开票时间 上限
        $page = $request->param('page',1,'trim'); #
        $rows = $request->param('rows',10,'trim'); #
        $begin_item = ($page-1)*$rows;

        $users = $this->getLowerLevelUsers($user_id);
        $where = [
            'invoice.user_id'   => ['in',$users]
        ];

        if($apply_company){
            $where['apply_company'] = ['LIKE',$apply_company];
        }

        if($min_time){
            $where['estimate_time'] = ['egt',$min_time];
        }

        if($max_time){
            $where['estimate_time'] = ['elt',$max_time];
        }

        $result = Db::view('invoice','*')
            ->view('user','user_name','user.user_id = invoice.user_id','left')
            ->where($where)
            ->limit($begin_item,$rows)
            ->order('modified desc')
            ->select();

        $count = Db('invoice')
            ->where($where)
            ->count();

        if($result){
            return $this->success_msg($result,$count);
        }else{
            return $this->success_msg(3);
        }

    }




}