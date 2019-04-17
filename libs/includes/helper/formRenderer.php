<?php

namespace ATFApp\Helper;

/**
 * simple generator for html5 forms
 *
 * all private render* methods return an array containing html for label and element:
 * 
 * return array(
 * 	'element' => '<.... />', 
 * 	'label' => '<label... >
 * );
 *
 * @author cre8.info
 *
 */
abstract class FormRenderer {
	
	private $currentGroup = null;
	
	public function renderForm($return=false) {
		// form config
		$novalidate = ($this->getNovalidate() == true) ? ' novalidate ' : '';
		$onSubmit = (!is_null($this->getFormOnSubmit())) ? ' onsubmit="'.$this->getFormOnSubmit().'" ' : '';
		$target = ($this->getTarget()) ? ' target="'.$this->getTarget().'" ' : '';
		
		// main form tag
		$formHtml = '<form name="' . $this->getFormName() .'"';
		$formHtml .= ' id="' . $this->getFormId() .'"';
		$formHtml .= ' class="form-default form-gen ' . $this->getFormClass() . '"'; 
		$formHtml .= ' action="' . $this->getAction() . '"';
		$formHtml .= ' method="' . $this->getMethod() . '"';
		$formHtml .= ' enctype="' . $this->getEnctype() . '"';
		$formHtml .= ' autocomplete="' . $this->getAutocomplete() . '" ';
		$formHtml .= $novalidate . $onSubmit . $target;
		$formHtml .= '>';
		
		// form element (rows)
		$formHtml .= $this->renderAllElements();
		
		$formHtml .= '</form>'; // close form
		
		if ($return) {
			return $formHtml;
		} else {
			echo $formHtml;
		}
	}
	
	private function renderAllElements() {
		$formHtml = "";
		foreach ($this->formElements AS $elemName => $data) {
			$method = "render" . ucfirst($data['type']);
			if (method_exists($this, $method)) {
				// group form elements
				if (isset($data['group']) && $data['group'] !== $this->currentGroup) {
					if (!is_null($this->currentGroup)) {
						// close last group
						$formHtml .= '</div>';
					}
					$this->currentGroup = $data['group'];
					$formHtml .= '<div id="formgroup_' . $this->getFormId() . '_' . $this->currentGroup . '">';
				}
				if ($data['type'] == "hidden" || $data['type'] == "headline") {
					$rowHtml = $this->$method($elemName, $data);
					$formHtml .= $rowHtml['element'];
				} else {
					$requiredClass = (isset($data['required']) && $data['required'] == true) ? "required" : '';
					$errClass = $this->getErrorClass($elemName);

					// row div
					$formHtml .= '<div class="row form_row_' . $elemName . ' ' . $errClass . ' ' . $requiredClass . '">';
						$rowHtml = $this->$method($elemName, $data);
						// div around form element
						$elementHtml = '<div class="elem elem_' . $data['type'] . '">' . $rowHtml['element'] . '</div>';
						
						// position left or right
						if ($this->getPosition() == "right") {
							$formHtml .= $rowHtml['label'] . $elementHtml;
						} else {
							$formHtml .= $elementHtml . $rowHtml['label'];
						}
					$formHtml .= '</div>';
				}
			} else {
				$formHtml .= "<p>[cannot render element of type '".$data['type']."' - method does not exist: " . $method . "]</p>";
			}
		}
		// close last group div
		$formHtml .= '</div>';
		
		return $formHtml;
	}
	
	# +++++++++++++++++++++++++ elements render methods +++++++++++++++++++++++++
	
	private function renderSubmit($elemName, $data) {
		// element id
		$id = $this->getElemId($elemName);
		$eventhandlers = $this->getEventHandlersString($data['eventhandler']);
		
		$elementHtml = '<button class="btn" type="submit" name="' . $elemName . '" id="' . $id . '" ' . $eventhandlers . '>' . $data['value'] . '</button>';
		
		return array(
				'element' => $elementHtml,
				'label' => '<span></span>'
		);
	} 
	
	private function renderButton($elemName, $data) {
		// element id
		$id = $this->getElemId($elemName);
		$eventhandlers = $this->getEventHandlersString($data['eventhandler']);
		
		$elementHtml = '<input class="btn" type="button" name="' . $elemName . '" id="' . $id . '" value="' . $data['value'] . '" ' . $eventhandlers . '/>';
		
		return array(
				'element' => $elementHtml,
				'label' => ''
		);
	}
	
	private function renderFile($elemName, $data) {
		// element id
		$id = $this->getElemId($elemName);
		
		// label html 
		$label = $this->getElemLabel($id, $data['label']);
		
		// element html
		$eventhandlers = $this->getEventHandlersString($data['eventhandler']);
		$focus = $this->getAutofocusParam($elemName);
		$required = $this->getRequiredParam($data['required']);
		$accept = (!is_null($data['accept'])) ? ' accept="' . $data['accept'] . '" ' : '';
		$multiple = ($data['multiple'] == true) ? ' multiple="multiple" ' : '';
		$draggable = ($data['draggable'] == true) ? 'draggable="true" ' : '';
		
		$elementHtml = '<input type="file" name="' . $elemName . '" id="' . $id . '" ';
		$elementHtml .= $accept . $multiple . $focus;
		$elementHtml .= $required . $draggable . $eventhandlers;
		$elementHtml .= ' />';
		
		return array(
				'element' => $elementHtml, 
				'label' => $label
		);
	} 
	
	private function renderTextbox($elemName, $data) {
		// element id
		$id = $this->getElemId($elemName);
		
		// label html 
		$label = $this->getElemLabel($id, $data['label']);
		
		// element html
		$readonly = $this->getReadonlyParam($data['readonly']);
		$preselect = (!is_null($data['preselect'])) ? $data['preselect'] : '';
		$placeholder = $this->getPlaceholderParam($data['placeholder']);
		$pattern = $this->getPatternParam($data['pattern']);
		$maxlength = (!is_null($data['maxlength'])) ? ' maxlength="'.$data['maxlength'].'" ' : '';
		$eventhandlers = $this->getEventHandlersString($data['eventhandler']);
		$focus = $this->getAutofocusParam($elemName);
		$required = $this->getRequiredParam($data['required']);
		
		$elementHtml = '<input type="text" name="' . $elemName . '" id="' . $id . '" ';
		$elementHtml .= ' value="' . $preselect . '" ' .$readonly . $placeholder . $pattern;
		$elementHtml .= $maxlength . $focus . $required . $eventhandlers;
		$elementHtml .= ' />';
		
		return array(
				'element' => $elementHtml, 
				'label' => $label
		);
	}
	
	private function renderTextarea($elemName, $data) {
		// element id
		$id = $this->getElemId($elemName);
		
		// label html 
		$label = $this->getElemLabel($id, $data['label']);
		
		// element html
		$readonly = $this->getReadonlyParam($data['readonly']);
		$preselect = (!is_null($data['preselect'])) ? $data['preselect'] : '';
		$placeholder = $this->getPlaceholderParam($data['placeholder']);
		$cols = (!is_null($data['cols'])) ? ' cols="'.$data['cols'].'" ' : '';
		$rows = (!is_null($data['rows'])) ? ' rows="'.$data['rows'].'" ' : '';
		$eventhandlers = $this->getEventHandlersString($data['eventhandler']);
		$focus = $this->getAutofocusParam($elemName);
		$required = $this->getRequiredParam($data['required']);
		
		$elementHtml = '<textarea name="' . $elemName . '" id="' . $id . '" wrap="' . $data['wrap'] . '" ';
		$elementHtml .= $cols . $rows . $readonly . $placeholder;
		$elementHtml .= $focus . $required . $eventhandlers;
		$elementHtml .= '>'. $preselect . '</textarea>';
		
		return array(
				'element' => $elementHtml, 
				'label' => $label
		);
	}
	
	private function renderSearch($elemName, $data) {
		// element id
		$id = $this->getElemId($elemName);
		
		// label html 
		$label = $this->getElemLabel($id, $data['label']);
		
		// element html
		$readonly = $this->getReadonlyParam($data['readonly']);
		$preselect = (!is_null($data['preselect'])) ? $data['preselect'] : '';
		$placeholder = $this->getPlaceholderParam($data['placeholder']);
		$pattern = $this->getPatternParam($data['pattern']);
		$maxlength = (!is_null($data['maxlength'])) ? ' maxlength="'.$data['maxlength'].'" ' : '';
		$eventhandlers = $this->getEventHandlersString($data['eventhandler']);
		$focus = $this->getAutofocusParam($elemName);
		$required = $this->getRequiredParam($data['required']);
		
		$elementHtml = '<input type="search" name="' . $elemName . '" id="' . $id . '" ';
		$elementHtml .= ' value="' . $preselect . '" ' .$readonly . $placeholder . $pattern;
		$elementHtml .= $maxlength . $focus . $required . $eventhandlers;
		$elementHtml .= ' />';
		
		return array(
				'element' => $elementHtml, 
				'label' => $label
		);
	}
	
	private function renderPassword($elemName, $data) {
		// element id
		$id = $this->getElemId($elemName);
		
		// label html
		$label = $this->getElemLabel($id, $data['label']);
		
		// element html
		$placeholder = $this->getPlaceholderParam($data['placeholder']);
		$eventhandlers = $this->getEventHandlersString($data['eventhandler']);
		$required = $this->getRequiredParam($data['required']);
		$focus = $this->getAutofocusParam($elemName);
		
		$elementHtml = '<input type="password" name="'.$elemName.'" id="'.$id.'" ';
		$elementHtml .= $placeholder . $focus . $required . $eventhandlers;
		$elementHtml .= ' />';
		
		return array(
				'element' => $elementHtml,
				'label' => $label
		);
	}
	
	private function renderEmail($elemName, $data) {
		// element id
		$id = $this->getElemId($elemName);
	
		// label html
		$label = $this->getElemLabel($id, $data['label']);
	
		// element html
		$readonly = $this->getReadonlyParam($data['readonly']);
		$placeholder = $this->getPlaceholderParam($data['placeholder']);
		$eventhandlers = $this->getEventHandlersString($data['eventhandler']);
		$required = $this->getRequiredParam($data['required']);
		$focus = $this->getAutofocusParam($elemName);
		$preselect = (!is_null($data['preselect'])) ? $data['preselect'] : '';
	
		$elementHtml = '<input type="email" name="'.$elemName.'" id="'.$id.'" value="' . $preselect . '" ';
		$elementHtml .= $readonly .$placeholder . $focus . $required . $eventhandlers;
		$elementHtml .= ' />';
	
		return array(
				'element' => $elementHtml,
				'label' => $label
		);
	}
	
	private function renderUrl($elemName, $data) {
		// element id
		$id = $this->getElemId($elemName);
	
		// label html
		$label = $this->getElemLabel($id, $data['label']);
	
		// element html
		$readonly = $this->getReadonlyParam($data['readonly']);
		$placeholder = $this->getPlaceholderParam($data['placeholder']);
		$eventhandlers = $this->getEventHandlersString($data['eventhandler']);
		$required = $this->getRequiredParam($data['required']);
		$focus = $this->getAutofocusParam($elemName);
		$preselect = (!is_null($data['preselect'])) ? $data['preselect'] : '';
	
		$elementHtml = '<input type="url" name="'.$elemName.'" id="'.$id.'" value="' . $preselect . '" ';
		$elementHtml .= $readonly . $placeholder . $focus . $required . $eventhandlers;
		$elementHtml .= ' />';
		
		return array(
				'element' => $elementHtml,
				'label' => $label
		);
	}
	
	private function renderPhone($elemName, $data) {
		// element id
		$id = $this->getElemId($elemName);
	
		// label html
		$label = $this->getElemLabel($id, $data['label']);
	
		// element html
		$readonly = $this->getReadonlyParam($data['readonly']);
		$placeholder = $this->getPlaceholderParam($data['placeholder']);
		$eventhandlers = $this->getEventHandlersString($data['eventhandler']);
		$required = $this->getRequiredParam($data['required']);
		$focus = $this->getAutofocusParam($elemName);
		$preselect = (!is_null($data['preselect'])) ? $data['preselect'] : '';
		$pattern = $this->getPatternParam($data['pattern']);
	
		$elementHtml = '<input type="tel" name="'.$elemName.'" id="'.$id.'" value="' . $preselect . '" ';
		$elementHtml .= $readonly . $placeholder . $pattern . $focus . $required . $eventhandlers;
		$elementHtml .= ' />';
		
		return array(
				'element' => $elementHtml,
				'label' => $label
		);
	}
	
	private function renderNumber($elemName, $data) {
		// element id
		$id = $this->getElemId($elemName);
	
		// label html
		$label = $this->getElemLabel($id, $data['label']);
	
		// element html
		$readonly = $this->getReadonlyParam($data['readonly']);
		$placeholder = $this->getPlaceholderParam($data['placeholder']);
		$eventhandlers = $this->getEventHandlersString($data['eventhandler']);
		$required = $this->getRequiredParam($data['required']);
		$focus = $this->getAutofocusParam($elemName);
		$preselect = (!is_null($data['preselect'])) ? $data['preselect'] : '';
	
		$elementHtml = '<input type="number" name="'.$elemName.'" id="'.$id.'" value="' . $preselect . '" ';
		$elementHtml .= ' min="' . $data['min'] . '" max="' . $data['max'] . '" step="' . $data['step'] . '" ';
		$elementHtml .= $readonly . $placeholder . $focus . $required . $eventhandlers;
		$elementHtml .= ' />';
		
		return array(
				'element' => $elementHtml,
				'label' => $label
		);
	}
	
	private function renderRange($elemName, $data) {
		// element id
		$id = $this->getElemId($elemName);
	
		// label html
		$label = $this->getElemLabel($id, $data['label']);
	
		// element html
		$readonly = $this->getReadonlyParam($data['readonly']);
		$eventhandlers = $this->getEventHandlersString($data['eventhandler']);
		$focus = $this->getAutofocusParam($elemName);
		$preselect = (!is_null($data['preselect'])) ? $data['preselect'] : '';
	
		$elementHtml = '<input type="range" name="'.$elemName.'" id="'.$id.'" value="' . $preselect . '" ';
		$elementHtml .= ' min="' . $data['min'] . '" max="' . $data['max'] . '" step="' . $data['step'] . '" ';
		$elementHtml .= $readonly . $focus . $eventhandlers;
		$elementHtml .= ' />';
		
		return array(
				'element' => $elementHtml,
				'label' => $label
		);
	}
	
	private function renderRadiobuttons($elemName, $data) {
		// element id
		$id = $this->getElemId($elemName);
		
		// label html
		$label = $this->getElemLabel($id, $data['label']);
		
		// element html
		$preselect = (!is_null($data['preselect'])) ? $data['preselect'] : '';
		$required = $this->getRequiredParam($data['required']);
		$eventhandlers = $this->getEventHandlersString($data['eventhandler']);
		
		$elementHtml = '';
		foreach ($data['radiobuttons'] AS $value => $descr) {
			$checked = '';
			if ($value == $preselect) {
				$checked = ' checked="checked" ';
			} elseif ($data['readonly'] == true) {
				// workaround to enable 'readonly' functionality for radiobuttons
				$checked = ' disabled="disabled" ';
			}
			$radioId = $id . '_' . $value;
			$elementHtml .= '<input type="radio" name="' . $elemName . '" id="' . $radioId . '" value="' . $value . '" ';
			$elementHtml .= $checked . $required . $eventhandlers . '/>';
			$elementHtml .= '<label for="' . $radioId . '">' . $descr . '</label>';
		}
		
		return array(
				'element' => $elementHtml,
				'label' => $label
		);
	}
	
	private function renderCheckboxes($elemName, $data) {
		// element id
		$id = $this->getElemId($elemName);
		
		// label html 
		$label = $this->getElemLabel($id, $data['label']);
		
		// element html
		$preselect = $data['preselect'];
		$eventhandlers = $this->getEventHandlersString($data['eventhandler']);
		
		$elementHtml = '';
		foreach ($data['checkboxes'] AS $value => $descr) {
			$checked = (in_array($value, $preselect)) ? ' checked="checked" ' : '';
			$chkbxId = $id . '_' . $value;
			if ($data['readonly'] == true) {
				// workaround to enable 'readonly' functionality for checkboxes
				$elementHtml .= '<input type="checkbox" name="dummy_' . $elemName . '[]" id="dummy_' . $chkbxId . '" value="' . $value . '" disabled="disabled" ';
				$elementHtml .= $checked . $eventhandlers . '/>';
				if (in_array($value, $preselect)) {
					$elementHtml .= '<input type="hidden" name="' . $elemName . '[]" id="' . $chkbxId . '" value="' . $value . '" />';
				}
			} else {
				$elementHtml .= '<input type="checkbox" name="' . $elemName . '[]" id="' . $chkbxId . '" value="' . $value . '" ';
				$elementHtml .= $checked . $eventhandlers . '/>';
			}
			$elementHtml .= '<label for="' . $chkbxId . '">' . $descr . '</label>';
		}
		
		return array(
				'element' => $elementHtml,
				'label' => $label
		);
	}
	
	private function renderDropdown($elemName, $data) {
		// element id
		$id = $this->getElemId($elemName);
		
		// label html 
		$label = $this->getElemLabel($id, $data['label']);
		
		// element html
		$preselect = $data['preselect'];
		$readonly = $this->getReadonlyParam($data['readonly']);
		$required = $this->getRequiredParam($data['required']);
		$size = (!is_null($data['size'])) ? ' size="' . $data['size'] . '" ' : '';
		$multiple = ($data['multiple'] == true) ? ' multiple="multiple" ' : '';
		$eventhandlers = $this->getEventHandlersString($data['eventhandler']);
		
		$elementHtml = '<select name="' . $elemName . '" id="' . $id . '" ' . $size . $multiple . $readonly . $required . $eventhandlers . '>';
		$elementHtml .= '<option disabled selected="selected" label="" />';
		foreach ($data['values'] AS $value => $descr) {
			$selected = "";
			if (in_array($value, $preselect)) {
				$selected = ' selected="selected" ';
			} elseif ($data['readonly'] == true) {
				// workaround to enable 'readonly' functionality for dropdowns
				$selected = ' disabled="disabled" ';
			}
			$elementHtml .= '<option value="' . $value . '" label="'.$descr.'" ' . $selected . '>' . $descr . '</option>';
		}
		$elementHtml .= '</select>';
		
		return array(
				'element' => $elementHtml,
				'label' => $label
		);
	}
	
	private function renderDate($elemName, $data) {
		// element id
		$id = $this->getElemId($elemName);
		
		// label html
		$label = $this->getElemLabel($id, $data['label']);
		
		// element html
		$readonly = $this->getReadonlyParam($data['readonly']);
		$preselect = (!is_null($data['preselect'])) ? $data['preselect'] : '';
		$placeholder = $this->getPlaceholderParam($data['placeholder']);
		$pattern = $this->getPatternParam($data['pattern']);
		$required = $this->getRequiredParam($data['required']);
		$eventhandlers = $this->getEventHandlersString($data['eventhandler']);
		
		$elementHtml = '<input name="' . $elemName . '" id="' . $id . '" type="' . $data['datetype'] . '" value="' . $preselect . '" ';
		$elementHtml .= $readonly . $placeholder . $pattern . $required . $eventhandlers;
		$elementHtml .= ' />';
		
		return array(
				'element' => $elementHtml,
				'label' => $label
		);
	}

	private function renderHeadline($elemName, $data) {
		// element id
		$id = $this->getElemId($elemName);
		
		$elementHtml = '<div class="form_row form_row_headline" id=' . $id . '" id="">';
			$elementHtml .= '<div class="form_headline">' . $data['text'] . '</div>';
		$elementHtml .= '</div>';
		
		return array(
				'label' => '',
				'element' => $elementHtml
		);
	}

	private function renderHidden($elemName, $data) {
		// element id
		$id = $this->getElemId($elemName);
		
		return array(
				'label' => '',
				'element' => '<input type="hidden" id="' . $id . '" name="' . $elemName . '" value="' . $data['value'] . '" />'
		);
	}
	
	# +++++++++++++++++++++++++ helper methods +++++++++++++++++++++++++
	
	/**
	 * return required param string
	 * 
	 * @param boolean $required
	 * @return string
	 */
	private function getRequiredParam($required) {
		return ($required == true) ? ' required="required" ' : '';
	}
	
	/**
	 * return placeholder param string
	 * 
	 * @param string $placeholder
	 * @return string
	 */
	private function getPlaceholderParam($placeholder) {
		return (!is_null($placeholder)) ? ' placeholder="'.$placeholder.'" ' : '';
	}
	
	/**
	 * return pattern param string
	 * 
	 * @param string $pattern
	 */
	private function getPatternParam($pattern) {
		return (!is_null($pattern)) ? ' pattern="'.$pattern.'" ' : '';
	}
	
	/**
	 * return readonly param string
	 * 
	 * @param boolean $readonly
	 * @return string
	 */
	private function getReadonlyParam($readonly) {
		return ($readonly == true) ? ' readonly="readonly" ' : '';
	}
	
	/**
	 * return autofocus param string
	 * 
	 * @param string $elementName
	 * @return string
	 */
	private function getAutofocusParam($elementName) {
		return ($elementName == $this->getFocus()) ? ' autofocus="autofocus" ' : '';
	}
	
	/**
	 * return the label html
	 * 
	 * @param string $id
	 * @param string|null $labelText
	 * @return string
	 */
	private function getElemLabel($id, $labelText) {
		$labelHtml = '';
		if (!is_null($labelText)) {
			$labelHtml = '<label class="formhelper_label" for="' . $id . '" >' . $labelText . '</label>';
		}
		return $labelHtml;
	}
	
	/**
	 * get element dom id
	 * 
	 * @param string $elemName
	 * @return string
	 */
	private function getElemId($elemName) {
		return "form_elem_" . $elemName;
	}
	
	/**
	 * return all eventhandlers as html parameters string
	 * 
	 * @param array $eventhandler
	 * @return string
	 */
	private function getEventHandlersString($eventhandler) {
		$string = '';
		if (is_array($eventhandler)) {
			foreach ($eventhandler AS $event => $handler) {
				$string .= ' ' . $event . '="' . $handler . '" ';
			}
		}
		return $string;
	}

	/**
	 * return error css class if field is invalid
	 */
	private function getErrorClass($name) {
		if (in_array($name, $this->formErrors)) {
			return ' field-invalid ';
		}
		return '';
	}
}