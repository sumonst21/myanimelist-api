<?php

/**
 * MyAnimeList Manga API
 *
 * @author     		Magnum357 [https://github.com/magnum357i/]
 * @copyright  		2018
 * @license    		http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    		0.9.0
 */

namespace myanimelist\Types;

class Manga extends \myanimelist\Helper\Builder {

	/**
	 * Set type
	 */
	public static $type = 'manga';

	/**
	 * Prefix to call function
	 */
	public static $prefix = '';

	/**
	 * Call title functions
	 *
	 * @return this class
	 */
	public function title() {

		static::$prefix = 'title';

		return $this;
	}

	/**
	 * Get published values
	 *
	 * @return this class
	 */
	public function published() {

		if ( !isset( static::$data[ 'published' ] ) ) {

			$this->_published();
		}

		static::$prefix = 'published';

		return $this;
	}

	/**
	 * Get chapterdate values
	 *
	 * @return this class
	 */
	public function chapterdate() {

		if ( !isset( static::$data[ 'firstchapter' ] ) OR !isset( static::$data[ 'lastchapter' ] ) ) {

			$this->_firstchapter();
			$this->_lastchapter();
		}

		static::$prefix = 'chapterdate';

		return $this;
	}

	/**
	 * Call related functions
	 *
	 * @return this class
	 */
	public function related() {

		static::$prefix = 'related';

		return $this;
	}

	/**
	 * Get first of published values
	 *
	 * @return this class
	 */
	public function first() {

		if ( in_array( static::$prefix, array( 'published', 'chapterdate' ) ) ) {

			static::$prefix = static::$prefix . 'first';
		}

		return $this;
	}

	/**
	 * Get last of published values
	 *
	 * @return this class
	 */
	public function last() {

		if ( in_array( static::$prefix, array( 'published', 'chapterdate' ) ) ) {

			static::$prefix = static::$prefix . 'last';
		}

		return $this;
	}

	/**
	 * Set limit
	 *
	 * @return this class
	 */
	public function setLimit( $int ) {

		static::$limit = $int;

		return $this;
	}

	/**
	 * Page is correct?
	 *
	 * @return bool
	 */
	public function isSuccess() {

		return ( empty( $this->_titleoriginal() ) ) ? FALSE : TRUE;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	protected function _titleoriginal() {

		$key = 'titleoriginal';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = static::match( '<span itemprop="name">(.*?)</span>' );

		return static::setValue( $key, static::lastChanges( $data ) );
	}

	/**
	 * Get title for english
	 *
	 * @return string
	 */
	protected function _titleenglish() {

		$key = 'titleenglish';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = static::match( '<span class="dark_text">english:</span>(.*?)</div>' );

		return static::setValue( $key, static::lastChanges( $data ) );
	}

	/**
	 * Get title for japanese
	 *
	 * @return string
	 */
	protected function _titlejapanese() {

		$key = 'titlejapanese';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = static::match( '<span class="dark_text">japanese:</span>(.*?)</div>' );

		return static::setValue( $key, static::lastChanges( $data ) );
	}

	/**
	 * Get poster
	 *
	 * @return string
	 */
	protected function _poster() {

		$key = 'poster';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = static::match( '(https://myanimelist.cdn-dena.com/images/manga/[0-9]+/[0-9]+\.jpg)' );

		if ( $data == FALSE ) $data = static::match( '(https://cdn.myanimelist.net/images/manga/[0-9]+/[0-9]+\.jpg)' );

		return static::setValue( $key, $this->lastChanges( $data ) );
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	protected function _description() {

		$key = 'description';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = $this->match( '<span itemprop="description">(.*?)</span>', '<br>' );

		if ( $data == FALSE ) return FALSE;

		$data = static::descCleaner( $data );

		return static::setValue( $key, static::lastChanges( $data ) );
	}

	/**
	 * Get category
	 *
	 * @return string
	 */
	protected function _category() {

		$key = 'category';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = $this->match( '<span class="dark_text">type:</span>(.*?)</div>' );

		return static::setValue( $key, static::lastChanges( $data ) );
	}

	/**
	 * Get vote
	 *
	 * @return string
	 */
	protected function _vote() {

		$key = 'vote';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = static::match( 'scored by <span itemprop="ratingCount">(.*?)</span> users' );

		if ( $data == FALSE ) return FALSE;

		$data = static::formatK( $data );

		return static::setValue( $key, static::lastChanges( $data ) );
	}

	/**
	 * Get point
	 *
	 * @return string
	 */
	protected function _point() {

		$key = 'point';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = static::match( '<span class="dark_text">score:</span>(.*?)<sup>' );

		if ( $data == FALSE ) $data = static::match( '<span itemprop="ratingValue">(.*?)</span>' );

		$data = str_replace( ',', '.', $data );

		if ( !static::validate( array( 'mode' => 'regex', 'regex_code' => '^\d\.\d\d$' ), $data ) ) return FALSE;

		$data = mb_substr( $data, 0, 3 );

		return static::setValue( $key, static::lastChanges( $data ) );
	}

	/**
	 * Get rank
	 *
	 * @return string
	 */
	protected function _rank() {

		$key = 'rank';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = static::match( '<span class="dark_text">ranked:</span>(.*?)<sup>' );

		$data = str_replace( '#', '', $data );

		if ( !static::validate( array( 'mode' => 'number' ), $data ) )
		{
			return FALSE;
		}
		else
		{
			$data = '#' . $data;;
		}

		return static::setValue( $key, static::lastChanges( $data ) );
	}

	/**
	 * Get genres
	 *
	 * @return array
	 */
	protected function _genres() {

		$key = 'genres';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = $this->match( '<span class="dark_text">genres:</span>(.*?)</div>' );

		if ( $data == FALSE ) return FALSE;

		return static::setValue( $key, static::listValue( $data, ',' ) );
	}

	/**
	 * Get popularity
	 *
	 * @return string
	 */
	protected function _popularity() {

		$key = 'popularity';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = static::match( '<span class="dark_text">popularity:</span>(.*?)</div>' );

		$data = str_replace( '#', '', $data );

		if ( !static::validate( array( 'mode' => 'number' ), $data ) ) return FALSE;

		$data = '#' . $data;

		return static::setValue( $key, static::lastChanges( $data ) );
	}

	/**
	 * Get members
	 *
	 * @return string
	 */
	protected function _members() {

		$key = 'members';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = static::match( '<span class="dark_text">members:</span>(.*?)</div>' );

		if ( $data == FALSE ) return FALSE;

		$data = static::formatK( $data );

		return static::setValue( $key, static::lastChanges( $data ) );
	}

	/**
	 * Get favorites
	 *
	 * @return string
	 */
	protected function _favorites() {

		$key = 'favorites';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = static::match( '<span class="dark_text">favorites:</span>(.*?)</div>' );

		if ( $data == FALSE ) return FALSE;

		$data = str_replace( array( '.', ',' ), '', $data );

		if ( !static::validate( array( 'mode' => 'number' ), $data ) ) return FALSE;

		$data = ( $data > 1000 ) ? round( $data / 1000 ) : $data;

		return static::setValue( $key, static::lastChanges( $data ) );
	}

	/**
	 * Get status
	 *
	 * @return string
	 */
	protected function _status() {

		$key = 'status';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = static::match( '<span class="dark_text">status:</span>(.*?)</div>' );

		return static::setValue( $key, static::lastChanges( $data ) );
	}

	/**
	 * Get authors
	 *
	 * @return array
	 */
	protected function _authors() {

		$key = 'authors';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = $this->match( '<span class="dark_text">authors:</span>(.*?)</div>' );

		if ( $data == FALSE ) return FALSE;

		$data = static::listValue( $data, '),' );

		foreach ( $data as &$value )
		{
			if ( end( $data ) != $value ) $value = $value . ')';

			if ( $this->config()->reverseName == TRUE ) $value = static::reverseName( $value, '2' );
		}

		return static::setValue( $key, $data );
	}

	/**
	 * Get volume
	 *
	 * @return string
	 */
	protected function _volume() {

		$key = 'volume';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = $this->match('<span class="dark_text">volumes:</span>(.*?)</div>');

		if ( $data == FALSE OR !static::validate( array( 'mode' => 'number' ), $data ) ) return FALSE;

		return static::setValue( $key, static::lastChanges( $data ) );
	}

	/**
	 * Get chapter
	 *
	 * @return string
	 */
	protected function _chapter() {

		$key = 'chapter';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = $this->match( '<span class="dark_text">chapters:</span>(.*?)</div>' );

		if ( $data == FALSE OR !static::validate( array( 'mode' => 'number' ), $data ) ) return FALSE;

		return static::setValue( $key, static::lastChanges( $data ) );
	}

	/**
	 * Get serialization
	 *
	 * @return string
	 */
	protected function _serialization() {

		$key = 'serialization';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = $this->match( '<span class="dark_text">serialization:</span>(.*?)</div>' );

		if ( static::validate( array( 'mode' => 'regex', 'regex_code' => 'none', 'regex_flags' => 'si' ), $data ) ) return FALSE;

		return static::setValue( $key, static::lastChanges( $data ) );
	}

	/**
	 * Get published date
	 *
	 * @return string
	 */
	protected function _published() {

		$key = 'published';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = $this->match( '<span class="dark_text">published:</span>(.*?)</div>' );

		if ( $data == FALSE OR static::validate( array( 'mode' => 'count', 'max_len' => 100 ), $data ) ) return FALSE;

		preg_match( '/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s*(\d+),\s*(\d+)\s*to\s*(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s*(\d+),\s*(\d+)/', $data, $out );

		if ( !empty( $out ) ) {

			$data = array(
				'first_month' => static::lastChanges( $out[1] ),
				'first_day'   => static::lastChanges( $out[2] ),
				'first_year'  => static::lastChanges( $out[3] ),
				'last_month'  => static::lastChanges( $out[4] ),
				'last_day'    => static::lastChanges( $out[5] ),
				'last_year'   => static::lastChanges( $out[6] )
			);

			return static::setValue( $key, $data );
		}

		preg_match( '/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s*(\d+),\s*(\d+)\s*to\s*\?/', $data, $out );

		if ( !empty( $out ) ) {

			$data = array(
				'first_month' => static::lastChanges( $out[1] ),
				'first_day'   => static::lastChanges( $out[2] ),
				'first_year'  => static::lastChanges( $out[3] ),
				'last_month'  => 'no',
				'last_day'    => 'no',
				'last_year'   => 'no'
			);

			return static::setValue( $key, $data );
		}

		preg_match( '/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s*(\d+),\s*(\d+)/', $data, $out );

		if ( !empty( $out ) ) {

			$data = array(
				'first_month' => static::lastChanges( $out[1] ),
				'first_day'   => static::lastChanges( $out[2] ),
				'first_year'  => static::lastChanges( $out[3] )
			);

			return static::setValue( $key, $data );
		}

		return FALSE;
	}

	/**
	 * Get first month of published date
	 *
	 * @return string
	 */
	protected function _publishedfirstmonth() {

		return ( isset( static::$data[ 'published' ][ 'first_month' ] ) ) ? static::$data[ 'published' ][ 'first_month' ] : FALSE;
	}

	/**
	 * Get first day of published date
	 *
	 * @return string
	 */
	protected function _publishedfirstday() {

		return ( isset( static::$data[ 'published' ][ 'first_day' ] ) ) ? static::$data[ 'published' ][ 'first_day' ] : FALSE;
	}

	/**
	 * Get last month of published date
	 *
	 * @return string
	 */
	protected function _publishedlastmonth() {

		return ( isset( static::$data[ 'published' ][ 'last_month' ] ) ) ? static::$data[ 'published' ][ 'last_month' ] : FALSE;
	}

	/**
	 * Get last day of published date
	 *
	 * @return string
	 */
	protected function _publishedlastday() {

		return ( isset( static::$data[ 'published' ][ 'last_day' ] ) ) ? static::$data[ 'published' ][ 'last_day' ] : FALSE;
	}

	/**
	 * Get last year of published date
	 *
	 * @return string
	 */
	protected function _publishedlastyear() {

		return ( isset( static::$data[ 'published' ][ 'last_year' ] ) ) ? static::$data[ 'published' ][ 'last_year' ] : FALSE;
	}

	/**
	 * Get first year of published date
	 *
	 * @return string
	 */
	protected function _publishedfirstyear() {

		return ( isset( static::$data[ 'published' ][ 'first_year' ] ) ) ? static::$data[ 'published' ][ 'first_year' ] : FALSE;
	}

	/**
	 * Get date of first episode
	 *
	 * @return array
	 */
	protected function _firstchapter() {

		$key = 'firstchapter';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = static::match( '<span class="dark_text">published:</span>(.*?)</div>' );

		if ( $data == FALSE OR static::validate( array( 'mode' => 'count', 'max_len' => 100 ), $data ) ) return FALSE;

		preg_match( '/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+(\d\d?),\s*(\d\d\d\d)/', $data, $out );

		if ( !empty( $out ) ) {

			$data = array(
				'month' => static::lastChanges( $out[1] ),
				'day'   => static::lastChanges( $out[2] ),
				'year'  => static::lastChanges( $out[3] )
			);

			return static::setValue( $key, $data );
		}

		preg_match( '/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+(\d\d\d\d)/', $data, $out );

		if ( !empty( $out ) ) {

			$data = array(
				'month' => static::lastChanges( $out[1] ),
				'day'   => 1,
				'year'  => static::lastChanges( $out[2] )
			);

			return static::setValue( $key, $data );
		}

		return FALSE;
	}

	/**
	 * Get month of first chapter
	 *
	 * @return string
	 */
	protected function _chapterdatefirstmonth() {

		return ( isset( static::$data[ 'firstchapter' ][ 'month' ] ) ) ? static::$data[ 'firstchapter' ][ 'month' ] : FALSE;
	}

	/**
	 * Get day of first chapter
	 *
	 * @return string
	 */
	protected function _chapterdatefirstday() {

		return ( isset( static::$data[ 'firstchapter' ][ 'day' ] ) ) ? static::$data[ 'firstchapter' ][ 'day' ] : FALSE;
	}

	/**
	 * Get year of first chapter
	 *
	 * @return string
	 */
	protected function _chapterdatefirstyear() {

		return ( isset( static::$data[ 'firstchapter' ][ 'year' ] ) ) ? static::$data[ 'firstchapter' ][ 'year' ] : FALSE;
	}

	/**
	 * Get date of last episode
	 *
	 * @return array
	 */
	protected function _lastchapter() {

		$key = 'lastchapter';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = static::match( '<span class="dark_text">published:</span>(.*?)</div>' );

		if ( $data == FALSE OR static::validate( array( 'mode' => 'count', 'max_len' => 100 ), $data ) ) return FALSE;

		preg_match( '/\w+\s*\d+,\s*\d+\s*to\s*(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s*(\d\d?),\s*(\d\d\d\d)/', $data, $out );

		if ( !empty( $out ) ) {

			$data = array(
				'month' => static::lastChanges( $out[1] ),
				'day'   => static::lastChanges( $out[2] ),
				'year'  => static::lastChanges( $out[3] )
			);

			return static::setValue( $key, $data );
		}

		preg_match( '/\w+\s*\d+\s*to\s*(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s*(\d\d\d\d)/', $data, $out );

		if ( !empty( $out ) ) {

			$data = array(
				'month' => static::lastChanges( $out[1] ),
				'day'   => 1,
				'year'  => static::lastChanges( $out[2] )
			);

			return static::setValue( $key, $data );
		}

		return FALSE;
	}

	/**
	 * Get month of last chapter
	 *
	 * @return string
	 */
	protected function _chapterdatelastmonth() {

		return ( isset( static::$data[ 'lastchapter' ][ 'month' ] ) ) ? static::$data[ 'lastchapter' ][ 'month' ] : FALSE;
	}

	/**
	 * Get day of last chapter
	 *
	 * @return string
	 */
	protected function _chapterdatelastday() {

		return ( isset( static::$data[ 'lastchapter' ][ 'day' ] ) ) ? static::$data[ 'lastchapter' ][ 'day' ] : FALSE;
	}

	/**
	 * Get year of last chapter
	 *
	 * @return string
	 */
	protected function _chapterdatelastyear() {

		return ( isset( static::$data[ 'lastchapter' ][ 'year' ] ) ) ? static::$data[ 'lastchapter' ][ 'year' ] : FALSE;
	}

	/**
	 * Get year
	 *
	 * @return string
	 */
	protected function _year() {

		$key = 'year';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = static::match( '<span class="dark_text">published:</span>(.*?)</div>' );

		preg_match( '/(\d{4})/', $data, $out );

		return static::setValue( $key, ( isset( $out[1] ) AND $out[1] > 1800 AND $out[1] < 2200 ) ? $out[1] : FALSE );
	}

	/**
	 * Get character
	 *
	 * @return array
	 */
	protected function _characters() {

		$key = 'characters';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		$data = static::matchTable(
		'</div>characters</h2><div.*?">(.+?</table>)</div></div>',
		'<table[^>]*>(.*?)</table>',
		array(
		'<a href="[^"]+/(character/[0-9]+)/[^"]+">[^<]+</a>',
		'<a href="[^"]+/character/[0-9]+/[^"]+">([^<]+)</a>',
		'<small>([^<]+)</small>'
		),
		array(
		'character_link',
		'character_name',
		'character_role'
		),
		static::$limit
		);

		return static::setValue( $key, $data );
	}

	/**
	 * Get adaptation
	 *
	 * @return array
	 */
	protected function _relatedadaptation() {

		$key = 'adaptation';

		$data = static::matchTable(
		'<td.*?>adaptation:</td>.*?(<td.*?>.*?</td>)',
		'(<a href=[^>]+>.*?</a>)',
		array(
		'<a href="[^"]*/(anime/[0-9]+|manga/[0-9]+)[^"]*">.*?</a>',
		'<a href="[^"]+">(.*?)</a>'
		),
		array(
		'link',
		'title'
		),
		static::$limit
		);

		return static::setValue( $key, $data );
	}

	/**
	 * Get sequel
	 *
	 * @return array
	 */
	protected function _relatedsequel() {

		$key = 'sequel';

		$data = static::matchTable(
		'<td.*?>sequel:</td>.*?(<td.*?>.*?</td>)',
		'(<a href=[^>]+>.*?</a>)',
		array(
		'<a href="[^"]*/(anime/[0-9]+|manga/[0-9]+)[^"]*">.*?</a>',
		'<a href="[^"]+">(.*?)</a>'
		),
		array(
		'link',
		'title'
		),
		static::$limit
		);

		return static::setValue( $key, $data );
	}

	/**
	 * Get prequel
	 *
	 * @return array
	 */
	protected function _relatedprequel() {

		$key = 'prequel';

		$data = static::matchTable(
		'<td.*?>prequel:</td>.*?(<td.*?>.*?</td>)',
		'(<a href=[^>]+>.*?</a>)',
		array(
		'<a href="[^"]*/(anime/[0-9]+|manga/[0-9]+)[^"]*">.*?</a>',
		'<a href="[^"]+">(.*?)</a>'
		),
		array(
		'link',
		'title'
		),
		static::$limit
		);

		return static::setValue( $key, $data );
	}

	/**
	 * Get parentstory
	 *
	 * @return array
	 */
	protected function _relatedparentstory() {

		$key = 'parentstory';

		$data = static::matchTable(
		'<td.*?>parent story:</td>.*?(<td.*?>.*?</td>)',
		'(<a href=[^>]+>.*?</a>)',
		array(
		'<a href="[^"]*/(anime/[0-9]+|manga/[0-9]+)[^"]*">.*?</a>',
		'<a href="[^"]+">(.*?)</a>'
		),
		array(
		'link',
		'title'
		),
		static::$limit
		);

		return static::setValue( $key, $data );
	}

	/**
	 * Get sidestory
	 *
	 * @return array
	 */
	protected function _relatedsidestory() {

		$key = 'sidestory';

		$data = static::matchTable(
		'<td.*?>side story:</td>.*?(<td[^>]*>.*?</td>)',
		'(<a href=[^>]+>.*?</a>)',
		array(
		'<a href="[^"]*/(anime/[0-9]+|manga/[0-9]+)[^"]*">.*?</a>',
		'<a href="[^"]+">(.*?)</a>'
		),
		array(
		'link',
		'title'
		),
		static::$limit
		);

		return static::setValue( $key, $data );
	}

	/**
	 * Get other
	 *
	 * @return array
	 */
	protected function _relatedother() {

		$key = 'other';

		$data = static::matchTable(
		'<td.*?>other:</td>.*?(<td[^>]*>.*?</td>)',
		'(<a href=[^>]+>.*?</a>)',
		array(
		'<a href="[^"]*/(anime/[0-9]+|manga/[0-9]+)[^"]*">.*?</a>',
		'<a href="[^"]+">(.*?)</a>'
		),
		array(
		'link',
		'title'
		),
		static::$limit
		);

		return static::setValue( $key, $data );
	}

	/**
	 * Get spinoff
	 *
	 * @return array
	 */
	protected function _relatedspinoff() {

		$key = 'spinoff';

		$data = static::matchTable(
		'<td.*?>spin\-off:</td>.*?(<td[^>]*>.*?</td>)',
		'(<a href=[^>]+>.*?</a>)',
		array(
		'<a href="[^"]*/(anime/[0-9]+|manga/[0-9]+)[^"]*">.*?</a>',
		'<a href="[^"]+">(.*?)</a>'
		),
		array(
		'link',
		'title'
		),
		static::$limit
		);

		return static::setValue( $key, $data );
	}

	/**
	 * Get alternativeversion
	 *
	 * @return array
	 */
	protected function _relatedalternativeversion() {

		$key = 'alternativeversion';

		$data = static::matchTable(
		'<td.*?>alternative version:</td>.*?(<td[^>]*>.*?</td>)',
		'(<a href=[^>]+>.*?</a>)',
		array(
		'<a href="[^"]*/(anime/[0-9]+|manga/[0-9]+)[^"]*">.*?</a>',
		'<a href="[^"]+">(.*?)</a>'
		),
		array(
		'link',
		'title'
		),
		static::$limit
		);

		return static::setValue( $key, $data );
	}

	/**
	 * Get link of the request page
	 *
	 * @return string
	 */
	protected function _link() {

		$key = 'link';

		if ( isset( static::$data[ $key ] ) ) return static::$data[ $key ];

		return static::setValue( 'link', $this->lastChanges( $this->request()::$requestData[ 'url' ] ) );
	}
}