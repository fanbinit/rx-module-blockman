# Requirements Document

## Introduction

Blockman은 Rhymix CMS용 회원 차단/제재 관리 및 공개 이용제한 기록 모듈이다. 관리자가 신고(document.declaredDocument 트리거)를 통해 접수된 사안에 대해 경고, 기간차단, 영구차단 조치를 취할 수 있으며, 제재 기록을 공개 또는 회원 대상으로 열람할 수 있는 이용제한 기록 페이지를 제공한다.

이 모듈은 Rhymix v2 PSR-4 namespace 모듈(`modules/blockman/`)로 구현되며, Rhymix의 XML 쿼리 시스템, grant/permission 체계, communication 모듈(쪽지), member 시스템과 연동한다.

## Glossary

- **Blockman_Module**: 차단 관리 모듈 전체 시스템
- **Admin_Controller**: 관리자 제재 처리를 담당하는 컨트롤러
- **Ban_Record**: 개별 제재 건에 대한 데이터베이스 레코드 (제재 대상, 유형, 기간, 사유 등 포함)
- **Ban_List_View**: 이용제한 기록 목록 페이지
- **Ban_Detail_View**: 이용제한 기록 상세 페이지
- **Warning_Action**: 경고 조치 (쪽지 발송 + 기록)
- **Temporary_Ban_Action**: 기간차단 조치 (지정 일수 동안 활동 제한)
- **Permanent_Ban_Action**: 영구차단 조치 (무기한 활동 제한)
- **Appeal_System**: 소명(이의제기) 안내 시스템
- **Grant_System**: Rhymix의 module.xml 기반 모듈 접근 권한 체계
- **Declare_Trigger**: `document.declaredDocument` 트리거 — 문서 신고 시 발생하는 이벤트
- **Communication_Module**: Rhymix 기본 쪽지(PM) 모듈
- **Member_Srl**: Rhymix 회원 고유 식별 번호
- **Ban_Reason_Tag**: 제재 사유를 분류하는 태그 (여론조성, 이용방해, 다중이, 예의없음, 분란유도/갈등조장 등)

## Requirements

### Requirement 1: 신고 접수 시 관리자 제재 액션 처리

**User Story:** 관리자로서, 신고가 접수된 사안에 대해 경고/기간차단/영구차단 중 하나의 조치를 취하고 싶다. 이를 통해 커뮤니티 질서를 유지할 수 있다.

#### Acceptance Criteria

1. WHEN 관리자가 경고 조치를 선택하고 제재 사유(1자 이상 500자 이하)와 대상 회원을 지정하면, THE Admin_Controller SHALL Communication_Module을 통해 대상 회원에게 경고 쪽지를 발송하고, Ban_Record를 생성하며, 해당 신고 건의 상태를 '종결(closed)'로 변경한다.
2. WHEN 관리자가 기간차단 조치를 선택하고 차단 기간(1일, 5일, 30일, 180일 중 택일)과 제재 사유(1자 이상 500자 이하)를 지정하면, THE Admin_Controller SHALL 대상 회원의 활동을 지정 기간 동안 제한하고, Ban_Record를 생성하며, 해당 신고 건의 상태를 '종결(closed)'로 변경한다.
3. WHEN 관리자가 영구차단 조치를 선택하고 제재 사유(1자 이상 500자 이하)를 지정하면, THE Admin_Controller SHALL 대상 회원의 활동을 무기한 제한하고, Ban_Record를 생성하며, 해당 신고 건의 상태를 '종결(closed)'로 변경한다.
4. WHEN 관리자가 제재 조치를 실행하려 할 때, THE Admin_Controller SHALL Ban_Reason_Tag(여론조성, 이용방해, 다중이, 예의없음, 분란유도/갈등조장) 중 1개 이상 5개 이하를 필수로 선택하도록 요구한다.
5. WHEN 관리자가 제재 조치를 실행하면, THE Admin_Controller SHALL 제재 대상 Member_Srl, 제재 유형(경고/기간차단/영구차단), 시작일시, 종료일시(영구차단은 null), Ban_Reason_Tag 목록, 상세 사유 텍스트, 신고된 콘텐츠 참조(document_srl 또는 comment_srl), 처리 관리자 Member_Srl을 Ban_Record에 저장한다.
6. IF 대상 회원이 존재하지 않는 Member_Srl이면, THEN THE Admin_Controller SHALL 대상 회원이 존재하지 않음을 나타내는 오류 메시지를 반환하고 제재 처리를 중단한다.
7. IF 대상 회원이 이미 활성 상태의 기간차단 또는 영구차단 제재를 받고 있는 경우, THEN THE Admin_Controller SHALL 기존 제재를 종료 처리하고 새로운 제재를 적용하며, 두 Ban_Record를 모두 보존한다.
8. IF 관리자가 제재 사유 텍스트를 입력하지 않거나 Ban_Reason_Tag를 선택하지 않은 경우, THEN THE Admin_Controller SHALL 필수 항목 미입력을 나타내는 오류 메시지를 반환하고 제재 처리를 실행하지 않는다.

### Requirement 2: 이용제한 기록 목록 페이지

**User Story:** 이용자로서, 커뮤니티의 이용제한 기록을 열람하고 싶다. 이를 통해 어떤 행위가 제재 대상인지 파악하고 투명한 운영을 확인할 수 있다.

#### Acceptance Criteria

1. THE Ban_List_View SHALL Ban_Record 목록을 닉네임, 아이디, 시작일, 기간(주의/영구/N일), 사유(Ban_Reason_Tag) 컬럼으로 표시한다.
2. THE Ban_List_View SHALL Ban_Record를 시작일 기준 내림차순(최신순)으로 정렬하여 표시한다.
3. WHEN 사용자가 회원 아이디로 검색하면, THE Ban_List_View SHALL 입력된 검색어와 정확히 일치하는 아이디의 Ban_Record만 필터링하여 표시한다.
4. THE Ban_List_View SHALL Rhymix의 XML 쿼리 navigation 시스템을 사용하여 페이지당 20건(list_count=20), 페이지 네비게이션 표시 수 10개(page_count=10)로 페이지네이션을 제공한다.
5. WHEN 사용자가 목록의 특정 Ban_Record를 클릭하면, THE Ban_List_View SHALL 해당 Ban_Record의 상세 페이지(Ban_Detail_View)로 이동한다.
6. THE Ban_List_View SHALL 경고(주의) 조치의 경우 기간 컬럼에 "주의"로, 영구차단은 "영구"로, 기간차단은 "N일" 형식으로 표시한다.
7. IF 검색 결과 또는 전체 Ban_Record가 0건이면, THEN THE Ban_List_View SHALL 목록 영역에 기록이 없음을 나타내는 안내 메시지를 표시하고 페이지네이션을 숨긴다.

### Requirement 3: 이용제한 기록 상세 페이지

**User Story:** 이용자로서, 특정 제재 건의 상세 내역을 확인하고 싶다. 이를 통해 제재 사유와 관련 맥락을 이해할 수 있다.

#### Acceptance Criteria

1. THE Ban_Detail_View SHALL 기본 정보 섹션에 대상 회원의 닉네임, 아이디, 제재 기간(시작일~종료일을 YYYY-MM-DD 형식으로), 제재 유형을 표시한다.
2. THE Ban_Detail_View SHALL 제재 사유 섹션에 Ban_Reason_Tag 목록과 관리자가 작성한 상세 사유 텍스트를 표시한다.
3. IF Ban_Record에 신고 접수된 콘텐츠(document_srl 또는 comment_srl) 정보가 존재하면, THEN THE Ban_Detail_View SHALL 신고 접수된 글 섹션에 해당 콘텐츠로의 링크와 신고 사유 태그를 표시한다.
4. THE Ban_Detail_View SHALL 소명 안내 섹션에 소명 게시판으로의 링크를 표시한다.
5. IF 현재 시점이 제재 시작일시로부터 24시간 경과 이후이고 15일(360시간) 이내이면, THEN THE Ban_Detail_View SHALL 소명 안내 섹션의 소명 게시판 링크를 활성 상태로 표시한다.
6. IF 현재 시점이 제재 시작일시로부터 24시간 이내이거나 15일(360시간)을 초과하면, THEN THE Ban_Detail_View SHALL 소명 안내 섹션의 소명 게시판 링크를 비활성 상태로 표시하고, 소명 가능 기간(제재 시작일 기준 2일차~15일차)을 안내 문구로 표시한다.
7. THE Ban_Detail_View SHALL 해당 회원의 전체 이용제한 내역 섹션에 해당 Member_Srl의 모든 Ban_Record를 최신순(내림차순)으로 최대 50건까지 표시하며, 각 항목에 제재 시작일, 제재 기간, 해제 여부(해제됨/유지중), 사유를 포함한다.
8. IF 요청된 Ban_Record가 존재하지 않거나 현재 이용자에게 조회 권한이 없으면, THEN THE Ban_Detail_View SHALL 해당 제재 기록을 찾을 수 없음을 나타내는 안내 메시지를 표시하고 상세 정보를 노출하지 않는다.
9. IF Ban_Record에 신고 접수된 콘텐츠가 삭제되어 원본을 조회할 수 없으면, THEN THE Ban_Detail_View SHALL 해당 콘텐츠 링크 대신 원본이 삭제되었음을 나타내는 안내 문구를 표시한다.

### Requirement 4: 기간차단 회원의 활동 제한

**User Story:** 관리자로서, 차단된 회원의 글쓰기 및 댓글 작성을 자동으로 차단하고 싶다. 이를 통해 수동 감시 없이 제재를 집행할 수 있다.

#### Acceptance Criteria

1. WHILE 회원에게 유효한(status가 'active'이고, ban_type이 'permanent'이거나 end_date가 현재 시점 이후인 'temporary') Ban_Record가 존재하는 동안, THE Blockman_Module SHALL 해당 회원의 글 작성 시도를 차단하고, 차단 유형과 기간차단인 경우 종료일시를 포함하는 제재 안내 메시지를 반환한다.
2. WHILE 회원에게 유효한(status가 'active'이고, ban_type이 'permanent'이거나 end_date가 현재 시점 이후인 'temporary') Ban_Record가 존재하는 동안, THE Blockman_Module SHALL 해당 회원의 댓글 작성 시도를 차단하고, 차단 유형과 기간차단인 경우 종료일시를 포함하는 제재 안내 메시지를 반환한다.
3. WHEN 기간차단의 종료일시가 현재 시점을 경과한 후 해당 회원의 다음 글 또는 댓글 작성 시도가 발생하면, THE Blockman_Module SHALL 해당 Ban_Record의 status를 'expired'로 갱신하고 회원의 활동 제한을 즉시 해제하여 해당 작성 요청을 정상 처리한다.
4. THE Blockman_Module SHALL `document.insertDocument` before 트리거와 `comment.insertComment` before 트리거를 통해 차단 상태를 검사하며, 차단된 경우 BaseObject 에러를 반환하여 콘텐츠 삽입을 중단한다.
5. IF 차단 검사 중 데이터베이스 조회 오류가 발생하면, THEN THE Blockman_Module SHALL 차단하지 않고 정상 처리를 허용하며, 오류 로그를 기록한다.

### Requirement 5: 접근 권한 설정

**User Story:** 관리자로서, 이용제한 기록 목록의 열람 범위를 설정하고 싶다. 이를 통해 운영 정책에 따라 관리자만 보거나 전체 회원에게 공개할 수 있다.

#### Acceptance Criteria

1. THE Blockman_Module SHALL module.xml의 `<grants>` 요소 내에 이용제한 기록 열람 권한(`list_access`)을 `<grant name="list_access">` 형태로 정의한다.
2. THE Blockman_Module SHALL `list_access` 권한의 기본값을 `member`(로그인 회원 공개)로 설정한다.
3. WHEN 관리자가 관리자 설정 페이지(dispBlockmanAdminConfig)에서 열람 권한을 "관리자만"으로 선택하여 저장하면, THE Blockman_Module SHALL `list_access` 권한의 값을 `manager`로 갱신한다.
4. WHEN `list_access` 권한이 없는 사용자가 Ban_List_View에 접근하면, THE Blockman_Module SHALL Rhymix 표준 권한 오류 메시지(`msg_not_permitted`)를 표시하고, 해당 뷰의 콘텐츠를 렌더링하지 않는다.
5. IF 비로그인 사용자(guest)가 Ban_List_View에 접근하고 `list_access` 권한이 `member` 이상으로 설정되어 있다면, THEN THE Blockman_Module SHALL 로그인 유도 메시지(`msg_not_logged`)를 표시한다.
6. THE Blockman_Module SHALL 관리자 설정 페이지(dispBlockmanAdminConfig)에서 열람 권한을 "관리자만 열람"(`manager`) 또는 "회원공개"(`member`) 중 하나를 선택할 수 있는 설정 항목을 제공한다.
7. THE Admin_Controller SHALL 제재 조치 실행 액션에 `root` 권한(슈퍼관리자)을 요구한다.

### Requirement 6: 경고 쪽지 발송

**User Story:** 관리자로서, 경고 조치 시 대상 회원에게 자동으로 경고 내용이 담긴 쪽지를 보내고 싶다. 이를 통해 별도의 수동 연락 없이 경고를 전달할 수 있다.

#### Acceptance Criteria

1. WHEN 경고 조치가 실행되면, THE Admin_Controller SHALL Communication_Module의 `sendMessage` 메서드를 호출하여 대상 회원의 Member_Srl을 receiver_srl로, 제재 처리 관리자의 Member_Srl을 sender_srl로 설정하고 쪽지를 발송한다.
2. WHEN 경고 쪽지를 구성할 때, THE Admin_Controller SHALL 쪽지 제목을 "[경고] " 접두사와 제재 사유 요약 텍스트를 결합하여 생성하되, 전체 제목 길이가 250자를 초과하면 요약 텍스트를 잘라서 250자 이내로 구성한다.
3. WHEN 경고 쪽지를 구성할 때, THE Admin_Controller SHALL 쪽지 본문에 제재 사유 상세 텍스트, Ban_Reason_Tag, 신고된 콘텐츠 참조 링크를 포함한다.
4. IF 신고된 콘텐츠가 삭제되어 참조 링크를 생성할 수 없는 경우, THEN THE Admin_Controller SHALL 본문에 해당 콘텐츠가 삭제되었음을 나타내는 안내 문구를 링크 대신 포함한다.
5. IF Communication_Module의 `sendMessage` 호출이 실패를 반환하면, THEN THE Admin_Controller SHALL 경고 Ban_Record는 정상 생성하되, 관리자 화면에 쪽지 발송 실패를 나타내는 오류 메시지를 표시한다.

### Requirement 7: 데이터베이스 스키마

**User Story:** 개발자로서, 제재 기록을 안정적으로 저장하고 조회할 수 있는 데이터베이스 구조가 필요하다. 이를 통해 제재 이력을 정확하게 관리할 수 있다.

#### Acceptance Criteria

1. THE Blockman_Module SHALL `blockman_ban_records` 테이블에 다음 컬럼을 포함한다: ban_record_srl(number, size 11, PK, notnull), member_srl(number, size 11, notnull, 제재 대상), admin_member_srl(number, size 11, notnull, 처리 관리자), ban_type(varchar, size 20, notnull, 허용값: warning/temporary/permanent), start_date(date, notnull), end_date(date, nullable), reason_tags(varchar, size 250, notnull, 콤마 구분 문자열로 최대 10개 태그), reason_detail(text, notnull), document_srl(number, size 11, nullable), comment_srl(number, size 11, nullable), declare_message(text, nullable), status(varchar, size 20, notnull, default "active", 허용값: active/released/expired), regdate(date, notnull).
2. THE Blockman_Module SHALL `blockman_ban_records` 테이블에 member_srl 컬럼 인덱스를 생성한다.
3. THE Blockman_Module SHALL `blockman_ban_records` 테이블에 status + end_date 복합 인덱스를 생성한다 (인덱스명: idx_status_enddate).
4. THE Blockman_Module SHALL Rhymix의 schemas/*.xml 형식으로 스키마를 정의하여 모듈 설치 시 테이블이 자동 생성되도록 한다.
5. THE Blockman_Module SHALL ban_record_srl 발급에 Rhymix의 `getNextSequence()` 시퀀스 시스템을 사용한다.
6. IF ban_type이 "temporary"인 레코드가 저장될 때 end_date가 null이면, THEN THE Blockman_Module SHALL 해당 레코드 삽입을 거부하고 end_date 필수 오류를 반환한다.

### Requirement 8: 관리자 설정 페이지

**User Story:** 관리자로서, 모듈의 기본 설정을 관리하고 싶다. 이를 통해 소명 게시판 지정, 제재 사유 태그 관리, 열람 권한 설정을 할 수 있다.

#### Acceptance Criteria

1. THE Admin_Controller SHALL 관리자 설정 페이지에서 소명 게시판의 mid(모듈 인스턴스 식별자)를 지정할 수 있는 입력 필드를 제공하며, mid는 최대 40자의 영문 소문자로 시작하고 영문자, 숫자, 하이픈, 언더스코어로 구성된 문자열이어야 한다.
2. THE Admin_Controller SHALL 관리자 설정 페이지에서 Ban_Reason_Tag 목록을 추가/삭제할 수 있는 UI를 제공하며, 각 태그는 최대 30자이고 목록은 최소 1개에서 최대 20개까지 허용한다.
3. THE Admin_Controller SHALL Ban_Reason_Tag의 기본 목록으로 "여론조성", "이용방해", "다중이", "예의없음", "분란유도/갈등조장"을 제공한다.
4. WHEN 관리자가 설정을 저장하면, THE Admin_Controller SHALL Rhymix의 `ModuleModel::getModuleConfig` / `ModuleController::insertModuleConfig` 패턴을 사용하여 모듈 설정을 저장하고, 저장 성공 시 성공 메시지를 표시한다.
5. THE Admin_Controller SHALL 관리자 설정 페이지에서 기간차단 시 선택 가능한 기간 옵션(일 단위 목록)을 편집할 수 있는 UI를 제공하며, 각 옵션 값은 1일 이상 3650일 이하의 정수이고 최소 1개에서 최대 10개까지 허용한다.
6. IF 관리자가 존재하지 않는 mid를 소명 게시판으로 지정하여 저장을 시도하면, THEN THE Admin_Controller SHALL 저장을 거부하고 해당 mid가 유효하지 않음을 나타내는 오류 메시지를 표시하며 기존 설정을 유지한다.
7. IF 설정 저장 중 오류가 발생하면, THEN THE Admin_Controller SHALL 저장 실패를 나타내는 오류 메시지를 표시하고 관리자가 입력한 값을 화면에 유지한다.

### Requirement 9: 트리거 연동

**User Story:** 개발자로서, Rhymix의 기존 신고 시스템 및 글/댓글 작성 시스템과 연동하고 싶다. 이를 통해 기존 시스템의 변경 없이 제재 기능을 추가할 수 있다.

#### Acceptance Criteria

1. WHEN `document.declaredDocument` after 트리거가 호출되면, THE Blockman_Module SHALL 전달받은 document_srl과 declare_message를 포함하여 해당 신고 건을 관리자 제재 처리 대기 목록에 추가한다.
2. WHEN `comment.declaredComment` after 트리거가 호출되면, THE Blockman_Module SHALL 전달받은 comment_srl과 declare_message를 포함하여 해당 댓글 신고 건을 관리자 제재 처리 대기 목록에 추가한다.
3. WHEN `document.insertDocument` before 트리거가 호출되면, THE Blockman_Module SHALL 작성자의 member_srl에 대해 status가 "active"인 기간차단 또는 영구차단 Ban_Record 존재 여부를 조회하고, 존재하면 차단 안내 메시지를 담은 에러 BaseObject를 반환하여 글 작성을 중단시킨다.
4. WHEN `comment.insertComment` before 트리거가 호출되면, THE Blockman_Module SHALL 작성자의 member_srl에 대해 status가 "active"인 기간차단 또는 영구차단 Ban_Record 존재 여부를 조회하고, 존재하면 차단 안내 메시지를 담은 에러 BaseObject를 반환하여 댓글 작성을 중단시킨다.
5. THE Blockman_Module SHALL module.xml의 `<eventHandlers>` 절에 `document.declaredDocument` (after), `comment.declaredComment` (after), `document.insertDocument` (before), `comment.insertComment` (before) 총 4개의 eventHandler를 선언한다.
6. IF 신고 접수 트리거 핸들러(`document.declaredDocument` after, `comment.declaredComment` after) 내부에서 예외가 발생하면, THEN THE Blockman_Module SHALL 예외를 포착하여 Rhymix 로그에 기록하고, 정상 BaseObject를 반환하여 원래 신고 처리의 진행을 방해하지 않는다.
7. IF 차단 검사 트리거 핸들러(`document.insertDocument` before, `comment.insertComment` before) 내부에서 데이터베이스 조회 오류가 발생하면, THEN THE Blockman_Module SHALL 오류를 Rhymix 로그에 기록하고, 차단하지 않고 정상 BaseObject를 반환하여 글/댓글 작성을 허용한다.
8. IF before 트리거에서 차단 검사 대상 회원이 관리자(`is_admin == 'Y'`)이면, THEN THE Blockman_Module SHALL 차단 검사를 건너뛰고 정상 BaseObject를 반환한다.

### Requirement 10: 모듈 설치 및 구조

**User Story:** 개발자로서, Rhymix v2 PSR-4 namespace 모듈 표준에 맞는 구조로 모듈을 설치하고 싶다. 이를 통해 Rhymix 생태계와 호환되는 모듈을 배포할 수 있다.

#### Acceptance Criteria

1. THE Blockman_Module SHALL `modules/blockman/` 경로에 위치하며, `Rhymix\Modules\Blockman` 네임스페이스를 사용하고, 모듈 진입점 클래스 파일 `blockman.class.php`를 포함한다.
2. THE Blockman_Module SHALL `conf/info.xml`에 모듈 메타정보를 정의하되, 최소한 제목(ko, en), 설명(ko, en), 버전(X.Y.Z 형식), 카테고리(`service`), 작성자 정보를 포함한다.
3. THE Blockman_Module SHALL `conf/module.xml`에 관리자 액션(admin view, admin controller), 프론트엔드 액션(view, controller), 권한(grants), 관리 메뉴, 이벤트 핸들러를 선언한다.
4. THE Blockman_Module SHALL `controllers/Install.php`에 `Rhymix\Modules\Blockman\Controllers` 네임스페이스로 `moduleInstall`(스키마 테이블 생성), `checkUpdate`(bool 반환), `moduleUpdate`(스키마 변경 적용), `moduleUninstall`(모듈 데이터 정리) 메서드를 구현한다.
5. THE Blockman_Module SHALL `schemas/blockman_ban_records.xml`에 Rhymix XML 스키마 형식으로 테이블명, 컬럼(이름, 타입, 크기, 제약조건), 인덱스를 정의한다.
6. THE Blockman_Module SHALL `queries/` 디렉토리에 최소 insertBanRecord, getBanRecordList, getBanRecord, getBanRecordsByMember, getActiveBan, updateBanRecordStatus 6개의 Rhymix XML 쿼리 파일을 정의한다.
7. THE Blockman_Module SHALL `lang/ko.php`에 모듈 제목, 설명, 관리자 메뉴 항목, 사용자 안내 메시지를 포함하는 한국어 언어 팩을 `$lang` 변수 배열 형식으로 제공한다.
8. THE Blockman_Module SHALL `skins/default/`에 최소 1개의 PC 스킨 템플릿 파일(.html)을, `m.skins/default/`에 최소 1개의 모바일 스킨 템플릿 파일(.html)을 제공한다.

### Requirement 11: 차단 해제 및 관리

**User Story:** 관리자로서, 기존 제재를 조기에 해제하거나 제재 상태를 관리하고 싶다. 이를 통해 소명이 받아들여진 경우 등 유연하게 대처할 수 있다.

#### Acceptance Criteria

1. WHEN 관리자가 status가 "active"인 Ban_Record에 대해 수동 해제를 실행하면, THE Admin_Controller SHALL 해당 Ban_Record의 status를 "released"로 변경하고 해제일시를 기록한다.
2. WHEN Ban_Record의 status가 "released"로 변경되면, THE Blockman_Module SHALL 해당 회원의 활동 제한(글쓰기, 댓글 등)을 즉시 해제한다.
3. THE Admin_Controller SHALL 관리자 페이지에서 전체 Ban_Record 목록을 페이지당 20건 단위로 조회하고, 상태별(active/released/expired) 필터링 및 제재일시 기준 최신순 정렬을 제공한다.
4. WHEN 관리자가 수동 해제를 실행할 때, THE Admin_Controller SHALL 해제 사유를 입력할 수 있는 필수 입력 필드를 제공하며, 해제 사유는 1자 이상 500자 이하로 제한한다.
5. IF 관리자가 status가 "active"가 아닌 Ban_Record(released 또는 expired)에 대해 해제를 시도하면, THEN THE Admin_Controller SHALL 해제를 거부하고 현재 상태에서는 해제할 수 없음을 나타내는 오류 메시지를 표시한다.
6. IF 수동 해제 시 해제 사유가 미입력이거나 500자를 초과하면, THEN THE Admin_Controller SHALL 해제를 거부하고 사유 입력 조건 불충족을 나타내는 오류 메시지를 표시한다.

## Correctness Properties

### Property 1: Ban_Record 무결성
FOR ALL Ban_Records in the database,
IF ban_type == "temporary" THEN end_date != null
AND IF ban_type == "permanent" THEN end_date == null
AND IF ban_type == "warning" THEN status != "active" (warnings don't restrict activity)

### Property 2: 차단 검사 일관성
FOR ALL write attempts by a member with member_srl M,
IF there EXISTS a Ban_Record with member_srl == M AND status == "active" AND (ban_type == "permanent" OR (ban_type == "temporary" AND end_date > now()))
THEN the write attempt SHALL be blocked

### Property 3: 기간차단 자동 만료
FOR ALL Ban_Records with ban_type == "temporary" AND status == "active",
IF end_date < now()
THEN on next activity check, status SHALL be changed to "expired"

### Property 4: 권한 격리
FOR ALL users without list_access grant,
Ban_List_View and Ban_Detail_View SHALL return permission error
AND FOR ALL non-root users,
admin ban actions SHALL return permission error
