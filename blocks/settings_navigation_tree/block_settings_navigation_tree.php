<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains classes used to manage the navigation structures in Moodle
 * and was introduced as part of the changes occuring in Moodle 2.0
 *
 * @since 2.0
 * @package blocks
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The settings navigation tree block class
 *
 * Used to produce the settings navigation block new to Moodle 2.0
 *
 * @package blocks
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_settings_navigation_tree extends block_tree {

    /** @var string */
    public static $navcount;
    public $blockname = null;
    /** @var bool */
    protected $contentgenerated = false;
    /** @var bool|null */
    protected $docked = null;

    /**
     * Set the initial properties for the block
     */
    function init() {
        $this->blockname = get_class($this);
        $this->title = get_string('blockname', $this->blockname);
        $this->version = 2009082800;
    }

    /**
     * All multiple instances of this block
     * @return bool Returns true
     */
    function instance_allow_multiple() {
        return false;
    }

    /**
     * Set the applicable formats for this block to all
     * @return array
     */
    function applicable_formats() {
        return array('all' => true);
    }

    /**
     * Allow the user to configure a block instance
     * @return bool Returns true
     */
    function instance_allow_config() {
        return true;
    }

    function get_required_javascript() {
        global $CFG;
        $this->_initialise_dock();
        $this->page->requires->js_module('blocks_navigation', array('fullpath'=>$CFG->wwwroot.'/blocks/global_navigation_tree/navigation.js', 'requires'=>array('blocks_dock', 'io', 'node', 'dom', 'event-custom')));
        $arguments = array($this->instance->id, array('instance'=>$this->instance->id, 'candock'=>$this->instance_can_be_docked()));
        $this->page->requires->js_object_init("M.blocks.navigation.treecollection[".$this->instance->id."]", 'M.blocks.navigation.classes.tree', $arguments, array('blocks_navigation'));
        user_preference_allow_ajax_update('M.docked_block_instance_'.$this->instance->id, PARAM_INT);
    }

    /**
     * Gets the content for this block by grabbing it from $this->page
     */
    function get_content() {
        global $CFG, $OUTPUT;
        // First check if we have already generated, don't waste cycles
        if ($this->contentgenerated === true) {
            return true;
        }
        $this->page->requires->yui2_lib('dom');
        // JS for navigation moved to the standard theme, the code will probably have to depend on the actual page structure
        // $this->page->requires->js('/lib/javascript-navigation.js');
        block_settings_navigation_tree::$navcount++;

        // Check if this block has been docked
        if ($this->docked === null) {
            $this->docked = get_user_preferences('nav_in_tab_panel_settingsnav'.block_settings_navigation_tree::$navcount, 0);
        }

        // Check if there is a param to change the docked state
        if ($this->docked && optional_param('undock', null, PARAM_INT)==$this->instance->id) {
            unset_user_preference('nav_in_tab_panel_settingsnav'.block_settings_navigation_tree::$navcount, 0);
            $url = $this->page->url;
            $url->remove_params(array('undock'));
            redirect($url);
        } else if (!$this->docked && optional_param('dock', null, PARAM_INT)==$this->instance->id) {
            set_user_preferences(array('nav_in_tab_panel_settingsnav'.block_settings_navigation_tree::$navcount=>1));
            $url = $this->page->url;
            $url->remove_params(array('dock'));
            redirect($url);
        }

        // Grab the children from settings nav, we have more than one root node
        // and we dont want to show the site node
        $this->content->items = $this->page->settingsnav->children;
        // only do search if you have moodle/site:config
        if (count($this->content->items)>0) {
            if (has_capability('moodle/site:config',get_context_instance(CONTEXT_SYSTEM)) ) {
                $searchform = new html_form();
                $searchform->url = new moodle_url("$CFG->wwwroot/$CFG->admin/search.php");
                $searchform->method = 'get';
                $searchform->button->text = get_string('search');
                $searchfield = html_field::make_text('query', optional_param('query', '', PARAM_RAW), '', 50);
                $searchfield->id = 'query';
                $searchfield->style .= 'width: 7em;';
                $searchfield->set_label(get_string('searchinsettings', 'admin'), 'query');
                $searchfield->label->add_class('accesshide');
                $this->content->footer = $OUTPUT->container($OUTPUT->form($searchform, $OUTPUT->field($searchfield)), 'adminsearchform');
            } else {
                $this->content->footer = '';
            }

            $reloadlink = new html_link(new moodle_url($this->page->url, array('regenerate'=>'navigation')));
            $reloadlink->add_class('customcommand');
            $this->content->footer .= $OUTPUT->action_icon($reloadlink, get_string('reload'), 't/reload');

            if (!empty($this->config->enablesidebarpopout) && $this->config->enablesidebarpopout == 'yes') {
                user_preference_allow_ajax_update('nav_in_tab_panel_settingsnav'.block_settings_navigation_tree::$navcount, PARAM_INT);
            }
        }

        $this->contentgenerated = true;
        return true;
    }

    function html_attributes() {
        $attributes = parent::html_attributes();
        if (!empty($this->config->enablehoverexpansion) && $this->config->enablehoverexpansion == 'yes') {
            $attributes['class'] .= ' block_js_expansion';
        }
        return $attributes;
    }
}