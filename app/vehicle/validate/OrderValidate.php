<?php
namespace app\vehicle\validate;

use think\Validate;

class OrderValidate extends Validate
{

    protected $message = [
        'name.require' => '姓名不能不能为空',
        'telephone.require' => '电话不能为空',
        'telephone.isMobile' => '请输入正确的电话号码',
        'brand_id.require' => '品牌不能为空',
        'brand_id.notZero' => '品牌不能为空',
        'series_id.require' => '车型不能为空',
        'series_id.notZero' => '车型值不能为0',
        'style_id.require'  => '车型不能为空',
        'style_id.notZero' => '车型值不能为0',
        'dealers_id.require' => '4S不能为空',
        'dealers_id.notZero' => '4S值不能为0',
        'area_id.require' => '城市不能为空',
        'source.require' => '来源不能为空',
        'book_to_time.require' => '计划到店时间不能为空',
    ];
    protected $rule = [
        'name'  => 'require',
        'type'  => 'require',
        'telephone'  => 'require|isMobile',
        'brand_id'  => 'require|notZero',
        'series_id'  => 'require|notZero',
        'style_id'  => 'require|notZero',
        'dealers_id'  => 'require|notZero',
        'area_id'  => 'require',
        'source'  => 'require',
        'book_to_time'  => 'require',
    ];

    protected $scene = [
        'add'  => ['name','type','telephone','brand_id','series_id','dealers_id'],
        'edit' => ['name','type','telephone','brand_id','series_id','dealers_id'],
        'book' =>['name','type','telephone','brand_id','series_id','dealers_id'],
        'ask' =>['name','type','telephone','brand_id','series_id','dealers_id'],
    ];

    protected function isMobile($value,$rule,$data){

        if( strlen($value) !== 11 || !preg_match('/^(13[0-9]|15[0-9]|18[0-9])\d{8}$/',$value)){
            return '请输入正确的电话号码';
        }
        return true;
    }

    protected function notZero($value,$rule,$data){
       if($value == 0){
           return '字段 :attribute 不能为空';
       }
       return true;
    }



}