<?php
/**
 * Created by PhpStorm.
 * User: yuhq
 * Date: 2018-11-17
 * Time: 09:50
 */

namespace app\vehicle\controller;


use app\vehicle\model\BrandModel;
use cmf\controller\AdminBaseController;
use think\db;
use think\exception\ErrorException;

/**
 *
 * Class AdminBrandController
 * @package app\goods\controller
 * @deprecated 品牌管理
 */
class AdminBrandController extends AdminBaseController
{
    /**
     * 品牌列表
     * @adminMenu(
     *     'name'   => '品牌列表',
     *     'parent' => 'vehicle/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '品牌列表',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $this->model = new BrandModel();
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
     * 添加品牌
     * @adminMenu(
     *     'name'   => '添加品牌',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加品牌',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * 添加品牌提交
     * @adminMenu(
     *     'name'   => '添加品牌',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $data['name'] = trim($data['name']);

            $data['description'] = htmlspecialchars(trim($data['description']),ENT_QUOTES);
            try{
                $result = $this->validate($data, 'Brand.add');
                if ($result !== true) {
                    $this->error($result);
                }

                $brandModel = new BrandModel();
                $result = $brandModel->isUpdate(false)->allowField(true)->save($data);
                if ($result === false) {
                    $this->error('添加失败!');
                }
            }catch (ErrorException $exception){
                $this->error('保存失败! '.$exception->getMessage());
            }
        }
        $this->success('添加成功!', url('AdminBrand/index'));

    }

    /**
     * 编辑品牌
     * @adminMenu(
     *     'name'   => '编辑品牌',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑品牌',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');
        if ($id > 0) {
            $brand = BrandModel::get($id);
            $brand = $brand?$brand->toArray():[];
            if(empty($brand)){
                $this->error('品牌不存在或已经删除!');
            }
            $this->assign($brand);
            return $this->fetch();
        } else {
            $this->error('操作错误!');
        }
    }

    /**
     * 编辑品牌提交
     * @adminMenu(
     *     'name'   => '编辑品牌提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑品牌提交',
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

           $data['description'] = htmlspecialchars(trim($data['description']),ENT_QUOTES);

           try{
               $result = $this->validate($data, 'Brand.edit');

               if ($result !== true) {
                   $this->error($result);
               }
               $brandModel = new BrandModel();
               $result = $brandModel->isUpdate(true)->allowField(true)->save($data);
               if ($result === false) {
                   $this->error('保存失败!');
               }
               $this->success('保存成功!');
           }catch (ErrorException $exception){
               $this->error('保存失败! '.$exception->getMessage());
           }

       }

    }


    /**
     * 编辑品牌排序
     * @adminMenu(
     *     'name'   => '编辑品牌排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑品牌排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        parent::listOrders(db::name('vehicle_brand'));
        $this->success("排序更新成功！", '');
    }

    /**
     * 删除品牌
     * @adminMenu(
     *     'name'   => '删除品牌',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除品牌',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $brandModel = new BrandModel();
        $id                  = $this->request->param('id');
        //获取删除的内容
        $findBrandgory = BrandModel::get($id);

        if (empty($findBrandgory)) {
            $this->error('品牌不存在!');
        }

        $data   = [
            'object_id'   => $findBrandgory['id'],
            'create_time' => time(),
            'table_name'  => 'vehicle_brand',
            'name'        => $findBrandgory['name'],
            'user_id' =>cmf_get_current_admin_id()
        ];
        $result = $brandModel
            ->where('id', $id)
            ->update(['delete_time' => time()]);
        if ($result) {
            Db::name('recycleBin')->insert($data);
            $this->success('删除成功!');
        } else {
            $this->error('删除失败');
        }
    }

}