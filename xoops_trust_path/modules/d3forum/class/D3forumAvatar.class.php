<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bluemooninc
 * Date: 2013/03/04
 * Time: 17:38
 * To change this template use File | Settings | File Templates.
 */
class D3forumAvatar
{
	protected static $poster_avatar = array();

	/**
	 * @param $poster_obj
	 */
	private function _getGravatar(&$poster_obj){
		$email = trim(strtolower($poster_obj->getVar('email')));
		$rating = 'R'; //rating = the highest possible rating displayed image [ G | PG | R | X ]
		$avatar_width = $avatar_height = 80;
		self::$poster_avatar = array(
			'path' => false,
			'url' => "http://www.gravatar.com/avatar/" . md5($email) . "?r=" . $rating . "&amp;s=" . $avatar_width,
			'width' => $avatar_width,
			'height' => $avatar_height,
			'type' => 1,
			'attr' => $rating
		);
	}

	/**
	 * @param $poster_obj
	 */
	private function _getLocalAvatar(&$poster_obj){
		if (is_file(XOOPS_UPLOAD_PATH . '/' . $poster_obj->getVar('user_avatar'))) {
			list($avatar_width, $avatar_height, $avatar_type, $avatar_attr) = getimagesize(XOOPS_UPLOAD_PATH . '/' . $poster_obj->getVar('user_avatar'));
			self::$poster_avatar = array(
				'path' => htmlspecialchars($poster_obj->getVar('user_avatar'), ENT_QUOTES),
				'width' => $avatar_width,
				'height' => $avatar_height,
				'type' => $avatar_type,
				'attr' => $avatar_attr
			);
		}
	}

	/**
	 * @param $poster_obj
	 * @return array
	 */
	public static function &getAvatar(&$poster_obj)
	{
		$root = XCube_Root::getSingleton();
		if ($root->mContext->mModuleConfig['use_gravatar'] && $poster_obj->getVar('user_avatar') == "blank.gif") {
			self::_getGravatar($poster_obj);
		} else {
			self::_getLocalAvatar($poster_obj);
		}
		return self::$poster_avatar;
	}
}
