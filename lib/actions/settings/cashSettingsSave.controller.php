<?php

class cashSettingsSaveController extends waJsonController 
{
	public function execute()
	{
		$app_config = wa()->getConfig()->getAppConfig('cash');
		$settings_file = $app_config->getAppConfigPath('cash', false);
		$settings_config = @include($settings_file);

		$settings = array();
		$settings['startmoney'] = $settings_config['startmoney'];
		$settings['title'] 		= waRequest::post('title');
		$settings['headtext'] 	= waRequest::post('headtext');
		$settings['limits'] 	= waRequest::post('limits', array());

		// Сохраняем настройки
		waUtils::varExportToFile($settings, $settings_file);
		
	}
}