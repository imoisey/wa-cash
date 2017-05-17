<?php
class cashEventsAction extends waViewAction
{
	public function execute()
	{
		// Получаем настройки приложения
		$app_config = wa()->getConfig()->getAppConfig('cash');
		$app_settings = include( $app_config->getAppConfigPath('cash', false) );

		// Получаем последние 100 событий
		$m_event = new cashEventModel();
		$events = $m_event->getAll(100);

		// Получаем список пользователей
		$user = wa()->getUser();
		$users = $user->getUsers();

		// Данные пользователя
		$user_data = array(
			'id'	=> $user->getId(),
			'photo' => $user->getPhoto(),
			'name'	=> $user->getName()
		);

		$limit_user = array();
		$limit_user['awards'] 	= isset($app_settings['limits'][$user_data['id']]['awards']) ? $app_settings['limits'][$user_data['id']]['awards'] : 500;
		$limit_user['fine'] 	= isset($app_settings['limits'][$user_data['id']]['fine']) ? $app_settings['limits'][$user_data['id']]['fine'] : 0;
		$limit_user['cnt'] 		= isset($app_settings['limits'][$user_data['id']]['cnt']) ? $app_settings['limits'][$user_data['id']]['cnt'] : 2;

		$limit = $m_event->getLimitUser($user_data['id']);
		$user_data['limits'] = array(
			'awards' => $limit_user['awards'] == 0 ? 'null' : $limit_user['awards'] - $limit['awards'],
			'fine' => $limit_user['fine'] == 0 ? 'null' : $limit_user['fine'] + $limit['fine'],
			'man' => $limit_user['cnt'] == 0 ? 'null' : $limit_user['cnt'] - $limit['cnt']
		);

		// Остаток кассы
		$app_settings['cash_total'] = $m_event->getTotalAmount();

		$this->view->assign('user_data', $user_data);
		$this->view->assign('events', $events);
		$this->view->assign('settings', $app_settings);
	}
	
}