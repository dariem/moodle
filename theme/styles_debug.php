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
 * This file is responsible for serving of individual style sheets in designer mode.
 *
 * @package   moodlecore
 * @copyright 2009 Petr Skoda (skodak)  {@link http://skodak.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


define('ABORT_AFTER_CONFIG', true);
require('../config.php'); // this stops immediately at the beginning of lib/setup.php

$themename = min_optional_param('theme', 'standard', 'SAFEDIR');
$type      = min_optional_param('type', 'all', 'SAFEDIR');
$subtype   = min_optional_param('subtype', '', 'SAFEDIR');
$sheet     = min_optional_param('sheet', '', 'SAFEDIR');

if (!defined('THEME_DESIGNER_CACHE_LIFETIME')) {
    define('THEME_DESIGNER_CACHE_LIFETIME', 4); // this can be also set in config.php
}

if (file_exists("$CFG->dirroot/theme/$themename/config.php")) {
    // exists
} else if (!empty($CFG->themedir) and file_exists("$CFG->themedir/$themename/config.php")) {
    // exists
} else {
    css_not_found();
}

// no gzip compression when debugging

$candidatesheet = "$CFG->dataroot/cache/theme/$themename/designer.ser";

if (!file_exists($candidatesheet)) {
    css_not_found();
}

if (!$css = file_get_contents($candidatesheet)) {
    css_not_found();
}

$css = unserialize($css);

if ($type === 'plugin') {
    if (isset($css['plugins'][$subtype])) {
        send_uncached_css($css['plugins'][$subtype]);
    }

} else if ($type === 'parent') {
    if (isset($css['parents'][$subtype][$sheet])) {
        send_uncached_css($css['parents'][$subtype][$sheet], 30); // parent sheets are not supposed to change much, right?
    }

} else if ($type === 'theme') {
    if (isset($css['theme'][$sheet])) {
        send_uncached_css($css['theme'][$sheet]);
    }
}
css_not_found();

//=================================================================================
//=== utility functions ==
// we are not using filelib because we need to fine tune all header
// parameters to get the best performance.

function send_uncached_css($css, $lifetime = THEME_DESIGNER_CACHE_LIFETIME) {
    header('Content-Disposition: inline; filename="styles_debug.php"');
    header('Last-Modified: '. gmdate('D, d M Y H:i:s', time()) .' GMT');
    header('Expires: '. gmdate('D, d M Y H:i:s', time() + $lifetime) .' GMT');
    header('Pragma: ');
    header('Accept-Ranges: none');
    header('Content-Type: text/css');
    //header('Content-Length: '.strlen($css));

    echo($css);
    die;
}

function css_not_found() {
    header('HTTP/1.0 404 not found');
    die('CSS was not found, sorry.');
}