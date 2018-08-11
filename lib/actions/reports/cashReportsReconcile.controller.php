<?php
class cashReportsReconcileController extends waViewController
{
	public function execute()
	{
		if( !cashViewHelper::getRights('reports') )
			return false;
		// Получаем доступ к настройкам
		$app_config = wa()->getConfig()->getAppConfig('cash');
		$app_settings = include( $app_config->getAppConfigPath('cash', false) );
		// Фактически в кассе
		$really_total = $app_settings['startmoney'];
		// Начало периода
		$really_day = $app_settings['startday'];
		// Конец периода (текущий день)
		$finish_day = time();
		// Получаем список сотрудников и считаем их штрафы
		$m_event_operation = new cashEventOperationModel();
		$people = $m_event_operation->getAmountAll(date('Y-m-d H:i:s',$really_day), date('Y-m-d H:i:s',$finish_day));
		if( count($people) == 0 )
			$this->redirect("?module=reports");
		$total_people = 0;
		foreach($people as $man) {
			$total_people += $man['total'];
		}
		// Новая фактическая сумма в кассе
		$really_total_new = $really_total - $total_people;
		// Сохраняем новые данные в настройках (Сводим кассу)
		$app_settings['startmoney'] = $really_total_new;
		$app_settings['startday'] = $finish_day;
		waUtils::varExportToFile($app_settings, $app_config->getAppConfigPath('cash', false));
		// Добавляем событие в кассу о сведении кассы
		$view = wa()->getView();
		$view->assign('start', date("Y-m-d", $really_day) );
		$view->assign('finish', date("Y-m-d", $finish_day) );
		$view->assign('people', $people);
		$comment = $view->fetch($this->path.'templates/actions/reports/ReportsReconcileItem.html');
		$event_data = array(
			'total'		=> 0,
			'comment'	=> $comment,
		);
		// Добавляем событие в кассу
		$m_event = new cashEventModel();
		$m_event->add($event_data);

		$this->redirect("?module=reports&action=print&start={$really_day}&finish={$finish_day}");
	}
}