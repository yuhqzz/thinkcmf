<?php
/**
 * Created by PhpStorm.
 * User: yuhq
 * Date: 2018-11-15
 * Time: 11:25
 *
 *
 * 专题
 *
 */

namespace app\portal\controller;


use app\portal\model\VehicleOrderModel;
use app\vehicle\model\StoreModel;
use cmf\controller\HomeBaseController;
use think\exception\ErrorException;

class ChannelController extends HomeBaseController
{

    protected $book_car_manger_email = ['7124046@qq.com'];

    /**
     *
     * 专题首页
     */
    public function index(){

    }

    /**
     * 专题列表
     */
    public function listing(){

    }

    /**
     *
     * 专题
     *
     *
     * @param int $channel_id
     * @return mixed
     */
    public function channel($channel_id =1){
        if(empty($channel_id)){
            $this->error('404');
        }

        if(cmf_is_mobile()){
            return $this->fetch('channel/wap/channel');
        }

        return $this->fetch();
    }


    /**
     *
     * 预约试驾表单提交
     *
     */
    public function ajaxSubmitBookCar(){
        if($this->request->isPost()){
           // $params = $this->request->param();
            // 品牌
            $brand_id = intval($this->request->post('brand_id',0,'trim'));
            if(empty($brand_id)){
                $this->result('',0,'请选择品牌');
            }
            //车系
            $series_id = intval($this->request->post('series_id',0,'trim'));

            if(empty($series_id)){
                $this->result('',0,'请选择车系');
            }

            //车型
            /*$style_id = intval($this->request->post('style_id',0,'trim'));

            if(empty($style_id)){
                $this->result('',0,'请选择车型');
            }*/

            // 客户姓名
            $customer_name = strval($this->request->post('customer_name',0,'trim'));

            if(empty($customer_name)){
                $this->result('',0,'请填写姓名');
            }
            //电话
            $mobile = strval($this->request->post('mobile',0,'trim'));
            if(empty($mobile)){
                $this->result('',0,'请填写电话号码');
            }
            if(strlen($mobile) !== 11 || !preg_match('/^(13[0-9]|15[0-9]|18[0-9])\d{8}$/',$mobile)){
                $this->result('',0,'请输入正确的手机号码格式');
            }

            $dealers_id = intval($this->request->post('dealer',0,'trim'));

            if(empty($dealers_id)){
                $this->result('',0,'请选择要预约的4S店');
            }

            // 来源页面
            $source_id = intval($this->request->post('source',0,'trim'));

            if( cmf_is_mobile() && empty($source_id)){
                $source_id = 1;
            }else{
                $source_id = 2;
            }



            $wh = [];
            $wh['series_id'] = $series_id;
            $wh['telephone'] = $mobile;
            $wh['dealers_id'] = $dealers_id;
            $wh['delete_time'] = 0;

            $vehicleOrderModel = new VehicleOrderModel();
            $count = $vehicleOrderModel->where($wh)->count();

            //echo $vehicleOrderModel->getLastSql();die;

            if($count > 0){
                $this->result('',-1,'您已经预约');
            }
            $add['type'] = 0;
            $add['name'] = $customer_name; // 姓名
            $add['sex'] = 0; // 性别id
            $add['telephone'] = $mobile;
            $add['brand_id'] = $brand_id;
            $add['series_id'] = $series_id;
            $add['style_id'] = 0;
            $add['dealers_id'] = $dealers_id; // 供应商
            $add['area_id'] = 4403;
            $add['source'] = $source_id;
            $add['book_to_time'] = '';
            $add['createtime'] = time();

            try{
                $id = $vehicleOrderModel->addOrderBookData($add);
                if($id){
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
                    if($dealers_id){
                        $email_tpl .='<tr>
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
                    $book_date = date('Y-m-d H:i:s',$add['createtime']);

                    $store = StoreModel::get($dealers_id);
                    $storeName = !empty($store)?$store['name']:'广汽丰田';

                    $book_car_style = '广汽丰田 - '.getSeriesName($series_id);
                    if( $source_id == 1){
                        $source = '移动页面';
                    }elseif($source_id == 2){
                        $source = 'PC页面';
                    }else{
                        $source = '其他自定义页面';
                    }
                    //配置固定邮箱
                    if(isset($this->book_car_manger_email) && !empty($this->book_car_manger_email)){
                        $body = sprintf($email_tpl,$customer_name,$book_date,$book_car_style,$storeName,$source,$mobile);
                        foreach($this->book_car_manger_email as $email_user){
                            cmf_send_email($email_user, $email_config['subject'], $body);
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
     * @return mixed
     */
    public function gf(){

        $brand_name = 'GF';
        $page_data = $this->_bookPageData($brand_name);
        if(empty($page_data)) return $this->error('参数出错','/');

        $dealer_info = $page_data['dealer'];
        $brand_id = $page_data['brand_id'];
        $series_data = $page_data['series'];
        $this->assign('seriesData',$series_data);
        $this->assign('brand_id',$brand_id);
        $this->assign('brand_name',$page_data['brand_name']);
        $this->assign('page',$page_data);
        $this->assign('dealer_info',json_encode($dealer_info));
        $this->assign('source_id',$brand_id);
        $this->assign('version',time());
        $this->assign('bn','gf');
        if(cmf_is_mobile()){
            return $this->fetch('channel/wap/gf');
        }else{
            return $this->fetch('channel/gf');
        }

    }



    /**
     * 获取落地页配置数据
     * @param $brand_name
     * @return array
     */
    private function _bookPageData($brand_name){
        $data =  file_get_contents(APP_PATH.'../data/conf/gf.json');
        $page_datas  = \Qiniu\json_decode($data,true);
        $brand_name = strtoupper($brand_name);
        return isset($page_datas[$brand_name])?$page_datas[$brand_name]:[];
    }




}