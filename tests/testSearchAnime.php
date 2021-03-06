<?php

use PHPUnit\Framework\TestCase;

class testSearchAnime extends TestCase {

	public function testGetAnimeSearch() {

		$mal = new \myanimelist\Search\Anime( 'bleach' );

		$mal->sendRequestOrGetData();

		$success = TRUE;

		if ( !$mal->isSuccess() )                     $success = FALSE;
		if ( $mal->setLimit( 3 )->results === FALSE ) $success = FALSE;

		$this->assertTrue( $success );
	}
}