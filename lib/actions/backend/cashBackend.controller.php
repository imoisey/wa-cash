<?php
class cashBackendController extends waViewController
{
	public function execute()
	{
		$this->redirect('?module=events');
	}
}