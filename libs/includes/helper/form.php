<?php

namespace ATFApp\Helper;

/**
 * simple generator for html5 forms
 * 
 * 
 * @author cre8.info
 *
 */
class Form extends FormRenderer {
	
	# default form settings
	private $formName = "";
	private $formId = "";
	private $formClass = "";
	private $method = "post";
	private $actionUrl = "";
	private $enctype = "application/x-www-form-urlencoded";
	private $target = null;
	# html5 specific settings for the complete form
	private $autocomplete = "On";
	private $novalidate = false;

	protected $formErrors = [];					// form errors
	public $formElements = [];				// array of form elements
	
	private $formOnSubmit = null;				// javascript event handler on form submit
	private $formFocus = null;					// form element to set the focus to
	private $elementGroup = "";					// group form elements in one div
	private $elementsPosition = "right"; 		// position of the form elements (compared to their label)
	private $possiblePositions = array('left', 'right');
	
	
	/**
	 * constructor
	 * 
	 * @param string $formName
	 * @param string $formId
	 * @param string $formCssClass
	 */
	public function __construct($formName, $formId=null, $formCssClass=null) { 
		$this->formName = $formName;
		$this->formId = (!is_null($formId)) ? $formId : $formName;
		if (!is_null($formCssClass)) $this->formClass = $formCssClass;
	}
	
	// form errors
	/**
	 * add form errors as array
	 * 
	 * @param array $errorFields
	 */
	public function addFormErrors(array $errorFields) {
		foreach($errorFields as $field) {
			$this->addFormError($field);
		}
	}
	/**
	 * add form error
	 * 
	 * @param string $field
	 */
	public function addFormError($field) {
		$this->formErrors[] = $field;
	}


	# +++++++++++++++++++ add form elements +++++++++++++++++++
	
	/**
	 * add input type text
	 * 
	 * @param string $name name
	 * @param string $label label
	 * @param boolean $required required field
	 * @param string $preselect preselect value
	 * @param string $pattern reg exp pattern
	 * @param string $placeholder placeholder string
	 * @param integer $maxlength max length
	 * @param boolean $readonly readonly
	 * @param array $eventhandler array of 'event' => 'handler'
	 */
	public function addTextbox($name, $label=null, $required=false, $preselect=null, $pattern=null, $placeholder=null, $maxlength=null, $readonly=false, $eventhandler=[]) {
		$this->formElements[$name] = array(
			'type' => 'textbox',
			'group' => $this->elementGroup,
			'label' => $label,
			'required' => $required,
			'preselect' => $preselect,
			'pattern' => $pattern,
			'placeholder' => $placeholder,
			'maxlength' => $maxlength,
			'readonly' => $readonly,
			'eventhandler' => $eventhandler
		);
	}
	
	/**
	 * add a textarea
	 * 
	 * @param string $name
	 * @param string $label
	 * @param boolean $required
	 * @param string $preselect
	 * @param string $placeholder
	 * @param string $wrap (hard|soft)
	 * @param integer $cols
	 * @param integer $rows
	 * @param bollean $readonly
	 * @param array $eventhandler
	 */
	public function addTextarea($name, $label=null, $required=false, $preselect=null, $placeholder=null, $wrap="soft", $cols=null, $rows=null, $readonly=false, $eventhandler=[]) {
		$this->formElements[$name] = array(
				'type' => 'textarea',
				'group' => $this->elementGroup,
				'label' => $label,
				'required' => $required,
				'preselect' => $preselect,
				'placeholder' => $placeholder,
				'wrap' => $wrap,
				'cols' => $cols,
				'rows' => $rows,
				'readonly' => $readonly,
				'eventhandler' => $eventhandler
		);
	}
	
	/**
	 * add a simple editor 
	 */
	public function addSimpleEditor($name, $label=null, $preselect=null) {
		$this->formElements[$name] = array(
			'type' => 'simpleEditor',
			'group' => $this->elementGroup,
			'label' => $label,
			'preselect' => $preselect
		);
	}


	/**
	 * add a headline between form elements
	 * 
	 * @param string $name
	 * @param string $text
	 */
	public function addHeadline($name, $text) {
		$this->formElements[$name] = array(
			'type' => 'headline',
			'group' => $this->elementGroup,
			'text' => $text
		);
	}
	
	/**
	 * add input type text
	 * 
	 * @param string $name form field name
	 * @param string $label label
	 * @param boolean $required required field
	 * @param string $preselect preselect value
	 * @param string $pattern regexp pattern
	 * @param string $placeholder placeholder text
	 * @param integer $maxlength max length
	 * @param boolean $readonly read-only status
	 * @param array $eventhandler array('event' => handler)
	 */
	public function addSearch($name, $label=null, $required=false, $preselect=null, $pattern=null, $placeholder=null, $maxlength=null, $readonly=false, $eventhandler=[]) {
		$this->formElements[$name] = array(
			'type' => 'search',
			'group' => $this->elementGroup,
			'label' => $label,
			'required' => $required,
			'preselect' => $preselect,
			'pattern' => $pattern,
			'placeholder' => $placeholder,
			'maxlength' => $maxlength,
			'readonly' => $readonly,
			'eventhandler' => $eventhandler
		);
	}
	
	/**
	 * add input type password
	 * 
	 * @param string $name
	 * @param string $label
	 * @param boolean $required
	 * @param string $placeholder
	 * @param array $eventhandler
	 */
	public function addPassword($name, $label=null, $required=false, $placeholder=null, $eventhandler=[]) {
		$this->formElements[$name] = array(
			'type' => 'password',
			'group' => $this->elementGroup,
			'label' => $label,
			'required' => $required,
			'placeholder' => $placeholder,
			'eventhandler' => $eventhandler
		);
	}
	
	/**
	 * add email field
	 * 
	 * @param string $name
	 * @param string $label
	 * @param boolean $required
	 * @param string $preselect
	 * @param string $placeholder
	 * @param array $eventhandler
	 */
	public function addEmail($name, $label=null, $required=false, $preselect=null, $placeholder=null, $readonly=false, $eventhandler=[]) {
		$this->formElements[$name] = array(
				'type' => 'email',
				'group' => $this->elementGroup,
				'label' => $label,
				'required' => $required,
				'preselect' => $preselect,
				'placeholder' => $placeholder,
				'readonly' => $readonly,
				'eventhandler' => $eventhandler
		);
	}
		
	/**
	 * add checkboxes
	 * 
	 * @param string $name
	 * @param array $checkboxes (value => label)
	 * @param string $label
	 * @param string $preselect
	 * @param array $eventhandler
	 */
	public function addCheckboxes($name, $checkboxes, $label=null, $preselect=[], $readonly=false, $eventhandler=[]) {
		$this->formElements[$name] = array(
			'type' => 'checkboxes',
			'group' => $this->elementGroup,
			'checkboxes' => $checkboxes,
			'label' => $label,
			'preselect' => $preselect,
			'readonly' => $readonly,
			'eventhandler' => $eventhandler
		);
	}
	
	/**
	 * add radiobuttons
	 * 
	 * @param string $name form field name
	 * @param array $radiobuttons array('value' => 'label')
	 * @param string $label field label
	 * @param boolean $required selection required
	 * @param string $preselect preselect value
	 * @param boolean $readonly read-only
	 * @param array $eventhandler array('click' => 'handler')
	 */
	public function addRadiobuttons($name, $radiobuttons, $label=null, $required=false, $preselect=null, $readonly=false, $eventhandler=[]) {
		$this->formElements[$name] = array(
			'type' => 'radiobuttons',
			'group' => $this->elementGroup,
			'radiobuttons' => $radiobuttons,
			'label' => $label,
			'required' => $required,
			'preselect' => $preselect,
			'readonly' => $readonly,
			'eventhandler' => $eventhandler
		);
	}
	
	/**
	 * add a dropdown field
	 * 
	 * @param string $name
	 * @param array $values array('value' => 'label')
	 * @param string $label
	 * @param integer $size
	 * @param boolean $multiple
	 * @param boolean $required
	 * @param array $preselect
	 * @param boolean $readonly
	 * @param array $eventhandler
	 */
	public function addDropdown($name, $values, $label=null, $size=null, $multiple=false, $required=false, $preselect=[], $readonly=false, $eventhandler=[]) {
		$this->formElements[$name] = array(
				'type' => 'dropdown',
				'group' => $this->elementGroup,
				'values' => $values,
				'size' => $size,
				'multiple' => $multiple,
				'label' => $label,
				'required' => $required,
				'preselect' => $preselect,
				'readonly' => $readonly,
				'eventhandler' => $eventhandler
		);
	}
	
	/**
	 * add a date selector
	 * 
	 * values for param $type:
	 * date, datetime, datetime-local, month, week, time
	 * TODO (02/2015 chrome does not support 'datetime' currently)
	 * 
	 * @param string $name
	 * @param string $label
	 * @param string $type 
	 * @param string $preselect
	 * @param array $eventhandler
	 */
	public function addDateselector($name, $label=null, $type="date", $required=false, $preselect=null, $pattern=null, $placeholder=null, $readonly=false, $eventhandler=[]) {
		$this->formElements[$name] = array(
			'type' => 'date',
			'group' => $this->elementGroup,
			'datetype' => $type,
			'label' => $label,
			'required' => $required,
			'preselect' => $preselect,
			'placeholder' => $placeholder,
			'pattern' => $pattern,
			'readonly' => $readonly,
			'eventhandler' => $eventhandler
		);
	}
	
	/**
	 * add url field
	 * 
	 * @param string $name
	 * @param string $label
	 * @param boolean $required
	 * @param string $preselect
	 * @param string $placeholder
	 * @param array $eventhandler
	 */
	public function addUrl($name, $label=null, $required=false, $preselect=null, $placeholder=null, $readonly=false, $eventhandler=[]) {
		$this->formElements[$name] = array(
				'type' => 'url',
				'group' => $this->elementGroup,
				'label' => $label,
				'required' => $required,
				'preselect' => $preselect,
				'placeholder' => $placeholder,
				'readonly' => $readonly,
				'eventhandler' => $eventhandler
		);
	}	
	
	/**
	 * add phone field
	 * 
	 * @param string $name
	 * @param string $label
	 * @param boolean $required
	 * @param string $preselect
	 * @param string $placeholder
	 * @param array $eventhandler
	 */
	public function addPhone($name, $label=null, $required=false, $preselect=null, $pattern=null, $placeholder=null, $readonly=false, $eventhandler=[]) {
		$this->formElements[$name] = array(
				'type' => 'phone',
				'group' => $this->elementGroup,
				'label' => $label,
				'required' => $required,
				'preselect' => $preselect,
				'pattern' => $pattern,
				'placeholder' => $placeholder,
				'readonly' => $readonly,
				'eventhandler' => $eventhandler
		);
	}
	
	/**
	 * add a number field
	 * 
	 * @param string $name
	 * @param float $min
	 * @param float $max
	 * @param float $step
	 * @param string $label
	 * @param boolean $required
	 * @param string $preselect
	 * @param string $placeholder
	 * @param boolean $readonly
	 * @param array $eventhandler
	 */
	public function addNumber($name, $min, $max, $step='any', $label=null, $required=false, $preselect=null, $placeholder=null, $readonly=false, $eventhandler=[]) {
		$this->formElements[$name] = array(
				'type' => 'number',
				'group' => $this->elementGroup,
				'min' => $min,
				'max' => $max,
				'step' => $step,
				'label' => $label,
				'required' => $required,
				'preselect' => $preselect,
				'placeholder' => $placeholder,
				'readonly' => $readonly,
				'eventhandler' => $eventhandler
		);
	}
	
	/**
	 * add a slider field (input type range)
	 * wrapper for 'addRange'
	 * 
	 * @param string $name
	 * @param float $min
	 * @param float $max
	 * @param float $step
	 * @param string $label
	 * @param string $preselect
	 * @param boolean $readonly
	 * @param array $eventhandler
	 */
	public function addSlider($name, $min, $max, $step='any', $label=null, $preselect=null, $readonly=false, $eventhandler=[]) {
		$this->addRange($name, $min, $max, $step, $label, $preselect, $readonly, $eventhandler);
	}
	/**
	 * add a slider field (input type range)
	 * 
	 * @param string $name
	 * @param float $min
	 * @param float $max
	 * @param float $step
	 * @param string $label
	 * @param boolean $required
	 * @param string $preselect
	 * @param boolean $readonly
	 * @param array $eventhandler
	 */
	public function addRange ($name, $min, $max, $step='any', $label=null, $preselect=null, $readonly=false, $eventhandler=[]) {
		$this->formElements[$name] = array(
				'type' => 'range',
				'group' => $this->elementGroup,
				'min' => $min,
				'max' => $max,
				'step' => $step,
				'label' => $label,
				'preselect' => $preselect,
				'readonly' => $readonly,
				'eventhandler' => $eventhandler
		);
	}
	
	/**
	 * add hidden field
	 * 
	 * @param string $name
	 * @param string $value
	 */
	public function addHiddenField($name, $value) {
		$this->formElements[$name] = array(
			'type' => 'hidden',
			'group' => $this->elementGroup,
			'value' => $value
		);
	}
	
	/**
	 * add file upload field 
	 * 
	 * @param string $name name
	 * @param string $label label
	 * @param boolean $required is reqired
	 * @param boolean $mutiple multiple files allowed
	 * @param string $accept accepted types
	 * @param boolean $draggable drag'n'drop enabled
	 * @param array $eventhandler array of eventhandlers array('click' => 'handler')
	 */
	public function addFileField($name, $label=null, $required=false, $mutiple=false, $accept=null, $draggable=false, $eventhandler=[]) {
		$this->formElements[$name] = array(
				'type' => 'file',
				'group' => $this->elementGroup,
				'label' => $label,
				'required' => $required,
				'multiple' => $mutiple,
				'accept' => $accept,
				'draggable' => $draggable,
				'eventhandler' => $eventhandler
		);
	}
	
	/**
	 * add a click button 
	 * 
	 * @param strinbg $name name
	 * @param string $value button text
	 * @param array $eventhandler array('click' => 'eventHandler')
	 */
	public function addButton($name, $value, $eventhandler=[]) {
		$this->formElements[$name] = array(
				'type' => 'button',
				'group' => $this->elementGroup,
				'value' => $value,
				'eventhandler' => $eventhandler
		);
	}
	
	/**
	 * add submit button
	 * 
	 * @param string $name
	 * @param string $value
	 * @param array $eventhandler
	 */
	public function addSubmit($name, $value, $eventhandler=[]) {
		$this->formElements[$name] = array(
			'type' => 'submit',
			'group' => $this->elementGroup,
			'value' => $value,
			'eventhandler' => $eventhandler
		);
	}
	
	
	# +++++++++++++++++++ setter for elements eventhandler / preselect / placeholder / required property +++++++++++++++++++
	
	/**
	 * set an eventhandler value for an element
	 * 
	 * @param string $element
	 * @param array $value array('onclick' => 'alert(\'event...\');')
	 */
	public function setElementEventhandler($element, $value) {
		if (isset($this->formElements[$element])) {
			$this->formElements[$element]['eventhandler'] = $value;
		}
	}
	
	/**
	 * set a preseelct value for an element
	 * 
	 * @param string $element
	 * @param string $value
	 */
	public function setElementPreselect($element, $value) {
		if (isset($this->formElements[$element])) {
			$this->formElements[$element]['preselect'] = $value;
		}
	}
	
	/**
	 * set a placeholder value for an element
	 * 
	 * @param string $element
	 * @param string $value
	 */
	public function setElementPlaceholder($element, $value) {
		if (isset($this->formElements[$element])) {
			$this->formElements[$element]['placeholder'] = $value;
		}
	}
	
	/**
	 * set the required property for an element
	 *
	 * @param string $element
	 * @param boolean $value
	 */
	public function setElementRequired($element, $isRequired=true) {
		if (isset($this->formElements[$element])) {
			$this->formElements[$element]['required'] = $isRequired;
		}
	}
	
	/**
	 * set the required property to a list of elements
	 *
	 * @param array $elementsRequired array('elem1'=>true [, 'elem2'=>false, ..])
	 */
	public function setElementsRequired($elementsRequired) {
		foreach ($elementsRequired AS $element => $required) {
			$this->setElementRequired($element, $required);
		}
	}
	
	
	# +++++++++++++++++++ getter / setter for form settings +++++++++++++++++++

	public function getFormName() {
		return $this->formName;
	}
	public function getFormId() {
		return $this->formId;
	}
	public function getFormClass() {
		return $this->formClass;
	}
	public function getFormElements() {
		return $this->formElements;
	}
	
	/**
	 * set focus to form element
	 *
	 * @param string $element
	 */
	public function setFocus($element) {
		$this->formFocus = $element;
	}
	
	/**
	 * get focus element
	 *
	 * @return string
	 */
	public function getFocus() {
		return $this->formFocus;
	}

	/**
	 * set form on submit event
	 *
	 * @param string $eventhandler
	 */
	public function setFormOnSubmit($eventhandler) {
		$this->formOnSubmit = $eventhandler;
	}
	
	/**
	 * get form on submit event
	 *
	 * @return string
	 */
	public function getFormOnSubmit() {
		return $this->formOnSubmit;
	}
	
	/**
	 * set form target 
	 * 
	 * @param string $target
	 */
	public function setTarget($target) {
		$this->target = $target;
	}
	
	/**
	 * get form target
	 * 
	 * @return null|string
	 */
	public function getTarget() {
		return $this->target;
	}
	
	/**
	 * set form encoding type
	 * 
	 * @param string $enctype
	 */
	public function setEnctype($enctype) {
		$this->enctype = $enctype;
	}
	
	/**
	 * get form encoding type
	 * 
	 * @return string
	 */
	public function getEnctype() {
		return $this->enctype;
	}
	
	/**
	 * set form action url
	 *
	 * @param string $url
	 */
	public function setAction($url) {
		$this->actionUrl = $url;
	}
	
	/**
	 * get form action url
	 *
	 * @return string
	 */
	public function getAction() {
		return $this->actionUrl;
	}
	
	/**
	 * set form method
	 * 
	 * @param string $method
	 */
	public function setMethod($method) {
		$this->method = (strtolower($method) == "get") ? "get" : "post";
	}
	
	/**
	 * get form method
	 * 
	 * @return string
	 */
	public function getMethod() {
		return $this->method;
	}
	
	/**
	 * set current element group
	 * 
	 * all element rows are placed in groups
	 * set a new group to append all following elements
	 * 
	 * @param string $groupname
	 */
	public function setElementGroup($groupname) {
		$this->elementGroup = $groupname;
	}
	
	/**
	 * get current element group
	 * 
	 * @return string
	 */
	public function getElementGroup() {
		return $this->elementGroup;
	}
	
	/**
	 * set autocomplete setting
	 * 
	 * @param string $status
	 */
	public function setAutocomplete($status) {
		$this->autocomplete = (strtolower($status) == "on") ? "On" : "Off";
	}
	
	/**
	 * get autocomplete setting
	 * 
	 * @return string
	 */
	public function getAutocomplete() {
		return $this->autocomplete;
	}
	
	/**
	 * set novalidate setting
	 * 
	 * @param boolean $validate
	 */
	public function setNovalidate($validate) {
		$this->novalidate = ($validate == true) ? true : false;
	}
	
	/**
	 * get novalidate setting
	 * 
	 * @return string
	 */
	public function getNovalidate() {
		return ($this->novalidate == true) ? " novalidate " : "";
	}
	
	/**
	 * set the elements position
	 *
	 * @param string $position
	 */
	public function setPosition($position) {
		if (in_array($position, $this->possiblePositions)) {
			$this->elementsPosition = $position;
		}
	}
	
	/**
	 * get the elements position
	 * 
	 * @return string
	 */
	public function getPosition() {
		return $this->elementsPosition;
	}
	
}
