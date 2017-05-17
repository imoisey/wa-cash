<?php
class cashDefaultViewController extends waDefaultViewController
{
    public function execute()
    {
        $this->setLayout( new cashDefaultLayout() );
        $page = $this->getPage();
      	// Передаем данные в шаблон
        $this->getLayout()->assign('page', $page);
        // Запускаем action
        if( $page != 'events' )
			$this->executeAction( $this->getAction() );
		else
			$this->executeAction( new cashEventsAction() );
    }


    protected function getPage() 
	{
		$module = waRequest::get('module', 'events');
		// Проверка прав
		if( $module == "reports" )
			$default_page = 'reports';
		elseif( $module == "settings" and wa()->getUser()->getRights('cash', 'settings') )
			$default_page = 'settings';
		else
			$default_page = 'events';
		$page = $default_page;
		return $page;
	}

}