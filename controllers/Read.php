<?php

namespace Rhymix\Modules\Blockman\Controllers;

/**
 * 차단 관리
 * 
 * Copyright (c) 팬비닛
 * 
 * Generated with https://www.poesis.dev/tools/rxmodulegen
 */
class Read extends Base
{
	/**
	 * 초기화
	 */
	public function init()
	{
		// 스킨 또는 뷰 경로 지정
		$this->setTemplatePath($this->module_path . 'skins/' . ($this->module_info->skin ?: 'default'));
	}
	
	/**
	 * 글읽기 화면 예제
	 */
	public function dispBlockmanRead()
	{
		// 글번호 받아오기
		$item_srl = \Context::get('item_srl');
		
		// 뷰 파일명 지정
		$this->setTemplateFile('read');
	}
}
