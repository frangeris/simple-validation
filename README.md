Simple validation for PHP 5.5+
==============================

There are so many differents ways to make a validation for forms on php, each framework(Laravel 4+, Symfony 2+, CodeIgniter , FuelPHP, CakePHP, bla bla bla) has their own implementation on validations, ok, that's ok, it is expected that they supply us that funcionality, but, sometimes all those validations are complicated, sometimes we dont want to insert the rules inside a model when we just wanna validate a simple html form and get the possible failures, just simple like that, it ok that you insert your rules inside your model, this is another way just with super powers...

### Installation

Easiest way would be using [Composer](http://getcomposer.org) and adding this to
the composer.json requirement section.

```json
{
  "require": {
    "frangeris/simple-validation": "dev-master"
  }
}
```

It's PSR-0 compliant, so you can also use your own custom autoloader.

### Usage

In general, every form could have **rules**, what is a rule?, rule is just what you want to validate, just like that, here is the list of rules we support:

* regex (for regular expression purposes)
* min (validate that is upper than)
* max (validate that don't exced a number)
* required (validate that the value is not null or empty)
* filter ([php native filter](http://www.php.net/manual/en/filter.filters.php) :D)
* uploaded (validate that the file has been uploaded)
* file_max (validate the max size of the file using MB for comparation)
* allow_format (specify with formats are allowed for the file uploaded)
* not_equal (verify that the value is not equal to expressed pattern)

### So let's rock and roll:

Here is an example of the structure for write your own validations, in this case I wanna validate a form for just save users over 18 years old, and of course a valid first name, last name and email.

```html
<form method="post" action="#">	
	<input type="text" name="email">
	<input type="text" name="f_name">
	<input type="text" name="l_name">
	<input type="text" name="age">
</form>  

```

file with rules:

```php

<?php

/*
|--------------------------------------------------------------------------
| Secure validations
|--------------------------------------------------------------------------
| Here you need to define the rules of each form that you want to validate, 
| each index represent a form <form_name>, followed by an associative 
| array with the fields that contains; each field will have [requirements] 
| and [messages], each [requirements] must have a message to respond (in 
| case of failure). Putting the prefix on + <requirement> following 
| cammell case convention for the event
|
*/

return array(
	'frm_user_add' => array(
		'email' => array(
			'requirements' => array('required' => true, 'filter' => FILTER_VALIDATE_EMAIL),
			'messages' => array(				
				'on_required' => 'Insert the email or username for begin',
				'on_filter' => 'Invalid email, please try another'
			)			
		),

		'f_name' => array(
			'requirements' => array('required' => true, 'min' => 3, 'max' => 30, 'regex' => '/^[a-z ]+$/i'),
			'messages' => array(
				'on_required' => 'Last name is required for create a user',
				'on_min' => 'Last name is very short',
				'on_max' => 'Last name is too long',
				'on_regex' => 'Invalid user name, please try again'
			)
		),		

		'l_name' => array(
			'requirements' => array('required' => true, 'min' => 3, 'max' => 30 'regex' => '/^[a-z ]+$/i'),
			'messages' => array(
				'on_required' => 'Last name is required for create a user',
				'on_min' => 'Last name is very short',
				'on_max' => 'Last name is too long',
				'on_regex' => 'Invalid user name, please try again'
			)
		),

		'age' => array(
			'requirements' => array('required' => true, 'min' => 18),
			'messages' => array(
				'on_required' => 'Last name is required for create a user',
				'on_min' => 'Last name is very short',
				'on_max' => 'Last name is too long'
			)
		),		
	),
);

```

As you can see `frm_user_add` is the name of a form, followed by an associative array for the fields requirements and messages.

`email` is a field inside the form `frm_build_add`, we wanna that this field exist and must to be **valid** using the [filters inside PHP](http://www.php.net/manual/en/filter.filters.php), depending of each error, the string inside message is gonna be returned... Awesome right? :D

`f_name` is a little complicated, is this case we want that the field be required, > 3 and < 30, and needs to match with the regular expression `/^[a-z ]+$/i`... 

For `l_name` acts the same rule that `f_name`...

And the last one `age`, I just wanna that exist, and the minimun value must to be 18, and that's it... :)

In the PHP side just call the validator class and pass the fields on request and the name of the form:

This example is using [phalcon framework](http://phalconphp.com/en/) making a POST request

```php

<?php

use Simple\Validation\Validator;

class ApplicationController extends \Phalcon\Mvc\Controller
{
	public function addAction()
	{
		if($this->request->isPost())
		{
			$request = $this->request->getPost();

			// Here make the trick for pass the rules
    		$validator = Validator::getInstance(require_once(URL_TO_YOUR_FILE_WITH_RULES));

    		// Begin the validation (<form_name>, <array_with_values>)
			if($validator->isValid($request, $request))
			{
				echo 'valid';
				// Do your stuff
				
			} else
				echo 'Error: '.$validator->getLastError(true)); // This will give you the last error
			
			// If you wanna all the errors with messages just call
			print_r($validator->getErrors());
		}
	}	
}

```
The required function can be placed in another place, just remember that the `isValid` method needs this array for validate...

PS: If you are using phalcon, dont do this in this way, create a DI inside your services and use it as di, someting like:

```php

// Set up the validator service
$di->set('validator', function() use ($config)
{
    $validations = require_once($config->security->validations);
    return Simple\Validation\Validator::getInstance($validations);
});