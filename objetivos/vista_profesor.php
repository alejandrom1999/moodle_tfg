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
    $sql = " SELECT *
             FROM {role_assignments} o 
             WHERE o.roleid = 5";
    $users = $DB->get_records_sql($sql);
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
            $nombre = array();
            $nombre['nombre'] = $tabla_n->firstname . ' ' . $tabla_n->lastname;
            $nombres[$i++] = $nombre;
        }
    }

    return $nombres;
}

echo $OUTPUT->header();

$nombres = get_names_estudiantes();


$templatename = 'block_objetivos/vista_profesor';
$datos = [];
$datos['estudiantes'] = $nombres;
echo $OUTPUT->render_from_template($templatename, $datos);

echo $OUTPUT->footer();
