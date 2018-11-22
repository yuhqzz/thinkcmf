<?php
/**
 * Created by PhpStorm.
 * User: yuhq
 * Date: 2018-11-22
 * Time: 09:27
 */

namespace app\vehicle\service;


use think\exception\ErrorException;
use think\exception\TemplateNotFoundException;
use think\Log;
use think\View;

class EmailService
{


    /**
     *
     * 发送邮件
     * @param $vars 邮件模板变量
     * @param $type 邮件类型
     * @param $to_users 邮件模板
     * @param $tpl 邮件模板
     *
     */
    public static function send($vars,$type,$to_users,$tpl){

      //  $tpl = 'test_drive.html';

        $template = APP_PATH.'vehicle/tpl/'.$tpl.'.html';
        $subject = self::_getTitle($type);
        // 模板不存在 抛出异常
        if (!is_file($template)) {
            throw new TemplateNotFoundException('template not exists:' . $template, $template);
        }
        try{
            $view =  View::instance();
            $content =  $view->engine('Think')->fetch($template,$vars);

        }catch (ErrorException $exception){
            throw new ErrorException('template parse:' . $template, $template);
        }
        if(is_array($to_users)){
            foreach ($to_users as $to_user){
                $res = cmf_send_email($to_user, $subject, $content);
                if($res['error'] == 1){
                    Log::record($res['message']);
                }
            }
        }else{
               $res = cmf_send_email($to_users, $subject, $content);
            if($res['error'] == 1){
                Log::record($res['message']);
            }
        }

    }

    /**
     *
     * 获取邮件标题
     * @param $type
     * @return string
     */
    private static function _getTitle($type){
        switch (strtolower($type)){
            case 'test_drive':
                $title = '预约试驾';
                break;
            case 'ask_price':
                $title = '咨询底价';
                break;
            default:
                $title =   '系统邮件';
        }
        return $title;
    }


}