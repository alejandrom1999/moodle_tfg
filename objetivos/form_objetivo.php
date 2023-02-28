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
    global $COURSE, $DB;
    $objetivo_n = new stdClass();
    $objetivo_n->id = mt_rand();
    $objetivo_n->id_course = $id_curso;
    $objetivo_n->nombre = $nombre;
    $DB->insert_record(objetivo , $objetivo_n );
}
function get_id_curso($curso_name)
{
    global $DB;


    $records_course = $DB->get_records('course');

    foreach ($records_course as $c )
    {
        if($c->fullname === $curso_name)
        {
            $id_curso = $c->id;
        }
    }

    return $id_curso;

}
function get_nombres_curso()
{
    global $DB;
    // 1. Obtener los cursos
    $sql1 = "SELECT c.fullname
                 FROM {course} c
                 WHERE c.id > 1";

    $cursos = $DB->get_records_sql($sql1);
    // 2. Devolverlo en forma de array
    $nombres = array();
    $i = 0;
    foreach($cursos as $nom)
    {
        $nombres[$i++] = $nom->fullname;
    }

    return $nombres;
}

if ($form->is_cancelled())
{
    redirect(new moodle_url('/my/'));
} else if ($data = $form->get_data()) {
    // Process form data.
    $nombre = $data->nombre;
    $curso_arr_id = $data->curso;

    $array_nombres_cursos = get_nombres_curso();
    $nombre_seleccionado = $array_nombres_cursos[$curso_arr_id];
    $id_curso = get_id_curso($nombre_seleccionado);

    //insertar_objetivo($id_curso, $nombre);
    redirect(new moodle_url('/my/'));
 } else {
    // Display the form.
    echo $OUTPUT->header();

    $form->display();
    echo $OUTPUT->footer();
}
