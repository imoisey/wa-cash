<?php
class cashEventsSaveController extends waJsonController 
{
	public function execute()
	{
		$id = waRequest::post('id', false);
		$comment = waRequest::post('comment', '-');
		if( $id == false ) {
			$this->status = 'bad';
			return false;
		}

		// Проверка на автора комментария
		$m_event = new cashEventModel();
		$event_data = $m_event->getById($id);

		// Проверяем принадлежит ли комментарий пользователю
		if($event_data['contact_id'] != wa()->getUser()->getId()) {
			$this->status = 'bad';
			return false;
		}

		// Собираем данные в кучу
		$event_data = array(
			'comment' => $comment
		);
		// Сохраняем данные
		$m_event->updateById($id, $event_data);
		return true;
	}
}