<?php

require_once (__DIR__ . '/../../config.php');
require_once ($CFG->dirroot . '/blocks/objetivos/classes/form/form_asignar_tarea.php');


$PAGE->set_url(new moodle_url('/blocks/objetivos/form_asignar_tarea.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Formulario de asignación de tareas a un objetivo');
$PAGE->set_heading('Asignar una tarea ');

$form = new form_asignar_tarea();

function get_id_actividad($nombre_actividad)
{
    global $DB;

    $records_assign = $DB->get_records('assign');
    foreach ($records_assign as $record)
    {
        if($record->name === $nombre_actividad)
        {
            $sal1 = $record->id;
        }
    }

    return $sal1;
}

function get_tareas($curso_id)
{
    global $DB;

    // 1. Obtener lista de tareas de la BBDD.
    $nombres = $DB->get_records('assign', ['course' => $curso_id]);

    $tareas = array();
    $i = 0;
    // 2. Meterlas en un array y devolverlas.
    foreach($nombres as $nom)
    {
        $tareas[$i++] = $nom->name;
    }

    return $tareas;
}
function asignar_tarea ($id_objetivo, $id_tarea, $nombre_tarea)
{
    global $DB;

    $tarea_n = new stdClass();
    $tarea_n->id = mt_rand();
    $tarea_n->id_tarea = $id_tarea;
    $tarea_n->id_objetivo = $id_objetivo;
    $tarea_n->nombre = $nombre_tarea;
    $tarea_n->peso = 1;
    $DB->insert_record( tarea, $tarea_n );
}

function get_lista_objetivos()
{
    global $DB;

    $objetivos = $DB->get_records('objetivo');
    $sal1 = array();
    $i = 0;
    foreach ($objetivos as $obj)
    {
        $sal1[$i++] = $obj->nombre;
    }

    return $sal1;
}

function get_id_objetivo($curso_id, $nombre_obj)
{
    global $DB, $COURSE;

    $objetivos = $DB->get_records('objetivo', ['id_course' => $curso_id]);

    $sal1 = 0;
    foreach ($objetivos as $obj)
    {
        if($obj->nombre === $nombre_obj){
            $sal1 = $obj->id;
        }
    }

    return $sal1;
}

function function_alert($msg) {
    echo "<script type='text/javascript'>alert('$msg');</script>";
}
function esta_asignada_tarea($id_tarea)
{
    global $DB;
    return $DB->record_exists('tarea', ['id_tarea' => $id_tarea]);
}

if ($form->is_cancelled()) {

    redirect(new moodle_url('/my/'));
} else if ($data = $form->get_data()) {
    $pos_objetivo = $data->objetivo; // Posicion en el desplegable del formulario.
    $pos_tarea = $data->tarea; // Posicion en el desplegable del formulario.
    $id_curso = $data->id_curso; // Variable pasada por campo oculto.

    // Obtener id de un objetivo en base a la seleccion de la lista en el formulario.
    $lista_objetivos = get_lista_objetivos(); // Obtengo la lista de todos los objetivos.
    $nombre_objetivo = $lista_objetivos[$pos_objetivo]; // Filtro el objetivo seleccionado de la lista de objetivos
    $id_obj = get_id_objetivo($id_curso, $nombre_objetivo); // Id real del objetivo seleccionado.

    // Obtener el id de una tarea (actividad) en base a la seleccion de la lista en el formulario.
    $lista_tareas = get_tareas($id_curso);
    $nombre_tarea = $lista_tareas[$pos_tarea];
    $id_act = get_id_actividad($nombre_tarea);

    // Comprobar que la tarea no esta ya asignada.
    if(esta_asignada_tarea($id_act)) {
     //TODO Notificacion para informar que ya se ha asignado una tarea

    }else
        asignar_tarea($id_obj,$id_act,$nombre_tarea);
    //redirect(new moodle_url('/my/'));
} else {
    // Display the form.
    echo $OUTPUT->header();
    $form->display();
    echo $OUTPUT->footer();
}