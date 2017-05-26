<?php
class cashSettingsAction extends waViewAction
{
	public function execute()
	{	
		// Получаем настройки приложения
		$app_config = wa()->getConfig()->getAppConfig('cash');
		$app_settings = include( $app_config->getAppConfigPath('cash', false) );

		$userlist = json_decode(cashViewHelper::getUserListJSON('cash', true));
		$this->view->assign('userlist', $userlist);
		$this->view->assign('settings', $app_settings);
	}
}