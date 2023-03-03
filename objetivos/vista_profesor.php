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
    $numero = $DB->count_records_sql("
                    SELECT COUNT(*) 
                    FROM {tarea} t 
                    WHERE t.id_objetivo = $objetivo_id");

    return $numero;
}
function get_id_objetivo($nombre_obj): string {
    global $DB;

    $id_curso = optional_param('id_curso',' ', PARAM_TEXT);
    $sql_1 = $DB->get_records('objetivo', array('id_course' => $id_curso));

    $sal1 = '';
    foreach ($sql_1 as $n)
    {
        if($n->nombre === $nombre_obj){
            $sal1 = $n->id;
        }
    }

    return $sal1;
}
function tarea_objetivo_n($nombre_objetivo)
{
    global $DB;

    $id = get_id_objetivo($nombre_objetivo);

    $tareas = $DB->get_records('tarea', array('id_objetivo' => $id));

    $arr_tarea = array();
    $i = 0;

    foreach($tareas as $nom) {
        $arr_tarea[$i++] = $nom->nombre;
    }

    return $arr_tarea;

}
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
function get_status_tarea_usuario($user, $assignment_name)
{
    global $DB;

    $id_tar = get_id_actividad($assignment_name);

    $sql = "SELECT * 
                    FROM {assign_grades} as_s
                    WHERE as_s.userid = $user 
                    AND
                    as_s.assignment = $id_tar
                    AND
                    as_s.grade >= 50 ";

    return $DB->record_exists_sql($sql);;
}

function get_id_estudiante($nombre_est): string {
    global $DB;

    $sql_1 = $DB->get_records('user', array('firstname' => $nombre_est));

    $sal1 = '';
    foreach ($sql_1 as $n)
    {
        if($n->firstname === $nombre_est){
            $sal1 = $n->id;
        }
    }

    return $sal1;
}
function porcentaje_objetivo_usuario($objetivo_nombre, $usuario_id)
{

    $tareas_hechas = 0;
    $id_objetivo = get_id_objetivo($objetivo_nombre); // id del objetivo
    $numero_tareas_objetivo = numero_tareas($id_objetivo); // numero de tareas del objetivo

    if($numero_tareas_objetivo == 0)
    {
        return 0;
    }

    $tareas = tarea_objetivo_n($objetivo_nombre); // tareas del objetivo

    foreach ($tareas as $t)
    {
        if(get_status_tarea_usuario($usuario_id, $t) == 1)
        {
            $tareas_hechas++;
        }
    }

    return round(($tareas_hechas/$numero_tareas_objetivo) * 100,2);
}
function informacion_tabla()
{
    global $DB;
    // Parametro de la URL
    $id_curso = optional_param('id_curso',' ', PARAM_TEXT);

    // Consulta para obtener los nombres de los objetivos
    $nombres_objetivos = $DB->get_records('objetivo', array('id_course' => $id_curso));

    // Obtengo los nombres de los estudiantes
    $nombres_estudiantes = get_names_estudiantes();

    // Obtengo los ids de los estudiantes
    $ids_estudiantes = get_user_ids_estudiantes();

    $objetivos = array();
    $i = 0;
    foreach($nombres_estudiantes as $nom) {

        $objetivo = array();
        $objetivo['nombre_estudiante'] = $nom;
        $id_est = get_id_estudiante($nom);

        // Por cada estudiante, obtener su porcentaje de cada objetivo
        $j = 0;
        foreach ($nombres_objetivos as $nombre_objetivo_n) {
            $datos_estudiante = array();
            $progress = porcentaje_objetivo_usuario($nombre_objetivo_n->nombre, $id_est);

            $datos_estudiante['nombre_objetivo'] = $nombre_objetivo_n->nombre;
            $datos_estudiante['porcentaje'] = $progress;
            $objetivo[$i][$j++] = $datos_estudiante;
        }

        $objetivos[$i++] = $objetivo;
    }


    return $objetivos;
}

echo $OUTPUT->header();



$templatename = 'block_objetivos/vista_profesor';
$datos = [];


$info = informacion_tabla();


$datos['objetivos'] =  $info;

echo $OUTPUT->render_from_template($templatename, $datos);

echo $OUTPUT->footer();
