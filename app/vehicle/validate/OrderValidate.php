<?php
namespace app\vehicle\validate;

use think\Validate;

class OrderValidate extends Validate
{

    protected $rule = [
        'name'  => 'require',
        'type'  => 'require',
        'telephone'  => 'require|isMobile',
        'brand_id'  => 'require',
        'series_id'  => 'require',
        'style_id'  => 'require',
        'dealers_id'  => 'require',
        'area_id'  => 'require',
        'source'  => 'require',
        'book_to_time'  => 'require',
    ];
    protected $message = [
        'name.require' => '姓名不能不能为空',
        'telephone.require' => '电话不能为空',
        'telephone.isMobile' => '请输入正确的电话号码',
        'brand_id.require' => '品牌不能为空',
        'series_id.require' => '车型不能为空',
        'dealers_id.require' => '4S不能为空',
        'area_id.require' => '城市不能为空',
        'source.require' => '来源不能为空',
        'book_to_time.require' => '计划到店时间不能为空',
    ];

    protected $scene = [
        'add'  => ['name','type','telephone','brand_id','series_id','dealers_id'],
        'edit' => ['name','type','telephone','brand_id','series_id','dealers_id'],
        'book' =>['name','type','telephone','brand_id','series_id','dealers_id']
    ];

    protected function isMobile($value,$rule,$data){

        if( strlen($value) !== 11 || !preg_match('/^(13[0-9]|15[0-9]|18[0-9])\d{8}$/',$value)){
            return '请输入正确的电话号码';
        }
        return true;
    }



}