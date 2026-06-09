<?php

namespace Rhymix\Modules\Blockman\Controllers;

use Context;
use Rhymix\Modules\Blockman\Models\BanRecord;
use Rhymix\Modules\Blockman\Models\Config;

/**
 * Blockman 모듈 사용자 상세 컨트롤러
 * 
 * 이용제한 기록 상세 페이지를 표시한다.
 */
class Detail extends Base
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
	 * 이용제한 기록 상세
	 * 
	 * ban_record_srl로 상세 조회 → 회원 이력(최대 50건) → 소명 기간 계산 → 템플릿 변수 설정
	 */
	public function dispBlockmanDetail()
	{
		// 권한 검사
		$config = Config::getConfig();
		$logged_info = Context::get('logged_info');

		if (!$logged_info || !$logged_info->member_srl)
		{
			if (($config->list_access_level ?? 'member') !== 'guest')
			{
				return $this->setError('msg_not_logged');
			}
		}

		// ban_record_srl 파라미터 확인
		$ban_record_srl = Context::get('ban_record_srl');
		if (!$ban_record_srl)
		{
			return $this->setError('msg_blockman_not_found');
		}

		// 제재 기록 조회
		$record = BanRecord::getRecord($ban_record_srl);
		if (!$record)
		{
			return $this->setError('msg_blockman_not_found');
		}

		// 회원 이력 조회 (최대 50건)
		$member_history = BanRecord::getByMember($record->member_srl, 50);

		// 소명 기간 활성 여부 판단
		$appeal_window_open = BanRecord::isAppealWindowOpen($record);

		// 기간 포맷
		$duration_text = BanRecord::formatDuration($record);

		// 날짜 포맷 (YYYY-MM-DD 형식)
		$start_date_formatted = '';
		if (!empty($record->start_date))
		{
			$start_date_formatted = substr($record->start_date, 0, 4) . '-' .
				substr($record->start_date, 4, 2) . '-' .
				substr($record->start_date, 6, 2);
		}

		$end_date_formatted = '';
		if (!empty($record->end_date))
		{
			$end_date_formatted = substr($record->end_date, 0, 4) . '-' .
				substr($record->end_date, 4, 2) . '-' .
				substr($record->end_date, 6, 2);
		}

		// 소명 게시판 mid
		$appeal_board_mid = $config->appeal_board_mid ?? '';

		// 템플릿 변수 설정
		Context::set('ban_record', $record);
		Context::set('member_history', $member_history);
		Context::set('appeal_window_open', $appeal_window_open);
		Context::set('duration_text', $duration_text);
		Context::set('start_date_formatted', $start_date_formatted);
		Context::set('end_date_formatted', $end_date_formatted);
		Context::set('appeal_board_mid', $appeal_board_mid);
		Context::set('list_access_level', $config->list_access_level ?? 'member');

		$this->setTemplateFile('detail');
	}
}
