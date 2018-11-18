<?php
namespace app\vehicle\validate;

use think\Validate;
use think\db;

class SeriesValidate extends Validate
{
    protected $rule = [
        'name'  => 'require|checkNameUniqueValue',
        'brand_id'  => 'require',
    ];
    protected $message = [
        'name.require' => '品牌名称不能为空',
        'brand_id.require' => '品牌不能为空',
    ];

    protected $scene = [
        'add'  => ['name,brand_id'],
        'edit' => ['name,brand_id'],
    ];
    protected function checkNameUniqueValue($value,$rule,$data){
        if(isset($data['id'])){
            //编辑
            $wh['id'] = ['neq',$data['id']];
        }
        $wh['name'] = trim($value);
        $wh['delete_time'] = 0;
        $wh['brand_id'] = $data['brand_id'];
        $res =  Db::name('vehicle_series')->where($wh)->find();
        if($res){
            return '该品牌下已经存在该车系，不要重复添加';
        }
        return true;
    }

}