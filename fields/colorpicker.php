<?php
/**
  copyright Fiona Coulter 2011 Spiral Scripts http://www.spiralscripts.co.uk
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @deprecated  Will be removed when J! <2.5 support is dropped in favor of JHtmlBehavior::colorpicker
 */
class JFormFieldColorpicker extends JFormField
{
	/**
	 * Color picker form field type compatible with Joomla 1.6. Displays an Adobe type color picker panel, and returns a six-digit hex value, eg #cc99ff
	 */
	protected $type = 'Colorpicker';

	/**
	 */
	protected function getInput()
	{

        $baseurl = JURI::base();
		$baseurl = str_replace('administrator/','',$baseurl);


		// Initialize some field attributes.
		$size		= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$maxLength	= $this->element['maxlength'] ? ' maxlength="'.(int) $this->element['maxlength'].'"' : '';
		$class		= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$readonly	= ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled	= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$scriptname	 = $this->element['scriptpath'] ?(string) $this->element['scriptpath'] : $baseurl.'media/colorpicker/js/color-picker.js';


		//try to find the script
		if($scriptname == 'self')
		{
           $filedir = str_replace(JPATH_SITE . '/','',dirname(__FILE__));
    	   $filedir = str_replace('\\','/',$filedir);
           $scriptname = $baseurl . $filedir . '/color-picker.js';
		}


		$doc =& JFactory::getDocument();
		$doc->addScript($scriptname);

		$options = array();
		if( $this->element['cellwidth']){ $options[] = "cellWidth:". (int) $this->element['cellwidth'];}
		if( $this->element['cellheight']){ $options[] = "cellHeight:".(int) $this->element['cellheight'];}
		if( $this->element['top']){ $options[] = "top:". (int) $this->element['top'];}
		if( $this->element['left']){ $options[] = "left:". (int) $this->element['left'];}

        $optionString = implode(',',$options);

		$js = 'window.addEvent(\'domready\', function(){
		var colorInput = $(\''.$this->id.'\');
		var cpicker = new ColorPicker(colorInput,{'.$optionString.'});
});
';

        $doc->addScriptDeclaration($js);



		// Initialize JavaScript field attributes.
		$onchange	= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		return '<input type="text" name="'.$this->name.'" id="'.$this->id.'"' .
				' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'"' .
				$class.$size.$disabled.$readonly.$onchange.$maxLength.'/>';
	}
}
