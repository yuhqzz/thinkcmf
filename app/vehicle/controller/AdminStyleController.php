<?php
/**
 * Created by PhpStorm.
 * User: yuhq
 * Date: 2018-11-17
 * Time: 09:50
 */

namespace app\vehicle\controller;


use app\vehicle\model\BrandModel;
use app\vehicle\model\StyleModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\exception\ErrorException;

class AdminStyleController extends AdminBaseController
{

    public function _initialize(){
        parent::_initialize();
        $this->model = new StyleModel();

    }

    /**
     * 汽车车型列表
     * @adminMenu(
     *     'name'   => '车型管理',
     *     'parent' => 'vehicle/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '汽车车型列表',
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
                ->with(['brand','series'])
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
     * 添加车型
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
        // 获取汽车等级
        $this->assign('LevelList',config('vehicle.level_list'));
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
    public function addPost()
    {
       if($this->request->isPost()){
           $data = $this->request->param();

           $data['name'] = trim($data['name']);
           $data['brand_id'] = intval($data['brand_id']);
           $data['series_id'] = intval($data['series_id']);
           $data['level'] = strval($data['level']);
           $data['is_hot'] = isset($data['is_hot'])?intval($data['is_hot']):0;
           $data['is_recommend'] = isset($data['is_recommend'])?intval($data['is_recommend']):0;
           $data['factory_price'] = trim($data['factory_price']);
           $data['description'] = htmlspecialchars(trim($data['description']),ENT_QUOTES);
           $data['more'] = trim($data['more']);

           try{
               $result = $this->validate($data, 'Style.add');
               if ($result !== true) {
                   $this->error($result);
               }
               $result = $this->model->isUpdate(false)->allowField(true)->save($data);
               if ($result === false) {
                   $this->error('添加失败!');
               }
               $this->success('添加成功!', url('AdminStyle/index'));
           }catch (ErrorException $exception){
               $this->error($exception->getMessage());
           }

       }
    }

    /**
     * 编辑车型
     * @adminMenu(
     *     'name'   => '编辑车型',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑车型',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');
        if ($id > 0) {
            $carStyle = StyleModel::get($id);
            $carStyle = $carStyle?$carStyle->toArray():[];
            if(empty($carStyle)||$carStyle['delete_time']>0){
                $this->error('车型不存在或已经删除!');
            }
            $brandModel = new BrandModel();
            $brandList =  $brandModel->getBrandList();
            $this->assign('brandList',$brandList);
            $this->assign('LevelList',config('vehicle.level_list'));
            $this->assign($carStyle);
            return $this->fetch();
        } else {
            $this->error('操作错误!');
        }
        return $this->fetch();
    }

    /**
     * 编辑车型提交
     * @adminMenu(
     *     'name'   => '编辑车型提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑车型提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {

        if ($this->request->isPost()){
            $data = $this->request->param();

            $data['id'] = intval($data['id']);

            if(empty($data['id'])){
                $this->error('保存失败!');
            }
            $data['name'] = trim($data['name']);
            $data['is_hot'] = intval($data['is_hot']);
            $data['is_recommend'] = intval($data['is_recommend']);
            $data['factory_price'] = trim($data['factory_price']);
            $data['level'] = strval($data['level']);
            if(isset($data['status'])){
                $data['status'] = intval($data['status']);
            }
            $data['description'] = htmlspecialchars(trim($data['description']),ENT_QUOTES);
            $data['more'] = trim($data['more']);

            try{

                $result = $this->validate($data, 'Style.edit');
                if ($result !== true) {
                    $this->error($result);
                }
                $result =$this->model->isUpdate(true)->allowField(true)->save($data);
                if ($result === false) {
                    $this->error('保存失败!');
                }
                $this->success('保存成功!');
            }catch (ErrorException $exception){
                $this->error($exception->getMessage());
            }

        }

    }


    /**
     * 车型排序
     * @adminMenu(
     *     'name'   => '车型排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '车型排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        parent::listOrders(Db::name('vehicle_style'));
        $this->success("排序更新成功！", '');
    }

    /**
     * 删除车型
     * @adminMenu(
     *     'name'   => '删除车型',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除车型',
     *     'param'  => ''
     * )
     */
    public function delete()
    {

        $id  = $this->request->param('id');
        $findCarSeries = StyleModel::get($id);

        if ( empty($findCarSeries) || $findCarSeries['delete_time']>0) {
            $this->error('车型不存在!');
        }
        // 存在商品不允许被删除
        $rs = Db::name('goods')->where(['style_id'=>$id,'delete_time'=>0])->find();
        if($rs){
            $this->error('请先转移该车型下的车源');
        }
        $data   = [
            'object_id'   => $findCarSeries['id'],
            'create_time' => time(),
            'table_name'  => 'vehicle_style',
            'name'        => $findCarSeries['name'],
            'user_id' =>cmf_get_current_admin_id()
        ];
        $result = $this->model
            ->where('id', $id)
            ->update(['delete_time' => time()]);
        if ($result) {
            Db::name('recycleBin')->insert($data);
            // 删除参数配置表信息
            Db::name('goods_car_config_values')->where(['car_style_id'=>$id])->delete();
            $this->success('删除成功!');
        } else {
            $this->error('删除失败');
        }
    }


}