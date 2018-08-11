<?php
class cashReportsAction extends waViewAction
{
	public function execute()
	{
		$start 	= waRequest::request('start');
		$finish = waRequest::request('finish');
		if( empty($start) or empty($finish) )
			return true;

		// Подключаем модель и формируем таблицу
		$m_event_operation = new cashEventOperationModel();
		$m_event = new cashEventModel();
		// Список участником, которые были в событиях
		$people = $m_event_operation->getAmountAll($start, $finish);

		// Получаем настройки приложения
		$app_config = wa()->getConfig()->getAppConfig('cash');
		$app_settings = include( $app_config->getAppConfigPath('cash', false) );

		//
		// Считаем дополнительные параметры
		// 
		# Остаток на начало периода
		$begin_amount = $m_event->getAmountBeginPeriod($start);
		// Передаем данные в шаблон
		$this->view->assign('start', $start);
		$this->view->assign('finish', $finish);
		$this->view->assign('begin_amount', $begin_amount);
		$this->view->assign('true_amount', $app_settings['startmoney']);
		$this->view->assign('start_timestamp', waDateTime::format('timestamp', $start));
		$this->view->assign('finish_timestamp', waDateTime::format('timestamp', $finish));
		$this->view->assign('people', $people);
	}
	
}