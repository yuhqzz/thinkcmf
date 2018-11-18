<?php
/**
 * Created by PhpStorm.
 * User: yuhq
 * Date: 2018-11-17
 * Time: 10:08
 */

namespace app\vehicle\model;


use think\Model;

class OrderModel extends Model
{

    protected $name = "vehicle_order";

    /**
     *
     * 添加订单
     * @param array $data
     * @return bool|int|string
     */
    public function addOrderBookData($data = []){
        if(empty($data)) return false;
        $add['type'] = isset($data['type'])?$data['type']:0;
        $add['name'] = trim($data['name']); // 姓名
        $add['sex'] = intval($data['sex']); // 性别id
        $add['telephone'] = trim($data['telephone']);
        $add['ip'] = get_client_ip(0,true); // ip
        $add['brand_id'] = isset($data['brand_id'])?$data['brand_id']:0; // 车系
        $add['series_id'] = isset($data['series_id'])?$data['series_id']:0; // 车系
        $add['style_id'] = isset($data['style_id'])?$data['style_id']:0; // 车型
        $add['dealers_id'] = isset($data['dealers_id'])?$data['dealers_id']:0; // 供应商
        $add['area_id'] = isset($data['area_id'])?$data['area_id']:0;
        $add['source'] = isset($data['source'])?$data['source']:0;
        $add['book_to_time'] = isset($data['book_to_time'])?strtotime($data['book_to_time']):'';
        $add['createtime'] = $data['createtime']; // 发布时间
        $add['updatetime'] = 0; // 更新时间
        $add['remark'] = isset($data['remark'])?htmlspecialchars($data['remark'],ENT_QUOTES):''; //备注
        $id = $this->isUpdate(false)->allowField(true)->insertGetId($add);
        return $id;
    }

}