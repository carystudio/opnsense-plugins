<?php

require_once("filter.inc");
require_once("system.inc");
require_once("util.inc");
require_once("/usr/local/www/widgets/api/plugins/system.inc");

require_once("services.inc");
require_once("interfaces.inc");

class IndexController extends BaseController
{
    public function indexAction()
    {
    	if($this->session->has('username')){
			$this->response->redirect('/index/home', true);
		}else{
			$this->response->redirect('/login/showlogin', true);
		}
    }

	public function homeAction()
	{
		if(!$this->session->has('username')){
			$this->response->redirect('/login/showlogin', true);
		}
	}

	public function topAction()
	{

	}

	public function bottomAction()
	{

	}

	public function leftAction()
	{

	}

	public function titleAction()
	{
		$this->view->setVar('version', $this->config->csg2000p->version);
	}

	public function empty1Action()
	{

	}

	public function empty2Action()
	{

	}

	public function empty3Action()
	{

	}

	public function statusAction()
	{
	}
}

