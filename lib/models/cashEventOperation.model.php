<?php

class cashEventOperationModel extends waModel
{
	protected $table = 'cash_event_operation';

	/**
	 * Получение всех операций по номеру события
	 * @param  int $event_id Номер события
	 * @return array           Возвращает многомерный массив с данными операций
	 */
	public function getOperationEvent($event_id)
	{
		if( !is_numeric($event_id) or $event_id <= 0 )
			throw new waException('Неправильный параметр события');

		$operations = $this->getByField('event_id', $event_id, true);
		if( !is_array($operations) )
			return false;
		foreach($operations as $k => $operation) {
			if($operation['contact_id'] == 0) {
				$operations[$k]['name'] = _('Кассир');
				continue;
			}
			$contact = new waContact($operation['contact_id']);
			$operations[$k]['name'] = $contact->getName();
		}

		return $operations;
	}

	/**
	 * Возвращает массив операций с участием пользователя
	 * @param  [type]  $contact_id [description]
	 * @param  boolean $type       [description]
	 * @return [type]              [description]
	 */
	public function getOperationContact($contact_id, $type = null, $fields = "*")
	{
		if(!is_null($type)) {
			$where_type = "AND amount ";
			$type = ($type == '+') ? $where_type."> 0": $where_type."< 0";
		}

		if(!is_numeric($contact_id))
			$contact_id = wa()->getUser()->getId();

		$operations = $this->select($fields)
						->where("contact_id = i:contact_id {$type}", array('contact_id'=>$contact_id))->order("event_id DESC");

		return $operations->fetchAll();
	}


	/**
	 * Возвращает сумму операций у всех пользователей за указанный период
	 * @param  boolean $contact_id [description]
	 * @param  boolean $start      [description]
	 * @param  boolean $finish     [description]
	 * @return [type]              [description]
	 */
	public function getAmountAll($start = false, $finish = false)
	{
		if( empty($start) and empty($finish) )
			return false;
		// Увеличиваем на 1 день, чтобы попадали все данные по конечной дате
		$finish = date('Y-m-d', strtotime($finish) + 86400);
		$m_event = new cashEventModel();
		$oper_table = $this->table;
		$even_table = '`cash_event`';
		$sql = "SELECT {$oper_table}.`contact_id`, {$even_table}.`pub_date`, SUM( {$oper_table}.`amount` ) AS  total FROM  {$oper_table}
			LEFT JOIN {$even_table} ON $even_table.`id`={$oper_table}.`event_id`
			WHERE {$even_table}.`pub_date` >= s:start AND {$even_table}.`pub_date` <= s:finish
			GROUP BY  {$oper_table}.`contact_id` ORDER BY total";
		$data = $this->query($sql, array('start'=>$start,'finish'=>$finish))->fetchAll();
		$people = array();
		foreach ($data as $contact) {
			if($contact['contact_id'] == 0) {
				$people[] = array(
					'name' 	=> _('Кассир'),
					'total'	=> $contact['total'],
					'pub_date' => $contact['pub_date'],
				);
				continue;
			}

			$user = new waContact($contact['contact_id']);
			$people[] = array(
				'name' 	=> $user->getName(),
				'total'	=> $contact['total'],
				'pub_date' => $contact['pub_date'],
			);
		}
		return $people;
	}

}