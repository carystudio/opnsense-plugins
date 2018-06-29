<?php

class CgiController extends BaseController
{
    public function indexAction()
    {
        $result = array();
        $text = $this->request->getRawBody();
        $para = json_decode($text, true);
        if("getSysStatusCfg" == $para['topicurl']){
            $lanInfo = System::getLanIp();
            $result = array('lanIp'=>$lanInfo['Ip']);
        }
        echo json_encode($result);
    }
}

