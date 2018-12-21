<?php
/**
 * Created by PhpStorm.
 * User: mocha
 * Date: 2018/9/20
 * Time: 15:07
 * author:wjfeng
 */

namespace app\index\controller;


use think\Controller;
use think\Db;
use think\Request;
use think\Session;


#  指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
#  响应类型
header('Access-Control-Allow-Methods: POST,GET');
#  响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');

class Common extends Controller
{
    /**
     * 鉴权
     * @return bool|\think\response\Json|void
     */
    public function _initialize(){
        $request = Request::instance();
        $user_id = $request->param('user_id','958ee617b386f6c4b052e6ecce51d39c','trim');
        $token = $request->param('token','','trim');
        $isUpdate = $request->param('isUpdate','','trim'); # 是否是更新操作,更新操作传任意值即可
        $module = $request->module();
        $control= $request->controller();
        $action = $request->action();

        $route = $module.'/'.$control.'/'.$action;
        $route = strtolower($route);
        if($route == 'index/login/login'){
            return true; # 登录接口不检测权限
        }
//        dump($route);
        if(!$user_id){
            return $this->error_msg('参数错误');
        }
        $sid = Session::get('userInfo');
        $menu_result = Db::view('user','user_id')
            ->view('role','role_id','user.role_id = role.role_id','left')
            ->view('permission','menu_id','permission.role_id = role.role_id','left')
            ->where(['user.user_id'=>$user_id])
            ->select();

        # 先获取菜单权限列表
        # 如果这里查询不到结果,则直接判断无权限操作
        if(!$menu_result){
            return $this->error('非法操作,正在跳转'); #
        }

        # 通过菜单权限列表和路由双重判定是否有操作权限
        $menus = array_column($menu_result,'menu_id')[0];
        $where = [
            'menu_id'   =>  ['in',$menus],
            'route'     =>  ['LIKE',"%$route%"]
        ];
        # 如果是更新操作,则需要判断该用户是否有编辑权限
        if($isUpdate){
            $where['can_change']    =   1;
        }

        $isExists = Db('menu')->where($where)->fetchSql(true)->find();

        # 能查询到,则表明有权限
        if($isExists){
            return true;
        } else{
            //return $this->error('用户非法操作,正在跳转到登录页');
        }
    }


    /**
     * 将数据集转换为树形结构(最后一层的子节点都不带有child)
     * @param $rows                 array       待处理的数据集(二维数组)
     * @param string $pk            string      主键名
     * @param string $pid           string      父id键名
     * @param string $id           string      节点id
     * @return array                array       返回处理后的树状结构
     */
    function generate_tree($rows, $pk='id', $pid='parent_id',$id = 0){
        $array = array();
        foreach ($rows as $row) $array[$row[$pk]] = $row;
        foreach ($array as $item) $array[$item[$pid]]['child'][$item[$pk]] = &$array[$item[$pk]];
        if($id == 0){
            return isset($array[0]['child']) ? $array[0]['child'] : array();
        }else{
            return isset($array[0]['child'][$id]) ? $array[0]['child'][$id] : array();
        }
    }
    /**
     * 将数据集转换为树形结构(每一个子节点都带有child)
     * @param $rows                 array       待处理的数据集(二维数组)
     * @param string $id            string      节点id
     * @param string $pid           string      父节点id
     * @return array                array       返回处理后的树状结构
     */
    function generate_tree_with_child($rows, $id='id', $pid='parent_id'){
        $array = array();
        foreach ($rows as $row) {
            $array[$row[$id]] = $row;
            $array[$row[$id]]['child'] = [];
        }
        foreach ($array as $item)  $array[$item[$pid]]['child'][$item[$id]] = &$array[$item[$id]];
        return isset($array[0]['child']) ? $array[0]['child'] : array();
    }

    /**
     * 随机生成md5加密字符串
     * @author wjfeng
     */
    public function md5_str_rand(){
        $range = time().mt_rand(1111,9999999);
        return md5($range);
    }

    /**
     * 操作成功提示信息生成函数
     * @param  int $count 结果集长度
     * @param  array $result 返回数据
     * @return \think\response\Json
     */
    function success_msg($result = [], $count = 0)
    {
        $arr['status'] = 'success';
        if ($count > 0) {
            $arr['count'] = $count;
        } elseif (!is_numeric($count)) {
            $arr['status'] = 'failed';
            $arr['message'] = 'count参数格式错误';
        }
        if (is_array($result) && !empty($result)) {
            $arr['result'] = $result;
        } elseif (is_string($result)) {
            $arr['message'] = $result;
        } elseif (is_numeric($result)) {
            $arr['message'] = config('message.success_' . $result);
        } else {
            $arr['status'] = 'failed';
            $arr['message'] = '参数格式错误';
        }
        return json_encode($arr);
    }

    /**
     * 操作错误提示信息生成函数
     * @param  $message int|string 错误提示信息编码
     * @return \think\response\Json
     * @author wjfeng
     */
    function error_msg($message)
    {
        $arr['status'] = 'failed';
        if (is_numeric($message)) {
            $arr['message'] = config('message.error_' . $message);
        } elseif (is_string($message)) {
            $arr['message'] = $message;
        } else {
            $arr['message'] = 'message参数格式错误';
        }

        return json_encode($arr);
    }



    /**
     * 查出id下所有子节点, 包含自己
     * # 必须要按照[创建时间]排[正序],这样才能保证子节点不会遗漏
     *
     * @param $rows 被查找的数据集
     * @param $id   需要查找的id
     * @param $pk   主键名
     * @param $pid  父id键名
     * @return json
     */
    public function getAllChild($rows=[],$id='',$pk='dictionary_id',$pid='parent_id'){
        if(!is_array($rows)){
            return $rows;
        }
        if(empty($rows)){
            return '';
        }
        $map = [];
        foreach($rows as $item){
            $map[] = $item[$pid] . '_' . $item[$pk];
        }
        $data = [$id];
        foreach($map as $mix_str){
            $mix = explode('_', $mix_str);
            if(in_array($mix[0], $data)){
                $data[] = $mix[1];
            }
        }
        return json_encode($data);
    }


    /**
     * 返回字典与值的映射关系
     * @param string $dict_id
     * @return mixed|string
     */
    public function dict_id_map($dict_id=''){

        $where = false;
        if($dict_id){
            $where['dictionary_id'] = $dict_id;
        }
//        $where['valid'] = 1;

        $dict_result = Db('dictionary')->field('dictionary_id,name')->where($where)->select();

        $dict_map = [];
        if ($dict_result){
            foreach ($dict_result as $item){
                $dict_map[$item['dictionary_id']] = $item['name'];
            }
        }

        return json_encode($dict_map);

    }


    /**
     * 获取所有的下属用户
     * @param string $user_id   当前用户id
     */
    public function getLowerLevelUsers($user_id = ''){
        # 获取所在角色
//        $getRole = Db::view('user','user_id')
//            ->view('organization','organization_id','user.organization_id = organization.organization_id','inner')
//            ->where(['user.user_id'=>$user_id])->find();

        $getRole = Db('user')->field('organization_id')->where(['user.user_id'=>$user_id])->find();
        $organization_id = '';
        if($getRole){
            $organization_id = $getRole['organization_id'];
        }

        if($organization_id){
//            $where = [
//                'organization_id'   =>  $organization_id,
//                'valid'     =>  1
//            ];
//            $whereOr = [
//                'parent_id' =>  $organization_id,
//            ];
            # 获取所有子节点
            # 必须要按照[创建时间]排[正序],这样才能保证子节点不会遗漏
            $organization = Db('organization')->field('organization_id,parent_id')->order('created asc')->fetchSql(false)->select();

            $childs = $this->getAllChild($organization,$organization_id,'organization_id','parent_id');
            $childs = json_decode($childs);
//            $childs = array_diff($childs,[$organization_id]); # 同级之间的人员能否互相查看数据(如果能,则放开注释)

//            dump($childs);
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

        return json_encode($users);
    }

}