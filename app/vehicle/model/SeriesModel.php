<?php
/**
 * Created by PhpStorm.
 * User: yuhq
 * Date: 2018-11-17
 * Time: 10:04
 */

namespace app\vehicle\model;


use think\Collection;
use think\Model;

class SeriesModel extends Model
{
    protected $name = "vehicle_series";

    //品牌
    public function brand(){
        return $this->belongsTo('BrandModel', 'brand_id', 'id', [], 'INNER')->setEagerlyType(0)->field('name');
    }

    // 车型
    public function style()
    {
        return $this->hasMany('StyleModel', 'series_id', 'id');
    }

    // 图片
    public function images(){
        return $this->hasMany('ImagesModel', 'resource_id', 'id')->where('Images.type','=',1);
    }

    /**
     * 获取品牌下的车系
     * @param $brand_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSeriesByBrandId($brand_id){
        if(empty($brand_id)) return [];
        $list = $this->where('brand_id','=',(int)$brand_id)
            ->where('delete_time','=',0)
            ->order('is_hot','desc')
            ->order('id','desc')
            ->select();

       return Collection::make($list)->toArray();
    }









}