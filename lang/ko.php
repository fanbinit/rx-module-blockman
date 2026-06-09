<?php

$lang->blockman = '이용제한 기록';
$lang->blockman_description = '회원 제재 관리 및 이용제한 기록 공개 모듈';

// 관리자 메뉴
$lang->cmd_blockman_admin_config = '설정';
$lang->cmd_blockman_admin_list = '제재 관리';
$lang->cmd_blockman_admin_action = '제재 처리';

// 제재 유형
$lang->blockman_ban_type_warning = '주의';
$lang->blockman_ban_type_temporary = '기간차단';
$lang->blockman_ban_type_permanent = '영구차단';

// 상태
$lang->blockman_status = '상태';
$lang->blockman_status_active = '유지중';
$lang->blockman_status_released = '해제됨';
$lang->blockman_status_expired = '만료됨';

// 목록 컬럼
$lang->blockman_nickname = '닉네임';
$lang->blockman_user_id = '아이디';
$lang->blockman_start_date = '시작일';
$lang->blockman_duration = '기간';
$lang->blockman_reason = '사유';

// 상세 페이지
$lang->blockman_basic_info = '기본 정보';
$lang->blockman_ban_reason = '제재 사유';
$lang->blockman_reported_content = '신고 접수된 글';
$lang->blockman_appeal_info = '소명 안내';
$lang->blockman_member_history = '이 회원의 전체 이용제한 내역';
$lang->blockman_ban_period = '제재 기간';
$lang->blockman_permanent = '영구';
$lang->blockman_days = '%d일';
$lang->blockman_back_to_list = '목록으로';

// 소명 안내
$lang->blockman_appeal_guide = '이용제한에 대해 이의가 있으시면 소명 게시판에서 소명하실 수 있습니다. 소명은 제재 시작 후 1일이 지난 시점부터 15일 이내에만 가능합니다.';
$lang->blockman_appeal_not_available = '소명 가능 기간이 아닙니다.';

// 검색
$lang->blockman_search_placeholder = '회원 아이디로 검색';
$lang->blockman_search = '검색';
$lang->blockman_no_records = '기록이 없습니다.';

// 관리자 설정
$lang->blockman_config_appeal_board = '소명 게시판 mid';
$lang->blockman_config_reason_tags = '제재 사유 태그';
$lang->blockman_config_ban_duration_options = '기간차단 옵션 (일 단위)';
$lang->blockman_config_list_access = '열람 권한';
$lang->blockman_config_list_access_member = '회원공개';
$lang->blockman_config_list_access_manager = '관리자만 열람';

// 관리자 제재 처리
$lang->blockman_select_ban_type = '제재 유형 선택';
$lang->blockman_select_reason_tags = '사유 태그 선택';
$lang->blockman_reason_detail = '상세 사유';
$lang->blockman_select_duration = '기간 선택';
$lang->blockman_target_member = '대상 회원';
$lang->blockman_execute_ban = '제재 실행';
$lang->blockman_release_ban = '제재 해제';
$lang->blockman_release_reason = '해제 사유';

// 에러 메시지
$lang->msg_blockman_banned = '이용제한 상태입니다. 활동이 제한되어 있습니다.';
$lang->msg_blockman_banned_until = '이용제한 상태입니다. %s까지 활동이 제한됩니다.';
$lang->msg_blockman_member_not_found = '대상 회원을 찾을 수 없습니다.';
$lang->msg_blockman_required_fields = '필수 항목을 모두 입력해주세요.';
$lang->msg_blockman_message_send_failed = '경고 쪽지 발송에 실패했습니다. 제재 기록은 정상 생성되었습니다.';
$lang->msg_blockman_invalid_mid = '유효하지 않은 게시판 mid입니다.';
$lang->msg_blockman_cannot_release = '현재 상태에서는 해제할 수 없습니다.';
$lang->msg_blockman_release_reason_required = '해제 사유를 입력해주세요 (1자 이상 500자 이하).';
$lang->msg_blockman_record_not_found = '해당 제재 기록을 찾을 수 없습니다.';
$lang->msg_blockman_content_deleted = '원본 콘텐츠가 삭제되었습니다.';

// 성공 메시지
$lang->msg_blockman_ban_success = '제재 처리가 완료되었습니다.';
$lang->msg_blockman_release_success = '제재가 해제되었습니다.';
$lang->msg_blockman_config_saved = '설정이 저장되었습니다.';
