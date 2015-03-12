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
use Jax\Server;
use Jax\Client;

/**
 * Jax Example: Server and Client
 *
 * @author Shay Anderson 03.15 <http://www.shayanderson.com/contact>
 */

// include autoload for autoloading classes
require_once './lib/Jax/autoload.php';

// setup class autoloading
autoload(['./lib/']);

// setup error template
Server::template('error', function($message) {
	// first have console log error
	Server::response()->console->log('Jax server error: ' . $message);
	// next alert the error for debugging
	Server::response()->alert('Jax server error: ' . $message);
});

// register GET request action 'addUser'
Server::get('addUser', function($user_name) {
	// check if name exists in request
	if(!$user_name)
	{
		Server::error('Invalid user name'); // invalid user name
	}

	// save user in database (some database logic would go here)

	// set success response
	Server::response()->jQuery('#response')->append('User \'' . $user_name
		. '\' has been added<br />');

	// and turn the response message red
	Server::response()->jQuery('#response')->css('color', '#f00');
});

// next setup a client
// set ajax server request URL same as example page, normally
// this would be in a separate location like '/ajax-server.php'
$jax_client = new Client('example.php');
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Jax Demo</title>
		<!-- jquery required -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		<!-- print jax client function for making calls to server -->
		<?=$jax_client->script?>
	</head>
	<body>
		<label>Name:</label>
		<input type="text" value="Shay Anderson" id="uname" />
		<!-- add onclick event for adding user -->
		<button onclick="jax('addUser', $('#uname').val());">Add User</button>

		<!-- use placeholder for jax messages -->
		<div id="response"></div>
	</body>
</html>