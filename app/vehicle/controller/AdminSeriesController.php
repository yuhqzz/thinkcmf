<?php
/**
 * Created by PhpStorm.
 * User: yuhq
 * Date: 2018-11-17
 * Time: 09:50
 */

namespace app\vehicle\controller;


use app\vehicle\model\BrandModel;
use app\vehicle\model\SeriesModel;
use app\vehicle\model\StyleModel;
use cmf\controller\AdminBaseController;
use think\Collection;
use think\Db;
use think\exception\ErrorException;
use think\Model;

class AdminSeriesController extends AdminBaseController
{
    public function _initialize(){
        parent::_initialize();
        $this->model = new SeriesModel();

    }

    /**
     * 汽车车系列表
     * @adminMenu(
     *     'name'   => '车系管理',
     *     'parent' => 'vehicle/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '汽车车系列表',
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
                ->with(['brand'])
                ->where($where)
                ->where('series_model.delete_time','=',0)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['brand'])
                ->where($where)
                ->where('series_model.delete_time','=',0)
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
     * 添加车系
     * @adminMenu(
     *     'name'   => '添加车系',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加车系',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $brandModel = new BrandModel();
        $brandList =  $brandModel->getBrandList();
        $this->assign('brandList',$brandList);
        return $this->fetch();
    }

    /**
     * 添加车系提交
     * @adminMenu(
     *     'name'   => '添加车系提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加车系提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if($this->request->isPost()){
            $params = $this->request->param();
            if(empty($params)){
                $this->error('添加失败!参数为空！');
            }
            $params['name'] = trim($params['name']);
            $params['brand_id'] = intval($params['brand_id']);
            $params['is_hot'] = isset($params['is_hot'])?intval($params['is_hot']):0;
            $params['price'] = isset($params['price'])?floatval($params['price']):'0.00';
            $params['description'] = htmlspecialchars(trim($params['description']),ENT_QUOTES);
            $params['more'] = trim($params['more']);

            try{
                $result = $this->validate($params, 'Series.add');
                if ($result !== true) {
                    $this->error($result);
                }

                $result = $this->model->isUpdate(false)->allowField(true)->save($params);
                if ( $result === false) {
                    $this->error('添加失败!');
                }
                $this->success('添加成功!', url('AdminSeries/index'));
            }catch (ErrorException $e){
                $this->error($e->getMessage());

            }

        }

    }

    /**
     * 编辑车系
     * @adminMenu(
     *     'name'   => '编辑车系',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑车系',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id = $this->request->param('id');
        if ($id > 0) {
            $carSeries = SeriesModel::get($id);
            $carSeries = $carSeries?$carSeries->toArray():[];
            if(empty($carSeries)){
                $this->error('车系不存在或已经删除!');
            }
            $brandModel = new BrandModel();
            $brandList =  $brandModel->getBrandList();
            $this->assign('brandList',$brandList);
            $this->assign($carSeries);
            return $this->fetch();
        } else {
            $this->error('操作错误!');
        }
        return $this->fetch();
    }

    /**
     * 编辑车系提交
     * @adminMenu(
     *     'name'   => '编辑车系提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑车系提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {

        if($this->request->isPost()){
            $data = $this->request->param();

            $data['id'] = intval($data['id']);

            if(empty($data['id'])){
                $this->error('保存失败!');
            }

            $data['name'] = trim($data['name']);
            if( isset($data['is_hot']) ){
                $data['is_hot'] = intval($data['is_hot']);
            }
            if(isset($data['price'])){
                $data['price'] = floatval($data['price']);
            }

            $data['brand_id'] = intval($data['brand_id']);
            $data['description'] = htmlspecialchars(trim($data['description']),ENT_QUOTES);

            try{
                $result = $this->validate($data, 'Series.edit');

                if ($result !== true) {
                    $this->error($result);
                }
                $carSeriesModel = new SeriesModel();
                $result = $carSeriesModel->isUpdate(true)->allowField(true)->save($data);
                if ($result === false) {
                    $this->error('保存失败!');
                }
                $this->success('保存成功!');
            }catch (ErrorException $e){
                $this->error($e->getMessage());
            }

        }

    }

    /**
     * 删除车系
     * @adminMenu(
     *     'name'   => '删除车系',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除车系',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $ids                  = $this->request->param('ids');
        $findCarSeries = SeriesModel::get($ids);

        if (empty($findCarSeries) || $findCarSeries['delete_time'] > 0) {
            $this->error('车系不存在或已删除!');
        }
        // 存在车型不允许被删除
        $rs = Db::name('vehicle_style')->where(['series_id'=>$ids,'delete_time'=>0])->find();
        if($rs){
            $this->error('请先转移该车系下的车型');
        }

        $data   = [
            'object_id'   => $findCarSeries['id'],
            'create_time' => time(),
            'table_name'  => 'vehicle_series',
            'name'        => $findCarSeries['name'],
            'user_id' =>cmf_get_current_admin_id()
        ];
        $result = $this->model
            ->where('id', $ids)
            ->update(['delete_time' => time()]);
        if ($result) {
            Db::name('recycleBin')->insert($data);
            $this->success('删除成功!');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 批量删除
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function mutiDelete(){

        $ids                  = $this->request->param('ids');
        if(empty($ids)) {
            $this->error('请选择要删除的车系!');
        }
        $ids_arr = explode(',',$ids);
        if(count(array_filter(($ids_arr)) == 0)) {
            $this->error('请选择要删除的车系!');
        }
        try{
            $findSeriesList = Collection($this->model->where('id','in',$ids_arr)->select())->toArray();
            if($findSeriesList){
                $info = [];
                $styleModel = new StyleModel();
                foreach ($findSeriesList as $list){
                    // 存在车型不允许被删除
                    $rs = $styleModel->where(['series_id'=>$list['id'],'delete_time'=>0])->find();
                    if($rs){
                        $msg = "【".$list['name']."】删除失败!该车系存在引用车型,请先进行转移.";
                        $info[] = $msg;
                        continue;
                    }
                    if($list['delete_time']>0){
                        $msg = "【".$list['name']."】已经删除!.";
                        $info[] = $msg;
                        continue;
                    }
                    $data   = [
                        'object_id'   => $list['id'],
                        'create_time' => time(),
                        'table_name'  => 'vehicle_series',
                        'name'        => $list['name'],
                        'user_id' =>cmf_get_current_admin_id()
                    ];
                    $result = $this->model
                        ->where('id', $ids)
                        ->update(['delete_time' => time()]);
                    if ($result) {
                        Db::name('recycleBin')->insert($data);
                        $msg = "【".$list['name']."】删除成功!";
                        $info[] = $msg;
                    } else {
                        $msg = "【".$list['name']."】删除失败!";
                        $info[] = $msg;
                    }
                }
                $this->success(implode($info,"<br/"));
            }
            $this->error('删除失败,没有数据可以删除.');
        }catch (ErrorException $exception){
            $this->error($exception->getMessage());
        }

    }

    /**
     * 获取品牌下的车系
     * @adminMenu(
     *     'name'   => '获取品牌下的车系',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '获取品牌下的车系',
     *     'param'  => ''
     * )
     */
    public function getSeriesByBrandId(){
        $brand_id = $this->request->param('brand_id');
        $brand_id = intval($brand_id);
        $seriesData = [];
        if(!empty($brand_id)){
            $seriesData = $this->model->getSeriesByBrandId($brand_id);

        }
        $this->result($seriesData,1);
    }

}