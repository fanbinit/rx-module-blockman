<?php

namespace Rhymix\Modules\Blockman\Controllers;

use Context;
use MemberModel;
use ModuleModel;
use Rhymix\Modules\Blockman\Models\Config;
use Rhymix\Modules\Blockman\Models\BanRecord;

/**
 * Blockman 모듈 관리자 컨트롤러
 * 
 * 관리자 화면 표시 및 제재 처리 액션을 담당한다.
 */
class Admin extends Base
{
	/**
	 * 초기화 - 관리자 템플릿 경로 설정
	 */
	public function init()
	{
		$this->setTemplatePath($this->module_path . 'views/admin/');
	}

	/**
	 * 관리자 설정 페이지
	 * 
	 * 소명 게시판 mid, reason_tags 관리, 기간 옵션, 열람 권한 설정을 표시한다.
	 */
	public function dispBlockmanAdminConfig()
	{
		$config = Config::getConfig();

		Context::set('blockman_config', $config);
		Context::set('appeal_board_mid', $config->appeal_board_mid ?? '');
		Context::set('reason_tags', $config->reason_tags ?? []);
		Context::set('ban_duration_options', $config->ban_duration_options ?? [1, 5, 30, 180]);
		Context::set('list_access_level', $config->list_access_level ?? 'member');

		$this->setTemplateFile('config');
	}

	/**
	 * 관리자 제재 기록 목록
	 * 
	 * 전체 Ban_Record를 상태별 필터, 페이지네이션으로 표시한다.
	 */
	public function dispBlockmanAdminList()
	{
		$args = new \stdClass;
		$args->page = Context::get('page') ?: 1;
		$args->list_count = 20;
		$args->page_count = 10;

		// 상태별 필터
		$search_status = Context::get('search_status');
		if ($search_status && in_array($search_status, ['active', 'released', 'expired']))
		{
			$args->search_status = $search_status;
		}

		// 아이디 검색
		$search_user_id = Context::get('search_user_id');
		if ($search_user_id)
		{
			$args->search_user_id = $search_user_id;
		}

		$output = BanRecord::getList($args);

		Context::set('ban_list', $output->data ?: []);
		Context::set('page_navigation', $output->page_navigation ?? null);
		Context::set('total_count', $output->total_count ?? 0);
		Context::set('search_status', $search_status ?: '');
		Context::set('search_user_id', $search_user_id ?: '');

		$this->setTemplateFile('list');
	}

	/**
	 * 관리자 제재 처리 폼
	 * 
	 * 대상 회원 정보, 신고 내용, 제재 옵션을 표시한다.
	 */
	public function dispBlockmanAdminAction()
	{
		$config = Config::getConfig();

		// 대상 회원 정보 로드
		$target_member_srl = Context::get('member_srl');
		$target_member_info = null;
		if ($target_member_srl)
		{
			$oMemberModel = getModel('member');
			$target_member_info = $oMemberModel->getMemberInfoByMemberSrl($target_member_srl);
		}

		// 신고 관련 정보 로드
		$document_srl = Context::get('document_srl');
		$comment_srl = Context::get('comment_srl');
		$declare_message = Context::get('declare_message');

		Context::set('target_member_info', $target_member_info);
		Context::set('target_member_srl', $target_member_srl);
		Context::set('document_srl', $document_srl);
		Context::set('comment_srl', $comment_srl);
		Context::set('declare_message', $declare_message);
		Context::set('reason_tags', $config->reason_tags ?? []);
		Context::set('ban_duration_options', $config->ban_duration_options ?? [1, 5, 30, 180]);

		$this->setTemplateFile('action');
	}

	/**
	 * 제재 처리 실행
	 * 
	 * 제재 유형/사유/대상 유효성 검증 → 기존 활성 제재 종료 → Ban_Record 생성 → 경고 시 쪽지 발송
	 */
	public function procBlockmanAdminInsertBan()
	{
		// 입력값 수집
		$args = new \stdClass;
		$args->member_srl = Context::get('member_srl');
		$args->ban_type = Context::get('ban_type');
		$args->reason_tags = Context::get('reason_tags');
		$args->reason_detail = Context::get('reason_detail');
		$args->document_srl = Context::get('document_srl') ?: null;
		$args->comment_srl = Context::get('comment_srl') ?: null;
		$args->declare_message = Context::get('declare_message') ?: null;

		// 대상 회원 존재 확인
		if (!$args->member_srl)
		{
			return $this->setError('msg_blockman_required_fields');
		}

		$oMemberModel = getModel('member');
		$member_info = $oMemberModel->getMemberInfoByMemberSrl($args->member_srl);
		if (!$member_info || !$member_info->member_srl)
		{
			return $this->setError('msg_blockman_member_not_found');
		}

		// reason_tags 배열 변환
		if (is_array($args->reason_tags))
		{
			$tags = $args->reason_tags;
		}
		else
		{
			$tags = array_filter(explode(',', $args->reason_tags ?? ''));
		}
		$args->reason_tags = implode(',', $tags);

		// 입력 유효성 검증
		$validation_error = BanRecord::validateBanInput($args);
		if ($validation_error)
		{
			return $this->setError($validation_error);
		}

		// 기간차단 종료일 계산
		if ($args->ban_type === 'temporary')
		{
			$duration_days = (int)Context::get('ban_duration');
			if ($duration_days < 1)
			{
				return $this->setError('msg_blockman_required_fields');
			}
			$args->end_date = date('YmdHis', strtotime('+' . $duration_days . ' days'));
		}
		else
		{
			$args->end_date = null;
		}

		// 관리자 정보
		$logged_info = Context::get('logged_info');
		$args->admin_member_srl = $logged_info->member_srl;

		// 기존 활성 제재 종료 처리
		$active_ban = BanRecord::getActiveBan($args->member_srl);
		if ($active_ban)
		{
			BanRecord::updateStatus($active_ban->ban_record_srl, 'released', '새로운 제재 적용으로 인한 자동 종료');
		}

		// Ban_Record 생성
		$args->ban_record_srl = getNextSequence();
		$args->start_date = date('YmdHis');
		$args->status = 'active';
		$args->regdate = date('YmdHis');

		$output = BanRecord::insert($args);
		if (!$output->toBool())
		{
			return $output;
		}

		// 경고 조치 시 쪽지 발송
		if ($args->ban_type === 'warning')
		{
			$this->sendWarningMessage($args, $logged_info->member_srl);
		}

		$this->setMessage('success_registed');

		if (Context::get('success_return_url'))
		{
			$this->setRedirectUrl(Context::get('success_return_url'));
		}
		else
		{
			$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispBlockmanAdminList'));
		}
	}

	/**
	 * 제재 해제
	 * 
	 * ban_record_srl + 해제 사유 유효성 검증 → status 'released' 변경 + released_date 기록
	 */
	public function procBlockmanAdminReleaseBan()
	{
		$ban_record_srl = Context::get('ban_record_srl');
		$released_reason = trim(Context::get('released_reason') ?? '');

		// 유효성 검증
		if (!$ban_record_srl)
		{
			return $this->setError('msg_blockman_required_fields');
		}

		if (mb_strlen($released_reason) < 1 || mb_strlen($released_reason) > 500)
		{
			return $this->setError('msg_blockman_required_fields');
		}

		// 대상 Ban_Record 조회
		$record = BanRecord::getRecord($ban_record_srl);
		if (!$record)
		{
			return $this->setError('msg_blockman_required_fields');
		}

		// 활성 상태 확인
		if ($record->status !== 'active')
		{
			return $this->setError('msg_blockman_cannot_release');
		}

		// 해제 처리
		$output = BanRecord::updateStatus($ban_record_srl, 'released', $released_reason);
		if (!$output->toBool())
		{
			return $output;
		}

		$this->setMessage('success_updated');

		if (Context::get('success_return_url'))
		{
			$this->setRedirectUrl(Context::get('success_return_url'));
		}
		else
		{
			$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispBlockmanAdminList'));
		}
	}

	/**
	 * 모듈 설정 저장
	 * 
	 * 소명 게시판 mid 유효성 검증, reason_tags, ban_duration_options, list_access_level 저장
	 */
	public function procBlockmanAdminSaveConfig()
	{
		$config = new \stdClass;

		// 소명 게시판 mid 검증
		$appeal_board_mid = trim(Context::get('appeal_board_mid') ?? '');
		if ($appeal_board_mid)
		{
			// mid 형식 검증 (영문 소문자 시작, 영문자/숫자/하이픈/언더스코어)
			if (!preg_match('/^[a-z][a-z0-9_-]{0,39}$/', $appeal_board_mid))
			{
				return $this->setError('msg_blockman_invalid_mid');
			}

			// 실제 모듈 존재 여부 확인
			$oModuleModel = getModel('module');
			$module_info = $oModuleModel->getModuleInfoByMid($appeal_board_mid);
			if (!$module_info || !$module_info->module_srl)
			{
				return $this->setError('msg_blockman_invalid_mid');
			}
		}
		$config->appeal_board_mid = $appeal_board_mid;

		// reason_tags 검증 (1~20개, 각 30자 이하)
		$reason_tags = Context::get('reason_tags');
		if (!is_array($reason_tags))
		{
			$reason_tags = array_filter(explode(',', $reason_tags ?? ''));
		}
		$reason_tags = array_map('trim', $reason_tags);
		$reason_tags = array_filter($reason_tags);
		$reason_tags = array_values($reason_tags);

		if (count($reason_tags) < 1 || count($reason_tags) > 20)
		{
			return $this->setError('msg_blockman_required_fields');
		}
		foreach ($reason_tags as $tag)
		{
			if (mb_strlen($tag) > 30)
			{
				return $this->setError('msg_blockman_required_fields');
			}
		}
		$config->reason_tags = $reason_tags;

		// ban_duration_options 검증 (1~10개, 각 1~3650일)
		$ban_duration_options = Context::get('ban_duration_options');
		if (!is_array($ban_duration_options))
		{
			$ban_duration_options = array_filter(explode(',', $ban_duration_options ?? ''));
		}
		$ban_duration_options = array_map('intval', $ban_duration_options);
		$ban_duration_options = array_filter($ban_duration_options, function($v) {
			return $v >= 1 && $v <= 3650;
		});
		$ban_duration_options = array_values($ban_duration_options);

		if (count($ban_duration_options) < 1 || count($ban_duration_options) > 10)
		{
			return $this->setError('msg_blockman_required_fields');
		}
		$config->ban_duration_options = $ban_duration_options;

		// list_access_level 검증
		$list_access_level = Context::get('list_access_level');
		if (!in_array($list_access_level, ['member', 'manager']))
		{
			$list_access_level = 'member';
		}
		$config->list_access_level = $list_access_level;

		// 설정 저장
		$output = Config::setConfig($config);
		if (!$output->toBool())
		{
			return $output;
		}

		$this->setMessage('success_updated');

		if (Context::get('success_return_url'))
		{
			$this->setRedirectUrl(Context::get('success_return_url'));
		}
		else
		{
			$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispBlockmanAdminConfig'));
		}
	}

	/**
	 * 경고 쪽지 발송 헬퍼
	 * 
	 * @param object $args 제재 정보
	 * @param int $admin_member_srl 관리자 회원 SRL
	 */
	protected function sendWarningMessage($args, $admin_member_srl)
	{
		try
		{
			$title = BanRecord::buildWarningMessageTitle($args->reason_detail);
			$content = BanRecord::buildWarningMessageBody($args);

			$oCommunicationController = getController('communication');
			$output = $oCommunicationController->sendMessage($admin_member_srl, $args->member_srl, $title, $content);

			if (!$output->toBool())
			{
				$this->setMessage('msg_blockman_message_send_failed');
			}
		}
		catch (\Throwable $e)
		{
			\Rhymix\Framework\Debug::addError('[Blockman] Warning message send error: ' . $e->getMessage());
			$this->setMessage('msg_blockman_message_send_failed');
		}
	}
}
