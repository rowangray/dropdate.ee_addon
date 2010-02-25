<?php

if ( ! defined('EXT'))
{
	exit('Invalid file request');
}

/**
 * Fieldtype enabling users to select a date using 3 drop-downs (day, month, year).
 *
 * @package   	DropDate
 * @version   	1.0.0
 * @author    	Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright 	Copyright (c) 2010, Stephen Lewis
 * @link      	http://experienceinternet.co.uk/dropdate/
 */

class Dropdate extends Fieldframe_Fieldtype {
	
	/**
	 * --------------------------------------------------------------
	 * CLASS CONSTANTS
	 * --------------------------------------------------------------
	 */
	
	const DROPDATE_FMT_UNIX = 'unix';
	const DROPDATE_FMT_YMD	= 'ymd';
	
	
	/**
	 * --------------------------------------------------------------
	 * INSTANCE VARIABLES
	 * --------------------------------------------------------------
	 */
	
	/**
	 * Basic fieldtype information.
	 *
	 * @access	public
	 * @var 	array
	 */
	public $info = array(
		'name'				=> 'DropDate',
		'version'			=> '1.0.0',
		'desc'				=> 'Fieldtype enabling users to select a date using 3 drop-downs (day, month, year).',
		'docs_url'			=> 'http://experienceinternet.co.uk/dropdate/',
		'versions_xml_url'	=> 'http://experienceinternet.co.uk/addon-versions.xml'
	);

	/**
	 * Fieldtype requirements.
	 *
	 * @access	public
	 * @var 	array
	 */
	public $requirements = array(
		'ff'        => '1.3.4',
		'cp_jquery' => '1.1'
	);

	/**
	 * Default site settings.
	 *
	 * @access	public
	 * @var 	array
	 */
	public $default_site_settings = array('date_format' => self::DROPDATE_FMT_UNIX);
	
	/**
	 * The site ID.
	 *
	 * @access	private
	 * @var 	string
	 */
	private $site_id = '';
	
	/**
	 * The class name.
	 *
	 * @access	private
	 * @var 	string
	 */
	private $class = '';
	
	/**
	 * Lower-class classname.
	 *
	 * @access	private
	 * @var 	string
	 */
	private $lower_class = '';
	
	/**
	 * The Session namespace.
	 *
	 * @access	private
	 * @var 	string
	 */
	private $namespace = '';
	
	
	
	/**
	 * --------------------------------------------------------------
	 * PUBLIC METHODS
	 * --------------------------------------------------------------
	 */

	/**
	 * Constructor function.
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		global $DB, $PREFS;
		
		$this->site_id 		= $DB->escape_str($PREFS->ini('site_id'));
		$this->class 		= get_class($this);
		$this->lower_class 	= strtolower($this->class);
		$this->namespace	= 'sl';
	}
	
	
	/**
	 * Adds custom cell settings to an FF Matrix field in the "Create / Edit Field" form.
	 *
	 * @access	public
	 * @param	array		$cell_settings		Previously saved cell settings.
	 * @return	void
	 */
	public function display_cell_settings($cell_settings = array())
	{
		$html = $this->display_field_settings($cell_settings);
		return (isset($html['cell2'])) ? $html['cell2'] : '';
	}
	
	
	/**
	 * Displays the custom cell HTML for the "Publish / Edit" form.
	 *
	 * @access	public
	 * @param	string		$cell_name			The cell name.
	 * @param	string		$cell_data			Previously saved cell data.
	 * @param 	array 		$cell_settings		The cell settings.
	 * @return	string
	 */
	public function display_cell($cell_name = '', $cell_data = '', $cell_settings = array())
	{
		return $this->display_field($cell_name, $cell_data, $cell_settings);
	}
	
	
	/**
	 * Displays the custom field HTML for the "Publish / Edit" form.
	 *
	 * @access	public
	 * @param	string		$field_name			The field name.
	 * @param	string		$field_data			Previously saved field data.
	 * @param 	array 		$field_settings		The field settings.
	 * @return	string
	 */
	public function display_field($field_name = '', $field_data = '', $field_settings = array())
	{
		global $DSP, $LANG;
		
		$LANG->fetch_language_file($this->lower_class);
		$SD = new Fieldframe_SettingsDisplay();
		
		// Days.
		$days[] = $LANG->line('day');
		for ($count = 1; $count <= 31; $count++)
		{
			$days[] = str_pad($count, 2, '0', STR_PAD_LEFT);
		}
		
		// Months.
		$months = array(
			$LANG->line('month'),
			$LANG->line('jan'), $LANG->line('feb'),
			$LANG->line('mar'), $LANG->line('apr'),
			$LANG->line('may'), $LANG->line('jun'),
			$LANG->line('jul'), $LANG->line('aug'),
			$LANG->line('sep'), $LANG->line('oct'),
			$LANG->line('nov'), $LANG->line('dec')
		);
		
		// Years.
		$years[] = $LANG->line('year');
		for ($count = 1900; $count <= 2020; $count++)
		{
			$years[$count] = $count;
		}
		
		// Determine the existing day / month / year values.
		$saved_year = $saved_month = $saved_day = '';
		
		if ($field_data)
		{
			if (isset($field_settings['date_format']) && $field_settings['date_format'] == self::DROPDATE_FMT_YMD)
			{
				$pattern = '/^([0-9]{4})([0-9]{2})([0-9]{2})$/';
				if (preg_match($pattern, $field_data, $matches))
				{
					$saved_year		= $matches[1];
					$saved_month	= $matches[2];
					$saved_day		= $matches[3];
				}
			}
			else
			{
				$saved_year 	= date('Y', $field_data);
				$saved_month	= date('n', $field_data);
				$saved_day		= date('j', $field_data);
			}
		}
		
		// Generate the HTML.
		$html = '';
		$html .= $SD->select($field_name ."[]", $saved_day, $days);
		$html .= $SD->select($field_name ."[]", $saved_month, $months);
		$html .= $SD->select($field_name ."[]", $saved_year, $years);
			
		return $html;
	}
	
	
	/**
	 * Adds custom settings to the "Create / Edit Field" form.
	 *
	 * @access	public
	 * @param	array		$field_settings		Previously saved field settings.
	 * @return	array
	 */
	public function display_field_settings($field_settings = array())
	{
		global $LANG;
		
		$LANG->fetch_language_file($this->lower_class);
		$SD = new Fieldframe_SettingsDisplay();
		
		if (isset($field_settings['date_format']))
		{
			$value = $field_settings['date_format'];
		}
		else
		{
			$value = isset($this->site_settings['date_format'])
				? $this->site_settings['date_format']
				: '';
		}
		
		$options = array(
			self::DROPDATE_FMT_UNIX => $LANG->line('unix_format_label'),
			self::DROPDATE_FMT_YMD	=> $LANG->line('ymd_format_label')
		);
		
		$html = '<div class="itemWrapper"><label class="defaultBold">' .$LANG->line('save_format_label') .'</label></div>'
			.$SD->radio_group('date_format', $value, $options, array('extras' => ' style="width : auto;"'));
		
		return array('cell2' => $html);
	}
	
	
	/**
	 * Displays the field data in a template tag.
	 *
	 * @access	public
	 * @param	array 		$params				The template tag parameters (key / value pairs).
	 * @param	string		$tagdata			The content between the opening and closing tags, if it's a tag pair.
	 * @param 	string		$field_data			The field data.
	 * @param 	array 		$field_settings		The field settings.
	 * @return	string
	 */
	public function display_tag($params = array(), $tagdata = '', $field_data = '', $field_settings = array())
	{
		if (isset($field_settings['date_format']) && $field_settings['date_format'] == self::DROPDATE_FMT_YMD)
		{
			$pattern = '/^([0-9]{4})([0-9]{2})([0-9]{2})$/';
			$field_data = preg_match($pattern, $field_data, $matches)
				? $field_data = mktime(0, 0, 1, $matches[2], $matches[3], $matches[1])
				: '';
		}
		
		if ( ! $field_data)
		{
			return '';
		}
		
		$params = array_merge(array('format' => 'U'), $params);
		return date($params['format'], $field_data);
	}
	
	
	/**
	 * Modifies the cell's POST data, before it's saved to the database.
	 *
	 * @access	public
	 * @param	mixed		$cell_data			The cell's POST data.
	 * @param	array		$cell_settings		The cell's settings.
	 * @param 	mixed		$entry_id			The entry ID (if postponed saving is enabled), or FALSE.
	 * @return	string
	 */
	public function save_cell($cell_data = '', $cell_settings = array(), $entry_id = FALSE)
	{
		return $this->save_field($cell_data, $cell_settings, $entry_id);
	}
	
	
	/**
	 * Modifies the field's POST data, before it's saved to the database.
	 *
	 * @access	public
	 * @param	mixed		$field_data			The field's POST data.
	 * @param	array		$field_settings		The field settings.
	 * @param 	mixed		$entry_id			The entry ID (if postponed saving is enabled), or FALSE.
	 * @return	string
	 */
	public function save_field($field_data = '', $field_settings = array(), $entry_id = FALSE)
	{
		if ( ! is_array($field_data)
			OR count($field_data) != 3
			OR ! $field_data[0]
			OR ! $field_data[1]
			OR ! $field_data[2])
		{
			return '';
		}
		
		$day 	= $field_data[0];
		$month	= $field_data[1];
		$year	= $field_data[2];
		
		if (isset($field_settings['date_format']) && $field_settings['date_format'] == self::DROPDATE_FMT_YMD)
		{
			$date = $year .str_pad($month, 2, '0', STR_PAD_LEFT) .str_pad($day, 2, '0', STR_PAD_LEFT);
		}
		else
		{
			$date = mktime(0, 0, 1, $month, $day, $year);
		}
		
		return $date;
	}
	
}

/* End of file			: ft.dropdate.php */
/* Location of file		: /system/extensions/fieldtypes/dropdate/ft.dropdate.php */