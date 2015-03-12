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
namespace Jax\Server\Request;

/**
 * Server request all class
 *
 * @author Shay Anderson 03.15 <http://www.shayanderson.com/contact>
 */
class All extends \Jax\Server\Request
{
	/**
	 * Request data getter
	 *
	 * @return array
	 */
	protected function &_getData()
	{
		return $_REQUEST;
	}
}