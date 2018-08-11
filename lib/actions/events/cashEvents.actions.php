<?php
class cashEventsActions extends waJsonActions
{
	/**
	 * Показываем все события
	 * @return [type] [description]
	 */
	public function allAction() 
	{
		$limit = waRequest::post('limit', 10);
		$start = waRequest::post('start', 0);
		// Получаем последние 100 событий
		$m_event = new cashEventModel();
		$events = $m_event->getAll($limit, $start);
		if( count($events) > 0 )
			$this->_display($events);
		else
			$this->status = 'none';
		return true;
	}

	/**
	 * Показывает только штрафы пользователя
	 * @return [type] [description]
	 */
	public function penaltyAction()
	{
		// Получаем объект пользователя пользователя
		$user = wa()->getUser();
		$m_event = new cashEventModel();
		// Получаем все события, где есть штрафы для пользователя
		$events = $m_event->getEventsOperationContact($user->getId(), '-');
		// Отображаем события
		$this->_display($events);
	}

	/**
	 * Показываем только премии пользователя
	 * @return [type] [description]
	 */
	public function premiumAction()
	{
		// Получаем объект пользователя пользователя
		$user = wa()->getUser();
		$m_event = new cashEventModel();
		// Получаем все события, где есть штрафы для пользователя
		$events = $m_event->getEventsOperationContact($user->getId(), '+');
		// Отображаем события
		$this->_display($events);
	}

	/**
	 * Показываем только за определенный период
	 * @return [type] [description]
	 */
	public function periodAction()
	{
		$start = waRequest::post('start');
		$finish = waRequest::post('finish');
		if(!wa()->getUser()->isAdmin()) {
			$contact_id = array(wa()->getUser()->getId());
		} else {
			$contact_id = waRequest::post('contact_id');
		}

		if( empty($start) and empty($finish) )
			return false;
		$m_event = new cashEventModel();
		$events = $m_event->getEventsPeriod($start, $finish, $contact_id);
		// Отображаем события
		$this->_display($events);
	}

	/**
	 * Событие приходва в кассу
	 * @return [type] [description]
	 */
	public function entryAction()
	{
		// !!! Проверка на кассира должна быть 

		// Получаем параметры с суммойи комментарием
		$total = waRequest::post('total', '0');
		$comment = waRequest::post('comment');
		if( $total == 0 )
			return false;
		$data = array();
		$data['total'] = $total;
		$data['comment'] = "<p>Приход в кассу: <b>{$total}</b>р.<br><br>{$comment}</p>";
		$data['operations'] = array(
			array(
				'contact_id' => 0,
				'amount'	=> $total
			),
		);
		// Добавляем событие в БД
		$m_event = new cashEventModel();
		$m_event->add($data);

		$this->response = $data;
		return true;
	}

	/**
	 * Событие расхода из кассы
	 * @return [type] [description]
	 */
	public function outlayAction()
	{
		// !!! Проверка на кассира должна быть 
		
		// Получаем параметры с суммойи комментарием
		$total = waRequest::post('total', '0');
		$comment = waRequest::post('comment');
		if( $total == 0 )
			return false;
		$data = array();
		$total = $total * -1;
		$data['total'] = $total;
		$data['comment'] = "<p>Расход из кассы: <b>{$total}</b>р.<br><br>{$comment}</p>";
		$data['operations'] = array(
			array(
				'contact_id' => 0,
				'amount'	=> $total
			),
		);
		// Добавляем событие в БД
		$m_event = new cashEventModel();
		$m_event->add($data);

		$this->response = $data;
		return true;
	}

	private function _display($events) 
	{
		// Подключаем шаблонизатор
		$view = wa()->getView();
		$view->assign('events', $events);
		$output = $view->fetch(wa()->getAppPath('templates/actions/events/')."EventsItem.html");
		$this->response = $output;
	}
	
}