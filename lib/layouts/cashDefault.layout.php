<?php
class cashDefaultLayout extends waLayout
{
	public function execute() 
	{
		if( !wa()->getUser()->isAdmin() ) {
			if( wa()->getUser()->getRights('cash', 'reports') )
				$this->assign('reports_right', true);
			if( wa()->getUser()->getRights('cash', 'settings') )
				$this->assign('settings_right', true);
		}
		else
			$this->assign('is_admin', true);
	}
}