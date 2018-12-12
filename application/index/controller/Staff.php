<?php
/**
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/10/8
 * Time: 14:29
 */

namespace app\index\controller;


use think\Config;
use think\Db;
use think\Loader;
use think\Request;

class Staff extends Common
{
    /**
     * 保存/更新人员信息
     * @return \think\response\Json
     */
    public function saveStaff(){
        $request = Request::instance();

        $id   =       $request->param('id','','trim'); #人员id
        $staff_id   =       $request->param('staff_id','','trim'); #人员id
        $user_id    =       $request->param('user_id','','trim'); #录入人id
        $name       =       $request->param('name','','trim');  # 人员姓名
        $sex        =       $request->param('sex',1,'intval');  # 性别
        $card_place =       $request->param('card_place','','trim');    #资格证所在地
        $three      =       $request->param('three_category','','trim');    # 三类人员
        $cellphone  =       $request->param('cellphone','','trim'); # 电话号码
        $qq         =       $request->param('qq','','trim');    # qq号
        $education  =       $request->param('education','','trim'); #学历
        $duty       =       $request->param('duty','','trim');# 职称
        $bid_type   =       $request->param('bid_type','','trim');  #招标出场类型
        $customer_type  =   $request->param('customer_type','','trim'); # 客户类型
//        $talent_price   =   $request->param('talent_price','0','trim'); # 人才要价
//        $year           =   $request->param('year','0','trim'); # 年限
        $return_visit   =   $request->param('return_visit','','trim');  #是否回访
        $time_visit     =   $request->param('time_visit','','trim');    # 回访时间(如需回访,则时间必填)
        $prepay         =   $request->param('prepay','0','trim');   # 预付金
        $social_security=   $request->param('social_security','无','trim');  # 社保
        $other_card     =   $request->param('other_card/a',[]); # 其他证件(数组)
        $card_status    =   $request->param('card_status','1','trim');  # 证书状态
        $talent_type    =   $request->param('talent_type','','trim');   # 人才类型
//        $cards          =   $request->param('cards/a','[{"level":"22f29e6e68fef3937b37a1abda5aa46b","profession":"db00cddf8ddea531fa33126bfbaea799","register":"","talent_price":"10000","year":"1"}]');# 证书(接受数组(级别,专业,转注---逗号拼接))
        $cards          =   $request->param('cards','','trim');# 证书(接受json)
        $consult_cost   =   $request->param('consult_cost','0','trim'); # 咨询费
        $finish_education=  $request->param('finish_education',0,'intval'); # 是否结束继续教育
        $id_card        =   $request->param('id_card','','trim');   # 身份证号
        $remark         =   $request->param('remark','','trim');    #备注

        $cards = json_decode($cards,true);
//        dump($cards);
//        exit();
        $data = [
            'name'              =>          $name,
            'sex'               =>          $sex,
            'three_category'    =>          $three,
            'cellphone'         =>          $cellphone,
            'bid_type'          =>          $bid_type,
            'customer_type'     =>          $customer_type,
            'return_visit'      =>          $return_visit,
            'time_visit'        =>          $time_visit,
            'prepay'            =>          $prepay,
            'social_security'   =>          $social_security,
//            'other_card'        =>          $other_card,
            'card_status'       =>          $card_status,
            'talent_type'       =>          $talent_type,
            'consult_cost'      =>          $consult_cost,
            'id_card'           =>          $id_card,
            'user_id'           =>          $user_id,
        ];

//        dump($data);
        $result = $this->validate($data,'Staff.saveStaff');
        if($result !== true){
            return $this->error_msg($result);
        }
        unset($data['other_card']);

        $data['qq'] =  $qq;
        $data['card_place'] =  $card_place;
        $data['education'] =  $education;
        $data['duty'] =  $duty;
        $data['finish_education'] =  $finish_education;
        $data['remark'] =  $remark;

        $staff = Loader::model('staff');
        $staffCard = Loader::model('staff_card');

        $card_data = [];
        if(!$id){

            #插入

            $staff_id = $this->md5_str_rand();
            $data['staff_id']   =   $staff_id;
            $result = $staff->save($data);
//            $isExists = Db('staff')->field('staff_id')->where(['id_card'=>$id_card])->find();
//
//            if(!$isExists){
//                $staff_id = $this->md5_str_rand();
//                $data['staff_id']   =   $staff_id;
//                $result = $staff->save($data);
//            }else{
//                return $this->error_msg(9);
//            }


        }else{
            #更新
            $result = $staff->save($data,['staff_id'=>$staff_id]);

//            # 更新证书(直接删除更新更快)
//            $where = [
//                'staff_id'  =>  $staff_id
//            ];
//            $staffCard->where($where)->delete();

        }

        if($cards){
            $temp = [];
            foreach($cards as $card){
                $temp['staff_id'] = $staff_id;
                $temp['level'] = isset($card['level'])?$card['level']:'';
                $temp['profession'] = isset($card['profession'])?$card['profession']:'';
                $temp['register'] = isset($card['register'])?$card['register']:'';
//                $temp['education'] = isset($card['education'])?$card['education']:'';
//                $temp['duty'] = isset($card['duty'])?$card['duty']:'';
//                $temp['bid_type'] = isset($card['bid_type'])?$card['bid_type']:'';
//                $temp['number_needed'] = isset($card['number_needed'])?$card['number_needed']:'';
                $temp['talent_price'] = isset($card['talent_price'])?$card['talent_price']:'';
                $temp['year'] = isset($card['year'])?$card['year']:'';


//                $cardArr = explode(',',$card);
//                $temp['staff_id'] = $staff_id;
//                $temp['level'] = $cardArr[0];
//                $temp['profession'] = $cardArr[1];
//                $temp['register'] = $cardArr[2];
                $card_data[] = $temp;
            }
        }

        if(isset($other_card[0]) && $other_card[0]){
            $temp = [];
            foreach($other_card as $item){
                $temp['staff_id'] =   $staff_id;
                $temp['other_card'] =   $item;
                $card_data[] = $temp;
            }
        }

        if($card_data){
            if(!$id){
                # 证件没有则添加
                $card_result = $staffCard->saveAll($card_data);
            }else{
                # 有则更新
                $card_result = $staffCard->save($card_data[0],['id'=>$id]);
            }
        }


        if($result or $card_result){
            return $this->success_msg(1);
        }else{
            return $this->error_msg(2);
        }



    }


    /**
     * 获取人才列表
     * @return \think\response\Json
     */
    public function getStaffList(){
        $request = Request::instance();

        $user_id = $request->param('user_id','','trim');
        $type = $request->param('type','all','trim');

        $staff_name = $request->param('staff_name','','trim');
        $card_place = $request->param('card_place','','trim');
        $card_status = $request->param('card_status','','trim');
        $three_category = $request->param('three_category','','trim');
        $bid_type = $request->param('bid_type','','trim');
        $customer_type = $request->param('customer_type','','trim');
        $duty = $request->param('duty','','trim');
        $year = $request->param('year','','trim');
        $level = $request->param('level','','trim');
        $profession = $request->param('profession','','trim');

        $page = $request->param('page',1,'intval');
        $rows = $request->param('rows',10,'intval');
        $begin_item = ($page-1)*$rows;

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
//        dump($users);
//        exit();
        #开始查询人才信息
        # =====编辑where条件 start
        $where = [
            'staff.user_id' =>  ['in',$users],
//            'staff_cards.status'=>1
        ];

            # 如果用于匹配的人员列表  则应该只是签约客户5ebf1d7b31ded660cc201b500db053c9
            # 并且应该排除已匹配人员
        if($type == 'match'){
            $customer_type = '5ebf1d7b31ded660cc201b500db053c9';
//            # 是在匹配中获取人员列表，则应该排除已匹配的证件
            $matchRes = Db('match')->field('staff_card_id')->select();
            if($matchRes){
                $matched_staff_id = array_column($matchRes,'staff_card_id');

                $where['staff_cards.id'] = ['not in',$matched_staff_id];
            }



        }

        if($staff_name){
            $where['staff.name'] = ['LIKE',"%$staff_name%"];
        }
        if($card_place){
            $where['staff.card_place'] = $card_place;
        }
        if($card_status!=''){
            $where['staff.card_status'] = $card_status;
        }
        if($three_category){
            $where['staff.three_category'] = $three_category;
        }
        if($bid_type){
            $where['staff.bid_type'] = $bid_type;
        }
        if($customer_type){
            $where['staff_cards.status'] = $customer_type;
        }
        if($duty){
            $where['staff.duty'] = $duty;
        }
        if($year){
            $where['staff_cards.year'] = $year;
        }
        if($profession){
            $where['staff_cards.profession'] = $profession;
        }
        if($level){
            $where['staff_cards.level'] = $level;
        }

        # =====编辑where条件 end
//        $dict_map = $this->dict_id_map();
//        $dict_map = json_decode($dict_map,true);
//        $field_cfg = Config::get('parameter.staff_field_cfg');
        $staff_result = Db::view('staff','*')
            ->view('staff_cards','*','staff.staff_id = staff_cards.staff_id','left')
            ->view('user','user_name','user.user_id = staff.user_id','left')
            ->where($where)
            ->order('staff_cards.modified desc')
            ->limit($begin_item,$rows)
            ->select();
        $staff_count = Db::view('staff','staff_id')
            ->view('staff_cards','id','staff.staff_id = staff_cards.staff_id','left')
            ->view('user','user_name','user.user_id = staff.user_id','left')
            ->where($where)
            ->count();
        if($staff_result){
            $staff_id_list = [];
            foreach($staff_result as $value){
                $staff_id_list[] = $value['staff_id'];
            }
            $file_id_map = [];
            $file_id_list=[];
            $fileRes = Db('enclosure')->field('type_id,path')->where(['type_id'=>['IN',$staff_id_list]])->select();
            if($fileRes){
                foreach($fileRes as $f){
                    $file_id_list[] = $f['type_id'];
                    $file_id_map[$f['type_id']][] = $f['path'];
                }
            }

            foreach ($staff_result as &$item){
                $item['path'] = '';
                if(in_array($item['staff_id'],$file_id_list)){
                    $item['path'] = $file_id_map[$item['staff_id']];
                }
                if($item['card_place']){
                    $places = explode(',',$item['card_place']);
                    $arr = [];
                    foreach ($places as $place){
                        if(in_array($place,$area_id_arr)){
                            $arr[$place] = $area_map[$place];
                        }
                    }
                    $item['card_place'] = $arr;
//                    $item['card_place'] = trim($str,',');
                }
            }
            unset($item);
            return $this->success_msg($staff_result,$staff_count);
        }else{
            return $this->success_msg(3);
        }

    }



}