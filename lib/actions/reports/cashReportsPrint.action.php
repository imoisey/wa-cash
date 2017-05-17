<?php
class cashReportsPrintAction extends waViewAction
{
	public function execute()
	{
		$start = waRequest::request('start', false);
		$finish = waRequest::request('finish', false);
		// Получаем список сотрудников и считаем их штрафы
		$m_event_operation = new cashEventOperationModel();
		$people = $m_event_operation->getAmountAll(date('Y-m-d H:i:s',$start), date('Y-m-d H:i:s',$finish));
		if( count($people) == 0 )
			$this->redirect('?module=events');
		$view = $this->view;
		$view->assign('start', date('Y-m-d',$start));
		$view->assign('finish', date('Y-m-d',$finish));
		$view->assign('people', $people);
	}
}