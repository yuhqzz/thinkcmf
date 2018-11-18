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
use app\vehicle\service\VehicleService;
use cmf\controller\BaseController;
use think\db\exception\DataNotFoundException;


class AjaxController extends BaseController
{


    /**
     *
     * 预约试驾表单提交
     *
     */
    public function SubmitBookOrder(){
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

            if( cmf_is_mobile() && empty( $source_id )){
                $source_id = 1;
            }else{
                $source_id = 2;
            }

            $params['type'] = 0;
            $params['name'] = $customer_name;
            $params['sex'] = 0; // 性别id
            $params['telephone'] = $mobile;
            $params['brand_id'] = $brand_id;
            $params['series_id'] = $series_id;
            $params['style_id'] = $style_id;
            $params['dealers_id'] = $dealers_id;
            $params['area_id'] = 4403;
            $params['source'] = $source_id;
            $params['book_to_time'] = '';
            $params['createtime'] = time();

           $validate_res =  $this->validate($params,'Order.book');
           if($validate_res !== true){
               $this->result('',0,$validate_res);

           }
           // 已经预约过滤进行验证
            $wh = [];
            $wh['series_id'] = $series_id;
            $wh['telephone'] = $mobile;
            $wh['dealers_id'] = $dealers_id;
            $wh['delete_time'] = 0;

            $vehicleOrderModel = new OrderModel();
            $count = $vehicleOrderModel->where($wh)->count();

            if($count > 0){
                $this->result('',-1,'您已经预约');
            }
            try{
                $id = $vehicleOrderModel->addOrderBookData($params);
                if($id){
                    if(config('vehicle.send_email')) { // 邮件通知开启
                        // 给运维人员发邮件通知
                        $email_config['subject'] = '预约试驾';
                        $email_tpl = '<table border="0" cellspacing="0" cellpadding="0" width="60%%" align="center" >
        <tr>
            <td width="17%%" align="left" height="36" style="font-size: 14px;border-top: 1px solid  #cdd1dc;
border-left: 1px solid  #cdd1dc; padding-left: 5px;">客&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;户:</td>
            <td width="83%%" align="left" height="36" style="font-size: 14px;color: #40485B;border-top: 1px solid  #cdd1dc;
border-right: 1px solid  #cdd1dc;">%s</td>
        </tr>
        <tr>
            <td colspan="2" align="left" height="36" style="padding-left:30px;font-size: 14px;border-right: 1px solid #cdd1dc;border-left: 1px solid  #cdd1dc;color:#40485B;">于 %s 预约试驾！</td>
        </tr>
        <tr>
            <td width="17%%" style="font-size: 14px;
border-left: 1px solid  #cdd1dc; padding-left: 5px;">预约车型:</td>
            <td width="83%%" align="left" height="36" style="font-size: 14px; color: #40485B;
border-right: 1px solid  #cdd1dc;" >%s</td>
        </tr>';
                        if ($dealers_id) {
                            $email_tpl .= '<tr>
            <td width="17%%" style="font-size: 14px;
border-left: 1px solid  #cdd1dc; padding-left: 5px;">4S店:</td>
            <td width="83%%" align="left" height="36" style="font-size: 14px; color: #40485B;
border-right: 1px solid  #cdd1dc;" >%s</td></tr>';
                        }
                        $email_tpl .= '
        <tr>
            <td width="17%%" style="font-size: 14px;
border-left: 1px solid  #cdd1dc; padding-left: 5px;">来源页面:</td>
            <td width="83%%" align="left" height="36" style="font-size: 14px; color: #40485B;
border-right: 1px solid  #cdd1dc;" >%s</td>
        </tr>
        <tr>
            <td width="17%%" style="font-size: 14px;border-bottom: 1px solid  #cdd1dc;
border-left: 1px solid  #cdd1dc; padding-left: 5px;">联系电话:</td>
            <td width="83%%" align="left" height="36" style="font-size: 14px;color: #40485B;border-bottom: 1px solid  #cdd1dc;
border-right: 1px solid  #cdd1dc;" >%s</td>
        </tr>
    </table>';
                        $book_date = date('Y-m-d H:i:s', $params['createtime']);


                        $vehicleSer  = new VehicleService();
                        $series_info = $vehicleSer->getSeriesInfo($params['series_id']);



                        $book_car_style = '广汽丰田 C-HR';
                        if ( $source_id == 2 ) {
                            $source = '移动页面';
                        } elseif ($source_id == 1) {
                            $source = 'PC页面';
                        } else {
                            $source = '其他自定义页面';
                        }

                        //配置给4S店联系人发邮催
                        if( !empty($dealers_id) ){
                            try{
                                $dealer = StoreModel::get($dealers_id);
                                if($dealer['email']){
                                    $body = sprintf($email_tpl,$customer_name,$book_date,$book_car_style,$dealer['name'],$source,$mobile);
                                    $dealer_emails = explode(',',$dealer['email']);
                                    if($dealer_emails){
                                        foreach ( $dealer_emails as $email){
                                            if(empty($email)){
                                                continue;
                                            }
                                            cmf_send_email($email, $email_config['subject'], $body);
                                        }
                                    }
                                }
                            }catch (DataNotFoundException $foundException){

                            }
                        }else{
                            //给配置固定邮箱发邮催
                            $book_car_manger_email = config('vehicle.book_order_manger_email');

                            if (!empty($book_car_manger_email)) {
                                if (isset($book_car_manger_email[$params['brand_id']]) && !empty($book_car_manger_email[$params['brand_id']]) ) {
                                    $body = sprintf($email_tpl, $customer_name, $book_date, $book_car_style, $source, $mobile);
                                    foreach ($book_car_manger_email[$params['brand_id']] as $email_user) {
                                        cmf_send_email($email_user, $email_config['subject'], $body);
                                    }
                                }
                            }
                        }
                    }
                    $this->result(['order_book_id'=>$id],1);
                }
            }catch(ErrorException $e){


                $this->result('',0,$e);
            }
        }

        $this->result('',0,'非法请求');
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
        return $brandApi->seletpage(\request());
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