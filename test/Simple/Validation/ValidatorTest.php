<?php namespace Test\Simple\Validation;

use Simple\Validation\Validator,
	PHPUnit_Framework_TestCase
	;

/**
 * Test class
 */
class ValidatorTest extends PHPUnit_Framework_TestCase
{

	public function validationsProvider()
	{
		return [
			[
				'form_test_1' => [
					'file_test_1' => [
						'requirements' => ['uploaded' => true, 'file_max' => 1024, 'allow_format' => ['apk']],
						'messages' => [
							'on_uploaded' => 'File must be uploaded',
							'on_file_max' => 'Exceded size of file',
							'on_allow_format' => 'Invalid extension'
						]
					],
					'text_test_1' => [
						'requirements' => ['required' => true],
						'messages' => [
							'on_required' => 'The field is required'
						]
					],
				]
			],
			[
				'form_test_2' => [
					'file_test_2' => [
						'requirements' => ['uploaded' => true, 'file_max' => 1024, 'allow_format' => ['apk']],
						'messages' => [
							'on_uploaded' => 'File must be uploaded',
							'on_file_max' => 'Exceded size of file',
							'on_allow_format' => 'Invalid extension'
						]
					],
					'text_test_2' => [
						'requirements' => ['required' => true],
						'messages' => [
							'on_required' => 'The field is required'
						]
					],
				]
			]			
		];
	}

	/**
	 * @todo
	 */
	public function testValidatorLoadValidationFile()
	{}

	/**
	 * @dataProvider validationsProvider
	 */
	public function testIsValidCheckForValidationsGiven($validations)
	{
		$dummy_data = [
			'text_test' => 'dummy_value' 
		];

		$this->assertTrue(Validator::getInstance($validations)->isValid('form_test', $dummy_data));
	}

	/**
	 * @dataProvider validationsProvider
	 */
	public function testGetReturnValidArrayFromValidations($validations)
	{
		$expected = [];
		$this->assertEquals($expected, Validator::getInstance($validations)->get('form_test_2', 'text_test_2'));
	}

	public function testVefifyValidateUsingFuncEvents()
	{}

	public function testLoadRequirementsAreLoaded()
	{}

	public function testIfExistsRequirementsForTheForm()
	{}

	public function testInvalidFieldsExistence()
	{}

	public function testSetErrorEventIsSetted()
	{}

	public function testGetLastErrorReturnValidString()
	{}

	public function testCheckRegExpsCheckingIsHandled()
	{}

	public function testGetRequirementReturnValidData()
	{}

	public function testLenghtMaxOfValidValue()
	{}

	public function testLenghtMaxOfInvalidValue()
	{}

	public function testLenghtMinOfValidValue()
	{}

	public function testLenghtMinOfInvalidValue()
	{}

	public function testRegExpValidMatchWithPattern()
	{}

	public function testRegExpInvalidDoNotMatchWithPattern()
	{}	

	public function testFilledRequiredFieldReturnValid()
	{}

	public function testNotFilledRequiredFieldReturnValid()
	{}

	public function testFilterMethodIsValidForEachNativeFilters()
	{}

	public function testUploadedFileIsValid()
	{}

	public function testValidMaxSizeForFile()
	{}

	public function testInvalidMaxSizeForFile()
	{}

	public function testAllowedFormatsWithValidFormats()
	{}

	public function testAllowedFormatsWithInvalidFormats()
	{}

	public function testAValueAssertNotEqual()
	{}
}