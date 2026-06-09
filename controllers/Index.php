<?php

namespace Rhymix\Modules\Blockman\Controllers;

use Context;
use Rhymix\Modules\Blockman\Models\BanRecord;
use Rhymix\Modules\Blockman\Models\Config;

/**
 * Blockman 모듈 사용자 목록 컨트롤러
 * 
 * 이용제한 기록 목록 페이지를 표시한다.
 */
class Index extends Base
{
	/**
	 * 초기화 - 사용자 스킨 경로 설정
	 */
	public function init()
	{
		$skin = $this->module_info->skin ?? 'default';
		$this->setTemplatePath($this->module_path . 'skins/' . $skin . '/');
	}

	/**
	 * 이용제한 기록 목록
	 */
	public function dispBlockmanList()
	{
		// 권한 검사
		$config = Config::getConfig();
		$logged_info = Context::get('logged_info');
		$grant = $this->module_info->grant ?? new \stdClass;

		// 비로그인 사용자 체크
		if (!$logged_info || !$logged_info->member_srl)
		{
			if (($config->list_access_level ?? 'member') !== 'guest')
			{
				return $this->setError('msg_not_logged');
			}
		}

		// 검색 및 페이지네이션
		$args = new \stdClass;
		$args->page = Context::get('page') ?: 1;
		$args->list_count = 20;
		$args->page_count = 10;
		$args->search_user_id = Context::get('search_user_id') ?: '';

		$output = BanRecord::getList($args);

		// 템플릿 변수 설정
		Context::set('ban_list', $output->data ?: []);
		Context::set('page_navigation', $output->page_navigation ?? null);
		Context::set('total_count', $output->total_count ?? 0);
		Context::set('search_user_id', $args->search_user_id);

		$this->setTemplateFile('list');
	}
}
