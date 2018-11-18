<?php
namespace app\vehicle\validate;

use think\Validate;

class StyleValidate extends Validate
{
    protected $rule = [
       'name'       => 'require',
       'brand_id'   => 'require',
       'series_id'  => 'require',
       'level'      => 'require'
    ];
    protected $message = [
        'name.require' => '车型名称不能为空',
        'brand_id.require' => '品牌不能为空',
        'series_id.require' => '车型不能为空',
        'level.require' => '车规不能为空'
    ];

    protected $scene = [

    ];
}