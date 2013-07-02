<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

/**
 * VC Global Variables Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Alex Glover
 * @link		http://eecoder.com
 */
class Vc_global_variables_ext {

    public $settings = array();
    public $description = 'Creates global variables for channel ids and field ids';
    public $docs_url = '';
    public $name = 'VC Global Variables';
    public $settings_exist = 'y';
    public $version = '1.0';
    private $EE;

    /**
     * Constructor
     *
     * @param 	mixed	Settings array or empty string if none exist.
     */
    public function __construct($settings = '') {
        $this->EE = & get_instance();
        $this->settings = $settings;
    }

    // ----------------------------------------------------------------------

    /**
     * Settings Form
     *
     * If you wish for ExpressionEngine to automatically create your settings
     * page, work in this method.  If you wish to have fine-grained control
     * over your form, use the settings_form() and save_settings() methods 
     * instead, and delete this one.
     *
     * @see http://expressionengine.com/user_guide/development/extensions.html#settings
     */
    public function settings() {
        // get all channels
        $channels = $this->get_channels();

        // get all fields
        $fields = $this->get_fields();

        return array(
            'channels' => array('ms', $channels, ''),
            'fields' => array('ms', $fields, '')
        );
    }

    // ----------------------------------------------------------------------

    /**
     * Activate Extension
     *
     * This function enters the extension into the exp_extensions table
     *
     * @see http://codeigniter.com/user_guide/database/index.html for
     * more information on the db class.
     *
     * @return void
     */
    public function activate_extension() {
        // Setup custom settings in this array.
        $this->settings = array();

        $data = array(
            'class' => __CLASS__,
            'method' => 'sessions_end',
            'hook' => 'sessions_end',
            'settings' => serialize($this->settings),
            'version' => $this->version,
            'enabled' => 'y'
        );

        $this->EE->db->insert('extensions', $data);
    }

    // ----------------------------------------------------------------------

    /**
     * sessions_start
     *
     * @param 
     * @return 
     */
    public function sessions_end() 
    {
        if (isset($this->settings['channels'])) {
             $channels = $this->EE->db
                    ->select('*')
                    ->from('channels')
                    ->where('site_id', $this->EE->config->item('site_id'))
                    ->where_in('channel_id', $this->settings['channels'])
                    ->get();

            // loop through all channels and create global vars
            foreach ($channels->result() as $row) {
                $this->EE->config->_global_vars['channel_' . $row->channel_name] = $row->channel_id;
            }           
        }
        
        if (isset($this->settings['fields']) && count($this->settings['fields']) > 0) {
            $fields = $this->EE->db
                            ->select('*')
                            ->from('channel_fields')
                            ->where('site_id', $this->EE->config->item('site_id'))
                            ->where_in('field_id', $this->settings['fields'])
                            ->get();   

            foreach ($fields->result() as $row) {
                $this->EE->config->_global_vars['field_' . $row->field_name] = 'field_id_' . $row->field_id;
            }
        }
    }
    
    private function get_channels() {
        $query = $this->EE->db
                ->select('channel_id, channel_title')
                ->from('channels')
                ->where('site_id', $this->EE->config->item('site_id'))
                ->order_by('channel_title', 'ASC')
                ->get();

        if (!$query->num_rows()) {
            return false;
        }

        $channels = array();
        foreach ($query->result() as $row) {
            $channels[$row->channel_id] = $row->channel_title;
        }

        return $channels;
    }

    private function get_fields() {
        $query = $this->EE->db
                ->select('*')
                ->from('channel_fields')
                ->join('field_groups', 'channel_fields.group_id = field_groups.group_id')
                ->where('channel_fields.site_id', $this->EE->config->item('site_id'))
                ->order_by('field_groups.group_name', 'ASC')
                ->get();

        $fields = array();
        foreach ($query->result() as $row) {
            $fields[$row->field_id] = $row->field_label;
        }

        return $fields;
    }

    // ----------------------------------------------------------------------

    /**
     * Disable Extension
     *
     * This method removes information from the exp_extensions table
     *
     * @return void
     */
    function disable_extension() {
        $this->EE->db->where('class', __CLASS__);
        $this->EE->db->delete('extensions');
    }

    // ----------------------------------------------------------------------

    /**
     * Update Extension
     *
     * This function performs any necessary db updates when the extension
     * page is visited
     *
     * @return 	mixed	void on update / false if none
     */
    function update_extension($current = '') {
        if ($current == '' OR $current == $this->version) {
            return FALSE;
        }
    }

    // ----------------------------------------------------------------------
}

/* End of file ext.vc_global_variables.php */
/* Location: /system/expressionengine/third_party/vc_global_variables/ext.vc_global_variables.php */
