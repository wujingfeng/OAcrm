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

class Quality extends Common
{

    public function saveQuality(){
        $request = Request::instance();

        $quality_id   =       $request->param('quality_id','','trim'); #需求id
        $user_id    =       $request->param('user_id','','trim'); #录入人id
        $customer_name       =       $request->param('customer_name','','trim');  # 企业名称
        $customer_place =       $request->param('customer_place','','trim');    #资格证所在地
        $link_address =       $request->param('link_address','','trim');    #联系地址
        $linkman       =       $request->param('linkman','','trim');  # 联系人姓名
        $sex        =       $request->param('sex',1,'intval');  # 性别
        $link_number  =       $request->param('link_number','','trim'); # 联系人电话号码
        $landline_phone  =       $request->param('landline_phone','','trim'); # 座机电话号码
        $qq         =       $request->param('qq','','trim');    # qq号
        $customer_type  =   $request->param('customer_type','','trim'); # 客户类型
        $return_visit   =   $request->param('return_visit','','trim');  #是否回访
        $time_visit     =   $request->param('time_visit','','trim');    # 回访时间(如需回访,则时间必填)
        $due_time     =   $request->param('due_time','','trim');    # 合同到期时间
        $taxes         =   $request->param('taxes','0','trim');   # 税金
        $referee=  $request->param('referee','','trim'); # 推荐人
        $type=  $request->param('type',1,'intval'); # 收/出资质(1:收资质,2:出资质)
        $type = $type==2?2:1;
//        $cards          =   $request->param('cards','[{"quality_type":"0a8add783c7ca4eed388ec03877d7a71","level":"0a8add783c7ca4eed388ec03877d7a76","profession":"034535aa5e9824eadfa3eecc233fb73f","number_needed":2,"company_price":10000,"year":1,"is_sc":2,"split":1},{"quality_type":"0a8add783c7ca4eed388ec03877d7a71","level":"0a8add783c7ca4eed388ec03877d7a76","profession":"034535aa5e9824eadfa3eecc233fb73f","number_needed":2,"company_price":10000,"year":1,"is_sc":2,"split":1}]','trim');# 证书(接受json)
        $cards          =   $request->param('cards','','trim');# 证书(接受json)
        $consult_cost   =   $request->param('consult_cost','0','trim'); # 咨询费
        $remark         =   $request->param('remark','','trim');    #备注

        $cards = json_decode($cards,true);

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
            'customer_name'              =>          $customer_name,
            'linkman'              =>          $linkman,
            'sex'               =>          $sex,
            'link_number'         =>          $link_number,
            'landline_phone'         =>          $landline_phone,
//            'bid_type'          =>          $bid_type,
            'customer_type'     =>          $customer_type,
            'return_visit'      =>          $return_visit,
            'time_visit'        =>          $time_visit,
            'consult_cost'        =>          $consult_cost,
            'user_id'           =>          $user_id,
            'type'           =>          $type,
        ];

//        dump($data);
        $result = $this->validate($data,'Quality.saveQuality');
        if($result !== true){
            return $this->error_msg($result);
        }

        $data['qq'] =  $qq;
//        $data['year'] =  $year;
        $data['customer_place'] =  $customer_place;
        $data['link_address'] =  $link_address;
        $data['taxes'] =  $taxes;
        $data['referee'] =  $referee;
        $data['remark'] =  $remark;
        $data['due_time'] =  $due_time;

        if ($type == 2){
            $quality = Loader::model('QualityDemand');
            $qualityCard = Loader::model('quality_demand_card');
            $pk = 'quality_demand_id';
        }else{
            $quality = Loader::model('Quality');
            $qualityCard = Loader::model('quality_card');
            $pk = 'quality_id';
        }


        $card_data = [];
        if(!$quality_id){

            #插入

            $quality_id = $this->md5_str_rand();
            $data[$pk]   =   $quality_id;
            $result = $quality->save($data);


        }else{
            #更新
            $result = $quality->save($data,[$pk=>$quality_id]);

            # 更新证书(直接删除更新更快)
            $where = [
                $pk  =>  $quality_id
            ];
            $qualityCard->where($where)->delete();

        }


        if($cards){
            $temp = [];
            foreach($cards as $card){
                $temp[$pk] = $quality_id;
                $temp['quality_type'] = isset($card['quality_type'])?$card['quality_type']:'';
                $temp['profession'] = isset($card['profession'])?$card['profession']:'';
                $temp['level'] = isset($card['level'])?$card['level']:'';
                $temp['is_sc'] = isset($card['is_sc'])?$card['is_sc']:'2';
                $temp['split'] = isset($card['split'])?$card['split']:'1';
                $temp['number_needed'] = isset($card['number_needed'])?$card['number_needed']:'';
                $temp['company_price'] = isset($card['company_price'])?$card['company_price']:'';
                $temp['customer_price'] = isset($card['customer_price'])?$card['customer_price']:'';
                $temp['year'] = isset($card['year'])?$card['year']:'';
                $card_data[] = $temp;
            }
        }

//        DUMP($card_data);
        if($card_data){
            $cardResult = $qualityCard->saveAll($card_data);
        }

        if($result or $cardResult){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }
    }

    /**
     * 获取收资质的列表
     * @return \think\response\Json
     */
    public function getInQualityList()
    {
        $request = Request::instance();

        $user_id = $request->param('user_id', '', 'trim');
        $customer_name = $request->param('customer_name','','trim');
        $customer_place = $request->param('customer_place','','trim');
        $customer_type = $request->param('customer_type','','trim');
        $year = $request->param('year','','trim');
        $level = $request->param('level','','trim');
        $profession = $request->param('profession','','trim');
        $type = $request->param('type','all','trim');# all/match 所有列表还是用于匹配的列表

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
        $where['quality.user_id'] = ['IN',$users];

        # 如果用于匹配的人员列表  则应该只是签约客户5ebf1d7b31ded660cc201b500db053c9
        # 并且应该排除已匹配人员
        if($type == 'match'){
            $customer_type = '5ebf1d7b31ded660cc201b500db053c9';
//            # 是在匹配中获取人员列表，则应该排除已匹配的证件
            $matchRes = Db('quality_match')->field('quality_card_id')->select();
            if($matchRes){
                $matched_quality_id = array_column($matchRes,'quality_card_id');

                $where['quality_cards.id'] = ['not in',$matched_quality_id];
            }

        }
        if($customer_name){
            $where['quality.customer_name'] = ['LIKE',"%$customer_name%"];
        }
        if($customer_place){
            $where['quality.customer_place'] = $customer_place;
        }
        if($customer_type){
            $where['quality_cards.status'] = $customer_type;
        }
        if($year){
            $where['quality_cards.year'] = $year;
        }

        if($profession){
            $where['quality_cards.profession'] = $profession;
        }
        if($level){
            $where['quality_cards.level'] = $level;
        }

        #====== 编辑where条件 end
        #开始查询需求信息
//        $dict_map = $this->dict_id_map();
//        $dict_map = json_decode($dict_map,true);
//        $field_cfg = Config::get('parameter.quality_field_cfg');
        $quality_result = Db::view('quality','*')
            ->view('quality_cards','*','quality.quality_id = quality_cards.quality_id','left')
            ->view('user','user_name','user.user_id = quality.user_id','left')
            ->where($where)
            ->order('quality_cards.modified desc')
            ->limit($begin_item,$rows)
            ->select();
        $quality_count = Db::view('quality','quality_id')
            ->view('quality_cards','id','quality.quality_id = quality_cards.quality_id','left')
            ->view('user','user_name','user.user_id = quality.user_id','left')
            ->where($where)
            ->count();
        if($quality_result){
            $quality_id_list = [];
            foreach($quality_result as $value){
                $quality_id_list[] = $value['quality_id'];
            }
            $file_id_map = [];
            $file_id_list=[];
            $fileRes = Db('enclosure')->field('type_id,path')->where(['type_id'=>['IN',$quality_id_list]])->select();
            if($fileRes){
                foreach($fileRes as $f){
                    $file_id_list[] = $f['type_id'];
                    $file_id_map[$f['type_id']][] = $f['path'];
                }
            }

            foreach ($quality_result as &$item){
                $item['path'] = '';
                if(in_array($item['quality_id'],$file_id_list)){
                    $item['path'] = $file_id_map[$item['quality_id']];
                }
                if($item['customer_place']){
                    $places = explode(',',$item['customer_place']);
                    $arr = [];
                    foreach ($places as $place){
                        if(in_array($place,$area_id_arr)){
                            $arr[$place] = $area_map[$place];
                        }
                    }
                    $item['customer_place'] = $arr;
//                    $item['card_place'] = trim($str,',');
                }
            }
            unset($item);
            return $this->success_msg($quality_result,$quality_count);
        }else{
            return $this->success_msg(3);
        }


    }

    /**
     *
     * 出资质的列表
     * @return \think\response\Json
     */
    public function getOutQualityList()
    {
        $request = Request::instance();

        $user_id = $request->param('user_id', '', 'trim');
        $customer_name = $request->param('customer_name','','trim');
        $customer_place = $request->param('customer_place','','trim');
        $customer_type = $request->param('customer_type','','trim');
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
        $where['quality_demand.user_id'] = ['IN',$users];
//        $where['quality_demand.type'] = 2;

        if($customer_name){
            $where['quality_demand.customer_name'] = ['LIKE',"%$customer_name%"];
        }
        if($customer_place){
            $where['quality_demand.customer_place'] = $customer_place;
        }
        if($customer_type){
            $where['quality_demand.customer_type'] = $customer_type;
        }
        if($year){
            $where['quality_demand_card.year'] = $year;
        }

        if($profession){
            $where['quality_demand_card.profession'] = $profession;
        }
        if($level){
            $where['quality_demand_card.level'] = $level;
        }

        #====== 编辑where条件 end
        #开始查询需求信息
//        $dict_map = $this->dict_id_map();
//        $dict_map = json_decode($dict_map,true);
//        $field_cfg = Config::get('parameter.quality_field_cfg');
        $quality_result = Db::view('quality_demand','*')
            ->view('quality_demand_card','*','quality_demand.quality_demand_id = quality_demand_card.quality_demand_id','left')
            ->view('user','user_name','user.user_id = quality_demand.user_id','left')
            ->where($where)
            ->order('quality_demand.modified desc')
            ->limit($begin_item,$rows)
            ->select();
        $quality_count = Db::view('quality_demand','quality_demand_id')
            ->view('quality_demand_card','id as sid','quality_demand.quality_demand_id = quality_demand_card.quality_demand_id','inner')
            ->view('user','user_name','user.user_id = quality_demand.user_id','left')
            ->where($where)
            ->select();

        $final_result = [];
        if($quality_result){

            #=====统计总条数 start
            $countArr = [];
            foreach($quality_count as $c){
                $countArr[] = $c['quality_demand_id'];
            }
            $count = count(array_unique($countArr));
            #=====统计总条数 end

            #====将同一个需求下的多个证件合并为一条数据
            $quality_id_list = [];
            $quality_card_id_list = [];
            foreach($quality_result as $value){
                $quality_id_list[] = $value['quality_demand_id'];
                $quality_card_id_list[] = $value['id'];
            }
                #====查询附件  生成附件与需求的映射关系 start
            $file_id_map = [];
            $file_id_list=[];
            $fileRes = Db('enclosure')->field('type_id,path')->where(['type_id'=>['IN',$quality_id_list]])->select();
            if($fileRes){
                foreach($fileRes as $f){
                    $file_id_list[] = $f['type_id'];
                    $file_id_map[$f['type_id']][] = $f['path'];
                }
            }
                #====查询附件  生成附件与需求的映射关系 end

//                #====查询已匹配人员  生成匹配与人员证件映射关系 start
            $match_id_map = [];
            $match_id_list = [];
            $matchRes = Db::view('quality_match','quality_match_id,quality_demand_id,quality_demand_id')
                ->view('quality_demand_card','id as quality_demand_card_id','quality_match.quality_demand_card_id = quality_demand_card.id','left')
                ->view('quality_cards','id as quality_card_id,*','quality_match.quality_card_id = quality_cards.id','left')
                ->view('quality','customer_name','quality_cards.quality_id = quality.quality_id','left')
                ->where(['quality_demand_card_id'=>['in',$quality_card_id_list]])
                ->select();
            if($matchRes){
                foreach($matchRes as $m){
                    $match_id_list[] = $m['quality_demand_card_id'];
                    $match_id_map[$m['quality_demand_card_id']][] = $m;
                }
            }
//                #====查询已匹配人员  生成匹配与人员证件映射关系 end

            foreach ($quality_result as &$item){

                $item['path'] = '';

                # 追加附件数据
                if(in_array($item['quality_demand_id'],$file_id_list)){
                    $item['path'] = $file_id_map[$item['quality_demand_id']];
                }
                # 追加已匹配人员数据
                if(in_array($item['id'],$match_id_list)){
                    $item['match'] = $match_id_map[$item['id']];
                }else{
                    $item['match'] = '';
                }

                if($item['customer_place']){

                    $places = explode(',',$item['customer_place']);
                    $arr = [];
                    foreach ($places as $place){
                        if(in_array($place,$area_id_arr)){
                            $arr[$place] = $area_map[$place];
                        }
                    }
                    $item['customer_place'] = $arr;
                }

                $final_result[$item['quality_demand_id']][] = $item;
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
        $quality_demand_id        = $request->param('quality_demand_id','','trim'); # 需求id
        $user_id        = $request->param('user_id','','trim'); # 录入人id
        $params         = $request->param('argvs/a',[]);
//        dump($params);
//        exit();
//        $params = [["quality_id"=>"8556daa6204c14f3d1de9e21c6cc98d8","quality_card_id"=>"16","quality_demand_card_id"=>"20","status"=>''],["quality_id"=>"8556daa6204c14f3d1de9e21c6cc98d8","quality_card_id"=>"17","quality_demand_card_id"=>"21","status"=>'']];
        if(!$quality_demand_id|| !$params){
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
                'quality_demand_id'     =>  $quality_demand_id,
                'quality_id'     =>  $param['quality_id'],
                'quality_card_id'     =>  $param['quality_card_id'],
                'quality_demand_card_id'     =>  $param['quality_demand_card_id'],
//                'status'     =>  '',
                'quality_match_id'  => $match_id
            ];
            $result = Db('quality_match')->insert($data,true); # 更新插入
        }



        if($result){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }

    }

    private function checkMatchFinish($quality_demand_id=''){
        if(!$quality_demand_id){
            return $this->error_msg('参数错误');
        }

        # 获取需要的人才总数
        $needed_result = Db('quality_demand_card')
            ->field('sum(needed_number) as need')
            ->where(['quality_demand_id'=>$quality_demand_id])
            ->group('quality_demand_id')
            ->select();
        if($needed_result){
            $needed_number = $needed_result['need'];
        }else{
            $needed_number = 0;
        }

        # 获取已配对总数
        $matched_number = Db('quality_match')
            ->where(['quality_demand_id'=>$quality_demand_id])
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
        $match_id = $request->param('quality_match_id','','trim');

        if(!$match_id){
            return $this->error_msg('参数错误');
        }
        $where = [
            'quality_match_id' => $match_id
        ];

        $result = Db('quality_match')->where($where)->delete();

        if($result){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }

    }


    public function qualityDemandMatchList(){

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
        $where['quality_demand.user_id'] = ['IN',$users];

        #====== 编辑where条件 end
        #=====开始查询需求信息
        $demand_result = Db::view('quality_match','quality_match_id,quality_demand_id,quality_id,quality_card_id,paid,unpaid,quality_id')
            ->view('quality_demand_card','number_needed,company_price','quality_match.quality_demand_card_id = quality_demand_card.id','left')
            ->view('quality_demand','customer_name as demand_customer_name,customer_place,user_id,quality_status,created,due_time','quality_demand.quality_demand_id = quality_demand_card.quality_demand_id','left')
            ->view('quality','customer_name','quality.quality_id = quality_match.quality_id','left')
            ->view('user','user_name','user.user_id = quality_demand.user_id','left')
            ->where($where)
            ->limit($begin_item,$rows)
            ->select();
        $demand_count = Db::view('quality_match','quality_match_id')
            ->view('quality_demand_card','id','quality_match.quality_demand_card_id = quality_demand_card.id','left')
            ->view('quality_demand','quality_demand_id','quality_demand.quality_demand_id = quality_demand_card.quality_demand_id','left')
            ->view('user','user_name','user.user_id = quality_demand.user_id','left')
            ->where($where)
            ->select();

        $final_result = [];
        if($demand_result){
            #===统计总条数
            $count_Arr = [];
            foreach($demand_count as $c){
                $count_Arr[] = $c['quality_demand_id'];
            }
            $count = count(array_unique($count_Arr));

            #===统计总条数
            #======统计需求总数/价格 start
            $demand_list_page = [];
//            $staff_card_list_page = [];
            foreach($demand_result as $d){
                $demand_list_page[] = $d['quality_demand_id'];
//                $staff_card_list_page[] = $d['quality_card_id'];
            }
            $res = Db('quality_demand_card')
                ->field('quality_demand_id,SUM(number_needed) as number_needed,SUM(company_price) as company_price')
                ->where(['quality_demand_id'=>['in',$demand_list_page]])
                ->group('quality_demand_id')
                ->select();
            $demand_count_map = [];
            if($res){
                foreach($res as $re){
                    $demand_count_map[$re['quality_demand_id']]['number_needed'] = $re['number_needed'];
                    $demand_count_map[$re['quality_demand_id']]['company_price'] = $re['company_price'];
                }
            }

            #======统计需求总数/价格 end


            foreach($demand_result as $d){

                if(in_array($d['quality_demand_id'],array_keys($final_result))){
//                    $final_result[$d['quality_demand_id']]['number_needed'] += $d['number_needed'] ;
//                    $final_result[$d['quality_demand_id']]['company_price'] += $d['company_price'] ;
                    $final_result[$d['quality_demand_id']]['paid'] += $d['paid'] ;
                    $final_result[$d['quality_demand_id']]['unpaid'] -= $d['paid'] ;
                    $final_result[$d['quality_demand_id']]['customer_name'] .= ','.$d['customer_name'] ;
                }else{
                    if($d['customer_place']){

                        $places = explode(',',$d['customer_place']);
                        $arr = [];
                        foreach ($places as $place){
                            if(in_array($place,$area_id_arr)){
                                $arr[$place] = $area_map[$place];
                            }
                        }
                        $d['customer_place'] = $arr;
                    }
                    $final_result[$d['quality_demand_id']] = $d;

                    #=====修改统计需求总数/价格
                    if(in_array($d['quality_demand_id'],array_keys($demand_count_map))){
                        $final_result[$d['quality_demand_id']]['number_needed'] = $demand_count_map[$d['quality_demand_id']]['number_needed'];
                        $final_result[$d['quality_demand_id']]['company_price'] = $demand_count_map[$d['quality_demand_id']]['company_price'];
                    }else{
                        $final_result[$d['quality_demand_id']]['number_needed'] = 0;
                        $final_result[$d['quality_demand_id']]['company_price'] = 0;
                    }

                    # 计算未转入金额
                    $final_result[$d['quality_demand_id']]['unpaid'] = $final_result[$d['quality_demand_id']]['company_price']-$final_result[$d['quality_demand_id']]['paid'];


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
    public function qualityDemandMatchDetail(){
        $request = Request::instance();
        $quality_demand_id = $request->param('quality_demand_id','','trim');

        $where = [
            'quality_match.quality_demand_id'=>$quality_demand_id
        ];
        $result = Db::view('quality_match','quality_match_id,user_id,status,paid,unpaid,valid')
            ->view('quality_demand_card','company_price','quality_match.quality_demand_card_id = quality_demand_card.id','left')
            ->view('quality_demand','customer_name as demand_customer_name,due_time','quality_demand.quality_demand_id = quality_demand_card.quality_demand_id','left')
            ->view('quality_cards','level,profession,year,customer_price','quality_match.quality_card_id = quality_cards.id','left')
            ->view('quality','customer_name','quality.quality_id = quality_cards.quality_id','left')
            ->view('user','user_name','quality.user_id = user.user_id','left')
            ->where($where)
            ->select();

        if($result){
            $quality_match_id_list = array_column($result,'quality_match_id');

            # 生成日志
            $logMap = $this->generate_log($quality_match_id_list);
            $logMapKeys = array_keys($logMap);
            foreach ($result as &$re){
                if(in_array($re['quality_match_id'],$logMapKeys)){
                    $re['logs'] = $logMap[$re['quality_match_id']];
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
     * 生成日志
     * @param $quality_match_id_list
     * @return array
     */
    private function generate_log($quality_match_id_list){
        # 加field的目的在于指定字段的顺序
        $detailResult = Db::view('quality_match_detail','status,this_paid,transfer_way,company_account,staff_notice_time,received_time,audio_user_id,audio_date,valid,transfer_message,demand_over_time,id,quality_match_id,modified')
            ->view('quality_match','user_id','quality_match_detail.quality_match_id = quality_match.quality_match_id','left')
            ->where(['quality_match_detail.quality_match_id'=>['in',$quality_match_id_list]])
            ->order('modified desc')
            ->select();
        $logMap = [];
        # 生成配对信息日志
        $match_detail_remark = Config::get('parameter.match_detail_remark');
        $remark_keys = array_keys($match_detail_remark);
        if($detailResult){

            # ====查询字典对应的名称 start
            $find_dict_name_list = array_filter(array_unique(array_merge(array_column($detailResult,'transfer_way'),array_column($detailResult,'company_account'),array_column($detailResult,'status'))));
            $dict_result = Db('dictionary')->field('dictionary_id,name')->where(['dictionary_id'=>['in',$find_dict_name_list]])->select();
            $dict_map = array_column($dict_result,'name','dictionary_id');
            # === end

            # ==== 查询用户id对应的用户名 start
            $user_id_list = array_filter(array_unique(array_merge(array_column($detailResult,'user_id'),array_column($detailResult,'audio_user_id'))));
            $userResult = Db('user')->field('user_id,user_name')->where(['user_id'=>['in',$user_id_list]])->select();
            $user_map = array_column($userResult,'user_name','user_id');
            # ===end
            foreach($detailResult as $detail){
                $logMsg = $user_map[$detail['user_id']].'于'.$detail['modified'].'修改';
//                $logMsg = '';
                foreach($detail as $key=>$d){
                    if($key == 'valid' ){
                        if($d == 2){
                            $d = '驳回';
                        }elseif($d == 1){
                            $d = '通过';
                        }else{
                            $d = '审核中';
                        }
                    }
                    if(in_array($key,$remark_keys) && $d){
                        # 将字典值,转换为对应的名称

                        if(in_array($d,$find_dict_name_list)){
                            $logMsg .= $match_detail_remark[$key].$dict_map[$d].'。';
                        }elseif(in_array($d,$user_id_list)){
                            $logMsg .= $match_detail_remark[$key].$user_map[$d].'。';
                        }else{
                            $logMsg .= $match_detail_remark[$key].$d.'。';
                        }
                    }
                }
                $logMap[$detail['quality_match_id']][] = $logMsg;

            }
        }

        return $logMap;
    }

    /**
     * 修改配对详情状态信息
     * @return \think\response\Json
     */
    public function changeStatus(){
        $request = Request::instance();

        $id = $request->param('id','','intval');
        $user_id = $request->param('user_id','','trim');
        $match_id = $request->param('quality_match_id','','trim');
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
            $valid = 0;
//            $saveData = [
//                'status'=>$status,
//                'valid'=>$valid
//            ];
        }else{
            $valid = 1;
            $saveData = [
                'status'=>$status,
                'valid'=>$valid
            ];
            # 更新配对表中的状态
            $matchModel = Loader::model('quality_match');
            $result = $matchModel->save($saveData,['quality_match_id'=>$match_id]);
        }


//        # 查询字典对应的名称,用于写入日志
//        $find_dict_name_list = [];
//        $transfer_way?array_push($find_dict_name_list,$transfer_way):'';
//        $company_account?array_push($find_dict_name_list,$company_account):'';
//        $status?array_push($find_dict_name_list,$status):'';
////
////        # 查询操作者名字
//        $user = Db('user')->field('user_name')->where(['user_id'=>$user_id])->find();
//        $user_name = $user['user_name'];
//        $dict_result = Db('dictionary')->field('dictionary_id,name')->where(['dictionary_id'=>['in',$find_dict_name_list]])->select();
//        $dict_map = array_column($dict_result,'name','dictionary_id');
//        $logMsg = $user_name.'于'.date('Y-m-d H:i:s',time()).'将状态修改为:'.$dict_map[$status].'。';


        if($has_detail){
            $data = [
                'quality_match_id'  =>  $match_id,
                'this_paid'  =>  $this_paid,
                'transfer_way'  =>  $transfer_way,
                'transfer_message'  =>  $transfer_message,
                'company_account'  =>  $company_account,
                'staff_notice_time'  =>  $staff_notice_time,
                'demand_over_time'  =>  $demand_over_time,
                'received_time'  =>  $received_time,
                'valid'  =>  $valid,
                'status'  =>  $status,
            ];

            $detailModel = Loader::model('quality_match_detail');
            if(!$id){
                $result = $detailModel->save($data);
                $id = $detailModel->id;
            }else{
                $result = $detailModel->save($data,['id'=>$id]);
            }

//            # 将配对信息写入日志表
//            $match_detail_remark = Config::get('parameter.match_detail_remark');
//            $remark_keys = array_keys($match_detail_remark);
//            $data_keys = array_keys($data);
//            foreach($data_keys as $dkeys){
//                if(in_array($dkeys,$remark_keys) && $data[$dkeys]){
//                    if(in_array($data[$dkeys],$find_dict_name_list)){
//                        $logMsg .= $match_detail_remark[$dkeys].$dict_map[$data[$dkeys]].'。';
//                    }else{
//                        $logMsg .= $match_detail_remark[$dkeys].$data[$dkeys].'。';
//                    }
//                }
//            }

        }
//        $model = Loader::model('quality_match_log');
//        $logData = [
//            'quality_match_id'          =>  $match_id,
//            'user_id'           =>  $user_id,
//            'message'           =>  $logMsg,
//            'type'              =>  'quality'
//        ];
//
//        $model->save($logData);
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
            'quality_match_detail.status'  =>  ['in',$financial],
            'quality_match_detail.valid'         =>  0
        ];
        $result = Db::view('quality_match','quality_match_id,status,paid,unpaid')
            ->view('quality_match_detail','id,this_paid,transfer_way,transfer_message,company_account,staff_notice_time,demand_over_time,received_time','quality_match.quality_match_id = quality_match_detail.quality_match_id','left')
            ->view('quality_demand_card','company_price','quality_match.quality_demand_card_id = quality_demand_card.id','left')
            ->view('quality_demand','customer_name as demand_customer_name,due_time','quality_demand.quality_demand_id = quality_demand_card.quality_demand_id','left')
            ->view('quality_cards','level,profession,customer_price,year','quality_match.quality_card_id = quality_cards.id','left')
            ->view('quality','customer_name','quality.quality_id = quality_cards.quality_id','left')
            ->view('user','user_name','quality.user_id = user.user_id','left')
            ->where($where)
            ->limit($begin_item,$rows)
            ->select();
        $count = Db::view('quality_match','quality_match_id')
            ->view('quality_match_detail','id','quality_match.quality_match_id = quality_match_detail.quality_match_id','left')
            ->where($where)
            ->count();
        if($result){
            return $this->success_msg($result,$count);
        }else{
            return $this->success_msg(3);
        }


    }

    /**
     * 财务审核接口
     * @return \think\response\Json
     */
    public function finacialAudio(){
        $id = Request::instance()->param('id','','trim'); #
        $quality_match_id = Request::instance()->param('quality_match_id','','trim'); #
        $valid = Request::instance()->param('valid',1,'trim');
        $user_id = Request::instance()->param('user_id','','trim');
        $valid = $valid == 2?2:1;
        if(!$id || !$quality_match_id || !$user_id){
            return $this->error_msg('参数错误');
        }

        # 审核通过时,修改证件状态和有效值
        # 不通过时,只需要保存修改记录  不需要修改证件状态和有效值
        if ($valid == 1){
            $status = Db('quality_match_detail')->field('status')->where(['id'=>$id])->find();
            if($status){
                $status = $status['status'];
            }else{
                return $this->error_msg('参数错误');
            }
            $saveData = [
                'status'=>$status,
                'valid'=>$valid
            ];
            # 更新配对表中的状态
            $matchModel = Loader::model('quality_match');
            $result = $matchModel->save($saveData,['quality_match_id'=>$quality_match_id]);

            $detailData = [
                'audio_user_id' =>  $user_id,
                'audio_date'    =>  date('Y-m-d H:i:s',time()),
                'valid'         =>  $valid
            ];
            # 更新配对详情表信息
            $detailModel = Loader::model('quality_match_detail');
            $result_log = $detailModel->save($detailData,['id'=>$id]);
        }else{
            $result = true;
            $detailData = [
                'audio_user_id' =>  $user_id,
                'audio_date'    =>  date('Y-m-d H:i:s',time()),
                'valid'         =>  $valid
            ];
            # 更新配对详情表信息
            $detailModel = Loader::model('quality_match_detail');
            $result_log = $detailModel->save($detailData,['id'=>$id]);
        }

        if($result && $result_log){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }


    }

}