<?php

namespace Adamlc\AddressFormat;

use Adamlc\AddressFormat\Exceptions\AttributeInvalidException;
use Adamlc\AddressFormat\Exceptions\LocaleNotSupportedException;
use Adamlc\AddressFormat\Exceptions\LocaleParseErrorException;

/**
 * Use this call to format a street address according to different locales
 */
class Format {

	private $locale;

	/**
	 * This map specifies the content on how to format the address
	 * See this URL for origin reference - https://code.google.com/p/libaddressinput/source/browse/trunk/src/com/android/i18n/addressinput/AddressField.java?r=111
	 *
	 * @var mixed
	 * @access private
	 */
	private $address_map = array(
		'S' => 'ADMIN_AREA', //state
		'C' => 'LOCALITY', //city
		'N' => 'RECIPIENT', //name
		'O' => 'ORGANIZATION', //organization
		'1' => 'ADDRESS_LINE_1', //street1
		'2' => 'ADDRESS_LINE_2', //street1
		'D' => 'DEPENDENT_LOCALITY',
		'Z' => 'POSTAL_CODE',
		'X' => 'SORTING_CODE',
		'A' => 'STREET_ADDRESS', //Deprecated
		'R' => 'COUNTRY'
	);

	/**
	 * The input map will hold all the information we put in to the class
	 *
	 * @var mixed
	 * @access private
	 */
	private $input_map = array(
		'ADMIN_AREA' => '', //state
		'LOCALITY' => '', //city
		'RECIPIENT' => '', //name
		'ORGANIZATION' => '', //organization
		'ADDRESS_LINE_1' => '', //street1
		'ADDRESS_LINE_2' => '', //street1
		'DEPENDENT_LOCALITY' => '',
		'POSTAL_CODE' => '',
		'SORTING_CODE' => '',
		'STREET_ADDRESS' => '', //Deprecated
		'COUNTRY' => ''
	);

	/**
	 * setLocale will set the locale. This is currently a 2 digit ISO country code
	 *
	 * @access public
	 * @param mixed $locale
	 * @return void
	 */
	public function setLocale($locale) {
		//Check if we have information for this locale
		$file = __DIR__ . '/i18n/' . $locale . '.json';
		if (file_exists($file)) {
			//Read the locale information from the file
			$meta = json_decode(file_get_contents($file), true);
			if(is_array($meta)){
				$this->locale = $meta;

				return true;
			}
			else {
				throw new LocaleParseErrorException('Locale "' . $locale . '" could not be parsed correctly');
			}
		}
		else {
			throw new LocaleNotSupportedException('Locale not supported by this library');
		}
	}

	/**
	 * Return the formatted address, using the locale set. Optionally return HTML or plain text
	 *
	 * @access public
	 * @param bool $html (default: false)
	 * @return void
	 */
	public function formatAddress($html = false) {
		//Check if this locale has a fmt field
		if(isset($this->locale['fmt'])){
			$address_format = $this->locale['fmt'];

			//Loop through each address part and process it!
			$formatted_address = $address_format;

			//Replace the street values
			foreach($this->address_map as $key => $value){
				$formatted_address = str_replace('%' . $key, $this->input_map[$value], $formatted_address);
			}

			//Replace new lines!
			if($html){
				$formatted_address = str_replace('%n', "\n" . '<br>', $formatted_address);
			}
			else {
				$formatted_address = str_replace('%n', "\n", $formatted_address);
			}

			return $formatted_address;
		}
		else {
			throw new LocaleNotSupportedException('Locale not supported by this library');
		}
	}

	/**
	 * Set an address attribute.
	 *
	 * @access public
	 * @param mixed $attribute
	 * @param mixed $value
	 * @return string $value
	 */
	public function setAttribute($attribute, $value) {
		//Check this attribute is support
		if(isset($this->input_map[$attribute])){
			$this->input_map[$attribute] = $value;

			return $value;
		}
		else {
			throw new AttributeInvalidException('Attribute not supported by this library');
		}
	}

	/**
	 * Get an address attribute.
	 *
	 * @access public
	 * @param mixed $attribute
	 * @return void
	 */
	public function getAttribute($attribute) {
		//Check this attribute is support
		if(isset($this->input_map[$attribute])){
			return $this->input_map[$attribute];
		}
		else {
			throw new AttributeInvalidException('Attribute not supported by this library');
		}
	}

	/**
	 * Clear all attributes.
	 *
	 * @access public
	 * @return void
	 */
	public function clearAttributes() {
		foreach($this->input_map as $key => $value){
			$this->input_map[$key] = '';
		}
	}
}