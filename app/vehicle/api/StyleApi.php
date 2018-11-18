<?php
/**
 * Created by PhpStorm.
 * User: yuhq
 * Date: 2018-11-05
 * Time: 10:06
 *
 *
 * 车系
 */
namespace app\vehicle\api;

use app\vehicle\model\StyleModel;
use think\Collection;
use think\Request;

class StyleApi
{
    protected $model = null;
    protected $request = null;

    protected $selectpageFields = '*';

    public function selectPage( Request $request , $params = []){

        $this->model = new  StyleModel();
        $this->request = $request;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'htmlspecialchars']);

        //当前页
        $page = $this->request->param("pageNumber");
        //分页大小
        $pagesize = $this->request->param("pageSize");
        //搜索条件
        $andor = $this->request->param("andOr", "and", "strtoupper");
        //显示的字段
        $field = $this->request->param("showField");
        //主键
        $primarykey = $this->request->param("keyField");
        //主键值
        $primaryvalue = $this->request->param("keyValue");
        //搜索字段
        $searchfield = (array)$this->request->param("searchField/a");
        //自定义搜索条件
        $custom = (array)$this->request->param("custom/a");

        $order['createtime'] = 'desc';

        $field = $field ? $field : 'name';


        //如果有primaryvalue,说明当前是初始化传值
        if ($primaryvalue !== null) {
            $where = [$primarykey => ['in', $primaryvalue]];
        } else {
            $where = function ($query) use ( $andor, $field, $searchfield, $custom) {
                if ($custom && is_array($custom)) {
                    foreach ($custom as $k => $v) {
                        $query->where($k, '=', $v);
                    }
                }
            };
        }

        $list = [];
        $total = $this->model->where($where)->count();
        if ($total > 0) {
            $datalist = $this->model->where($where)
                ->order($order)
                ->page($page, $pagesize)
                ->field($this->selectpageFields)
                ->select();
            $datalist = Collection::make($datalist)->toArray();

            foreach ($datalist as $index => $item) {
                $list[] = [
                    $primarykey => isset($item[$primarykey]) ? $item[$primarykey] : '',
                    $field      => isset($item[$field]) ? $item[$field] : ''
                ];
            }
        }
        //这里一定要返回有list这个字段,total是可选的,如果total<=list的数量,则会隐藏分页按钮
        return json(['list' => $list, 'total' => $total]);
    }


}