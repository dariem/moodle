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
 * The workshop module configuration variables
 *
 * The values defined here are often used as defaults for all module instances.
 *
 * @package   mod-workshop
 * @copyright 2009 David Mudrak <david.mudrak@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/workshop/lib.php');

$grades = workshop_get_maxgrades();

$settings->add(new admin_setting_configselect('workshop/grade', get_string('submissiongrade', 'workshop'),
                    get_string('configgrade', 'workshop'), 80, $grades));

$settings->add(new admin_setting_configselect('workshop/gradinggrade', get_string('gradinggrade', 'workshop'),
                    get_string('configgradinggrade', 'workshop'), 20, $grades));

$options = array();
for ($i = 5; $i >= 0; $i--) {
    $options[$i] = $i;
}
$settings->add(new admin_setting_configselect('workshop/gradedecimals', get_string('gradedecimals', 'workshop'),
                    get_string('configgradedecimals', 'workshop'), 0, $options));

$options = get_max_upload_sizes($CFG->maxbytes);
$options[0] = get_string('courseuploadlimit');
$settings->add(new admin_setting_configselect('workshop/maxbytes', get_string('maxbytes', 'workshop'),
                    get_string('configmaxbytes', 'workshop'), 0, $options));

$settings->add(new admin_setting_configselect('workshop/strategy', get_string('strategy', 'workshop'),
                    get_string('configstrategy', 'workshop'), 'accumulative', workshop_get_strategies()));

$options = workshop_get_numbers_of_assessments();
$settings->add(new admin_setting_configselect('workshop/nsassessments', get_string('nsassessments', 'workshop'),
                    get_string('confignsassessments', 'workshop'), 3, $options));

$options = workshop_get_numbers_of_assessments();
$options[0] = get_string('assessallexamples', 'workshop');
$settings->add(new admin_setting_configselect('workshop/nexassessments', get_string('nexassessments', 'workshop'),
                    get_string('confignexassessments', 'workshop'), 0, $options));

$options = workshop_get_example_modes();
$settings->add(new admin_setting_configselect('workshop/examplesmode', get_string('examplesmode', 'workshop'),
                    get_string('configexamplesmode', 'workshop'), WORKSHOP_EXAMPLES_VOLUNTARY, $options));

$levels = array();
foreach (workshop_get_comparison_levels() as $code => $level) {
    $levels[$code] = $level->name;
}
$settings->add(new admin_setting_configselect('workshop/assessmentcomps', get_string('assessmentcomps', 'workshop'),
                    get_string('configassessmentcomps', 'workshop'), WORKSHOP_COMPARISON_NORMAL, $levels));

// include the settings of grading strategy subplugins
$strategies = get_plugin_list('workshopform');
foreach ($strategies as $strategy => $path) {
    if (file_exists($settingsfile = $path . '/settings.php')) {
        $settings->add(new admin_setting_heading('workshopformsetting'.$strategy,
                get_string('strategy', 'workshop') . ' - ' . get_string('pluginname', 'workshopform_' . $strategy), ''));
        include($settingsfile);
    }
}

// include the settings of grading evaluation subplugins
$evaluations = get_plugin_list('workshopeval');
foreach ($evaluations as $evaluation => $path) {
    if (file_exists($settingsfile = $path . '/settings.php')) {
        $settings->add(new admin_setting_heading('workshopevalsetting'.$evaluation,
                get_string('evaluation', 'workshop') . ' - ' . get_string('pluginname', 'workshopeval_' . $evaluation), ''));
        include($settingsfile);
    }
}