<?php namespace Simple\Validation;

/**
 * Validations manager for validate fields forms
 * 
 * @version 0.9
 */
class Validator
{
	/**
	 * Instance of class
	 * 
	 * @var \Simple\Validation\Validator
	 */
	private static $_instance;

	/**
	 * Collection of functions availables 
	 * to make validations
	 * 
	 * @var array
	 */
	private $collection;

	/**
	 * Requirements loaded from IValidator
	 * 
	 * @var array
	 */
	private $reqLoaded;

	/**
	 * Error occurred on validation
	 * 
	 * @var array
	 */
	private $errorEvent;

	/**
	 * Construct
	 */
	private function __construct(array $validations)
	{
		$this->validations = $validations;
		$this->collection = [
			'regex' 		=> '_regex',
			'min' 			=> '_min',
			'max' 			=> '_max',
			'required' 		=> '_required',
			'filter' 		=> '_filter',
			'uploaded'		=> '_uploaded',
			'file_max'		=> '_file_max',
			'allow_format'	=> '_allow_format',
			'not_equal'		=> '_not_equal'
		];	
	}

	/**
	 * Keeping concurrence
	 * 
	 * @param array $validation Validations
	 * @return \Simple\Validation\Validator
	 */
	public static function getInstance($validations = [])
	{
		if(null === self::$_instance)
			self::$_instance = new self($validations);
		return self::$_instance;
	}

	/**
	 * Check if the values received are valid using 
	 * requirements defined for each field
	 *
	 * @param $form_name Name of the form defined on validations file
	 * @param array $fields Fields of the form send on request
	 * @return boolean
	 */
	public function isValid($form_name, $fields)
	{
		if($this->ifExist($form_name))
		{
			if(isset($_FILES))
				$fields = array_merge($fields, $_FILES);
			$requirements = $this->get($form_name, 'requirements');			
			$difference = array_diff_key($requirements, $fields);	

			// Some fields with validation dont exist on fields request
			if($difference) 
			{				
				foreach($difference as $field => $reqs) // Are the required?
					if(array_key_exists('required', $reqs))
					{
						// Error processing validation, there are fields with 
						// validation that do not exist in the request post values
						$this->errorEvent[$field] = 'required';
						$this->setErrorEvent($form_name, $field);				
						return false;
					}
			}

			// Continue verifying others fields that exist
			foreach ($fields as $field => $value) 
			{
				if(isset($requirements[$field]) && !$this->verify($value, $requirements[$field], $field)) // Some field failed
				{
					$this->setErrorEvent($form_name, $field);									
					return false;					
				}
			} 

			return true; // All data is correct
		} 

		return true; // Dont exist validation for this form
	}	

	/**
	 * Get the parameter of form
	 * previously loaded from load()
	 *
	 * @param string $form_name Name of the form
	 * @param string $parameter Name of the parameter to get
	 * @return mixed
	 */
	public function get($form_name, $parameter)
	{
		$to_sent = [];		
		$fields = isset($this->validations[$form_name])?$this->validations[$form_name] :null;
		if(!empty($fields))
		{				
			foreach ($fields as $field => $value) 
				$to_sent[$field] = $value[$parameter];
		} 

		return $to_sent;
	}

	/**
	 * Check that the values be with
	 * the requirements using func events
	 *
	 * @param mixed $value Value to verify
	 * @param array $requirements Requirements of the field
	 * @param mixed $field [optional]
	 * @param mixed $extraField [optional]
	 */
	public function verify($value, array $requirements, $field = null, $extraField = null)
	{
		foreach($requirements as $key => $valueExp)
		{
			$func = $this->collection[$key];			
			if(!$this->$func($value, $valueExp))
			{
				$this->errorEvent[$field] = $key;
				return false;				
			} elseif(!empty($extraField))
			{
				$this->errorEvent[key(array_slice($extraField, 0, 1))] = $key;
				return false;							
			}
		} 

		return true;
	}

	/**
	 * Load the requirements inside this class
	 *
	 * @param array $requirements Requirements to load
	 * @return &\Simple\Validation\Validator
	 */
	public function &load(array $requirements)
	{
		if(!empty($requirements))
		{
			$reqs = [];
			foreach ($requirements as $obj => $fields) 
			{				
				foreach ($fields as $field => $params)
					$reqs[$field] = $params;
			} 

			$this->reqLoaded = $reqs;
		} 

		return $this;
	}

	/**
	 * Verify if the form has requirements
	 *
	 * @param string $formName Name of the form to verify if has requirements
	 * @return boolean
	 */
	public function ifExist($form_name)
	{	
		if(isset($this->validations[$form_name]))
			return true;
		return false;
	}

	/**
	 * Get the invalid fields with errors
	 * 
	 * @param  boolean $all [optional]
	 * @return mixed
	 */
	public function getInvalidField($all = false)
	{
		if(!empty($this->errorEvent))
			return $this->errorEvent;
		return false;
	}

	/**
	 * Set the error occurred
	 * 
	 * @param strin $form Form name to set the error
	 * @param string $field Field that have the error
	 * @throws Exception If have requirements and do not have messages
	 * @return void
	 */
	public function setErrorEvent($form_name, $field)
	{
		$field = is_array($field)?key(array_slice($field, 0, 1)) :$field;
		$requirements = $this->validations[$form_name];
		if($errorMessage = isset($requirements[$field]['messages']['on_'.$this->errorEvent[$field]])?$requirements[$field]['messages']['on_'.$this->errorEvent[$field]] :null)
		{
			$this->errorEvent['content'] = ['field' => $field, 'message' => $errorMessage];
			return $errorMessage;
		} 

		throw new \Exception("If the field \"{$field}\" has requirements, it must have an error message for each them.", 1);				
	}

	/**
	 * Get the last error occurred
	 *
	 * @param boolean $raw_message Get the raw message of the error, not array
	 * @return mixed
	 */
	public function getLastError($raw_message = false)
	{
		if($raw_message)
			return $this->errorEvent['content']['message'];
		return $this->errorEvent['content'];
	}

	/**
	 * Get all the errores on validation
	 *
	 * @return array
	 */
	public function getErrors()
	{}

	/**
	 * Check if a regular expression is valid
	 * on syntax looking for an error
	 *
	 * @param string
	 */
	public function checkRegExp($regex)
	{   
    	try 
    	{
    		$status = (@preg_match($regex, '') !== false);
    		if($status)
    			return true;
    		return false;
    	} catch(\Exception $ex)
    	{ 
    		return false; 
    	}
	}

	/**
	 * Get the requirements of a specific or all forms
	 * 
	 * @param string $formName Name of the form [optional]
	 * @return array
	 */
	public function getRequirements($formName = null)
	{
		if(!empty($formName))
			return isset($this->requirements[$formName])?$this->requirements[$formName]:null;
		return $this->requirements;
	}

	/**
	 * Validate the lenght of the value for max
	 * 
	 * @param mixed $value Value to make the comparation
	 * @param int $lenght Lenght to validate
	 * @return boolean
	 */
	public function _max($value, $lenght)
	{
		if(strlen($value) <= (int) $lenght)
			return true;
		return false;
	}

	/**
	 * Validate the lenght of the value for min
	 * 
	 * @param mixed $value Value to make the comparation
	 * @param int $lenght Lenght to validate
	 * @return boolean
	 */
	public function _min($value, $lenght)
	{
		if(strlen($value) >= (int) $lenght)
			return true;
		return false;
	}

	/**
	 * Validate if the value match with the RegExp
	 * 
	 * @param mixed $value Value to make the comparation
	 * @param int $lenght Lenght to validate
	 * @return boolean
	 */
	public function _regex($value, $pattern)
	{
		if(preg_match($pattern, $value))
			return true;
		return false;
	}

	/**
	 * Validate if the value is empty and valid
	 * 
	 * @param mixed $value Value to check
	 * @return boolean
	 */
	public function _required($value)
	{
		if(!empty($value) && isset($value) && null !== $value)
			return true;
		return false;
	}

	/**
	 * Validate value using native filters
	 * 
	 * @param mixed $value Value to check
	 * @param string $filter Filfer php to make the validation
	 * @link http://www.php.net/manual/en/filter.filters.php Type of filters
	 * @return boolean
	 */
	public function _filter($value, $filter)
	{
		if(filter_var($value, $filter))
			return true;
		return false;
	}


	/**
	 * Check if a file has been uploaded verifying by rule
	 * 
	 * @param array $file File array using $_FILE
	 * @return boolean
	 */
	public function _uploaded($file)
	{
		if(isset($file['name']) && $file['size'] > 0 && is_uploaded_file($file['tmp_name']))
			return true;
		return false;
	}

	/**
	 * Check if the file math with the max size specified on rule
	 *
	 * @param array $file Array with data of the file
	 * @param mixed $expression Expression to compare
	 * @return boolean
	 */
	public function _file_max($file, $expression)
	{
		if(($file['size'] / 1024) < $expression) // Using MB for comparation
			return true;
		return false;
	}

	/**
	 * Verify if the format of the uploaded file match with required
	 * 
	 * @param array $file File array using $_FILE 
	 * @param array $expression Collection of extension allowed
	 * @return boolean
	 */
	public function _allow_format($file, $expression)
	{
		$info = pathinfo($file['name']);
		if(in_array($info['extension'], $expression))
			return true;
		return false;
	}

	/**
	 * Verify if the value is not equal to the pattern
	 * 
	 * @param mixed $value Value filled to compare
	 * @param string $patten Pattern for use as rule in comparation of values
	 * @return boolean
	 */
	public function _not_equal($value, $pattern)
	{
		return (boolean) ($value !== $pattern);
	}
}