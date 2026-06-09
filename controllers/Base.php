<?php

namespace Rhymix\Modules\Blockman\Controllers;

/**
 * Blockman 모듈 베이스 컨트롤러
 * 
 * 모든 컨트롤러의 부모 클래스.
 * v2 모듈에서는 이 클래스가 \ModuleObject를 상속한다.
 */
class Base extends \ModuleObject
{
	/**
	 * 초기화 (각 컨트롤러가 자체 init()에서 오버라이드)
	 */
	public function init()
	{
	}
}
