<?php
/**
 * Created by PhpStorm.
 * User: yuhq
 * Date: 2018-11-17
 * Time: 15:00
 */
namespace app\vehicle\service;


use app\vehicle\model\SeriesModel;
use think\db\exception\DataNotFoundException;

class VehicleService
{


    public function getSeriesInfo($series_id){
        if(empty($series_id)) return [];
        try{
            $series_id = (int)$series_id;
            $seriesModel = new SeriesModel();
            $data = $seriesModel
                ->with(['brand'])
                ->where('series_model.id','=',$series_id)
                ->find();

        }catch (DataNotFoundException $dataNotFoundException){
            $data = [];
        }

        //echo $seriesModel->getLastSql();die;
        return $data?$data->toArray():[];

    }





















}