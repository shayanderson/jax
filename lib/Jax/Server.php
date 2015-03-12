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
namespace Jax;

/**
 * Server class
 *
 * @author Shay Anderson 03.15 <http://www.shayanderson.com/contact>
 */
class Server
{
	/**
	 * Request data keys
	 */
	const REQUEST_KEY_DATA = 'data';

	/**
	 * Request types
	 */
	const REQUEST_TYPE_ALL = 'All';
	const REQUEST_TYPE_GET = 'Get';
	const REQUEST_TYPE_POST = 'Post';

	/**
	 * Response is JSON flag
	 *
	 * @var boolean
	 */
	private static $__is_json_response = false;

	/**
	 * Dispatch request and print formatted response
	 *
	 * @param string $type
	 * @param string $action
	 * @param \callable $callback
	 * @return void
	 */
	private static function __dispatch($type, $action, callable &$callback)
	{
		if(($data = self::__getData($type, $action)) !== false) // action matched
		{
			// check if callback has more paramters than data key/value pairs
			if(count((new \ReflectionFunction($callback))->getParameters()) > count($data))
			{
				self::error('Action callback has more parameters than data contains');
			}

			// execute callback
			call_user_func_array($callback, $data);

			if(!headers_sent()) // set headers
			{
				header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // no cache

				if(self::$__is_json_response)
				{
					header('Content-Type: application/json');
				}
			}

			// print response
			echo self::response()->__getData__(true);

			// end response
			exit;
		}
	}

	/**
	 * Request type data getter
	 *
	 * @staticvar array $request
	 * @param string $type
	 * @param string $action
	 * @return mixed (boolean|array, false when action does not match request action)
	 */
	private static function &__getData($type, $action)
	{
		static $request = [
			self::REQUEST_TYPE_ALL => null,
			self::REQUEST_TYPE_GET => null,
			self::REQUEST_TYPE_POST => null
		];

		$data = false;

		if($request[$type] === null) // lazy load
		{
			$request[$type] = __NAMESPACE__ . '\\Server\\Request\\' . $type;
			$request[$type] = new $request[$type](self::REQUEST_KEY_DATA);
		}

		if($action === $request[$type]->action)
		{
			$data = &$request[$type]->data;
		}

		return $data;
	}

	/**
	 * Error template alias method (same as self::template('error', 'message'))
	 *
	 * @param string $message
	 * @return void
	 */
	public static function error($message)
	{
		self::template('error', $message);
	}

	/**
	 * Register GET request action and callback
	 *
	 * @param string $action
	 * @param \callable $callback
	 * @return void
	 */
	public static function get($action, callable $callback)
	{
		self::__dispatch(self::REQUEST_TYPE_GET, $action, $callback);
	}

	/**
	 * Register POST request action and callback
	 *
	 * @param string $action
	 * @param \callable $callback
	 * @return void
	 */
	public static function post($action, callable $callback)
	{
		self::__dispatch(self::REQUEST_TYPE_POST, $action, $callback);
	}

	/**
	 * Register generic request (GET and/or POST) action and callback
	 *
	 * @param string $action
	 * @param \callable $callback
	 * @return void
	 */
	public static function request($action, callable $callback)
	{
		self::__dispatch(self::REQUEST_TYPE_ALL, $action, $callback);
	}

	/**
	 * Response object getter
	 *
	 * @staticvar \Jax\Server\Response $response
	 * @param mixed $data (null or array for JSON data response)
	 * @return \Jax\Server\Response
	 */
	public static function &response($data = null)
	{
		static $response;

		if($response === null) // instantiate object
		{
			$response = new Server\Response;
		}

		// array data only (JSON) response setter (non-script response)
		if($data !== null && is_array($data))
		{
			$response->__setData__($data);
			self::$__is_json_response = true;
		}

		$response::$__response_id__++; // increment response ID

		return $response;
	}

	/**
	 * Register/use template
	 *
	 * @staticvar array $templates
	 * @param string $name (template name)
	 * @param mixed $value_or_callback (callable when registering, other when using template)
	 * @return boolean (true on handled, false on not handled)
	 */
	public static function template($name, $value_or_callback = null)
	{
		static $templates = [];

		if(is_callable($value_or_callback)) // register/setter
		{
			$templates[$name] = $value_or_callback;
			return true; // handled
		}
		elseif(isset($templates[$name])) // use/getter
		{
			$templates[$name]($value_or_callback);
			return true; // handled
		}

		return false; // not handled
	}
}