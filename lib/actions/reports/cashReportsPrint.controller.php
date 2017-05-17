<?php
class cashReportsPrintController extends waViewController
{
	public function execute()
	{
		$this->executeAction(new cashReportsPrintAction());
	}
}