Simple validation for PHP 5.3+
==============================

There are so many differents ways to make a validation for forms on php, each framework(Laravel 4+, Symfony 2+, Codeignitier, FuelPHP, CakePHP, bla bla bla) has their own implementation on validations, ok, that's ok, it is expected that they supply us that funcionality, but, sometimes all those validations are complicated, sometime we need to insert the rules inside a model when we just wanna validate a simple html form and get the possible failures, just simple like that, it ok that you insert your rules inside your model, this is another way just with supper powers...

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

* required (validate that the value is not null or empty)
* max (validate that dont exced a number)
* min (validate that is upper than)
* regex (for regular expression purposes)
* filter ([php native filter](http://www.php.net/manual/en/filter.filters.php) :D)

### So let's rock and roll:

Here is an example of the structure for write your own validations, in this case I wanna validate a form for just save users over 18 years old, and of course valid first name, last name and email

```html
<form method="post" action="#">
	<input type="hidden" name="_form" value="frm_user_add">
	<input type="text" name="email">
	<input type="text" name="f_name">
	<input type="text" name="l_name">
	<input type="text" name="age">
</form>  

```

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
				'onRequired' => 'Insert the email or username for begin',
				'onFilter' => 'Invalid email, please try another'
			)			
		),

		'f_name' => array(
			'requirements' => array('required' => true, 'min' => 3, 'max' => 30, 'regex' => '/^[a-z ]+$/i'),
			'messages' => array(
				'onRequired' => 'Last name is required for create a user',
				'onMin' => 'Last name is very short',
				'onMin' => 'Last name is too long',
				'onRegex' => 'Invalid user name, please try again'
			)
		),		

		'l_name' => array(
			'requirements' => array('required' => true, 'min' => 3, 'max' => 30 'regex' => '/^[a-z ]+$/i'),
			'messages' => array(
				'onRequired' => 'Last name is required for create a user',
				'onMin' => 'Last name is very short',
				'onMin' => 'Last name is too long',
				'onRegex' => 'Invalid user name, please try again'
			)
		),

		'age' => array(
			'requirements' => array('required' => true, 'min' => 18),
			'messages' => array(
				'onRequired' => 'Last name is required for create a user',
				'onMin' => 'Last name is very short',
				'onMin' => 'Last name is too long'
			)
		),		
	),
);

```

As you can see `frm_user_add` is the name of a form, why inside a hidden field? cuz is the onlyt way the the form name in POST, there are some hacks appending the name uwing js, for now, do this way :), followed by an associative array for the fields.

`email` is a field inside the form `frm_build_add`, we wanna that this field exist and must to be **valid** using the [filters inside PHP](http://www.php.net/manual/en/filter.filters.php), depending of each error, the string inside message is gonna be returned... Awesome right? :D

`f_name` is a little complicated, is this case we want that the field be required, > 3 and < 30, and needs to match with the regular expression `/^[a-z ]+$/i`... 

For `l_name` acts the same rule that `f_name`...

And the last one `age`, I just wanna that exist, and the minimun value must to be 18, and that's it... :)