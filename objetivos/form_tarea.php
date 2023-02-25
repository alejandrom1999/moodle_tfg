<?php

require_once (__DIR__ . '/../../config.php');
require_once ($CFG->dirroot . '/blocks/objetivos/classes/form/form_tarea.php');

$PAGE->set_url(new moodle_url('/blocks/objetivos/form_tarea.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Formulario de una tarea');
$PAGE->set_heading('Creacion de una tarea');

// Form displayed
$mform = new form_tarea();


if($fromform = $mform->is_cancelled())
{
    redirect($CFG->wwwroot.'/my/');
} else if ($fromform = $mform->get_data()) {




} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    $mform->set_data($toform);
}

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();