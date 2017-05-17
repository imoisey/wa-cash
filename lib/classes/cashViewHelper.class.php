<?php

class cashViewHelper
{
	/**
	 * Возвращает список пользователей в системе
	 * @return [type] [description]
	 */
	public static function getUserList($name = 'contact_id', $first_el = false)
	{
		$users = waUser::getUsers();
		if( !is_array($users) )
			return false;
		if( $first_el != false ) {
			$opt = array(''=>$first_el);
			foreach ($users as $user_id => $user) {
				$opt[$user_id] = $user;
			}
			$users = $opt;
		}
		return waHtmlControl::getControl(waHtmlControl::SELECT, $name, array(
			'options'=>$users,
		));
	}

	/**
	 * Возвращает массив с пользователями в формате JSON
	 * @return [type] [description]
	 */
	public static function getUserListJSON($app_id='cash')
	{
		$users = waUser::getUsers($app_id);
		if( !is_array($users) )
			return false;
		$mans = array();
		foreach ($users as $contact_id => $user) {
			$contact = new waContact($contact_id);
			$mans[$contact->get('lastname')] = array(
				'name' => $contact->getName(),
				'contact_id' => $contact_id
			);
		}
		return json_encode($mans, JSON_UNESCAPED_UNICODE);
	}

	/**
	 * Возвращает права пользователя
	 */
	public static function getRights($right)
	{
		return wa()->getUser()->getRights('cash', $right);
	}


	/**
	 * Возвращает объект авторизованного пользователя
	 * @return [type] [description]
	 */
	public static function getUser()
	{
		return wa()->getUser();
	}


}