<?php
/**
 * Created by PhpStorm.
 * User: yuhq
 * Date: 2018-11-17
 * Time: 15:00
 */
namespace app\vehicle\service;


use app\vehicle\model\SeriesModel;

class VehicleService
{


    public function getSeriesInfo($series_id){
        if(empty($series_id)) return [];
        $series_id = (int)$series_id;
        $seriesModel = new SeriesModel();
        $data = $seriesModel
            ->with(['brand','style','images'])
            ->where('series_model.id','=',$series_id)
            ->find();
        //echo $seriesModel->getLastSql();
        return $data?$data:[];

    }





















}