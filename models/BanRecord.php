<?php

namespace Rhymix\Modules\Blockman\Models;

/**
 * Blockman 모듈 - Ban_Record 데이터 모델
 * 
 * 제재 기록의 CRUD 및 유틸리티 메서드를 제공한다.
 * Rhymix의 executeQuery() 패턴을 사용한다.
 */
class BanRecord
{
	/**
	 * 제재 기록 목록 조회 (페이지네이션 포함)
	 * 
	 * @param object $args search_user_id, search_status, page, list_count 등
	 * @return object executeQuery 결과
	 */
	public static function getList($args)
	{
		return executeQuery('blockman.getBanRecordList', $args);
	}

	/**
	 * 제재 기록 단건 조회
	 * 
	 * @param int $ban_record_srl
	 * @return object|null
	 */
	public static function getRecord($ban_record_srl)
	{
		$args = new \stdClass;
		$args->ban_record_srl = $ban_record_srl;
		$output = executeQuery('blockman.getBanRecord', $args);
		if (!$output->toBool() || !$output->data)
		{
			return null;
		}
		return $output->data;
	}

	/**
	 * 특정 회원의 제재 기록 조회 (최신순, 최대 50건)
	 * 
	 * @param int $member_srl
	 * @param int $limit
	 * @return array
	 */
	public static function getByMember($member_srl, $limit = 50)
	{
		$args = new \stdClass;
		$args->member_srl = $member_srl;
		$args->list_count = $limit;
		$args->page = 1;
		$output = executeQuery('blockman.getBanRecordsByMember', $args);
		if (!$output->toBool() || !$output->data)
		{
			return [];
		}
		return is_array($output->data) ? $output->data : [$output->data];
	}

	/**
	 * 회원의 활성 차단 조회 (기간차단/영구차단 중 active 상태)
	 * 
	 * @param int $member_srl
	 * @return object|null
	 */
	public static function getActiveBan($member_srl)
	{
		$args = new \stdClass;
		$args->member_srl = $member_srl;
		$output = executeQuery('blockman.getActiveBan', $args);
		if (!$output->toBool() || !$output->data)
		{
			return null;
		}
		$data = $output->data;
		if (is_array($data))
		{
			$data = $data[0] ?? null;
		}
		return $data;
	}

	/**
	 * 제재 기록 삽입
	 * 
	 * @param object $args
	 * @return \BaseObject
	 */
	public static function insert($args)
	{
		if (!$args->ban_record_srl)
		{
			$args->ban_record_srl = getNextSequence();
		}
		if (!$args->regdate)
		{
			$args->regdate = date('YmdHis');
		}
		if (!$args->start_date)
		{
			$args->start_date = date('YmdHis');
		}
		if (!isset($args->status))
		{
			$args->status = 'active';
		}
		return executeQuery('blockman.insertBanRecord', $args);
	}

	/**
	 * 제재 기록 상태 변경
	 * 
	 * @param int $ban_record_srl
	 * @param string $status active/released/expired
	 * @param string|null $released_reason
	 * @return \BaseObject
	 */
	public static function updateStatus($ban_record_srl, $status, $released_reason = null)
	{
		$args = new \stdClass;
		$args->ban_record_srl = $ban_record_srl;
		$args->status = $status;
		if ($status === 'released')
		{
			$args->released_date = date('YmdHis');
			$args->released_reason = $released_reason;
		}
		return executeQuery('blockman.updateBanRecordStatus', $args);
	}

	/**
	 * 입력 유효성 검증
	 * 
	 * @param object $args
	 * @return string|null 에러 메시지 키 또는 null(유효)
	 */
	public static function validateBanInput($args)
	{
		// ban_type 검증
		if (!in_array($args->ban_type ?? '', ['warning', 'temporary', 'permanent']))
		{
			return 'msg_blockman_required_fields';
		}

		// reason_tags 검증 (1~5개)
		$tags = $args->reason_tags ?? '';
		if (is_array($tags))
		{
			$tag_count = count($tags);
		}
		else
		{
			$tag_list = array_filter(explode(',', $tags));
			$tag_count = count($tag_list);
		}
		if ($tag_count < 1 || $tag_count > 5)
		{
			return 'msg_blockman_required_fields';
		}

		// reason_detail 검증 (1~500자)
		$detail = trim($args->reason_detail ?? '');
		if (mb_strlen($detail) < 1 || mb_strlen($detail) > 500)
		{
			return 'msg_blockman_required_fields';
		}

		// temporary인 경우 end_date 필수
		if ($args->ban_type === 'temporary' && empty($args->end_date))
		{
			return 'msg_blockman_required_fields';
		}

		return null;
	}

	/**
	 * 기간 표시 포맷
	 * 
	 * @param object $record
	 * @return string "주의" / "영구" / "N일"
	 */
	public static function formatDuration($record)
	{
		if ($record->ban_type === 'warning')
		{
			return '주의';
		}
		if ($record->ban_type === 'permanent')
		{
			return '영구';
		}
		if ($record->ban_type === 'temporary' && $record->start_date && $record->end_date)
		{
			$start = strtotime(
				substr($record->start_date, 0, 4) . '-' .
				substr($record->start_date, 4, 2) . '-' .
				substr($record->start_date, 6, 2)
			);
			$end = strtotime(
				substr($record->end_date, 0, 4) . '-' .
				substr($record->end_date, 4, 2) . '-' .
				substr($record->end_date, 6, 2)
			);
			$days = (int)round(($end - $start) / 86400);
			return $days . '일';
		}
		return '';
	}

	/**
	 * 소명 기간 활성 여부 확인
	 * 
	 * 제재 시작일로부터 24시간 이후 ~ 15일(360시간) 이내
	 * 
	 * @param object $record
	 * @return bool
	 */
	public static function isAppealWindowOpen($record)
	{
		if (empty($record->start_date))
		{
			return false;
		}

		$start_time = strtotime(
			substr($record->start_date, 0, 4) . '-' .
			substr($record->start_date, 4, 2) . '-' .
			substr($record->start_date, 6, 2) . ' ' .
			substr($record->start_date, 8, 2) . ':' .
			substr($record->start_date, 10, 2) . ':' .
			substr($record->start_date, 12, 2)
		);

		$now = time();
		$elapsed_hours = ($now - $start_time) / 3600;

		return ($elapsed_hours >= 24 && $elapsed_hours <= 360);
	}

	/**
	 * 경고 쪽지 제목 생성
	 * 
	 * "[경고] " 접두사 + 사유 요약 (전체 250자 이내)
	 * 
	 * @param string $reason_detail
	 * @return string
	 */
	public static function buildWarningMessageTitle($reason_detail)
	{
		$prefix = '[경고] ';
		$max_length = 250;
		$available = $max_length - mb_strlen($prefix);
		$summary = mb_strlen($reason_detail) > $available
			? mb_substr($reason_detail, 0, $available - 3) . '...'
			: $reason_detail;
		return $prefix . $summary;
	}

	/**
	 * 경고 쪽지 본문 생성
	 * 
	 * @param object $args ban_type, reason_tags, reason_detail, document_srl, comment_srl
	 * @return string
	 */
	public static function buildWarningMessageBody($args)
	{
		$body = '';
		$body .= "■ 제재 사유\n";

		// 태그 표시
		$tags = $args->reason_tags ?? '';
		if (is_array($tags))
		{
			$tags = implode(', ', $tags);
		}
		$body .= "사유 태그: {$tags}\n\n";

		// 상세 사유
		$body .= "상세 사유:\n";
		$body .= ($args->reason_detail ?? '') . "\n\n";

		// 신고된 콘텐츠 참조
		if (!empty($args->document_srl))
		{
			$body .= "■ 관련 콘텐츠\n";
			$body .= "문서 번호: {$args->document_srl}\n\n";
		}
		elseif (!empty($args->comment_srl))
		{
			$body .= "■ 관련 콘텐츠\n";
			$body .= "댓글 번호: {$args->comment_srl}\n\n";
		}

		$body .= "본 경고는 커뮤니티 규정 위반에 대한 조치입니다.\n";
		$body .= "반복 위반 시 기간차단 또는 영구차단이 적용될 수 있습니다.\n";

		return $body;
	}
}
