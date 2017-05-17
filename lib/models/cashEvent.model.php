<?php

class cashEventModel extends waModel
{
	protected $table = 'cash_event';

	/**
	 * Получаем данные о событии
	 * @param  $event_id
	 * @return void
	 */
	public function get($event_id)
	{
		if( !is_numeric($event_id) )
			return false;
		$event_data = $this->getById($event_id);
		$event_data = $this->getDetail($event_data);
		return $event_data;
	}

	/**
	 * Добавляет событие в БД
	 * @param $data
	 */
	public function add($data) 
	{
		if( !is_array($data) )
			return false;

		$data['contact_id'] = ($data['contact_id']) ?: wa()->getUser()->getId();
		$data['status'] = ($data['status']) ?: 1;
		$data['pub_date'] = ($data['pud_data']) ?: date('Y-m-d H:i:s');

		// Добавляем запись события в БД
		$event_id = parent::insert($data);
		// Добавляем операции события
		if( is_array($data['operations']) ) {
			$m_event_operation = new cashEventOperationModel();
			foreach ($data['operations'] as $operation) {
				if( $operation['amount'] == 0 )
					continue;

				$oper_data = array();
				$oper_data['event_id'] = $event_id;
				$oper_data['contact_id'] = $operation['contact_id'];
				$oper_data['amount'] = ($operation['operation'] == '+') ? $operation['amount'] : $operation['amount'] * -1;
				$m_event_operation->insert($oper_data);
			} 
		}
		return true;
	}


	/**
	 * Получаем все события
	 * @param  int $limit Ограничение на количество отображаемых событий
	 * @param  int $start Позиция с которой начинать выборку
	 * @return void
	 */
	public function getAll($limit = 0, $start = 0) 
	{
		// Проверяем ограничения
		$limit = ($limit == 0) ? "" : "{$start},{$limit}";
		$event_data = $this->select("*")->limit($limit)->order('pub_date DESC')->fetchAll();
		if( !is_array($event_data) )
			return false;
		foreach ($event_data as $k => $event) {
			$event_data[$k] = $this->getDetail($event);
		}
		return $event_data;
	}


	/**
	 * Расчитывает остаток суммы событий
	 * @return void
	 */
	public function getTotalAmount()
	{
		return $this->query("SELECT SUM(`total`) as summ FROM {$this->table}")->fetchField('summ');
	}


	/**
	 * Возвращает события, которые оставил пользователь
	 * @param  int 		$contact_id ID пользователя
	 * @param  boolean 	$type 		Тип операции
	 * @return array    Массив событий
	 */
	public function getEventsContact($contact_id = null) 
	{
		if( !is_numeric($contact_id) )
			$contact_id = wa()->getUser()->getId();
		$events = $this->getByField('contact_id', $contact_id, true);
		return $events;
	}

	/**
	 * Возвращает события в которых участвовал пользователь
	 * @param  int $contact_id 		ID пользователя
	 * @param  string $type       	Тип операции
	 * @return array             	Массив событий
	 */
	public function getEventsOperationContact($contact_id = null, $type = null)
	{
		if( !is_numeric($contact_id) )
			$contact_id = wa()->getUser()->getId();
		$m_event_operation = new cashEventOperationModel();
		$operations = $m_event_operation->getOperationContact($contact_id, $type, 'DISTINCT event_id');
		// Проходим по каждой операции
		$events_data = array();
		foreach ($operations as $operation) {
			$event_data[] = $this->get($operation['event_id']);
		}
		// Вытаскиваем нужные события
		return $event_data;
	}

	public function getEventsPeriod($start = null, $finish = null)
	{
		if( $start and $finish )
			$where = "pub_date BETWEEN s:start AND s:finish";
		else if( $start )
			$where = "pub_date >= s:start";
		else if( $finish )
			$where = "pub_date <= s:finish";
		else
			return false;

		$events = $this->select('id')->where($where, array('start'=>$start, 'finish'=>$finish))->order('pub_date DESC')->fetchAll();
		if( !is_array($events) )
			return false;
		$events_data = array();
		foreach ($events as $event) {
			$events_data[] = $this->get($event['id']);
		}
		return $events_data;
	}

	/**
	 * Получает остаток на начало периода
	 * @return void
	 */
	public function getAmountBeginPeriod($begin_period = false)
	{
		if($begin_period == false)
			return false;
		$sql = "SELECT SUM(`total`) as total FROM {$this->table} WHERE  `pub_date` <  s:begin_period";
		$total = $this->query($sql, array('begin_period'=>$begin_period))->fetchField('total');
		if(!is_numeric($total))
			return 0;
		return $total;
	}

	/**
	 * Получаем дополнительные данные для события
	 * @param  array $event параметры события
	 * @return array        детали события
	 */
	private function getDetail($event)
	{
		if( !is_array($event) )
			return false;
		$detail = $event;
		$contact = new waContact($event['contact_id']);
		$detail['contact'] = array(
			'name' 	=> $contact->getName(),
			'avator' => $contact->getPhoto(60),
		);
		// Получаем операции для события
		$m_event_operation = new cashEventOperationModel();
		$detail['operations'] = $m_event_operation->getOperationEvent($event['id']);
		return $detail;
	}


	/**
	 * Возвращает данные по событиям пользователя
	 *
	 * @param integer $contact_id
	 * @return void
	 */
	public function getLimitUser($contact_id) {
		$sql = "SELECT SUM(`eo`.`amount`) AS awards, COUNT(*) AS cnt
				FROM `cash_event_operation` AS eo 
				LEFT JOIN `cash_event` AS e ON `eo`.`event_id` = `e`.`id`
				WHERE `e`.`contact_id` = i:contact_id 
				AND `eo`.`amount` NOT LIKE '-%'
				AND `e`.`pub_date` > DATE_FORMAT(CURDATE(), '%Y-%m')";
		$limit = $this->query($sql, array('contact_id' => $contact_id))->fetch();
		$sql = "SELECT SUM(`eo`.`amount`) AS fine, COUNT(*) AS cnt
				FROM `cash_event_operation` AS eo 
				LEFT JOIN `cash_event` AS e ON `eo`.`event_id` = `e`.`id`
				WHERE `e`.`contact_id` = i:contact_id 
				AND `eo`.`amount` LIKE '-%'
				AND `e`.`pub_date` > DATE_FORMAT(CURDATE(), '%Y-%m')";
		$limit_fine = $this->query($sql, array('contact_id' => $contact_id))->fetch();
		$limit['fine'] = $limit_fine['fine'];
		return $limit;
	}

}