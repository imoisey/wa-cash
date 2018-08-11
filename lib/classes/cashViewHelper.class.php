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
			// Удаляем себя из списка
			$user_id = wa()->getUser()->getId();
			unset($users[$user_id]);
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
	 * @return
	 */
	public static function getUserListJSON($app_id='cash', $view_me = false)
	{
		$mans = self::getUserListObject($app_id, $view_me);
		return json_encode($mans, JSON_UNESCAPED_UNICODE);
	}

	/**
	 * Возвращает массив с пользователями
	 * @return
	 */
	public static function getUserListObject($app_id='cash', $view_me = false)
	{
		$users = waUser::getUsers($app_id);
		if( !is_array($users) )
			return false;
		$mans = array();
		// Удаляем себя из списка
		if($view_me == false) {
			$user_id = wa()->getUser()->getId();
			unset($users[$user_id]);
		}
		foreach ($users as $contact_id => $user) {
			$contact = new waContact($contact_id);
			$mans[$contact->get('lastname')] = array(
				'name' => $contact->getName(),
				'contact_id' => $contact_id
			);
		}
		return $mans;
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