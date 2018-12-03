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
        $talent_price   =   $request->param('talent_price','0','trim'); # 人才要价
        $year           =   $request->param('year','0','trim'); # 年限
        $return_visit   =   $request->param('return_visit','','trim');  #是否回访
        $time_visit     =   $request->param('time_visit','','trim');    # 回访时间(如需回访,则时间必填)
        $prepay         =   $request->param('prepay','0','trim');   # 预付金
        $social_security=   $request->param('social_security','无','trim');  # 社保
        $other_card     =   $request->param('other_card/a',[]); # 其他证件(数组)
        $card_status    =   $request->param('card_status','1','trim');  # 证书状态
        $talent_type    =   $request->param('talent_type','','trim');   # 人才类型
        $cards          =   $request->param('cards/a',['职称证书-高级,建设设计,初始','职称证书-高级,公路设计,转注']);# 证书(接受数组(级别,专业,转注---逗号拼接))
        $consult_cost   =   $request->param('consult_cost','0','trim'); # 咨询费
        $finish_education=  $request->param('finish_education',0,'intval'); # 是否结束继续教育
        $id_card        =   $request->param('id_card','','trim');   # 身份证号
        $remark         =   $request->param('remark','','trim');    #备注

//        dump($cards);
//        exit();
        $data = [
            'name'              =>          $name,
            'sex'               =>          $sex,
            'three_category'    =>          $three,
            'cellphone'         =>          $cellphone,
            'bid_type'          =>          $bid_type,
            'customer_type'     =>          $customer_type,
            'talent_price'      =>          $talent_price,
            'year'              =>          $year,
            'return_visit'      =>          $return_visit,
            'time_visit'        =>          $time_visit,
            'prepay'            =>          $prepay,
            'social_security'   =>          $social_security,
            'other_card'        =>          $other_card,
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
        if(!$staff_id){

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

            # 更新证书(直接删除更新更快)
            $where = [
                'staff_id'  =>  $staff_id
            ];
            $staff->where($where)->delete();

        }

        if($cards){
            $temp = [];
            foreach($cards as $card){
                $cardArr = explode(',',$card);
                $temp['staff_id'] = $staff_id;
                $temp['level'] = $cardArr[0];
                $temp['profession'] = $cardArr[1];
                $temp['register'] = $cardArr[2];
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
            $staffCard->saveAll($card_data);
        }

        if($result){
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

        $user_id = $request->param('user_id','f9e0881dd590b264c7b8b37ac0846fb7','trim');
        $page = $request->param('page',1,'intval');
        $rows = $request->param('rows',10,'intval');
        $begin_item = ($page-1)*$rows;

        # 获取所在角色
        $getRole = Db::view('user','user_id')
            ->view('organization','organization_id','user.organization_id = organization.organization_id','inner')
            ->where(['user.user_id'=>$user_id])->find();
        $organization_id = '';
        if($getRole){
            $organization_id = $getRole['organization_id'];
        }

        if($organization_id){
            $where = [
                'organization_id'   =>  $organization_id,
                'valid'     =>  1
            ];
            $whereOr = [
                'parent_id' =>  $organization_id,
            ];
            # 获取所有子节点
            # 必须要按照[创建时间]排[正序],这样才能保证子节点不会遗漏
            $organization = Db('organization')->where($where)->whereOr($whereOr)->order('created asc')->fetchSql(false)->select();
            $childs = $this->getAllChild($organization,$organization_id,'organization_id','parent_id');
            $childs = json_decode($childs);
            $childs = array_diff($childs,[$organization_id]);

            # 获取所有子节点对应的用户

            $where = [
                'user_id'=>$user_id
            ];
            $whereOr = [
                'organization_id'   =>  ['in',$childs]
            ];
            $users = Db('user')->field('user_id')->where($where)->whereOr($whereOr)->select();
            $users = array_column($users,'user_id');


        }else{
            $users[] = $user_id;
        }
        #开始查询人才信息

//        $dict_map = $this->dict_id_map();
//        $dict_map = json_decode($dict_map,true);
//        $field_cfg = Config::get('parameter.staff_field_cfg');
        $staff_result = Db::view('staff','*')
            ->view('staff_cards','*','staff.staff_id = staff_cards.staff_id','left')
            ->where([
                'staff.user_id' =>  ['in',$users]
            ])
            ->limit($begin_item,$rows)
            ->select();
        $staff_count = Db::view('staff','id')
            ->view('staff_cards','id as sid','staff.staff_id = staff_cards.staff_id','left')
            ->where([
                'staff.user_id' =>  ['in',$users]
            ])
            ->count();
        if($staff_result){
//            $final_result = [];
//            foreach($staff_result as &$item){
//                foreach ($field_cfg as $cfg){
//                    if(array_key_exists($cfg,$item) && $item[$cfg]){
//                        $item[$cfg] = $dict_map[$item[$cfg]];
////                        $final_result[] = $item;
//                    }
//                }
//            }

            return $this->success_msg($staff_result,$staff_count);
        }else{
            return $this->success_msg(3);
        }

    }

}