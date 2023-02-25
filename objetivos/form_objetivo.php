<?php

require_once (__DIR__ . '/../../config.php');
require_once ($CFG->dirroot . '/blocks/objetivos/classes/form/form_objetivo.php');

$PAGE->set_url(new moodle_url('/blocks/objetivos/form_objetivo.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Formulario de un objetivo');
$PAGE->set_heading('Creacion de objetivo');

// Form displayed
$mform = new form_objetivo();

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
/*Crear un objetivo a partir de un nombre*/
function crear_objetivo($nombre)
{
    global $COURSE, $DB;
    $objetivo_n = new stdClass();
    $objetivo_n->id = mt_rand();
    $objetivo_n->id_course = $COURSE->id;
    $objetivo_n->nombre = $nombre;
    $DB->insert_record(objetivo , $objetivo_n );
}
// Obtener la id de un curso en base a su nombre
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
// Obtenemos las tareas totales de un curso.
function get_tareas($curso_id)
{
    global $DB;


    // 1. Obtener lista de tareas de la BBDD.
    $sql1 = "SELECT b_o.name
             FROM {assign} b_o
             WHERE b_o.course = $curso_id";

    $nombres = $DB->get_records_sql($sql1);

    $tareas = array();
    $i = 0;
    // 2. Meterlas en un array y devolverlas.
    foreach($nombres as $nom)
    {
        $tareas[$i++] = $nom->name;
    }

    return $tareas;
}




if($fromform = $mform->is_cancelled())
{
    redirect($CFG->wwwroot.'/my/', 'Has cancelado la creación del objetivo');
} else if ($fromform = $mform->get_data()) {
    $nombre = $fromform->nombre;
    $curso_arr_id = $fromform->curso;

    $array_nombres_cursos = get_nombres_curso();
    $nombre_seleccionado = $array_nombres_cursos[$curso_arr_id];
    $id_curso = get_id_curso($nombre_seleccionado);

    redirect(new moodle_url('/blocks/objetivos/form_tarea.php',
        array('id_curso' => $id_curso, 'nombre_objetivo' => $nombre )));

} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    $mform->set_data($toform);
}

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();