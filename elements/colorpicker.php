<?php
/**
  copyright Fiona Coulter 2011 Spiral Scripts http://www.spiralscripts.co.uk
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');


class JElementColorpicker extends JElement
{
	/**
	 * Color picker form element type compatible with Joomla 1.5. Displays an Adobe type color picker panel, and returns a six-digit hex value, eg #cc99ff
	 */
	var	$_name = 'colorpicker';

	function fetchElement($name, $value, &$node, $control_name){
		
		//try to find script
        $baseurl = JURI::base();
		$baseurl = str_replace('administrator/','',$baseurl);
				
		$size = $node->attributes('size') ? 'size="'.$node->attributes('size').'"' : '';
		$class = $node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="text_area"';
		$scriptname = $node->attributes('scriptpath') ? $node->attributes('scriptpath') : $baseurl.'media/colorpicker/js/color-picker.js';	
		
		if($scriptname == 'self')
		{
           $filedir = str_replace(JPATH_SITE . '/','',dirname(__FILE__));
    	   $filedir = str_replace('\\','/',$filedir);
           $scriptname = $baseurl . $filedir . '/color-picker.js';
		}
		
		$doc =& JFactory::getDocument();
		$doc->addScript($scriptname);
		
		$options = array();
		if( $node->attributes('cellwidth')){ $options[] = "cellWidth:". (int)$node->attributes('cellwidth');}
		if( $node->attributes('cellheight')){ $options[] = "cellHeight:".(int)$node->attributes('cellheight');}
		if( $node->attributes('top')){ $options[] = "top:". (int)$node->attributes('top');}
		if( $node->attributes('left')){ $options[] = "left:". (int)$node->attributes('left');}
																			  
        $optionString = implode(',',$options);

		$js = 'window.addEvent(\'domready\', function(){
		var colorInput = $(\''.$control_name.$name.'\');
		var cpicker = new ColorPicker(colorInput,{'.$optionString.'});
});
';

        $doc->addScriptDeclaration($js);

		
        /*
         * Required to avoid a cycle of encoding &
         * html_entity_decode was used in place of htmlspecialchars_decode because
         * htmlspecialchars_decode is not compatible with PHP 4
         */
        $value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES), ENT_QUOTES);

		$output = '<input type="text" name="'.$control_name.'['.$name.']" id="'.$control_name.$name.'" value="'.$value.'" '.$class.' '.$size.' />';
		
		return $output;
	}
}
