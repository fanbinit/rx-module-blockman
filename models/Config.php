<?php

namespace Rhymix\Modules\Blockman\Models;

/**
 * Blockman 모듈 설정 모델
 * 
 * ModuleModel::getModuleConfig / ModuleController::insertModuleConfig 패턴 사용
 */
class Config
{
	/**
	 * 정적 캐시
	 */
	private static $_cache = null;

	/**
	 * 모듈 설정 조회 (캐시 포함)
	 * 
	 * @return object
	 */
	public static function getConfig()
	{
		if (self::$_cache !== null)
		{
			return self::$_cache;
		}

		$oModuleModel = getModel('module');
		$config = $oModuleModel->getModuleConfig('blockman');
		if (!$config)
		{
			$config = new \stdClass;
			$config->appeal_board_mid = '';
			$config->reason_tags = ['여론조성', '이용방해', '다중이', '예의없음', '분란유도/갈등조장'];
			$config->ban_duration_options = [1, 5, 30, 180];
			$config->list_access_level = 'member';
		}
		self::$_cache = $config;
		return self::$_cache;
	}

	/**
	 * 모듈 설정 저장 (캐시 갱신)
	 * 
	 * @param object $config
	 * @return \BaseObject
	 */
	public static function setConfig($config)
	{
		$oModuleController = getController('module');
		$output = $oModuleController->insertModuleConfig('blockman', $config);
		self::$_cache = $config;
		return $output;
	}

	/**
	 * 캐시 초기화
	 */
	public static function clearCache()
	{
		self::$_cache = null;
	}
}
