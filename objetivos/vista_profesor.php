<?php

require_once(__DIR__ . '/../../config.php');
$PAGE->set_url(new moodle_url('/blocks/objetivos/vista_profesor.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Vista resumen objetivos del curso');
$PAGE->set_heading('Progreso alumnos');

// FUNCIONES
function get_user_ids_estudiantes() {
    global $DB;

    // Usuarios que sean estudiantes.
    $users = $DB->get_records('role_assignments', array('roleid' => 5));

    $arr_users = array();
    $i = 0;

    foreach ($users as $us)
    {
        if(!in_array($us->userid, $arr_users))
            $arr_users[$i++] = $us->userid;
    }

    return $arr_users;
}
function get_names_estudiantes()
{
    global $DB;
    // 1. Obtenemos todos los usuarios.
    $tabla_user = $DB->get_records('user');

    $user_ids = array();
    $i = 0;
    foreach ($tabla_user as $tabla_n)
    {
        // 2. Guardamos todos sus ids.
        $user_ids[$i++] = $tabla_n->id;
    }

    // 3. Obtengo los ids de los estudiantes.
    $ids_estudiantes = get_user_ids_estudiantes();


    $i = 0;
    $nombres = array();
    // 4. Obtenemos los nombres
    foreach ($tabla_user as $tabla_n)
    {
        if($tabla_n->id == $ids_estudiantes[$i])
        {
            $nombres[$i++] = $tabla_n->firstname;
        }
    }

    return $nombres;
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
function get_id_objetivo($nombre_obj): string {
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
    $pesos_suma = 0;
    $tarea_compleja = 1;

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
        if(get_status_tarea_usuario($usuario_id, $nom->nombre) == 1 )
        {
            $pesos_suma += $nom->peso;
            if($nom->peso > 1)
            {
                $tarea_compleja *= $nom->peso;
            }
        }

    }
    foreach($tareas_quizes as $tar) {

        if(get_status_tarea_usuario($usuario_id, $tar->nombre) == 1 )
        {
            $pesos_suma += $tar->peso;
            if($tar->peso > 1)
            {
                $tarea_compleja *= $tar->peso;
            }
        }
    }

    return round(($pesos_suma / ($numero_tareas_objetivo * $tarea_compleja )) * 100,2);
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
    $ids_estudiantes = get_user_ids_estudiantes();
    $nombres_estudiantes = get_names_estudiantes();

    $objetivos2 = array();
    $i = 0;
    foreach ($ids_estudiantes as $id_est)
    {
        $j = 0;
        $objetivos1 = array();
        $objetivos1['nombre_estudiante'] = $nombres_estudiantes[$i];
        foreach ($nombres_objetivos as $nombre_objetivo_n) {
            $progreso = array();

            $progreso['progreso_user'] =  porcentaje_objetivo_usuario($nombre_objetivo_n->nombre,$id_est);
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
