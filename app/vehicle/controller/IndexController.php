<?php
/**
 * Created by PhpStorm.
 * User: yuhq
 * Date: 2018-11-17
 * Time: 09:54
 */

namespace app\vehicle\controller;


use app\vehicle\service\VehicleService;
use cmf\controller\HomeBaseController;

class IndexController extends HomeBaseController
{

    protected $vehicleSer = null;

    public function _initialize()
    {
        parent::_initialize();

        if(is_null($this->vehicleSer)){
            $this->vehicleSer =  new  VehicleService();
        }

    }

    /**
     *
     * 首页
     *
     */
    public function index(){

        return $this->fetch();
    }

    /**
     *
     * 车型展示
     *
     */
    public function listing(){

    }


    /**
     *
     * 关于我们
     *
     */
    public function about(){

    }

    /**
     *
     * 联系我们
     *
     */
    public function concat(){

    }

}