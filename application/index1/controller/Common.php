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
use think\Request;


#  指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
#  响应类型
header('Access-Control-Allow-Methods: POST,GET');
#  响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');

class Common extends Controller
{
    /**
     * 用户鉴权
     * 鉴权使用使用用户id加上当前操作的页面进行鉴权
     * @param token        string      用于验证当前用户是否有权限创建账号
     * @return bool
     * @author wjfeng
     */
    public function _initialize(){
        $token = Request::instance()->param('token','123','trim');
//        var_dump($token);
        if($token){
            return true;
        } else{
            return $this->error('用户非法操作,正在跳转到登录页');
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


}