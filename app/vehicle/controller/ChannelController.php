<?php
/**
 * Created by PhpStorm.
 * User: yuhq
 * Date: 2018-11-17
 * Time: 09:54
 */

namespace app\vehicle\controller;


use cmf\controller\HomeBaseController;

class ChannelController extends HomeBaseController
{

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     *
     * 专题
     * 首页
     */
    public function index(){

    }

    /**
     *
     * 专题详情页
     *
     * @param int $channel_id
     * @return mixed
     *
     */
    public function channel( $channel_id = 0 ){


        return $this->fetch();
    }



}