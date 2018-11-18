<?php
/**
 * Created by PhpStorm.
 * User: yuhq
 * Date: 2018-11-17
 * Time: 09:52
 */

namespace app\vehicle\controller;


use app\vehicle\model\BrandModel;
use app\vehicle\model\StoreModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\exception\ErrorException;
use think\exception\PDOException;

class AdminStoreController extends AdminBaseController
{
    public function _initialize(){
        parent::_initialize();
        $this->model = new StoreModel();

    }

    /**
     * 4S店管理
     * @adminMenu(
     *     'name'   => '4S店管理',
     *     'parent' => 'vehicle/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '4S店管理列表',
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
     * 添加4S店
     * @adminMenu(
     *     'name'   => '添加4S店',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加4S店',
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
     * 添加4S店提交
     * @adminMenu(
     *     'name'   => '添加4S店提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加4S店提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if($this->request->isPost()){
            $data = $this->request->param();

            $data['name'] = trim($data['name']);
            $data['main_brand'] = strval($data['main_brand']);
            $data['type'] = intval($data['type']);
            try{
                $result = $this->validate($data, 'Store.add');
                if ($result !== true) {
                    $this->error($result);
                }
                $result = $this->model->isUpdate(false)->allowField(true)->save($data);
                if ($result === false) {
                    $this->error('添加失败!');
                }
                $this->success('添加成功!', url('AdminStore/index'));

            }catch (ErrorException $e){
                $this->error($e->getMessage());
            }

        }
    }

    /**
     * 编辑4S店
     * @adminMenu(
     *     'name'   => '编辑4S店',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑4S店',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');
        if ($id > 0) {
            $carStore = StoreModel::get($id);
            $carStore = $carStore?$carStore->toArray():[];
            if(empty($carStore)){
                $this->error('4S店不存在或已经删除!');
            }
            $this->assign($carStore);
            return $this->fetch();
        } else {
            $this->error('操作错误!');
        }
        return $this->fetch();
    }

    /**
     * 编辑4S店提交
     * @adminMenu(
     *     'name'   => '编辑4S店提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑4S店提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {

        if ($this->request->isPost()){
            $params = $this->request->param();
            if(empty($data)){
                $this->error('参数错误');
            }
            try{
                $result = $this->validate($params, 'Store.edit');
                if ($result !== true) {
                    $this->error($result);
                }
                $result =$this->model->isUpdate(true)->allowField(true)->save($params);
                if ($result === false) {
                    $this->error('保存失败!');
                }
                $this->success('保存成功!');

            }catch (PDOException $e){
                $this->error($e->getMessage());
            }catch (ErrorException $e){
                $this->error($e->getMessage());
            }



        }

    }


    /**
     * 删除4S店
     * @adminMenu(
     *     'name'   => '删除4S店',
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
        $id  = $this->request->param('id');
        try{
            $this->model->where('id','=',(int)$id)->delete();
            $this->success('删除成功！');
        }catch (ErrorException $e){
            $this->error($e->getMessage());
        }

    }

}