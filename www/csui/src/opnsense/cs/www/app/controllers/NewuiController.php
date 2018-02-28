<?php

require_once("filter.inc");
require_once("system.inc");
require_once("util.inc");
require_once("/usr/local/www/widgets/api/plugins/system.inc");

require_once("services.inc");
require_once("interfaces.inc");

class NewuiController extends BaseController
{
    public function indexAction()
    {
    	if($this->session->has('username')){
			$this->response->redirect('/newui/index.html', true);
		}else{
			$this->response->redirect('/newui/login.html', true);
		}
    }

}

