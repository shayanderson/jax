<?php
/**
 * Jax - Ajax Library for PHP 5.5.0+
 *
 * @package Jax
 * @version 0.0.1
 * @copyright 2015 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <http://www.opensource.org/licenses/mit-license.php>
 * @link <https://github.com/shayanderson/jax>
 */
namespace Jax\Server;

/**
 * Server response class
 *
 * @author Shay Anderson 03.15 <http://www.shayanderson.com/contact>
 */
class Response
{
	/**
	 * Response data
	 *
	 * @var array
	 */
	private static $__data = [];

	/**
	 * JSON only response data
	 *
	 * @var array
	 */
	private static $__data_response;

	/**
	 * Current response ID
	 *
	 * @var int
	 */
	public static $__response_id__ = 0;

	/**
	 * Function data setter (ex: ->alert(x))
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return \Jax\Server\Response
	 */
	public function &__call($name, array $arguments)
	{
		foreach($arguments as $k => &$v)
		{
			$v = json_encode($v);
		}

		// set function call: func(arg1, ...)
		self::$__data[self::$__response_id__][] = $name . '(' . implode(',', $arguments) . ')';

		return $this;
	}

	/**
	 * Data property setter (ex: ->document...)
	 *
	 * @param string $name
	 * @return \Jax\Server\Response
	 */
	public function &__get($name)
	{
		self::$__data[self::$__response_id__][] = $name;
		return $this;
	}

	/**
	 * Response data getter
	 *
	 * @param boolean $format_data
	 * @return mixed (array when not formatting, else string)
	 */
	final public static function __getData__($format_data = false)
	{
		if(self::$__data_response !== null) // data only response
		{
			return $format_data ? json_encode(self::$__data_response) : self::$__data_response;
		}
		else // script response
		{
			if(!$format_data)
			{
				return self::$__data;
			}

			$res = '';

			foreach(self::$__data as $k => $v)
			{
				$res .= implode('.', $v) . ';';
			}

			return $res;
		}
	}

	/**
	 * Name/value pair data setter (ex: ->innerHTML = 'x')
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function __set($name, $value)
	{
		self::$__data[self::$__response_id__][] = $name . '=' . json_encode($value);
	}

	/**
	 * Response data setter (when JSON response only)
	 *
	 * @param array $data
	 * @return void
	 */
	final public static function __setData__(array $data)
	{
		self::$__data_response = $data;
	}

	/**
	 * Script string setter
	 *
	 * @param string $script (ex: 'alert("test")')
	 * @return void
	 */
	final public static function script($script)
	{
		self::$__data[self::$__response_id__][] = $script;
		self::$__response_id__++;
	}
}