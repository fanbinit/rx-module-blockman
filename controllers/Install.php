<?php

namespace Rhymix\Modules\Blockman\Controllers;

use Rhymix\Modules\Blockman\Models\Config;

/**
 * Blockman 모듈 설치/업데이트 컨트롤러
 */
class Install extends Base
{
	/**
	 * 모듈 설치 시 실행
	 * 
	 * @return \BaseObject
	 */
	public function moduleInstall()
	{
		// 기본 설정 저장
		$config = new \stdClass;
		$config->appeal_board_mid = '';
		$config->reason_tags = ['여론조성', '이용방해', '다중이', '예의없음', '분란유도/갈등조장'];
		$config->ban_duration_options = [1, 5, 30, 180];
		$config->list_access_level = 'member';
		Config::setConfig($config);

		return new \BaseObject();
	}

	/**
	 * 업데이트 필요 여부 확인
	 * 
	 * @return bool
	 */
	public function checkUpdate()
	{
		return false;
	}

	/**
	 * 모듈 업데이트 시 실행
	 * 
	 * @return \BaseObject
	 */
	public function moduleUpdate()
	{
		return new \BaseObject();
	}

	/**
	 * 캐시 재생성
	 */
	public function recompileCache()
	{
		Config::clearCache();
	}
}
