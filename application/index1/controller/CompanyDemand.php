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
        $other_card     =   $request->param('other_card/a',[]); # 其他证件(数组)
        $taxes         =   $request->param('taxes','0','trim');   # 税金
        $referee=  $request->param('referee','','intval'); # 推荐人
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
        $data['quality'] =  $quality;
        $data['link_address'] =  $link_address;
        $data['taxes'] =  $taxes;
        $data['referee'] =  $referee;
        $data['remark'] =  $remark;

        $demand = Loader::model('CompanyDemand');
        $demandCard = Loader::model('DemandCards');

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
            $demand->where($where)->delete();

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
        if($card_data){
            $demandCard->saveAll($card_data);
        }

        if($result){
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

        $user_id = $request->param('user_id', 'f9e0881dd590b264c7b8b37ac0846fb7', 'trim');
        $page = $request->param('page', 1, 'intval');
        $rows = $request->param('rows', 10, 'intval');
        $begin_item = ($page - 1) * $rows;

        # 获取所在角色
        $getRole = Db::view('user', 'user_id')
            ->view('organization', 'organization_id', 'user.organization_id = organization.organization_id', 'inner')
            ->where(['user.user_id' => $user_id])->find();
        $organization_id = '';
        if ($getRole) {
            $organization_id = $getRole['organization_id'];
        }

        if ($organization_id) {
            $where = [
                'organization_id' => $organization_id,
                'valid' => 1
            ];
            $whereOr = [
                'parent_id' => $organization_id,
            ];
            # 获取所有子节点
            # 必须要按照[创建时间]排[正序],这样才能保证子节点不会遗漏
            $organization = Db('organization')->where($where)->whereOr($whereOr)->order('created asc')->fetchSql(false)->select();
            $childs = $this->getAllChild($organization, $organization_id, 'organization_id', 'parent_id');
            $childs = json_decode($childs);
            $childs = array_diff($childs, [$organization_id]);

            # 获取所有子节点对应的用户

            $where = [
                'user_id' => $user_id
            ];
            $whereOr = [
                'organization_id' => ['in', $childs]
            ];
            $users = Db('user')->field('user_id')->where($where)->whereOr($whereOr)->select();
            $users = array_column($users, 'user_id');


        } else {
            $users[] = $user_id;
        }

        #开始查询需求信息
//        $dict_map = $this->dict_id_map();
//        $dict_map = json_decode($dict_map,true);
//        $field_cfg = Config::get('parameter.demand_field_cfg');
        $demand_result = Db::view('company_demand','*')
            ->view('demand_cards','*','company_demand.demand_id = demand_cards.demand_id','left')
            ->where([
                'company_demand.user_id' =>  ['in',$users]
            ])
            ->limit($begin_item,$rows)
            ->select();
        $demand_count = Db::view('company_demand','id')
            ->view('demand_cards','id as sid','company_demand.demand_id = demand_cards.demand_id','left')
            ->where([
                'company_demand.user_id' =>  ['in',$users]
            ])
            ->count();

        if($demand_result){
//            $final_result = [];
//            foreach($demand_result as &$item){
//                foreach ($field_cfg as $cfg){
//                    if(array_key_exists($cfg,$item) && $item[$cfg]){
//                        $item[$cfg] = $dict_map[$item[$cfg]];
//                        $final_result[] = $item;
//                    }
//                }
//            }

            return $this->success_msg($demand_result,$demand_count);
        }else{
            return $this->success_msg(3);
        }
    }

}