<?php
/**
 * Created by PhpStorm.
 * User: yuhq
 * Date: 2018-11-17
 * Time: 09:53
 */

namespace app\vehicle\controller;


use app\vehicle\model\OrderModel;
use cmf\controller\AdminBaseController;
use think\exception\ErrorException;

class AdminOrderController extends AdminBaseController
{

    public function _initialize(){
        parent::_initialize();
        $this->model = new OrderModel();

    }
    /**
     * 预约/询价订单列表
     * @adminMenu(
     *     'name'   => '预约/询价订单列表',
     *     'parent' => 'vehicle/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '预约/询价订单列表',
     *     'param'  => ''
     * )
     */
    public function index()
    {

        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
            return json($result);

        }
        return $this->fetch();
    }

    /**
     * 订单详情
     * @adminMenu(
     *     'name'   => '订单详情',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '订单详情',
     *     'param'  => ''
     * )
     */
    public function detail( $id = null ){

        $id = empty($id)?intval($this->request->get('id')):0;
        if(empty($id)){
            $this->error('订单不存在');
        }
        $order = $this->model->get($id);
        $this->assign('order',$order);
        return $this->fetch();
    }


    /**
     * 删除订单
     * @adminMenu(
     *     'name'   => '删除订单',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除4S店',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $ids = $this->request->param('ids');

        if(empty($ids)){
            $this->error('无数据可删除');
        }
        try{
            $this->model->where($this->model->getPk(),'in',$ids)->delete();
            $this->success('删除成功！');
        }catch (ErrorException $e){
            $this->error($e->getMessage());
        }
        $this->success('删除成功！');
    }



}