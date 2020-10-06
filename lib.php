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
 * Trax Local for Moodle.
 *
 * @package    local_trax
 * @copyright  2019 Sébastien Fraysse {@link http://fraysse.eu}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use logstore_trax\src\services\actors;

function local_trax_customize_statement($statement, $event) {
    global $DB;
    $actors = new actors();

    // Don't process virtual events.
    if (!empty($event->virtual)) {
        return $statement;
    }

    // Get the user from the statement actor.
    $user = $actors->get_reverse_user($statement['actor']);
    if (!$user) {
        return $statement;
    }

    // Get the 'school' field.
    $field = $DB->get_record('user_info_field', ['shortname' => 'School']);
    if (!$field) {
        return $statement;
    }

    // Get the user 'school' value.
    $data = $DB->get_record('user_info_data', ['userid' => $user->id, 'fieldid' => $field->id]);
    if (!$data || empty($data->data)) {
        return $statement;
    }

    // We got it!
    $school = $data->data;

    // Add context extensions when needed.
    if (!isset($statement['context']['extensions'])) {
        $statement['context']['extensions'] = [];
    }

    // Add the school in the context extensions.
    $statement['context']['extensions']['http://vocab.xapi.fr/extensions/school'] = $school;

    return $statement;
}
