<?php

namespace Rhymix\Modules\Blockman\Controllers;

use Context;
use Rhymix\Modules\Blockman\Models\BanRecord;

/**
 * Blockman 모듈 트리거 핸들러
 * 
 * Rhymix의 이벤트 시스템과 연동하여:
 * - 글/댓글 작성 전 차단 검사
 * - 문서/댓글 신고 후 처리
 */
class EventHandlers extends Base
{
	/**
	 * 문서 신고 후 트리거
	 * 
	 * document.declaredDocument (after)
	 * 신고 건을 기록하거나 관리자에게 알림 (현재는 로깅만)
	 * 
	 * @param object $obj
	 * @return \BaseObject
	 */
	public function onAfterDeclareDocument(&$obj)
	{
		try
		{
			// 향후 신고 대기 목록 기능 추가 시 여기에 구현
			// 현재는 별도 처리 없이 정상 반환
		}
		catch (\Throwable $e)
		{
			\Rhymix\Framework\Debug::addError($e->getMessage());
		}

		return new \BaseObject();
	}

	/**
	 * 댓글 신고 후 트리거
	 * 
	 * comment.declaredComment (after)
	 * 
	 * @param object $obj
	 * @return \BaseObject
	 */
	public function onAfterDeclareComment(&$obj)
	{
		try
		{
			// 향후 신고 대기 목록 기능 추가 시 여기에 구현
			// 현재는 별도 처리 없이 정상 반환
		}
		catch (\Throwable $e)
		{
			\Rhymix\Framework\Debug::addError($e->getMessage());
		}

		return new \BaseObject();
	}

	/**
	 * 글 작성 전 차단 검사 트리거
	 * 
	 * document.insertDocument (before)
	 * 차단된 회원의 글 작성을 차단한다.
	 * 
	 * @param object $obj
	 * @return \BaseObject
	 */
	public function onBeforeInsertDocument(&$obj)
	{
		return $this->checkBanStatus($obj);
	}

	/**
	 * 댓글 작성 전 차단 검사 트리거
	 * 
	 * comment.insertComment (before)
	 * 차단된 회원의 댓글 작성을 차단한다.
	 * 
	 * @param object $obj
	 * @return \BaseObject
	 */
	public function onBeforeInsertComment(&$obj)
	{
		return $this->checkBanStatus($obj);
	}

	/**
	 * 차단 상태 검사 공통 로직
	 * 
	 * 1. member_srl 확인 (비로그인 시 건너뛰기)
	 * 2. 관리자 면제
	 * 3. 활성 차단 조회
	 * 4. 기간 만료 시 자동 해제
	 * 5. 활성 차단이면 에러 반환
	 * 
	 * Fail-open 원칙: DB 오류 시 차단하지 않고 허용
	 * 
	 * @param object $obj
	 * @return \BaseObject
	 */
	protected function checkBanStatus(&$obj)
	{
		try
		{
			// 1. member_srl 확인
			$member_srl = $obj->member_srl ?? 0;
			if (!$member_srl)
			{
				$logged_info = Context::get('logged_info');
				$member_srl = $logged_info->member_srl ?? 0;
			}
			if (!$member_srl)
			{
				return new \BaseObject();
			}

			// 2. 관리자 면제
			$logged_info = Context::get('logged_info');
			if ($logged_info && $logged_info->is_admin === 'Y')
			{
				return new \BaseObject();
			}

			// 3. 활성 차단 조회
			$active_ban = BanRecord::getActiveBan($member_srl);
			if (!$active_ban)
			{
				return new \BaseObject();
			}

			// 4. 기간차단 만료 검사 (lazy evaluation)
			if ($active_ban->ban_type === 'temporary' && $active_ban->end_date)
			{
				$now = date('YmdHis');
				if ($active_ban->end_date < $now)
				{
					// 만료 처리
					BanRecord::updateStatus($active_ban->ban_record_srl, 'expired');
					return new \BaseObject();
				}
			}

			// 5. 활성 차단 → 에러 반환
			if ($active_ban->ban_type === 'temporary' && $active_ban->end_date)
			{
				// 기간차단: 종료일시 포함 메시지
				$end_formatted = substr($active_ban->end_date, 0, 4) . '-' .
					substr($active_ban->end_date, 4, 2) . '-' .
					substr($active_ban->end_date, 6, 2);
				return new \BaseObject(-1, sprintf(lang('msg_blockman_banned_until'), $end_formatted));
			}
			else
			{
				// 영구차단
				return new \BaseObject(-1, 'msg_blockman_banned');
			}
		}
		catch (\Throwable $e)
		{
			// Fail-open: DB 오류 시 차단하지 않고 허용 + 로그 기록
			\Rhymix\Framework\Debug::addError('[Blockman] Ban check error: ' . $e->getMessage());
			return new \BaseObject();
		}
	}
}
