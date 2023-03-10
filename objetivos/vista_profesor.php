<?php

require_once(__DIR__ . '/../../config.php');
$PAGE->set_url(new moodle_url('/blocks/objetivos/vista_profesor.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Vista resumen objetivos del curso');
$PAGE->set_heading('Progreso alumnos');

// FUNCIONES
function get_user_data_estudiantes() {
    global $DB;

    // Usuarios que sean estudiantes.z
    $id_curso = optional_param('id_curso',' ', PARAM_TEXT);

    // Obtenemos solo los estudiantes matriculados en el curso actual.
    $sql = "SELECT u.id, u.firstname
            FROM mdl_user u
            INNER JOIN mdl_user_enrolments ue ON ue.userid = u.id
            INNER JOIN mdl_enrol e ON e.id = ue.enrolid
            INNER JOIN mdl_role_assignments r_a ON r_a.userid = ue.userid && r_a.roleid = 5
            WHERE e.courseid = $id_curso";

    return $DB->get_records_sql($sql);
}
function numero_tareas($objetivo_id)
{
    global $DB;
    $numero = 0;
    // Comprobamos y sumamos las tareas totales de ese objetivo tanto en la tabla de actividades como en la de quizes.
    $numero += $DB->count_records_sql("
                    SELECT COUNT(*) 
                    FROM {tarea} t 
                    WHERE t.id_objetivo = $objetivo_id");

    $numero += $DB->count_records_sql("
                    SELECT COUNT(*) 
                    FROM {quiz_asignados} q 
                    WHERE q.id_objetivo = $objetivo_id");


    return $numero;
}
function get_id_objetivo($nombre_obj){
    global $DB;

    $id_curso = optional_param('id_curso',' ', PARAM_TEXT);
    $sql_1 = $DB->get_records('objetivo', array('id_course' => $id_curso));

    $sal1 = '';
    foreach ($sql_1 as $n)
    {
        if(strcmp($n->nombre,$nombre_obj) == 0) {
            $sal1 = $n->id;
        }
    }

    return $sal1;
}
function get_id_actividad($nombre_actividad)
{
    global $DB;

    // Comprobar si la actividad es un quiz o un assign:
    if($DB->record_exists('quiz', array('name' => $nombre_actividad)))
    {
        $records = $DB->get_records('quiz');
    } else {
        $records = $DB->get_records('assign');
    }

    foreach ($records as $record)
    {
        if(strcmp($record->name,$nombre_actividad) == 0)
        {
            $sal1 = $record->id;
        }
    }

    return $sal1;
}
function get_status_tarea_usuario($user_id, $assignment_name)
{
    global $DB;
    $id_tar = get_id_actividad($assignment_name);

    // 1. Miramos que tipo de tarea es: Quiz o Actividad.
    if($DB->record_exists('quiz', array('name' => $assignment_name)))
    {
        $sql = "SELECT * 
                    FROM {quiz_grades} as_s
                    WHERE as_s.userid = $user_id 
                    AND
                    as_s.quiz = $id_tar
                    AND
                    as_s.grade >= 50 ";

    } else {
        $sql = "SELECT * 
                FROM {assign_grades} as_s
                WHERE as_s.userid = $user_id 
                AND
                as_s.assignment = $id_tar
                AND
                as_s.grade >= 50 ";
    }

    return $DB->record_exists_sql($sql);
}
function porcentaje_objetivo_usuario($objetivo_nombre, $usuario_id)
{

    global $DB;
    $pesos_tareas_objetivo = 0;
    $pesos_tarea_completada = 0;


    $id = get_id_objetivo($objetivo_nombre);
    $tareas_actividades = $DB->get_records('tarea', array('id_objetivo' => $id));
    $tareas_quizes = $DB->get_records('quiz_asignados', array('id_objetivo' => $id));
    $numero_tareas_objetivo = numero_tareas($id); // numero de tareas del objetivo

    if($numero_tareas_objetivo == 0)
    {
        return 0;
    }
    // Parte de actividades.
    foreach($tareas_actividades as $nom) {

        $pesos_tareas_objetivo +=  $nom->peso;

        if(get_status_tarea_usuario($usuario_id, $nom->nombre) == 1)
        {
            $pesos_tarea_completada += $nom->peso;

        }
    }

    foreach($tareas_quizes as $tar) {
        $pesos_tareas_objetivo += $nom->peso;
        if(get_status_tarea_usuario($usuario_id, $tar->nombre) == 1 )
        {
            $pesos_tarea_completada += $nom->peso;
        }
    }


    return round(($pesos_tarea_completada / $pesos_tareas_objetivo ) * 100,2);
}
function nombres_objetivos()
{
    global $DB;
    $id_curso = optional_param('id_curso',' ', PARAM_TEXT);
    $nombres_objetivos = $DB->get_records('objetivo', array('id_course' => $id_curso));

    $i = 0;
    $objetivos = array();
    foreach ($nombres_objetivos as $nombre_objetivo_n) {
        $nombres = array();
        $nombres['nombre_objetivo'] =  $nombre_objetivo_n->nombre;
        $objetivos[$i++] = $nombres;
    }
    return $objetivos;
}
function progreso_objetivos()
{
    global $DB;
    $id_curso = optional_param('id_curso',' ', PARAM_TEXT);

    $nombres_objetivos = $DB->get_records('objetivo', array('id_course' => $id_curso));
    $datos_estudiantes = get_user_data_estudiantes();


    $objetivos2 = array();
    $i = 0;
    foreach ($datos_estudiantes as $valor_i)
    {
        $j = 0;
        $objetivos1 = array();
        $objetivos1['nombre_estudiante'] = $valor_i->firstname;

        foreach ($nombres_objetivos as $nombre_objetivo_n) {
            $progreso = array();
            $progreso['progreso_user'] =  porcentaje_objetivo_usuario($nombre_objetivo_n->nombre,$valor_i->id);
            $objetivos1['progreso'][$j++] = $progreso;
        }

        $objetivos2[$i++] = $objetivos1;
    }



    return $objetivos2 ;
}

echo $OUTPUT->header();

$templatename = 'block_objetivos/vista_profesor';
$datos = [];


$nombres_objetivos = nombres_objetivos();

$progreso = progreso_objetivos();


$datos['nombres_objetivos'] =  $nombres_objetivos;
$datos['progreso'] = $progreso;

echo $OUTPUT->render_from_template($templatename, $datos);

echo $OUTPUT->footer();
