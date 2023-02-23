<?php


class block_objetivos extends block_base {

    public function init() {
        global $DB,$COURSE;

        $this->title = get_string('objetivos', 'block_objetivos');

        // CUIDADO QUE ESTA ESTO PARA BORRAR LOS OBJETIVOS!!

    }
    function hide_header() {
        return true;
    }

    public function get_content() {
        global $OUTPUT, $DB, $COURSE, $USER;
        $data = [];
        $templatename = 'block_objetivos/barra_progreso';

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         =  new stdClass;
        $this->content->text   = '';

        function porcentaje_objetivo_usuario($objetivo_nombre, $usuario_id)
        {

            $tareas_hechas = 0;
            $id_objetivo = get_id_objetivo($objetivo_nombre); // id del objetivo
            $numero_tareas_objetivo = numero_tareas($id_objetivo); // numero de tareas del objetivo
            $tareas = tarea_objetivo_n($objetivo_nombre); // tareas del objetivo

            foreach ($tareas as $t)
            {
                if(get_status_tarea_usuario($usuario_id, $t) == 1)
                {
                    $tareas_hechas++;
                }

            }

            return round(($tareas_hechas/$numero_tareas_objetivo) * 100,2);;
        }

        function porcentaje_curso_usuario($usuario_id)
        {
            global $DB;

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


            return round($porcentaje_total/$num_objetivos,2);;
        }


        /* Numero de tareas de un objetivo */
        function numero_tareas($objetivo_id)
        {
            global $DB;
            $numero = $DB->count_records_sql("
                    SELECT COUNT(*) 
                    FROM {tarea} t 
                    WHERE t.id_objetivo = $objetivo_id");

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
                $objetivo['progreso_objetivo'] = porcentaje_objetivo_usuario($nom->nombre, 3);
                $objetivos[$i++] = $objetivo;
            }


            return $objetivos;
        }
        /*Crear un objetivo a partir de un nombre*/
        function crear_objetivo($nombre) : void
        {
            global $COURSE, $DB;
            $objetivo_n = new stdClass();
            $objetivo_n->id = mt_rand();
            $objetivo_n->id_course = $COURSE->id;
            $objetivo_n->nombre = $nombre;
            $DB->insert_record(objetivo , $objetivo_n );
        }

        /*Crear una tarea a partir de una id_objetivo, id_tarea y nombre de la tarea*/
        function crear_tarea($id_objetivo, $id_tarea, $nombre) : void
        {
            global $COURSE, $DB;
            $tarea_n = new stdClass();
            $tarea_n->id = mt_rand();
            $tarea_n->id_tarea = $id_tarea;
            $tarea_n->id_objetivo = $$id_objetivo;
            $tarea_n->nombre = $nombre;
            $DB->insert_record(tarea, $tarea_n);

        }
        /*Obtener el id de un objetivo a partir de su nombre*/
        function get_id_objetivo($nombre_obj): string {
            global $DB, $COURSE;
            $sql = " SELECT *
                     FROM {objetivo} o WHERE o.id_course = $COURSE->id";
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

           $id = get_id_objetivo($nombre_objetivo);

           $sql2 = "SELECT t.nombre
                 FROM {tarea} t
                 WHERE t.id_objetivo = $id";

            $tareas = $DB->get_records_sql($sql2);

            $arr_tarea = array();
            $i = 0;

            foreach($tareas as $nom) {
                $arr_tarea[$i++] = $nom->nombre;
            }

            return $arr_tarea;

        }
        /*FUNCION QUE DEVUELVE LA ID DE UNA ACTIVIDAD EN BASE A SU NOMBRE*/
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

        /*  FUNCION QUE DEVUELVE LA COMPROBACION ( 0 = FALSE, 1 = TRUE) SI SE HA ENTREGADO UNA
            TAREA POR UN USUARIO Y HA SIDO APROBADA
            A PARTIR DEL NOMBRE DE ESEA TAREA.

            $user = user_id.
            $assignment_name = nombre de la tarea.

        */
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

        $objetivos = lista_objetivos();

        $data['objetivos'] = $objetivos;
        $data['progreso_curso'] = porcentaje_curso_usuario(3);

        $this->content->text .= $OUTPUT->render_from_template($templatename, $data);
        return $this->content;
    }
}

