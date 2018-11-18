<?php
/**
 * Created by PhpStorm.
 * User: yuhq
 * Date: 2018-11-17
 * Time: 09:51
 */

namespace app\vehicle\controller;


use app\user\model\AssetModel;
use app\vehicle\model\ImagesModel;
use app\vehicle\model\SeriesModel;
use app\vehicle\model\StyleModel;
use cmf\controller\AdminBaseController;
use think\Collection;
use think\Controller;
use think\Db;
use think\exception\ErrorException;

class AdminImagesController extends AdminBaseController
{

   protected $type = 0;

   protected $resource_id = 0;

    public function _initialize(){
        parent::_initialize();
        $this->model = new ImagesModel();
        $type = $this->request->param('type',0,'intval');
        $resource_id = $this->request->param('resource_id',0,'intval');
        $this->type = $type;
        $this->resource_id = $resource_id;
        $this->assign('type', $this->type);
        $this->assign('resource_id',$this->resource_id);
    }
    /**
     * 图片列表
     * @adminMenu(
     *     'name'   => '图片列表',
     *     'parent' => 'vehicle/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '图片列表',
     *     'param'  => ''
     * )
     */
    public function index()
    {

        if(empty($this->type) || empty($this->resource_id)){
            $this->error('非法操作');
        }
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->where('type','=',$this->type)
                ->where('resource_id','=',$this->resource_id)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->where('type','=',$this->type)
                ->where('resource_id','=',$this->resource_id)
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
     * 发布图片
     * @adminMenu(
     *     'name'   => '发布图片',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '发布图片',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        if(empty($this->type) || empty($this->resource_id)){
            $this->error('非法操作');
        }
        if($this->type == 1){
            $series = SeriesModel::get($this->resource_id);
            $this->assign('resource',$series);
        }elseif ($this->type == 2){
            $style = StyleModel::get($this->resource_id);
            $this->assign('resource',$style);
        }
        $images = collection(
            $this->model
            ->where('type','=',$this->type)
            ->where('resource_id','=',$this->resource_id)
            ->order('create_time desc')
            ->select())->toArray();
        $this->assign('images',$images);
        return $this->fetch();
    }

    /**
     * 发布图片保存
     * @adminMenu(
     *     'name'   => '发布图片保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '发布图片保存',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if(empty($this->type) || empty($this->resource_id)){
            $this->error('非法操作');
        }
        if ($this->request->isPost()) {
            $params = $this->request->param();
            if(empty($params['type']) || empty($params['resource_id'])){
                $this->error('保存图片失败');
            }
            if( empty($params['images'])){
                $this->error('保存图片为空');
            }
            //var_dump($params);die;
            $assetModel = new AssetModel();
            $create_time = time();
            $s_count = 0;
            foreach ($params['images']['photo_keys'] as $key => $img_key){
                $asset_info = $assetModel->getAssetByKey($img_key);
               // dump($asset_info);die;
                if($asset_info){
                    $save = [];
                    $save['name'] = trim($params['images']['photo_names'][$key]);
                    $save['type'] = (int)$params['type'];
                    $save['resource_id'] = (int)$params['resource_id'];
                    $save['image_path'] = (string)$params['images']['photo_urls'][$key];
                    $save['asset_id'] = (int)$asset_info['id'];
                    $save['create_time'] = $create_time;
                    try{
                        $where['type'] = (int)$params['type'];
                        $where['resource_id'] = (int)$params['resource_id'];
                        $where['asset_id'] = (int)$save['asset_id'];
                        $img = $this->model->where(function ($query) use($where){
                            foreach ($where as $key=>$val){
                                $query->where($key,'=',$val);
                            }
                        })->find();


                        if($img){
                            $result = $this->model->where('id','=',$img['id'])->update($save);
                            if($result){
                                $s_count++;
                            }
                        }else{
                            $result = Db::name('vehicle_images')->insert($save);
                            if($result){
                                $s_count++;
                            }
                        }
                    }catch (ErrorException $exception){
                        $this->error($exception->getMessage());
                        continue;
                    }
                }
            }
            $this->success($s_count.'个保存图片成功',url('AdminImages/index',array('type'=>$params['type'],'resource_id'=>$params['resource_id'])));
        }

    }

    public function delete(){
        $imagesModel = new ImagesModel();
        $ids                 = $this->request->param('ids');
        if(empty($ids)){
            $this->error('删除失败');
        }

        $result = $imagesModel->where('id','=',(int)$ids)->delete();
        if ($result) {
            $this->success('删除成功!');
        } else {
            $this->error('删除失败');
        }
    }
}