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
 * Server request class
 *
 * @author Shay Anderson 03.15 <http://www.shayanderson.com/contact>
 */
abstract class Request
{
	/**
	 * Action name
	 *
	 * @var string
	 */
	public $action;

	/**
	 * Request data
	 *
	 * @var array
	 */
	public $data;

	/**
	 * Init
	 *
	 * @param string $key_data
	 */
	public function __construct($key_data)
	{
		$this->data = &$this->_getData();

		if(isset($this->data[$key_data]))
		{
			if(is_array($this->data[$key_data])) // data is array
			{
				$this->data = $this->data[$key_data];
			}
			else // data is JSON string
			{
				$this->data = json_decode($this->data[$key_data], true);
			}

			if(isset($this->data[0]) || isset($this->data['action'])) // set action
			{
				$this->action = array_shift($this->data);

				// check for data with labels [0 => ['param1' => 'val1', ...]]
				if(isset($this->data[0]) && is_array($this->data[0]))
				{
					$this->data = $this->data[0]; // set to ['param1' => 'val1', ...]
				}
			}
		}
	}

	/**
	 * Request data getter
	 *
	 * @return array
	 */
	abstract protected function &_getData();
}