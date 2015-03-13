# Jax
#### Ajax Library for PHP 5.5.0+
Jax is a library that make Ajax easy.

#### Example
Here is a quick example of how Jax works using a *server* and *client*
```php
<?php
use Jax\Server;
use Jax\Client;

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
		// invalid user name (uses template 'error' above)
		Server::error('Invalid user name');
	}

	// save user in database (some database logic would go here)

	// set success response
	Server::response()->jQuery('#response')->append('User ' . $user_name
		. ' has been added<br />');

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
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js">
			</script>
		<!-- print jax client function for making calls to server -->
		<?=$jax_client->script?>
	</head>
	<body>
		<label>Name:</label>
		<input type="text" id="uname" />
		<!-- add onclick event for adding user -->
		<button onclick="jax('addUser', $('#uname').val());">Add User</button>

		<!-- use placeholder for jax messages -->
		<div id="response"></div>
	</body>
</html>
```
This simple example uses a Jax client function to send user names using ajax to the Jax server.


#### Setting up a Jax Server
Setting up a Jax server that is capable of listening for ajax client requests is simple. For example, create a file `/ajax-server.php` in your project directory, then add the following code
```php
use Jax\Server;

// setup class autoloading (not required if using different autoloader)
require_once './lib/Jax/autoload.php';
autoload(['./lib/']); // or wherever class files are stored

// set an error template
// this will be used if an error occurs
Server::template('error', function($message) {
	// first have console log error
	Server::response()->console->log('Jax server error: ' . $message);
	// some logic could go here to store in database to warn sys admins
	// finally warn end-user
	Server::response()->alert('An event error has occurred.');
});
// set a success template
// this will be used if a call is successful
Server::template('success', function() {
    Server::response(['success' => 1]);
});

// now setup listeners for ajax client actions
// set GET listener for 'addUser' action
Server::get('addUser', function($user_name) {
	// check if name exists in request
	if(!$user_name)
	{
	    // invalid user name, warn end-user
	    // this will use the 'error' template that is set above
		Server::error('Invalid user name');
	}

	// save user in database (some database logic would go here)

	// set success response using template
	Server::template('success');
});

// set GET listener for 'getUser' action
Server::get('getUser', function($id) {
    // classes can also be used for response (make sure autoloading is setup correctly)
    $user = new \User($id);
    if($user)
    {
        // set response data
        Server::response(['name' => $user->name]);
    }
    else
    {
        // cannot find user
        Server::error('User not found');
    }
});
```
Now we can setup client calls to the Jax server.
> Response templates can be used for any type of canned response. For example, if many response are simply updating a DIV's content this can be done using a template, for example
```php
// register template
Server::template('status', function($data) {
	Server::response()->jQuery('#' . $data['id']).text($data['status']);
});
// ... more code
// now we can use in a response
Server::get('updateUser', function($id, $new_name) {
	// do a database call to update user
	if(db_update_user($id, $new_name))
	{
		Server::template('status', ['id' => 'div_status', 'status' => 'User updated']);
	}
	else // update failed
	{
		Server::template('status', ['id' => 'div_status',
			'status' => 'Failed to update user']);
	}
});
```


#### Using the Response Object
Responses are set in the Jax *server*. For example
```php
Server::get('getUser', function($user_name) {
    // query database to get user data
    $user = db_get_user();
    // respond with user data in a DIV
    Server::response()->jQuery('#response')->append('The user is: ' . $user->name);
});
```
This type of response would be sent to `eval()` when used with the `jax()` JavaScript function. There are two types of responses:

1. JavaScript code returned as `string` and processed with `eval()`
2. JSON data

The example above illustrates the first type of response. The second type of response, JSON data, can be acheived using the `Server::response(array $data)` method, for example the abover code could be change to return JSON:
```php
    // responsd with user data
    Server::response(['name' => $user->name]);
```
Now the response from the Jax server would be:
```
{"name":"Shay Anderson"}
```
> Methods/properties can be called when using the `\Jax\Server::response()` method, like changing the background color:
```php
Server::response()->jQuery('body')->css('background-color', '#ccc');
```
Or using the JavaScript `console.log(x)` method like:
```php
Server::response()->console->log('Log something');
```
However, the methods `__getData__()` and `__setData__()` cannot be used as they are already defined in the `\Jax\Server\Response` class.


#### Using Jax Client
Using client ajax requests are easy using the Jax client object. Here is an example page `/add-user.php` that makes use of the client request
```php
<?php
// setup autoloading/bootstrapping here
use Jax\Client;

// setup client object that points to the Jax server location
$client = new Client('/ajax-server.php');

// now for the page HTML
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js">
			</script>
		<?=$client->script?>
		<script>
			function addUserFunc(data, div)
			{
				if(data.hasOwnProperty('success')) // check for success flag
				{
				    // show end-user user has been added
					$('#response').append('User has been added<br />');
				}
			}
		</script>
	</head>
	<body>
		<label>Name:</label>
		<input type="text" id="uname" />
		<button onclick="addUserFunc(jaxGet('addUser', $('#uname').val()));">
			Add User</button>
		<div id="response"></div>
	</body>
</html>
```
This example is very simple. The JavaScript function `addUserFunc()` is called when the button is clicked. The Jax function `jaxGet()` is used to query the Jax server. The `jaxGet()` function is loaded because of the `<?=$client->script?>` code - this imports the core JS `jax()` (used when `eval()` is used on response string data) and `jaxGet()` (used when getting back JSON data).

> The <?=$client->script?> could easily be replaced with a client-side JS file, as long as the Jax server location doesn't change, for example the file `/skin/js/jax.js` could be include and hold the code:
```
function jax(){$.ajax({url:'/ajax-server.php',
type:'GET',data:{data:arguments},async:false,success:function(r)
{if(typeof(r) === 'string') {try{eval(r);}catch(e){}}else{}}});}
function jaxGet(){var ar;$.ajax({url:'/ajax-server.php',type:'GET',
data:{data:arguments},dataType:'json',async:false,
success:function(r){ar=r;}});return ar;}
```

Next, a simple page `/get-user.php` could be created with the same kind of logic for querying the Jax server for user data.
> Other options can be used when setting the `\Jax\Client` object, for example the constructor uses the params:
```php
new Client($server_url, $request_type = self::REQUEST_TYPE_GET, $async = false,
	$debug = false, $script_tags = true, $func_name = 'jax', $func_name_get = 'jaxGet')
```
Any of these options can be changed to as required by the client functions `jax()` or `jaxGet()` (and even those function names can be changed using the `$func_name` and `$func_name_get` params).
When debugging is set to `true` the `console.log()` method is used in the `jax()` and `jaxGet()` functions for helpful debugging messages, like when the requested action has executed correctly.


#### Manual Client Requests
Manual client requests can be used with a Jax server *without* having to use the Jax client functions. Here is an example of a simple GET request using jQuery:
```javascript
function getUser()
{
	$.get('/ajax-server.php', {data:{action:'getUser', id:$('#uid').val()}},
		function(response){
			console.log(response);
			try { eval(response); }
			catch(e) { alert(e); }
		});
}
```
This is a simple request that will `eval()` the server response. If this was a post request the `$.get` function name can be replaced with `$.post`
> When call the Jax server the *first* data element must be the *action name* and have the element key of `0` or `action`, for example `[0 => 'getUser', ...]` or `['action' => 'getUser', ...]`

If the client call needs to get a JSON string response back use different handling, for example:
```javascript
function getUserData()
{
	$.ajax({
		url:'/ajax-server.php',
		data:{data:{action:'getUserData',id:$('#uid').val()}},
		type:'GET',
		async:false, // must have async set to false
		success: function(response) {
			console.log(response);
			// use data:
			alert('User name is: ' + response.name);
		}
	});
}
```
> The Jax client object can be helpful even when creating manual ajax calls, for example, if the Jax client object is set as `$client = new \Jax\Client('/ajax-server.php')` then a JavaScript function can build using the data:
```javascript
function getUserData()
{
	$.ajax({
		url:<?=$client->url?>,
		data:<?=$client->data?>,
		// rest of code here
```
Now `getUserData()` can be called using something like:
```javascript
getUserData('getUserData', $('#uid').val());
```

Posting form data can also be accomplished. Here is an example HTML form:
```html
<form id="user_form">
	<label>Name:</label>
	<input type="text" id="uname" />
	<label>Age:</label>
	<input type="text" id="uage" />
	<button type="submit">Add User</button>
</form>
```
Next, setup the ajax call to the Jax server:
```javascript
<script>
$('form').submit(function(event){
	var fdata = {
		action:'postForm',
		uname: $('#uname').val(),
		uage: $('#uage').val()
	};

	$.ajax({
		type:'POST',
		url:'/ajax-server.php',
		data:{data:fdata},
		async:false,
		success: function(data)
		{
			try { eval(data); }
			catch(e) { alert(e); }
		}
	});
	
	event.preventDefault();
});
</script>
```