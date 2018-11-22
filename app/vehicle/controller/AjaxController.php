<?php
/**
 * Created by PhpStorm.
 * User: yuhq
 * Date: 2018-11-17
 * Time: 09:55
 *
 * ajax 操作类
 */

namespace app\vehicle\controller;


use app\vehicle\api\BrandApi;
use app\vehicle\api\SeriesApi;
use app\vehicle\api\StoreApi;
use app\vehicle\api\StyleApi;
use app\vehicle\model\OrderModel;
use app\vehicle\model\SeriesModel;
use app\vehicle\model\StoreModel;
use app\vehicle\model\StyleModel;
use app\vehicle\service\EmailService;
use app\vehicle\service\VehicleService;
use cmf\controller\BaseController;
use think\exception\ErrorException;


class AjaxController extends BaseController
{


    /**
     *
     * 预约试驾表单提交
     *
     */
    public function submitBookOrder(){
        if($this->request->isPost()){
            // 品牌
            $brand_id = intval($this->request->post('brand_id',0,'trim'));
            //车系
            $series_id = intval($this->request->post('series_id',0,'trim'));

            //车型
            $style_id = intval($this->request->post('style_id',0,'trim'));
            // 客户姓名
            $customer_name = strval($this->request->post('customer_name',0,'trim'));
            //电话
            $mobile = strval($this->request->post('mobile',0,'trim'));

            $dealers_id = intval($this->request->post('dealer',0,'trim'));

            // 来源页面
            $source_id = intval($this->request->post('source',0,'trim'));

            // 性别
            $sex = intval($this->request->post('sex',0,'trim'));
            // 区域
            $area_id = intval($this->request->post('area_id',0,'trim'));

            // 计划到店时间
            $book_to_time = strtotime($this->request->post('book_to_time',0,'trim'));

            if( cmf_is_mobile() && empty( $source_id )){
                $source_id = 2;
            }else{
                $source_id = 1;
            }

            $params['type'] = 0; // 预约试驾
            $params['name'] = $customer_name;
            $params['sex'] = $sex?$sex:0; // 性别id
            $params['telephone'] = $mobile;
            $params['brand_id'] = $brand_id;
            $params['series_id'] = $series_id;
            $params['style_id'] = $style_id;
            $params['dealers_id'] = $dealers_id;
            $params['area_id'] = $area_id?$area_id:4403;
            $params['source'] = $source_id;
            $params['book_to_time'] = $book_to_time?:$book_to_time;
            $params['createtime'] = time();

            // 验证必须参数
            try{
                $validate_res =  $this->validate($params,'Order.book');
                if( $validate_res !== true ){
                    $this->result('',0,$validate_res);
                }
            }catch (ErrorException $exception){

                $this->result('',0,$exception->getMessage());
            }

           // 已经预约过滤进行验证
            $wh = [];
            $wh['series_id'] = $series_id;
            $wh['telephone'] = $mobile;
            $wh['dealers_id'] = $dealers_id;
            $wh['type'] = 0;
            $wh['delete_time'] = 0;
            $wh['createtime'] = ['gt',time()-86400*7];// 过滤7天内咨询过的用户

            $vehicleOrderModel = new OrderModel();

            $count = $vehicleOrderModel->where($wh)->count();

            if($count > 0){
                $this->result('',-1,'您已经预约');
            }
            try{
                $id = $vehicleOrderModel->addOrderBookData($params);
                if($id){

                    // 发邮件通知
                    $this->sendEmailNotice($params,'test_drive','预约试驾','test_drive');
                    $store_name = getStoreName($dealers_id)?:'';
                    $this->result(['order_book_id'=>$id,'store_name'=>$store_name],1);
                }
            }catch(ErrorException $e){
                $this->result('',0,$e);
            }
        }
        $this->result('',0,'非法请求');
    }

    /**
     * 咨询底价
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function submitAskPrice(){

        if($this->request->isPost()){
            // 品牌
            $brand_id = intval($this->request->post('brand_id',0,'trim'));

            //车系
            $series_id = intval($this->request->post('series_id',0,'trim'));

            //车型
            $style_id = intval($this->request->post('style_id',0,'trim'));

            // 客户姓名
            $customer_name = strval($this->request->post('customer_name',0,'trim'));

            //电话
            $mobile = strval($this->request->post('mobile',0,'trim'));

            //4s店
            $dealers_id = intval($this->request->post('dealer',0,'trim'));

            // 来源页面
            $source_id = intval($this->request->post('source',0,'trim'));

            // 性别
            $sex = intval($this->request->post('sex',0,'trim'));

            // 区域
            $area_id = intval($this->request->post('area_id',0,'trim'));

            // 计划到店时间
            $book_to_time = strtotime($this->request->post('book_to_time',0,'trim'));

            if( cmf_is_mobile() && empty( $source_id )){
                $source_id = 2;
            }else{
                $source_id = 1;
            }

            $params['type'] = 1; // 咨询底价
            $params['name'] = $customer_name;
            $params['sex'] = $sex; // 性别id
            $params['telephone'] = $mobile;
            $params['brand_id'] = $brand_id;
            $params['series_id'] = $series_id;
            $params['style_id'] = $style_id;
            $params['dealers_id'] = $dealers_id;
            $params['area_id'] = $area_id?:4403;
            $params['source'] = $source_id;
            $params['book_to_time'] = $book_to_time;
            $params['createtime'] = time();
            // 验证必须参数
            try{
                $validate_res =  $this->validate($params,'Order.ask');
                if( $validate_res !== true ){
                    $this->result('',0,$validate_res);
                }
            }catch (ErrorException $exception){

                $this->result('',0,$exception->getMessage());
            }

            // 已经咨询过滤进行验证
            $wh = [];
            $wh['series_id'] = $series_id;
            $wh['type'] = 1;
            $wh['telephone'] = $mobile;
            $wh['dealers_id'] = $dealers_id;
            $wh['delete_time'] = 0;
            $wh['createtime'] = ['gt',time()-86400*7];// 过滤7天内咨询过的用户

            $vehicleOrderModel = new OrderModel();
            $count = $vehicleOrderModel->where($wh)->count();

            if($count > 0){
                $this->result('',-1,'您已经提交,请勿重复提交！');
            }

            try{
                $id = $vehicleOrderModel->addOrderBookData($params);
                if($id){
                    // 发邮件通知
                    $this->sendEmailNotice($params,'ask_price','获取底价','ask_price');
                    $store_name = getStoreName($dealers_id)?:'';
                    $this->result(['order_book_id'=>$id,'store_name'=>$store_name],1);
                }
            }catch(ErrorException $e){

                $this->result('',0,$e);
            }
        }
        $this->result('',0,'非法请求');
    }



    /**
     * 发邮件通知
     * @param $params
     * @throws ErrorException
     * @throws \think\exception\DbException
     */
     protected function sendEmailNotice($params,$type,$title,$email_tpl){
        if(config('vehicle.send_email')) { // 邮件通知开启
            //配置给4S店联系人发邮催
            $email_config['subject'] = $title;

            $vehicleSer  = new VehicleService();
            $series_info = $vehicleSer->getSeriesInfo($params['series_id']);

            if ( $params['source'] == 2 ) {
                $source = '移动页面';
            } elseif ($params['source'] == 1) {
                $source = 'PC页面';
            } elseif($params['source'] == 99){
                $source = '多品牌页面';
            }else{
                $source = '其他自定义页面';

            }
            $vars['customer_name'] = $params['name'];
            $vars['book_time'] = date('Y-m-d H:i:s',$params['createtime']);
            $vars['brand_name'] = $series_info['brand']['name']?:'';
            $vars['series_name'] = $series_info['name']?:'';
            $vars['telephone'] = $params['telephone'];
            $vars['source'] = $source;
            try{
                if( !empty($params['dealers_id']) ){
                    try{
                        $dealer = StoreModel::get($params['dealers_id']);
                        $store_name = $dealer['name'];
                        $vars['store_name'] = $store_name?:'';
                        if($dealer['email']){
                            $dealer_emails = array_filter(explode(',',$dealer['email']));
                            if($dealer_emails){
                                EmailService::send($vars,$type,$dealer_emails,$email_tpl);
                            }
                        }
                    }catch (ErrorException $foundException){
                        throw new ErrorException(1, '邮件发送失败'.$foundException->getMessage());
                    }
                }else{
                    //给配置固定邮箱发邮催
                    $book_car_manger_email = config('vehicle.book_order_manger_email');
                    if (!empty($book_car_manger_email)) {
                        if (isset($book_car_manger_email[$params['brand_id']]) && !empty($book_car_manger_email[$params['brand_id']]) ) {
                            EmailService::send($vars,$type,$book_car_manger_email[$params['brand_id']],$email_tpl);
                        }
                    }
                }
            }catch (ErrorException $exception){
                throw new ErrorException(1, '邮件发送失败'.$exception->getMessage());
            }
        }
        return ;
    }


    /**
     * 获取手机号码归属地
     */
    public function getmobileposition(){
        if($this->request->isAjax()){
            $mobile = $this->request->param('tel');
            if(!isTelNumber($mobile)){
                $this->result([],0,'电话号码非法');
            }

            $req_url = 'http://mobsec-dianhua.baidu.com/dianhua_api/open/location';
            $req_url .= '?tel='.$mobile;
            $result = httpRequest($req_url);
            if($result){
                $result = \Qiniu\json_decode($result,true);
                if($result['responseHeader']['status'] == 200){
                    if(isset($result['response'][$mobile])){
                        $this->result(['location'=>$result['response'][$mobile]['location']],1);
                    }
                }
                $this->result([],0,'未知电话号码');
            }
        }
        $this->result([],0,'非法请求');
    }

    /**
     * 品牌selectPage
     * @return mixed
     */
    public function brand(){
        $brandApi = new BrandApi();
        return $brandApi->selectPage(\request());
    }

    /**
     * 车系selectPage
     * @return \think\response\Json
     */
    public function series(){
        $seriesApi = new SeriesApi();
        return $seriesApi->selectPage(\request());
    }

    /**
     * 车型selectPage
     * @return \think\response\Json
     */
    public function style(){
        $styleApi = new StyleApi();
        return $styleApi->selectPage(\request());
    }

    /**
     * 4S店 selectPage
     * @return \think\response\Json
     */
    public function store(){
        $storeApi = new StoreApi();
        return $storeApi->selectPage(\request());
    }

    /**
     *  ajax异步 id转换名称
     */
    public function idToName(){

        if($this->request->isAjax()){
            $field = $this->request->param('field');
            $id = $this->request->param('id',0,'intval');
            if(empty($field)||empty($id)){
                $this->result([],0);
            }
            $fun = 'get'.ucfirst($field).'Name';
            if(function_exists($fun)){
                $name = $fun($id);
                $this->result(['name'=>$name],1);
            }
            $this->result([],0);
        }
    }

    /**
     *
     * 获取品牌下车系
     *
     */
    public function getSeriesCount(){
        if($this->request->isAjax()){
            $brand_id = $this->request->param('$brand_id',0,'intval');
            if(empty($brand_id)){
                $this->result(['total'=>0],1);
            }
            $seriesModel = new  SeriesModel();
            $total = $seriesModel
                ->where('brand_id','=',(int)$brand_id)
                ->where('delete_time','=',0)
                ->count();
            $this->result(['total'=>(int)$total],1);
        }
        $this->result(['total'=>0],1);
    }

    /**
     *
     * 获取车系下车型
     *
     */
    public function getStyleCount(){
        if($this->request->isAjax()){
            $series_id = $this->request->param('series_id',0,'intval');
            if(empty($series_id)){
                $this->result(['total'=>0],1);
            }
            $styleModel = new  StyleModel();
            $total = $styleModel
                ->where('series_id','=',(int)$series_id)
                ->where('delete_time','=',0)
                ->count();
            $this->result(['total'=>(int)$total],1);
        }

        $this->result(['total'=>0],1);
    }




}