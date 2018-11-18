<?php
namespace app\vehicle\validate;

use think\Validate;
use think\db;

class BrandValidate extends Validate
{
    protected $rule = [
        'name'  => 'require|checkNameUniqueValue',
        'icon'  => 'require',
        'first_char'  => 'require|checkFirstCharValue',
    ];
    protected $message = [
        'name.require' => '品牌名称不能为空',
        'icon.require' => '品牌LOGO不能为空',
        'first_char.require' => '品牌首字母不能为空',
    ];

    protected $scene = [
         'add'  => ['name,icon,first_char'],
         'edit' => ['name,first_char'],
    ];

    protected function checkFirstCharValue($value){
        $char =range('A','Z');
        if(!in_array(strtoupper($value),$char)){
            return '首字母非法,必须是在【'.implode(',',$char).'】中';
        }
        return true;
    }

    protected function checkNameUniqueValue($value,$rule,$data){
        if(isset($data['id'])){
            //编辑
            $wh['id'] = ['neq',$data['id']];
        }
        $wh['name'] = trim($value);
        $wh['delete_time'] = 0;
        $res =  db::name('vehicle_brand')->where($wh)->find();
        if($res){
            return '该品牌已经存在，不要重复添加';
        }
        return true;
    }



}