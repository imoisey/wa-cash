<?php
class cashRightConfig extends waRightConfig
{
	public function init() 
	{
		$this->addItem('reports', 'Раздел "Отчеты"', 'checkbox');
		$this->addItem('settings', 'Раздел "Настройки"', 'checkbox');
	}
}