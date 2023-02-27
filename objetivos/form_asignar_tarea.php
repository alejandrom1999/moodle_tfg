<?php

require_once (__DIR__ . '/../../config.php');
require_once ($CFG->dirroot . '/blocks/objetivos/classes/form/form_asignar_tarea.php');


$PAGE->set_url(new moodle_url('/blocks/objetivos/form_asignar_tarea.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Formulario de asignación de tareas a un objetivo');
$PAGE->set_heading('Asignar una tarea');

$form = new form_asignar_tarea();

function asignar_tarea ()
{

}


if ($form->is_cancelled()) {
    redirect(new moodle_url('/my/'));
} else if ($data = $form->get_data()) {
    $objetivo = $data->objetivo;
    $tarea = $data->tarea;

    var_dump($objetivo);
    die;

    redirect(new moodle_url('/my/'));
} else {
    // Display the form.
    echo $OUTPUT->header();
    $form->display();
    echo $OUTPUT->footer();
}