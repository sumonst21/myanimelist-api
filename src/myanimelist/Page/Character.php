<?php

/**
 * MyAnimeList Character Page API
 *
 * @package	 		MyAnimeList API
 * @author     		Magnum357 [https://github.com/magnum357i/]
 * @copyright  		2018
 * @license    		http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace myanimelist\Page;

class Character extends \myanimelist\Builder\Page {

	/**
	 * Set type
	 */
	protected static $type = 'character';

	/**
	 * Methods to allow for prefix
	 */
	protected static $methodsToAllow = [

		'title',
		'statistic'
	];

	/**
	 * Patterns for externalLink
	 */
	protected static $externalLinks = [

		'people'    => 'people/{s}',
		'anime'     => 'anime/{s}',
		'manga'     => 'manga/{s}'
	];

	/**
	 * Get character name
	 *
	 * @return 		string
	 * @usage 		title()->self
	 */
	protected function _titleself() {

		$key = 'charactername';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		if ( !$this->request()->isSent() ) return FALSE;

		$data = $this->request()::match( '</div><h1.*?>(.*?)</h1></div><div id="content" ?>' );

		if ( $data == FALSE ) return FALSE;

		if ( $this->config->isOnNameConverting() ) $data = $this->text()->reverseName( $data, 3 );

		$data = $this->text()->replace( '\s*".+"\s*', ' ', $data );

		return static::setValue( $key, $this->lastChanges( $data ) );
	}

	/**
	 * Get character name
	 *
	 * @return 		string
	 * @usage 		title()->nickname
	 */
	protected function _titlenickname() {

		$key = 'nickname';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		if ( !$this->request()->isSent() ) return FALSE;

		$data = $this->request()::match( '</div><h1.*?>.*?"(.*?)".*?</h1></div><div id="content" ?>' );

		if ( $data == FALSE ) return FALSE;

		if ( $this->config()->isOnNameConverting() ) $data = $this->text()->reverseName( $data );

		return static::setValue( $key, $this->lastChanges( $data ) );
	}

	/**
	 * Get category
	 *
	 * @return 		string
	 * @usage 		category
	 */
	protected function _category() {

		$key = 'category';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		if ( !$this->request()->isSent() ) return FALSE;

		$data = 'character';

		return static::setValue( $key, $this->lastChanges( $data ) );
	}

	/**
	 * Get poster
	 *
	 * @return 		string
	 * @usage 		poster
	 */
	protected function _poster() {

		$key = 'poster';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		if ( !$this->request()->isSent() ) return FALSE;

		$data = $this->request()::matchGroup( [

				'(https://myanimelist.cdn-dena.com/images/characters/[0-9]+/[0-9]+\.jpg)',
				'(https://cdn.myanimelist.net/images/characters/[0-9]+/[0-9]+\.jpg)'
			]
		);

		if ( $data == FALSE ) return FALSE;

		if ( $this->config()->isOnCache() ) {

			$newPoster = $this->cache()->savePoster( $this->imageName(), $data );
			$data      = $newPoster;
		}

		return static::setValue( $key, $this->lastChanges( $data ) );
	}

	/**
	 * Get description
	 *
	 * @return 		string
	 * @usage 		description
	 */
	protected function _description() {

		$key = 'description';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		if ( !$this->request()->isSent() ) return FALSE;

		$data = $this->request()::match( '<div class="breadcrumb ?"[^>]*>.*?</div></div>.*?<div.*?>.*?</div>(.*?)<div[^>]*>voice actors</div>', "<br><span>" );

		if ( $data == FALSE ) return FALSE;

		$data = $this->text()->replace( 'Bounty:\s*<div class="spoiler">.*?<\/span>', '', $data, 'si' );
		$data = $this->text()->replace( '[^\n]+:[^\n]+', '', $data, 'si' );

		$data = $this->text()->descCleaner( $data );

		return static::setValue( $key, $this->lastChanges( $data ) );
	}

	/**
	 * Get favorites
	 *
	 * @return 		array
	 * @usage 		none
	 */
	protected function favorite() {

		$key = 'favorite';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		if ( !$this->request()->isSent() ) return FALSE;

		$data = $this->request()::match( 'member favorites:\s*([\d,]+)' );

		if ( $data == FALSE ) return FALSE;

		$data = $this->text()->replace( '[^0-9]+', '', $data );
		$data = [

			'simple' => $this->lastChanges( $this->text()->formatK( $data ) ),
			'full'   => $this->lastChanges( $data )
		];

		return static::setValue( $key, $this->lastChanges( $data ) );
	}

	/**
	 * Get number with K of favorite
	 *
	 * @return 		string
	 * @usage 		statistic()->favorite
	 */
	protected function _statisticfavorite() {

		if ( !isset( static::$data[ 'favorite' ] ) ) $this->favorite();

		return ( isset( static::$data[ 'favorite' ][ 'simple' ] ) ) ? static::$data[ 'favorite' ][ 'simple' ] : FALSE;
	}

	/**
	 * Get number without K of favorite
	 *
	 * @return 		string
	 * @usage 		statistic()->favoriteraw
	 */
	protected function _statisticfavoriteraw() {

		if ( !isset( static::$data[ 'favorite' ] ) ) $this->favorite();

		return ( isset( static::$data[ 'favorite' ][ 'full' ] ) ) ? static::$data[ 'favorite' ][ 'full' ] : FALSE;
	}

	/**
	 * Get recent anime list
	 *
	 * @return 		array
	 * @usage 		recentanime
	 */
	protected function _recentanime() {

		$key = 'recentanime';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		if ( !$this->request()->isSent() ) return FALSE;

		$data = $this->request()::matchTable(
		[ $this, 'lastChanges' ],
		$this->config(),
		$this->text(),
		'<div class="normal_header">animeography</div>.*?<table.*?(.*?)</table>',
		'<tr>(.*?)</tr>',
		[
		'<a href="[^"]+anime/(\d+)[^"]+">[^<>]+</a>',
		'<a href="[^"]+anime/\d+[^"]+">([^<>]+)</a>',
		'<small>([^<]+)</small>'
		],
		[
		'id',
		'title',
		'role'
		],
		static::$limit,
		NULL,
		TRUE
		);

		return static::setValue( $key, $data );
	}

	/**
	 * Get recent manga list
	 *
	 * @return 		array
	 * @usage 		recentmanga
	 */
	protected function _recentmanga() {

		$key = 'recentmanga';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		if ( !$this->request()->isSent() ) return FALSE;

		$data = $this->request()::matchTable(
		[ $this, 'lastChanges' ],
		$this->config(),
		$this->text(),
		'<div class="normal_header">mangaography</div>.*?<table.*?(.*?)</table>',
		'<tr>(.*?)</tr>',
		[
		'<a href="[^"]+manga/(\d+)[^"]+">[^<>]+</a>',
		'<a href="[^"]+manga/\d+[^"]+">([^<>]+)</a>',
		'<small>([^<]+)</small>'
		],
		[
		'id',
		'title',
		'role'
		],
		static::$limit,
		NULL,
		TRUE,
		'id'
		);

		return static::setValue( $key, $data );
	}

	/**
	 * Get voice actors
	 *
	 * @return 		array
	 * @usage 		voiceactors
	 */
	protected function _voiceactors() {

		$key  = 'voiceactors';
		$lang = 'japanese';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		if ( !$this->request()->isSent() ) return FALSE;

		$data = $this->request()::matchTable(
		[ $this, 'lastChanges' ],
		$this->config(),
		$this->text(),
		'voice actors</div>(.+</table>.*<br>)',
		'<tr>(.*?)</tr>',
		[
		'<a href="[^"]+people/(\d+)[^"]+">[^<]+</a>.*?<div[^>]+><small>' . $lang . '</small>',
		'<a href="[^"]+people/\d+[^"]+">([^<]+)</a>.*?<div[^>]+><small>' . $lang . '</small>',
		'<a href="[^"]+people/\d+[^"]+">[^<]+</a>.*?<div[^>]+><small>(' . $lang . ')</small>'
		],
		[
		'id',
		'name',
		'lang'
		],
		static::$limit
		);

		return static::setValue( $key, $data );
	}

	/**
	 * Get link of the request page
	 *
	 * @return 		string
	 * @usage 		link
	 */
	protected function _link() {

		return $this->lastChanges( $this->request()::$url );
	}
}