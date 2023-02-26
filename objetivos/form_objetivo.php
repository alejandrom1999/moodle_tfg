<?php

require_once (__DIR__ . '/../../config.php');
require_once ($CFG->dirroot . '/blocks/objetivos/classes/form/form_objetivo.php');


$PAGE->set_url(new moodle_url('/blocks/objetivos/form_objetivo.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Formulario de un objetivo');
$PAGE->set_heading('Creacion de objetivo');

$form = new form_objetivo();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/my/'));
} else if ($data = $form->get_data()) {
    // Process form data.
    $form->process_data($data);
} else {
    // Display the form.
    echo $OUTPUT->header();

    $form->display();
    echo $OUTPUT->footer();
}
