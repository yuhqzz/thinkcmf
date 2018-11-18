<?php
/**
 * Created by PhpStorm.
 * User: yuhq
 * Date: 2018-11-17
 * Time: 10:05
 */

namespace app\vehicle\model;


use think\Model;

class StyleModel extends Model
{


    protected $name = "vehicle_style";


    //品牌
    public function brand(){
        return $this->belongsTo('BrandModel', 'brand_id', 'id', [], 'INNER')->setEagerlyType(0);
    }

    // 车系
    public function series()
    {
        return $this->belongsTo('SeriesModel', 'series_id', 'id',[], 'INNER')->setEagerlyType(0);
    }

    // 图片
    public function images(){
        return $this->hasMany('ImagesModel', 'resource_id', 'id')->where('Images.type','=',2);
    }

}