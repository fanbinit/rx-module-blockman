# 구현 계획: Blockman 모듈

## 개요

Rhymix v2 PSR-4 namespace 패턴(`Rhymix\Modules\Blockman`)으로 회원 제재 관리 모듈을 구현한다. 기존 샘플 코드를 삭제하고 처음부터 전체 모듈 구조를 새로 작성한다. 구현 언어는 PHP이며, 템플릿은 Blade(.blade.php), 쿼리/스키마는 Rhymix XML 형식을 사용한다.

v2 모듈은 `blockman.class.php` 진입점 파일을 사용하지 않는다. 대신 `controllers/Base.php`가 `\ModuleObject`를 상속하는 모듈 베이스 역할을 하며, `module.xml`의 `class=` 속성이 직접 컨트롤러 클래스를 가리킨다 (예: `class="Controllers\Admin"` → `Rhymix\Modules\Blockman\Controllers\Admin`).

## Tasks

- [x] 1. 프로젝트 구조 및 모듈 설정 파일 생성
  - [x] 1.1 기존 샘플 코드 삭제 및 디렉토리 구조 생성
    - `modules/blockman/` 하위의 기존 파일 전체 삭제
    - 디렉토리 생성: `conf/`, `controllers/`, `models/`, `queries/`, `schemas/`, `lang/`, `skins/default/`, `m.skins/default/`, `views/admin/`
    - 참고: `tpl/` 디렉토리는 사용하지 않음 — 관리자 템플릿은 `views/admin/`에 배치
    - 참고: `blockman.class.php`는 생성하지 않음 — v2 모듈은 Base.php가 ModuleObject 역할
    - _Requirements: 10.1_

  - [x] 1.2 conf/info.xml 작성
    - 모듈 메타정보 정의: 제목(ko: "이용제한 기록"), 설명(ko), 버전(0.1.0), 카테고리(`service`), 작성자 정보
    - _Requirements: 10.2_

  - [x] 1.3 conf/module.xml 작성
    - 관리자 액션 선언: `dispBlockmanAdminConfig`(class="Controllers\Admin", admin-index="true"), `dispBlockmanAdminList`(class="Controllers\Admin"), `dispBlockmanAdminAction`(class="Controllers\Admin")
    - 관리자 proc 액션: `procBlockmanAdminInsertBan`(class="Controllers\Admin"), `procBlockmanAdminReleaseBan`(class="Controllers\Admin"), `procBlockmanAdminSaveConfig`(class="Controllers\Admin")
    - 프론트엔드 액션: `dispBlockmanList`(class="Controllers\Index", index="true"), `dispBlockmanDetail`(class="Controllers\Detail")
    - `<grants>` 내 `list_access` 권한 정의 (기본값: `member`)
    - `<eventHandlers>` 절에 4개 트리거 선언: `document.declaredDocument`(after, class="Controllers\EventHandlers"), `comment.declaredComment`(after, class="Controllers\EventHandlers"), `document.insertDocument`(before, class="Controllers\EventHandlers"), `comment.insertComment`(before, class="Controllers\EventHandlers")
    - 관리 메뉴 정의
    - _Requirements: 5.1, 5.2, 5.7, 9.5, 10.3_

  - [x] 1.4 lang/ko.php 작성
    - `$lang` 배열로 한국어 언어 팩 정의
    - 모듈 제목, 설명, 관리자 메뉴 항목, 에러 메시지(`msg_blockman_banned`, `msg_blockman_member_not_found`, `msg_blockman_required_fields`, `msg_blockman_message_send_failed`, `msg_blockman_invalid_mid`, `msg_blockman_cannot_release` 등), 사용자 안내 메시지 포함
    - _Requirements: 10.7_

- [x] 2. 데이터베이스 스키마 및 쿼리 정의
  - [x] 2.1 schemas/blockman_ban_records.xml 작성
    - Rhymix XML 스키마 형식으로 테이블 정의
    - 컬럼: ban_record_srl(number,11,PK,notnull), member_srl(number,11,notnull), admin_member_srl(number,11,notnull), ban_type(varchar,20,notnull), start_date(date,notnull), end_date(date,nullable), reason_tags(varchar,250,notnull), reason_detail(text,notnull), document_srl(number,11,nullable), comment_srl(number,11,nullable), declare_message(text,nullable), status(varchar,20,notnull,default:active), released_date(date,nullable), released_reason(text,nullable), regdate(date,notnull)
    - 인덱스: idx_member_srl(member_srl), idx_status_enddate(status,end_date), idx_start_date(start_date)
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 10.5_

  - [x] 2.2 queries/insertBanRecord.xml 작성
    - `blockman_ban_records` 테이블에 신규 제재 기록 INSERT 쿼리
    - _Requirements: 10.6_

  - [x] 2.3 queries/getBanRecordList.xml 작성
    - 목록 조회용 SELECT 쿼리 (검색 조건: user_id, 페이지네이션 navigation 포함, start_date 내림차순 정렬)
    - member 테이블 JOIN으로 닉네임/아이디 가져오기
    - list_count=20, page_count=10 설정
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 10.6_

  - [x] 2.4 queries/getBanRecord.xml 작성
    - ban_record_srl로 단건 조회 SELECT 쿼리 (member 테이블 JOIN)
    - _Requirements: 10.6_

  - [x] 2.5 queries/getBanRecordsByMember.xml 작성
    - member_srl로 해당 회원의 제재 기록 조회 (start_date 내림차순, limit 50)
    - _Requirements: 3.7, 10.6_

  - [x] 2.6 queries/getActiveBan.xml 작성
    - member_srl + status='active' + ban_type IN ('temporary','permanent') 조건으로 활성 차단 조회
    - _Requirements: 4.1, 4.2, 9.3, 9.4, 10.6_

  - [x] 2.7 queries/updateBanRecordStatus.xml 작성
    - ban_record_srl 기준으로 status, released_date, released_reason 필드 UPDATE 쿼리
    - _Requirements: 10.6_

- [x] 3. 체크포인트 - 스키마 및 쿼리 XML 검증
  - 모든 XML 파일의 문법이 올바른지 확인하고, 질문이 있으면 사용자에게 문의한다.

- [x] 4. 모델 및 컨트롤러 기본 구조 구현
  - [x] 4.1 controllers/Base.php 작성
    - `Rhymix\Modules\Blockman\Controllers` 네임스페이스
    - `\ModuleObject` 상속
    - `init()` 메서드는 비워둠 (각 컨트롤러가 자체 init()에서 템플릿 경로 설정)
    - _Requirements: 10.1_

  - [x] 4.2 models/Config.php 작성
    - `Rhymix\Modules\Blockman\Models` 네임스페이스
    - 정적 캐시 변수 `$_cache`
    - `getConfig()`: `ModuleModel::getModuleConfig('blockman')` 호출 + 캐시
    - `setConfig($config)`: `ModuleController::insertModuleConfig('blockman', $config)` 호출 + 캐시 갱신
    - _Requirements: 8.4_

  - [x] 4.3 models/BanRecord.php 작성
    - `Rhymix\Modules\Blockman\Models` 네임스페이스
    - 정적 메서드: `getList($args)`, `getRecord($ban_record_srl)`, `getByMember($member_srl, $limit)`, `getActiveBan($member_srl)`, `insert($args)`, `updateStatus($ban_record_srl, $status, $released_reason)`
    - 유효성 검증: `validateBanInput($args)` — reason_tags 1~5개, reason_detail 1~500자 체크
    - 헬퍼: `formatDuration($record)`, `isAppealWindowOpen($record)`, `buildWarningMessageTitle($reason_detail)`, `buildWarningMessageBody($args)`
    - 각 메서드는 Rhymix의 `executeQuery('blockman.쿼리명', $args)` 패턴 사용
    - _Requirements: 1.4, 1.5, 2.6, 3.5, 3.6, 6.2, 7.6_

  - [x] 4.4 controllers/Install.php 작성
    - `Rhymix\Modules\Blockman\Controllers` 네임스페이스, `Base` 상속
    - `moduleInstall()`: 기본 설정 저장 (reason_tags 기본값, ban_duration_options 기본값)
    - `checkUpdate()`: 버전 비교하여 업데이트 필요 여부 bool 반환
    - `moduleUpdate()`: 스키마 변경 적용
    - `recompileCache()`: 캐시 재생성 콜백 (v2 패턴 — moduleUninstall 대신 사용)
    - _Requirements: 10.4_

- [x] 5. 관리자 기능 구현
  - [x] 5.1 controllers/Admin.php 작성
    - `Rhymix\Modules\Blockman\Controllers` 네임스페이스, `Base` 상속
    - `init()`: `$this->setTemplatePath($this->module_path . 'views/admin/')` 설정
    - 관리자 화면 메서드:
      - `dispBlockmanAdminConfig()`: 현재 모듈 설정 로드 → 설정 폼 템플릿 변수 전달
      - `dispBlockmanAdminList()`: 전체 Ban_Record 목록 조회 (상태별 필터, 페이지네이션) → 템플릿 변수 전달
      - `dispBlockmanAdminAction()`: 제재 처리 폼 (대상 회원 정보, 신고 내용, 제재 옵션) → 템플릿 변수 전달
    - 관리자 proc 메서드:
      - `procBlockmanAdminInsertBan()`: 제재 유형/사유/대상 입력 유효성 검증 → 기존 활성 제재 종료 → Ban_Record 생성 → 경고 시 쪽지 발송
      - `procBlockmanAdminReleaseBan()`: ban_record_srl + 해제 사유 유효성 검증 → status 'released' 변경 + released_date 기록
      - `procBlockmanAdminSaveConfig()`: 소명 게시판 mid 유효성 검증, reason_tags 목록(1~20개, 각 30자 이하), ban_duration_options(1~10개, 각 1~3650일), list_access_level 저장
    - Communication 모듈 연동 (`getController('communication')->sendMessage(...)`)
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8, 5.6, 6.1, 6.2, 6.3, 6.4, 6.5, 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.7, 11.1, 11.3, 11.4, 11.5, 11.6_

  - [x] 5.2 관리자 템플릿 작성 (views/admin/)
    - `views/admin/config.blade.php`: 소명 게시판 mid 입력, reason_tags 관리 UI, 기간 옵션 편집, 열람 권한 선택(관리자만/회원공개)
    - `views/admin/list.blade.php`: 제재 기록 목록 (상태별 필터 탭, 페이지네이션, 해제 버튼)
    - `views/admin/action.blade.php`: 제재 처리 폼 (제재 유형 선택, reason_tag 체크박스, 상세 사유 텍스트 입력, 기간 선택 드롭다운)
    - _Requirements: 5.6, 8.1, 8.2, 8.5, 11.3, 11.4_

- [x] 6. 체크포인트 - 관리자 기능 검증
  - 모든 파일의 PHP 문법이 올바른지(`php -l`) 확인하고, 질문이 있으면 사용자에게 문의한다.

- [x] 7. 트리거 핸들러 구현
  - [x] 7.1 controllers/EventHandlers.php 작성
    - `Rhymix\Modules\Blockman\Controllers` 네임스페이스, `Base` 상속
    - `onAfterDeclareDocument(&$obj)`: 문서 신고 후 처리 — 예외 격리, 정상 BaseObject 반환
    - `onAfterDeclareComment(&$obj)`: 댓글 신고 후 처리 — 예외 격리, 정상 BaseObject 반환
    - `onBeforeInsertDocument(&$obj)`: 글 작성 전 차단 검사 — member_srl 확인 → 관리자 면제 → getActiveBan → 기간 만료 검사 → 차단 또는 통과
    - `onBeforeInsertComment(&$obj)`: 댓글 작성 전 차단 검사 — 동일 로직
    - Fail-open 원칙: DB 오류 시 차단하지 않고 허용 + 로그 기록
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 9.1, 9.2, 9.3, 9.4, 9.6, 9.7, 9.8_

- [x] 8. 사용자 화면 구현
  - [x] 8.1 controllers/Index.php 작성
    - `Rhymix\Modules\Blockman\Controllers` 네임스페이스, `Base` 상속
    - `init()`: `$this->setTemplatePath($this->module_path . 'skins/' . ($this->module_info->skin ?: 'default'))` 설정
    - `dispBlockmanList()`: getBanRecordList 호출 → 검색/페이지네이션 처리 → 템플릿 변수 설정
    - 권한 검사: list_access grant 체크
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.7, 5.4, 5.5_

  - [x] 8.2 controllers/Detail.php 작성
    - `Rhymix\Modules\Blockman\Controllers` 네임스페이스, `Base` 상속
    - `init()`: `$this->setTemplatePath($this->module_path . 'skins/' . ($this->module_info->skin ?: 'default'))` 설정
    - `dispBlockmanDetail()`: ban_record_srl로 상세 조회 → 회원 이력(최대 50건) → 소명 기간 계산 → 템플릿 변수 설정
    - 권한 검사: list_access grant 체크
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9_

  - [x] 8.3 PC 스킨 템플릿 작성 (skins/default/)
    - `skins/default/list.blade.php`: 목록 테이블(닉네임, 아이디, 시작일, 기간, 사유), 검색 폼, 페이지네이션, 빈 목록 안내 메시지
    - `skins/default/detail.blade.php`: 기본 정보 섹션, 제재 사유 섹션, 신고 콘텐츠 섹션(삭제 시 안내 문구), 소명 안내 섹션(링크 활성/비활성), 회원 이력 목록
    - _Requirements: 2.1, 2.5, 2.6, 2.7, 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 10.8_

  - [x] 8.4 모바일 스킨 템플릿 작성 (m.skins/default/)
    - `m.skins/default/list.blade.php`: PC 스킨과 동일 데이터, 모바일 레이아웃 최적화
    - `m.skins/default/detail.blade.php`: PC 스킨과 동일 데이터, 모바일 레이아웃 최적화
    - _Requirements: 10.8_

- [x] 9. 최종 체크포인트 - 전체 문법 검증
  - `php -l`로 모든 PHP 파일의 문법 오류 확인
  - XML 파일 well-formed 검증
  - 질문이 있으면 사용자에게 문의한다.

## Notes

- **v2 모듈은 `blockman.class.php`를 사용하지 않는다**: `controllers/Base.php`가 `\ModuleObject`를 상속하며, `module.xml`의 `class=` 속성이 직접 컨트롤러를 참조한다 (예: `class="Controllers\Admin"` → `Rhymix\Modules\Blockman\Controllers\Admin`)
- **관리자 템플릿 경로**: `views/admin/` (v1의 `tpl/`이 아님)
- **사용자 스킨 경로**: `skins/{skin_name}/` (skin 설정에 따라 동적)
- **Install.php 메서드**: `moduleInstall()`, `checkUpdate()`, `moduleUpdate()`, `recompileCache()` — `moduleUninstall()`은 v2에서 사용하지 않음
- **Config 모델**: `models/Config.php`에서 `ModuleModel::getModuleConfig('blockman')` 결과를 정적 캐시하여 반복 조회 최적화
- 테스트는 최소화: PHP 문법 체크(`php -l`) 및 XML well-formed 검증만 수행
- Rhymix v2 PSR-4 namespace 패턴 준수 (`Rhymix\Modules\Blockman`)
- Blade 템플릿 엔진 사용 (.blade.php)
- 기존 샘플 코드는 Task 1.1에서 삭제 후 재구현

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1"] },
    { "id": 1, "tasks": ["1.2", "1.3", "1.4"] },
    { "id": 2, "tasks": ["2.1", "2.2", "2.3", "2.4", "2.5", "2.6", "2.7"] },
    { "id": 3, "tasks": ["4.1", "4.2", "4.3"] },
    { "id": 4, "tasks": ["4.4", "5.1", "7.1"] },
    { "id": 5, "tasks": ["5.2", "8.1", "8.2"] },
    { "id": 6, "tasks": ["8.3", "8.4"] }
  ]
}
```
