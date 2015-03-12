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
 * Client class
 *
 * @author Shay Anderson 03.15 <http://www.shayanderson.com/contact>
 */
class Client
{
	/**
	 * Request methods
	 */
	const REQUEST_TYPE_GET = 'GET';
	const REQUEST_TYPE_POST = 'POST';

	/**
	 * Data string (ex: '{data:JSON.stringify(arguments)}')
	 *
	 * @var string
	 */
	public $data;

	/**
	 * Script with function block
	 *
	 * @var string
	 */
	public $script;

	/**
	 * Request type string (ex: 'GET')
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Request URL (ex: '/ajax-server.php')
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Init
	 *
	 * @param string $server_url (relative or absolute, ex: '/ajax-server.php')
	 * @param string $request_type (ex: 'GET')
	 * @param boolean $async (false is synchronous, true is asynchronously)
	 * @param boolean $debug (debug console + alert messages allowed if errors)
	 * @param boolean $script_tags (include <script>*</script> tags in $script property value)
	 * @param string $func_name (actual function name in script block, ex: 'jax' => 'jax()')
	 * @param string $func_name_get (actual get function name in script block)
	 */
	public function __construct($server_url, $request_type = self::REQUEST_TYPE_GET,
		$async = false, $debug = false, $script_tags = true, $func_name = 'jax',
		$func_name_get = 'jaxGet')
	{
		$this->url = $server_url;
		$this->type = $request_type;
		$this->data = '{' . Server::REQUEST_KEY_DATA . ':arguments}';

		// prepare script
		$sep = $debug ? PHP_EOL : '';
		$this->script = ( $script_tags ? '<script>' : '') . $sep
			. 'function ' . $func_name . '(){' . $sep
				. ( $debug ? 'console.log(\'Jax calling: \' + arguments[0]);' : '' )
				. '$.ajax({' . $sep
					. 'url:\'' . $this->url . '\',' . $sep
					. 'type:\'' .  $this->type . '\',' . $sep
					. 'data:' . $this->data . ',' . $sep
					. ( !$async ? 'async:false,' . $sep : '' )
					. 'success:function(r) {' . $sep
						. 'if(typeof(r) === \'string\') {' . $sep
							. 'try{eval(r);' . ( $debug
								? 'console.log(\'Jax successful script call\');' : '' ) . '}'
									. $sep
							. 'catch(e){' . ( $debug
								? 'console.log(\'Jax error: \' + e);' . $sep
									. 'alert(\'Jax error: \' + e);' : '' ) . '}' . $sep
						. '}else{' . $sep
							. ( $debug ? 'if(r.hasOwnProperty(\'error\')) {' . $sep
								. 'console.log(\'Jax return error: \' + r.error);' . $sep
							. '}else{' . $sep
								. 'console.log(\'Jax successful string call\');' . $sep
							. '}' . $sep : '' )
						. '}' . $sep
					. '}' . $sep
					. ( $debug ?
						',error:function(r,s,e){' . $sep
							. 'console.log(\'Jax Error: \' + e);' . $sep
							. 'alert(\'Jax error: \' + e);}' . $sep : '' )
				. '});' . $sep
			. '}' . $sep
			. 'function ' . $func_name_get . '(){' . $sep
				. ( $debug ? 'console.log(\'Jax calling: \' + arguments[0]);' : '' )
				. 'var ar;' . $sep
				. '$.ajax({' . $sep
					. 'url:\'' . $this->url . '\',' . $sep
					. 'type:\'' .  $this->type . '\',' . $sep
					. 'data:' . $this->data . ',' . $sep
					. 'dataType:\'json\',' . $sep
					. 'async:false,' . $sep
					. 'success:function(r){' . $sep
						. ( $debug ? 'console.log(\'Jax successful script call\');' . $sep : '' )
						. 'ar=r;' . $sep
					. '}' . $sep
					. ( $debug ?
						',error:function(r,s,e){' . $sep
							. 'console.log(\'Jax Error: \' + e);' . $sep
							. 'alert(\'Jax error: \' + e);}' . $sep : '' )
				. '});' . $sep
				. 'return ar;' . $sep
			. '}' . $sep
		. ( $script_tags ? '</script>' : '' ) . $sep;
	}
}