<?php
/**
 * Created by PhpStorm.
 * User: yuhq
 * Date: 2018-11-17
 * Time: 10:02
 */
namespace app\vehicle\model;
use think\Collection;
use think\Model;

class BrandModel extends Model
{

    protected $name = 'vehicle_brand';


    public function getBrandList(){
      $list =   $this->where('delete_time','=',0)
          ->order('first_char','asc')
          ->order('list_order','asc')
          ->select();

        return Collection::make($list)->toArray();
    }

}