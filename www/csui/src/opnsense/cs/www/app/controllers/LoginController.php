<?php

require_once("auth.inc");

class LoginController extends BaseController
{
    public function showloginAction()
    {
	    $this->request->getPost();
    }

    public function indexAction(){

    }

    public function dologinAction()
    {
        $username = $this->request->getPost("username", "striptags", null);
        $password = $this->request->getPost("password", "striptags", null);
        if('admin'==$username){
            $username = 'root';
        }else if('root'==$username){
            $username = 'admin';
        }
        
        if('csrecovery'!=$username){
            $res = authenticate_user($username, $password);
        }else{
            $res = false;
        }


        if($res){
            $this->session->set('username',$username);
            $this->response->redirect('http://'.$_SERVER['HTTP_HOST'].'/index/index');
        }else{
            $this->view->setVar('msg', '用户名或者密码不正确');
            $this->view->pick("login/index");
        }

    }

    public function logoutAction(){
        $this->session->destroy(true);
        $this->response->redirect('http://'.$_SERVER['HTTP_HOST'].'/index/index');
    }
}

