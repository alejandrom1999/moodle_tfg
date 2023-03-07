<?php


class block_objetivos extends block_base {

    public function init() {
        global $DB;

        $this->title = get_string('objetivos', 'block_objetivos');
    }
    function hide_header() {
        return true;
    }

    public function get_content() {
        global $OUTPUT, $USER, $COURSE;
        $data = [];


        // Rutas a los archivos.
        $templatename1 = 'block_objetivos/barra_progreso';


        if ($this->content !== null) {
            return $this->content;
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
        function porcentaje_curso_usuario($usuario_id)
        {
            global $DB, $COURSE;
            $sql1 = "SELECT b_o.nombre
                     FROM {objetivo} b_o
                     WHERE b_o.id_course = $COURSE->id";

            $nombres = $DB->get_records_sql($sql1); // Obtenemos los nombes de los objetivos

            $num_objetivos = 0;
            $porcentaje_total = 0;
            // Contamos el numero de objetivos que hay y el porcentaje de cada objetivo de ese usuario.


            foreach ($nombres as $n)
            {
                $num_objetivos++;
                $porcentaje_total += porcentaje_objetivo_usuario($n->nombre,$usuario_id);
            }

            if($num_objetivos == 0)
            {
                return 0;
            }

            return round($porcentaje_total/$num_objetivos,2);;
        }

        /* Numero de tareas de un objetivo */
        function numero_tareas($objetivo_id)
        {
            global $DB;
            // Comprobamos y sumamos las tareas totales de ese objetivo tanto en la tabla de actividades como en la de quizes.
            $numero = $DB->count_records_sql("
                    SELECT COUNT(*) 
                    FROM {tarea} t 
                    WHERE t.id_objetivo = $objetivo_id");

            $numero += $DB->count_records_sql("
                    SELECT COUNT(*) 
                    FROM {quiz_asignados} q 
                    WHERE q.id_objetivo = $objetivo_id");

            return $numero;
         }

        /*Lista de objetivos del curso*/
        function lista_objetivos()
        {
            global $DB, $COURSE, $USER;
            $sql1 = "SELECT b_o.nombre
                 FROM {objetivo} b_o
                 WHERE b_o.id_course = $COURSE->id";

            $nombres = $DB->get_records_sql($sql1);

            $objetivos = array();
            $i = 0;

            foreach($nombres as $nom) {
                $objetivo = array();
                $objetivo['nombre'] = $nom->nombre;
                $objetivo['progreso_objetivo'] = porcentaje_objetivo_usuario($nom->nombre, $USER->id);
                $objetivos[$i++] = $objetivo;
            }


            return $objetivos;
        }

        /*Obtener el id de un objetivo a partir de su nombre*/
        function get_id_objetivo($nombre_obj): string {
            global $DB, $COURSE;
            $sql = " SELECT *
                     FROM {objetivo} o 
                     WHERE o.id_course = $COURSE->id";
            $sql_1 = $DB->get_records_sql($sql);

            $sal1 = '';
            foreach ($sql_1 as $n)
            {
                if($n->nombre === $nombre_obj){
                    $sal1 = $n->id;
                }
            }

            return $sal1;
        }

        /*TAREAS DE UN OBJETIVO EN CONCRETO*/
        function tarea_objetivo_n($nombre_objetivo)
        {
            global $DB;
            // 0. Obtenemos el id del objetivo.
            $id = get_id_objetivo($nombre_objetivo);

            // 1. Incluimos las tareas que sean actividades
            $tareas_actividades = $DB->get_records('tarea', array('id_objetivo' => $id));

            $arr_tarea = array();
            $i = 0;

            foreach($tareas_actividades as $nom) {
                $arr_tarea[$i++] = $nom->nombre;
            }

            // 2. Incluimos a la lista las tareas que sean quizes.
            $tareas_quizes = $DB->get_records('quiz_asignados', array('id_objetivo' => $id));

            foreach($tareas_quizes as $tar) {
                $arr_tarea[$i++] = $tar->nombre;
            }

            return $arr_tarea;

        }

        /*FUNCION QUE DEVUELVE LA ID DE UNA ACTIVIDAD EN BASE A SU NOMBRE*/
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

        // Comprueba si el usuario actual es estudiante.
        function user_is_student()
        {
            global $DB, $USER;

            $records_assig  = $DB->get_records('role_assignments');
            foreach ($records_assig as $record)
            {
                // Comprobamos que el usuario coincide con el actual.
                if($record->userid === $USER->id)
                {
                    // Comprobamos que tiene el rol 5, que es el de ser estudiante.
                    if($record->roleid == 5)
                    {
                        return true;
                    }
                }
            }
            return false;
        }
        function get_status_tarea_usuario($user_id, $assignment_name)
        {
            global $DB;

            $id_tar = get_id_actividad($assignment_name);



            if($DB->record_exists('quiz', array('id' => $id_tar)))
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

        $objetivos = lista_objetivos();

        $es_estudiante = user_is_student();

        if ($es_estudiante) {
            // Vista del estudiante.
            $data['vista_profe'] = false;
            $data['vista_estudiante'] = true;
            $data['objetivos'] = $objetivos;
            $data['progreso_curso'] = porcentaje_curso_usuario($USER->id);
        } else {
            // Vista del Profesor.
            $data['vista_profe'] = true;
            $data['vista_estudiante'] = false;
            $data['url_listado'] = '/blocks/objetivos/vista_profesor.php?id_curso='. $COURSE->id;
            $data['formulario1'] = '/blocks/objetivos/form_objetivo.php?id_curso='. $COURSE->id;
            $data['formulario2'] = '/blocks/objetivos/form_asignar_tarea.php?id_curso='. $COURSE->id;
        }



        $this->content         =  new stdClass;
        $this->content->text   =  $OUTPUT->render_from_template($templatename1, $data);
        $this->content->footer = '';

        return $this->content;
    }


}


