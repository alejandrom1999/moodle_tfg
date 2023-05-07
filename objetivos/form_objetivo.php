<?php

require_once (__DIR__ . '/../../config.php');
require_once ($CFG->dirroot . '/blocks/objetivos/classes/form/form_objetivo.php');


$PAGE->set_url(new moodle_url('/blocks/objetivos/form_objetivo.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Formulario de un objetivo');
$PAGE->set_heading('Creacion de objetivo');

$form = new form_objetivo();

function insertar_objetivo($id_curso, $nombre)
{
    global $DB;
    $objetivo_n = new stdClass();
    $objetivo_n->id = mt_rand();
    $objetivo_n->id_course = $id_curso;
    $objetivo_n->nombre = $nombre;
    $DB->insert_record('objetivo' , $objetivo_n );
}

function get_data()
{
    return $form->get_data();
}

if ($form->is_cancelled())
{
    $id_curso = optional_param('id_curso',' ', PARAM_TEXT);
    redirect(new moodle_url('/course/view.php?id=' . $id_curso . '/'));
} else if ($data = $form->get_data()) {
    // Process form data.
    $nombre = $data->nombre;
    $id_curso = optional_param('id_curso',' ', PARAM_TEXT);

    insertar_objetivo($id_curso, $nombre);
    redirect(new moodle_url('/course/view.php?id=' . $id_curso . '/'));
 } else {
    // Display the form.
    echo $OUTPUT->header();

    $form->display();
    echo $OUTPUT->footer();
}
