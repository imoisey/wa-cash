<?php
class cashEventsAddController extends waJsonController
{
	public function execute()
	{
		// Данные из формы
		$comment = waRequest::post('comment');
		$contacts = waRequest::post('contacts');

		if(empty($comment) or !is_array($contacts)) {
			$this->status = 'error';
			return false;
		}


		$m_event = new cashEventModel();

		// Получаем настройки приложения
		$app_config = wa()->getConfig()->getAppConfig('cash');
		$app_settings = include( $app_config->getAppConfigPath('cash', false) );
		$user_id = wa()->getUser()->getId();

		// Лимиты
		$limit_user = array();
		$limit_user['awards'] 	= isset($app_settings['limits'][$user_id]['awards']) ? $app_settings['limits'][$user_id]['awards'] : 500;
		$limit_user['fine'] 	= isset($app_settings['limits'][$user_id]['fine']) ? $app_settings['limits'][$user_id]['fine'] : 0;
		$limit_user['cnt'] 		= isset($app_settings['limits'][$user_id]['cnt']) ? $app_settings['limits'][$user_id]['cnt'] : 10;

		// Остатки по лимитам
		$limit = $m_event->getLimitUser($user_id);
		$free_limit = array(
			'awards' => $limit_user['awards'] == 0 ? 'null' : $limit_user['awards'] - $limit['awards'],
			'fine' => $limit_user['fine'] == 0 ? 'null' : $limit_user['fine'] + $limit['fine'],
			'man' => $limit_user['cnt'] == 0 ? 'null' : $limit_user['cnt'] - $limit['cnt']
		);

		if($free_limit['awards'] < 0)
			$free_limit['awards'] = 0;
		if($free_limit['man'] < 0)
			$free_limit['man'] = 0;
		if($free_limit['fine'] > 0)
			$free_limit['fine'] = 0;

		// Заменяем все ссылки на открытие в новом окне
		$comment = preg_replace("/href=\"(.*)\"/", "href=\"$1\" target=\"blank\"", $comment);

		// Формируем данные
		$event_data = array();
		$event_data['comment'] = $comment;
		// Проходим по каждой операции
		$total = 0;
		// Убираем пустышки
		$contacts = array_filter($contacts);
		// Кол-во премий
		$cnt_awards = 0;
		// Кол-во штрафов
		$cnt_fine = 0;
		// Cумма премий
		$total_awards = 0;
		// Сумма штрафа
		$total_fine = 0;
		foreach ($contacts as $key => $contact) {
			// Проверка на себя. Себе нельзя выписать штраф\премию
			if($contact['contact_id'] == wa()->getUser()->getId())
				continue;

			// amount не может быть меньше единицы
			if($contact['amount'] <= 0) {
				unset($contacts[$key]);
				continue;
			}
			if( $contact['operation'] == '+' ) {
				$total += $contact['amount'];
				$total_awards += $contact['amount'];
				$cnt_awards++;
			}
			else {
				$total -= $contact['amount'];
				$total_fine += $contact['amount'];
				$cnt_fine++;
			}
				
		}
		$event_data['total'] = $total * -1;
		$event_data['operations'] = $contacts;

		if(	($free_limit['awards'] != 'null' && $total_awards > $free_limit['awards']) ||
			($free_limit['fine'] != 'null' && $total_fine > $free_limit['fine']) ||
			$free_limit['man'] != 'null' && $cnt_awards > $free_limit['man'])
			throw new waException('Лимиты превышены.', 500);

		// Добавляем событие в БД
		$m_event->add($event_data);

		$this->response = true;
	}
	
}