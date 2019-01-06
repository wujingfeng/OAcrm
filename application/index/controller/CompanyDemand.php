<?php
/**
 * Created by PhpStorm.
 * User: 11633
 * Date: 2018/10/23
 * Time: 22:03
 */

namespace app\index\controller;


use think\Config;
use think\Db;
use think\Loader;
use think\Request;

class CompanyDemand extends Common
{

    public function saveDemand(){
        $request = Request::instance();

        $demand_id   =       $request->param('demand_id','','trim'); #需求id
        $user_id    =       $request->param('user_id','','trim'); #录入人id
        $company_name       =       $request->param('company_name','','trim');  # 企业名称
        $quality       =       $request->param('quality','','trim');  # 资质q
        $company_place =       $request->param('company_place','','trim');    #资格证所在地
        $link_address =       $request->param('link_address','','trim');    #联系地址
        $linkman       =       $request->param('linkman','','trim');  # 联系人姓名
        $sex        =       $request->param('sex',1,'intval');  # 性别
        $link_number  =       $request->param('link_number','','trim'); # 联系人电话号码
        $landline_phone  =       $request->param('landline_phone','','trim'); # 座机电话号码
        $qq         =       $request->param('qq','','trim');    # qq号
        $customer_type  =   $request->param('customer_type','','trim'); # 客户类型
        $company_type  =   $request->param('company_type','','trim'); # 企业类型
        $return_visit   =   $request->param('return_visit','','trim');  #是否回访
        $time_visit     =   $request->param('time_visit','','trim');    # 回访时间(如需回访,则时间必填)
        $due_time     =   $request->param('due_time','','trim');    # 合同到期时间
        $other_card     =   $request->param('other_card/a',[]); # 其他证件(数组)
        $taxes         =   $request->param('taxes','0','trim');   # 税金
        $referee=  $request->param('referee','','trim'); # 推荐人
//        $cards          =   $request->param('cards','[{"level":"22f29e6e68fef3937b37a1abda5aa46b","profession":"db00cddf8ddea531fa33126bfbaea799","register":"","three_category":"5db3fc01a34786defc52ed4d02836035","education":"","duty":"","bid_type":"829eb3bdc5179cbef278d71a4060d700","number_needed":"3","company_price":"10000","year":"1"}]','trim');# 证书(接受json)
        $cards          =   $request->param('cards','','trim');# 证书(接受json)
        $consult_cost   =   $request->param('consult_cost','0','trim'); # 咨询费
        $remark         =   $request->param('remark','','trim');    #备注

        $cards = json_decode($cards,true);

//        $three      =       $request->param('three_category','','trim');    # 三类人员
//        $education  =       $request->param('education','','trim'); #学历
//        $duty       =       $request->param('duty','','trim');# 职称
//        $bid_type   =       $request->param('bid_type','','trim');  #招标出场类型
//        $company_price   =   $request->param('company_price','0','trim'); # 企业出价
//        $year           =   $request->param('year','0','trim'); # 年限
//        $social_security=   $request->param('social_security','无','trim');  # 社保
//        $card_status    =   $request->param('card_status','1','trim');  # 证书状态
//        $talent_type    =   $request->param('talent_type','','trim');   # 人才类型

//        dump($cards);
//        exit();
        $data = [
            'company_name'              =>          $company_name,
            'linkman'              =>          $linkman,
            'sex'               =>          $sex,
//            'three_category'    =>          $three,
            'link_number'         =>          $link_number,
            'landline_phone'         =>          $landline_phone,
//            'bid_type'          =>          $bid_type,
            'customer_type'     =>          $customer_type,
            'return_visit'      =>          $return_visit,
            'time_visit'        =>          $time_visit,
            'consult_cost'        =>          $consult_cost,
            'user_id'           =>          $user_id,
        ];

//        dump($data);
        $result = $this->validate($data,'Demand.saveDemand');
        if($result !== true){
            return $this->error_msg($result);
        }

        $data['qq'] =  $qq;
//        $data['year'] =  $year;
        $data['company_place'] =  $company_place;
        $data['company_type'] =  $company_type;
        $data['quality'] =  $quality;
        $data['link_address'] =  $link_address;
        $data['taxes'] =  $taxes;
        $data['referee'] =  $referee;
        $data['remark'] =  $remark;
        $data['due_time'] =  $due_time;

        $demand = Loader::model('CompanyDemand');
        $demandCard = Loader::model('demand_card');

        $card_data = [];
        if(!$demand_id){

            #插入

            $demand_id = $this->md5_str_rand();
            $data['demand_id']   =   $demand_id;
            $result = $demand->save($data);


        }else{
            #更新
            $result = $demand->save($data,['demand_id'=>$demand_id]);

            # 更新证书(直接删除更新更快)
            $where = [
                'demand_id'  =>  $demand_id
            ];
            $demandCard->where($where)->delete();

        }


        if($cards){
            $temp = [];
            foreach($cards as $card){
                $temp['demand_id'] = $demand_id;
                $temp['level'] = isset($card['level'])?$card['level']:'';
                $temp['profession'] = isset($card['profession'])?$card['profession']:'';
                $temp['register'] = isset($card['register'])?$card['register']:'';
                $temp['three_category'] = isset($card['three_category'])?$card['three_category']:'';
                $temp['education'] = isset($card['education'])?$card['education']:'';
                $temp['duty'] = isset($card['duty'])?$card['duty']:'';
                $temp['bid_type'] = isset($card['bid_type'])?$card['bid_type']:'';
                $temp['number_needed'] = isset($card['number_needed'])?$card['number_needed']:'';
                $temp['company_price'] = isset($card['company_price'])?$card['company_price']:'';
                $temp['year'] = isset($card['year'])?$card['year']:'';
                $card_data[] = $temp;
            }
        }

        if(isset($other_card[0]) && $other_card[0]){
            $temp = [];
            foreach($other_card as $item){
                $temp['demand_id'] =   $demand_id;
                $temp['other_card'] =   $item;
                $card_data[] = $temp;
            }
        }
//        DUMP($card_data);
        if($card_data){
            $cardResult = $demandCard->saveAll($card_data);
        }

        if($result or $cardResult){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }
    }

    /**
     * 获取需求列表
     * @return \think\response\Json
     */
    public function getDemandList()
    {
        $request = Request::instance();

        $user_id = $request->param('user_id', '', 'trim');
        $company_name = $request->param('company_name','','trim');
        $company_place = $request->param('company_place','','trim');
        $three_category = $request->param('three_category','','trim');
        $bid_type = $request->param('bid_type','','trim');
        $customer_type = $request->param('customer_type','','trim');
        $duty = $request->param('duty','','trim');
        $year = $request->param('year','','trim');
        $level = $request->param('level','','trim');
        $profession = $request->param('profession','','trim');

        $page = $request->param('page', 1, 'intval');
        $rows = $request->param('rows', 10, 'intval');
        $begin_item = ($page - 1) * $rows;


        $users = $this->getLowerLevelUsers($user_id);
        $users = json_decode($users,true);

        $areaResult = Db('area_map')->field('area_id,short_name')->where(['level_type'=>['<=',2]])->select();
        $area_map = [];
        $area_id_arr = [];
        if ($areaResult){
            foreach ($areaResult as $item){
                $area_id_arr[] = $item['area_id'];
                $area_map[$item['area_id']] = $item['short_name'];
            }
        }
        #====== 编辑where条件 start
        $where['company_demand.user_id'] = ['IN',$users];

        if($company_name){
            $where['company_demand.company_name'] = ['LIKE',"%$company_name%"];
        }
        if($company_place){
            $where['company_demand.company_place'] = $company_place;
        }
        if($three_category){
            $where['demand_cards.three_category'] = $three_category;
        }
        if($bid_type){
            $where['demand_cards.bid_type'] = $bid_type;
        }
        if($customer_type){
            $where['company_demand.customer_type'] = $customer_type;
        }
        if($duty){
            $where['demand_cards.duty'] = $duty;
        }
        if($year){
            $where['demand_cards.year'] = $year;
        }

        if($profession){
            $where['demand_cards.profession'] = $profession;
        }
        if($level){
            $where['demand_cards.level'] = $level;
        }

        #====== 编辑where条件 end
        #开始查询需求信息
//        $dict_map = $this->dict_id_map();
//        $dict_map = json_decode($dict_map,true);
//        $field_cfg = Config::get('parameter.demand_field_cfg');
        $demand_result = Db::view('company_demand','*')
            ->view('demand_cards','*','company_demand.demand_id = demand_cards.demand_id','left')
            ->view('user','user_name','user.user_id = company_demand.user_id','left')
            ->where($where)
            ->order('company_demand.modified desc')
            ->limit($begin_item,$rows)
            ->select();
        $demand_count = Db::view('company_demand','demand_id')
            ->view('demand_cards','id as sid','company_demand.demand_id = demand_cards.demand_id','inner')
            ->view('user','user_name','user.user_id = company_demand.user_id','left')
            ->where($where)
            ->select();

        $final_result = [];
        if($demand_result){

            #=====统计总条数 start
            $countArr = [];
            foreach($demand_count as $c){
                $countArr[] = $c['demand_id'];
            }
            $count = count(array_unique($countArr));
            #=====统计总条数 end

            #====将同一个需求下的多个证件合并为一条数据
            $demand_id_list = [];
            $demand_card_id_list = [];
            foreach($demand_result as $value){
                $demand_id_list[] = $value['demand_id'];
                $demand_card_id_list[] = $value['id'];
            }
                #====查询附件  生成附件与需求的映射关系 start
            $file_id_map = [];
            $file_id_list=[];
            $fileRes = Db('enclosure')->field('type_id,path')->where(['type_id'=>['IN',$demand_id_list]])->select();
            if($fileRes){
                foreach($fileRes as $f){
                    $file_id_list[] = $f['type_id'];
                    $file_id_map[$f['type_id']][] = $f['path'];
                }
            }
                #====查询附件  生成附件与需求的映射关系 end

                #====查询已匹配人员  生成匹配与人员证件映射关系 start
            $match_id_map = [];
            $match_id_list = [];
            $matchRes = Db::view('match','match_id,demand_id,staff_id')
                ->view('demand_cards','id as demand_card_id','match.demand_card_id = demand_cards.id','left')
                ->view('staff_cards','id as staff_card_id,*','match.staff_card_id = staff_cards.id','left')
                ->view('staff','name','staff_cards.staff_id = staff.staff_id','left')
                ->where(['demand_card_id'=>['in',$demand_card_id_list]])
                ->select();
            if($matchRes){
                foreach($matchRes as $m){
                    $match_id_list[] = $m['demand_card_id'];
                    $match_id_map[$m['demand_card_id']][] = $m;
                }
            }
                #====查询已匹配人员  生成匹配与人员证件映射关系 end

            foreach ($demand_result as &$item){

                $item['path'] = '';

                # 追加附件数据
                if(in_array($item['demand_id'],$file_id_list)){
                    $item['path'] = $file_id_map[$item['demand_id']];
                }
                # 追加已匹配人员数据
                if(in_array($item['id'],$match_id_list)){
                    $item['match'] = $match_id_map[$item['id']];
                }else{
                    $item['match'] = '';
                }

                if($item['company_place']){

                    $places = explode(',',$item['company_place']);
                    $arr = [];
                    foreach ($places as $place){
                        if(in_array($place,$area_id_arr)){
                            $arr[$place] = $area_map[$place];
                        }
                    }
                    $item['company_place'] = $arr;
                }

                $final_result[$item['demand_id']][] = $item;
            }
            unset($item);
            return $this->success_msg($final_result,$count);
        }else{
            return $this->success_msg(3);
        }
    }


    /**
     *
     * @return \think\response\Json
     */
    public function saveMatches(){
        $request = Request::instance();
        $demand_id        = $request->param('demand_id','','trim'); # 需求id
        $user_id        = $request->param('user_id','','trim'); # 录入人id
        $params         = $request->param('argvs/a',[]);
//        dump($params);
//        exit();
//        $params = [['staff_id'=>'0ee7e0de2d11c90fb931bf90b7f1c2d1','staff_card_id'=>'8','demand_card_id'=>'8','status'=>''],['staff_id'=>'414ce849b684507010b4c0ddf44d38c3','staff_card_id'=>'4','demand_card_id'=>'4','status'=>'']];
        if(!$demand_id|| !$params){
            return $this->error_msg('参数错误');
        }

//        if(!$match_id){
//            $match_id   =  $this->md5_str_rand();
//        }

        $result = '';

        foreach($params as $param){
            $match_id = $this->md5_str_rand();
            $data = [
                'user_id'     =>  $user_id,
                'demand_id'     =>  $demand_id,
                'staff_id'     =>  $param['staff_id'],
                'staff_card_id'     =>  $param['staff_card_id'],
                'demand_card_id'     =>  $param['demand_card_id'],
//                'status'     =>  '',
                'match_id'  => $match_id
            ];
            $result = Db('match')->insert($data,true); # 更新插入
        }



        if($result){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }

    }

    private function checkMatchFinish($demand_id=''){
        if(!$demand_id){
            return $this->error_msg('参数错误');
        }

        # 获取需要的人才总数
        $needed_result = Db('demand_cards')
            ->field('sum(needed_number) as need')
            ->where(['demand_id'=>$demand_id])
            ->group('demand_id')
            ->select();
        if($needed_result){
            $needed_number = $needed_result['need'];
        }else{
            $needed_number = 0;
        }

        # 获取已配对总数
        $matched_number = Db('match')
            ->where(['demand_id'=>$demand_id])
            ->count();
        if($matched_number>=$needed_number){
            $info = '配对完成';
        }else{
            $info = '配对中';
        }


    }

    /**
     * 删除匹配的人员
     * @return \think\response\Json
     */
    public function delMatches(){
        $request = Request::instance();
        $match_id = $request->param('match_id','','trim');

        if(!$match_id){
            return $this->error_msg('参数错误');
        }
        $where = [
            'match_id' => $match_id
        ];

        $result = Db('match')->where($where)->delete();

        if($result){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }

    }


    public function demandMatchList(){

        $request = Request::instance();

        $user_id = $request->param('user_id', '', 'trim');
        $page = $request->param('page', 1, 'intval');
        $rows = $request->param('rows', 10, 'intval');
        $begin_item = ($page - 1) * $rows;

        $users = $this->getLowerLevelUsers($user_id);
        $users = json_decode($users,true);

        $areaResult = Db('area_map')->field('area_id,short_name')->where(['level_type'=>['<=',2]])->select();
        $area_map = [];
        $area_id_arr = [];
        if ($areaResult){
            foreach ($areaResult as $item){
                $area_id_arr[] = $item['area_id'];
                $area_map[$item['area_id']] = $item['short_name'];
            }
        }

        #====== 编辑where条件 start
        $where['company_demand.user_id'] = ['IN',$users];

        #====== 编辑where条件 end
        #=====开始查询需求信息
        $demand_result = Db::view('match','match_id,demand_id,staff_id,staff_card_id,paid,unpaid,staff_id')
            ->view('demand_cards','number_needed,company_price','match.demand_card_id = demand_cards.id','left')
            ->view('company_demand','company_name,company_place,user_id,demand_status,created,due_time','company_demand.demand_id = demand_cards.demand_id','left')
            ->view('staff','name as staff_name','staff.staff_id = match.staff_id','left')
            ->view('user','user_name','user.user_id = company_demand.user_id','left')
            ->where($where)
            ->limit($begin_item,$rows)
            ->select();

        $demand_count = Db::view('match','match_id')
            ->view('demand_cards','id','match.demand_card_id = demand_cards.id','left')
            ->view('company_demand','demand_id','company_demand.demand_id = demand_cards.demand_id','left')
            ->view('user','user_name','user.user_id = company_demand.user_id','left')
            ->where($where)
            ->select();

        $final_result = [];
        if($demand_result){
            #===统计总条数
            $count_Arr = [];
            foreach($demand_count as $c){
                $count_Arr[] = $c['demand_id'];
            }
            $count = count(array_unique($count_Arr));

            #===统计总条数
            #======统计需求总数/价格 start
            $demand_list_page = [];
//            $staff_card_list_page = [];
            foreach($demand_result as $d){
                $demand_list_page[] = $d['demand_id'];
//                $staff_card_list_page[] = $d['staff_card_id'];
            }
            $res = Db('demand_cards')
                ->field('demand_id,SUM(number_needed) as number_needed,SUM(company_price) as company_price')
                ->where(['demand_id'=>['in',$demand_list_page]])
                ->group('demand_id')
                ->select();
            $demand_count_map = [];
            if($res){
                foreach($res as $re){
                    $demand_count_map[$re['demand_id']]['number_needed'] = $re['number_needed'];
                    $demand_count_map[$re['demand_id']]['company_price'] = $re['company_price'];
                }
            }

            #======统计需求总数/价格 end


            foreach($demand_result as $d){

                if(in_array($d['demand_id'],array_keys($final_result))){
//                    $final_result[$d['demand_id']]['number_needed'] += $d['number_needed'] ;
//                    $final_result[$d['demand_id']]['company_price'] += $d['company_price'] ;
                    $final_result[$d['demand_id']]['paid'] += $d['paid'] ;
                    $final_result[$d['demand_id']]['unpaid'] -= $d['paid'] ;
                    $final_result[$d['demand_id']]['staff_name'] .= ','.$d['staff_name'] ;
                }else{
                    if($d['company_place']){

                        $places = explode(',',$d['company_place']);
                        $arr = [];
                        foreach ($places as $place){
                            if(in_array($place,$area_id_arr)){
                                $arr[$place] = $area_map[$place];
                            }
                        }
                        $d['company_place'] = $arr;
                    }
                    $final_result[$d['demand_id']] = $d;

                    #=====修改统计需求总数/价格
                    if(in_array($d['demand_id'],array_keys($demand_count_map))){
                        $final_result[$d['demand_id']]['number_needed'] = $demand_count_map[$d['demand_id']]['number_needed'];
                        $final_result[$d['demand_id']]['company_price'] = $demand_count_map[$d['demand_id']]['company_price'];
                    }else{
                        $final_result[$d['demand_id']]['number_needed'] = 0;
                        $final_result[$d['demand_id']]['company_price'] = 0;
                    }

                    # 计算未转入金额
                    $final_result[$d['demand_id']]['unpaid'] = $final_result[$d['demand_id']]['company_price']-$final_result[$d['demand_id']]['paid'];


                }

            }
        }

        if($final_result){
            return $this->success_msg($final_result,$count);
        }else{
            return $this->error_msg(1);
        }


    }
    /**
     * 订单配对详情
     * @return \think\response\Json
     */
    public function demandMatchDetail(){
        $request = Request::instance();
        $demand_id = $request->param('demand_id','','trim');

        $where = [
            'match.demand_id'=>$demand_id
        ];
        $result = Db::view('match','match_id,status,paid,unpaid,valid')
            ->view('demand_cards','company_price','match.demand_card_id = demand_cards.id','left')
            ->view('company_demand','company_name,due_time','company_demand.demand_id = demand_cards.demand_id','left')
            ->view('staff_cards','level,profession,register,other_card,talent_price,year','match.staff_card_id = staff_cards.id','left')
            ->view('staff','name,three_category','staff.staff_id = staff_cards.staff_id','left')
            ->view('user','user_name','staff.user_id = user.user_id','left')
            ->where($where)
            ->select();
        $logResult = Db('match_detail_log')->field('match_id,message')->where(['type'=>'staff'])->select();
        $logMap = [];
        if($logResult){
            foreach($logResult as $log){
                $logMap[$log['match_id']][] = $log['message'];
            }
        }
        if($result){
            $logMapKeys = array_keys($logMap);
            foreach ($result as &$re){
                if(in_array($re['match_id'],$logMapKeys)){
                    $re['logs'] = $logMap[$re['match_id']];
                }else{
                    $re['logs'] = [];
                }
                if($re['valid'] == 2){
                    $re['status'] ='驳回';
                }
                unset($re);
            }
            return $this->success_msg($result);
        }else{
            return $this->error_msg(1);
        }

    }


    /**
     * 修改配对详情状态信息
     * @return \think\response\Json
     */
    public function changeStatus(){
        $request = Request::instance();

        $id = $request->param('id','','intval');
        $user_id = $request->param('user_id','','trim');
        $match_id = $request->param('match_id','','trim');
        $status = $request->param('status','','trim');

        $has_detail =  $request->param('has_detail','','trim'); # 是否有详情 没有就不传或传空
        $this_paid = $request->param('paid','','trim'); # 本次支付金额
        $transfer_way = $request->param('payway','','trim'); # 支付方式
        $transfer_message = $request->param('transfer_message','','trim'); # 转账方信息
        $company_account = $request->param('account','','trim'); # 公司账号
        $staff_notice_time = $request->param('notice_time','','trim'); # 人才公告时间
        $demand_over_time = $request->param('over_time','','trim'); # 合同到期时间
        $received_time = $request->param('receive_time','','trim'); # 到账时间

        if(!$match_id){
            return $this->error_msg('参数异常');
        }
        $financial = Config::get('parameter.financial_audit_status');
        # 如果修改的状态不需要财务审核,则直接判断状态修改真实有效,否则需要财务审核
        if(in_array($status,$financial)){
            $saveData = [
                'status'=>$status,
                'valid'=>0
            ];
        }else{
            $saveData = [
                'status'=>$status,
                'valid'=>1
            ];
        }
        # 更新配对表中的状态
        $matchModel = Loader::model('match');
        $result = $matchModel->save($saveData,['match_id'=>$match_id]);

        # 查询字典对应的名称,用于写入日志
        $find_dict_name_list = [];
        $transfer_way?array_push($find_dict_name_list,$transfer_way):'';
        $company_account?array_push($find_dict_name_list,$company_account):'';
        $status?array_push($find_dict_name_list,$status):'';

        # 查询操作者名字
        $user = Db('user')->field('user_name')->where(['user_id'=>$user_id])->find();
        $user_name = $user['user_name'];
        $dict_result = Db('dictionary')->field('dictionary_id,name')->where(['dictionary_id'=>['in',$find_dict_name_list]])->select();
        $dict_map = array_column($dict_result,'name','dictionary_id');
        $logMsg = $user_name.'于'.date('Y-m-d H:i:s',time()).'将状态修改为:'.$dict_map[$status].'。';


        if($has_detail){
            $data = [
                'match_id'  =>  $match_id,
                'this_paid'  =>  $this_paid,
                'transfer_way'  =>  $transfer_way,
                'transfer_message'  =>  $transfer_message,
                'company_account'  =>  $company_account,
                'staff_notice_time'  =>  $staff_notice_time,
                'demand_over_time'  =>  $demand_over_time,
                'received_time'  =>  $received_time,
            ];

            $detailModel = Loader::model('match_detail');
            if(!$id){
                $result = $detailModel->save($data);
                $id = $detailModel->id;
            }else{
                $result = $detailModel->save($data,['id'=>$id]);
            }

            # 将配对信息写入日志表



            $match_detail_remark = Config::get('parameter.match_detail_remark');
            $remark_keys = array_keys($match_detail_remark);
            $data_keys = array_keys($data);
            foreach($data_keys as $dkeys){
                if(in_array($dkeys,$remark_keys) && $data[$dkeys]){
                    if(in_array($data[$dkeys],$find_dict_name_list)){
                        $logMsg .= $match_detail_remark[$dkeys].$dict_map[$data[$dkeys]].'。';
                    }else{
                        $logMsg .= $match_detail_remark[$dkeys].$data[$dkeys].'。';
                    }
                }
            }

        }
        $model = Loader::model('match_log');
        $logData = [
            'match_id'          =>  $match_id,
            'user_id'           =>  $user_id,
            'message'           =>  $logMsg,
            'status'            =>  $status,
            'type'              =>  'staff'
        ];

        $model->save($logData);
        if($result){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }

    }

    /**
     * 获取审核列表
     * @return \think\response\Json
     */
    public function financialAuditList(){
        $request = Request::instance();
        $page = $request->param('page',1,'intval');
        $rows = $request->param('rows',10,'intval');
        $begin_item = ($page-1)*$rows;

        $financial = Config::get('parameter.financial_audit_status');
        $where = [
            'match.status'  =>  ['in',$financial],
            'match.valid'         =>  0
        ];
        $result = Db::view('match','match_id,status,paid,unpaid')
            ->view('match_detail','id,this_paid,transfer_way,transfer_message,company_account,staff_notice_time,demand_over_time,received_time','match.match_id = match_detail.match_id','left')
            ->view('demand_cards','company_price','match.demand_card_id = demand_cards.id','left')
            ->view('company_demand','company_name,due_time','company_demand.demand_id = demand_cards.demand_id','left')
            ->view('staff_cards','level,profession,register,other_card,talent_price,year','match.staff_card_id = staff_cards.id','left')
            ->view('staff','name,three_category','staff.staff_id = staff_cards.staff_id','left')
            ->view('user','user_name','staff.user_id = user.user_id','left')
            ->where($where)
            ->limit($begin_item,$rows)
            ->select();
        $count = Db::view('match','match_id')
            ->view('match_detail','id','match.match_id = match_detail.match_id','left')
            ->where($where)
            ->count();
        if($result){
            return $this->success_msg($result,$count);
        }else{
            return $this->success_msg(3);
        }

    }


    public function finacialAudio(){
        $id = Request::instance()->param('id','','trim'); #
        $matcch_id = Request::instance()->param('matcch_id','','trim'); #
        $status = Request::instance()->param('status','','trim');
        $user_id = Request::instance()->param('user_id','','trim');

        if(!$id){
            return $this->error_msg('参数错误');
        }

        $model = Loader::model('');

        # 查询操作者名字
        $user = Db('user')->field('user_name')->where(['user_id'=>$user_id])->find();
        $user_name = $user['user_name'];
        if($status==1){
            $logMsg = '财务审核员('.$user_name.')的审核结果为:通过';
        }else{
            $logMsg = '财务审核员('.$user_name.')的审核结果为:驳回';
        }
        $model = Loader::model('match_log');

        $data = [
            'match_id'          =>  $id,
            'user_id'           =>  $user_id,
            'message'           =>  $logMsg,
            'type'              =>  'staff'
        ];
        $model->save($data);
    }

}