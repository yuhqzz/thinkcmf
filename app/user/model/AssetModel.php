<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\user\model;

use think\Collection;
use think\Model;

class AssetModel extends Model
{


    public function getAssetByKey($key){
        if(empty($key)) return null;
        $data = $this
            ->where('file_key','=',':key')
            ->bind('key',$key)
            ->find();
       // $data = Collection::make($data)->toArray();

        return $data?$data->toArray():[];
    }
}