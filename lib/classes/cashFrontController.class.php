<?php
class cashFrontController extends waFrontController
{
    public function dispatch()
    {
        $env = $this->system->getEnv();
        if($env == 'backend') {
            $module = waRequest::get($this->options['module']);
            if(empty($module)) {
                $cash_routing = new waRouting($this->system, array(
                    'default'   => array(
                        array(
                            'url'       => wa()->getConfig()->systemOption('backend_url').'/cash/',
                            'app'       => 'cash'
                        ),
                    ),
                ));
                $cash_routing->dispatch();
                if(!waRequest::param('module')) {
                    throw new waException('Page not found', 404);
                }
            }
        }
        parent::dispatch();
    }
}