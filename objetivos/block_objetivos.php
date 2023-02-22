<?php

require_once("$CFG->libdir/formslib.php");

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


        function lista_objetivos()
        {
            global $DB, $COURSE;
            $sql1 = "SELECT b_o.nombre
                 FROM {objetivo} b_o
                 WHERE b_o.id_course = $COURSE->id";

            $nombres = $DB->get_records_sql($sql1);

            $objetivos = array();
            $i = 0;

            foreach($nombres as $nom) {
                $objetivo = array();
                $objetivo['nombre'] = $nom->nombre;
                $objetivo['progreso'] = $i*10;

                $objetivos[$i++] = $objetivo;
            }

            return $objetivos;
        }

        function crear_objetivo($nombre) : void
        {
            global $COURSE, $DB;
            $objetivo_n = new stdClass();
            $objetivo_n->id = mt_rand();
            $objetivo_n->id_course = $COURSE->id;
            $objetivo_n->nombre = $nombre;
            $DB->insert_record(objetivo , $objetivo_n );
        }

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

        function tarea_objetivo_n($name) : string
        {

           global $DB;

           $id = get_id_objetivo($name);

           $sql2 = "SELECT t.nombre
                 FROM {tarea} t
                 WHERE t.id_objetivo = $id";
            $sal2 = '';
            $sal2_2 = $DB->get_records_sql($sql2);
            foreach ($sal2_2 as $obj_n)
            {
                $sal2 .= $obj_n->nombre . ',';
            }

            return $sal2;

        }

        function get_id_actividad($name) : int
        {
            global $DB;
            $sql_2 = "SELECT *
                      FROM {assign} a
                      WHERE a.name = $name";

            $assign_id = '';
            $sal2_2 = $DB->get_records_sql($sql_2);
            foreach ($sal2_2 as $obj_n)
            {
                if($obj_n->name === $name){
                    $assign_id = $obj_n->id;
                }
            }
            return $assign_id;
        }

        /*Se devuelve true o false segun se ha completado o no esa tarea ( si se ha entregado ). */
        function get_status_tarea_usuario($user, $assignment_name): string
        {
            global $DB;

            $id_tar = get_id_actividad($assignment_name);

            $sql = "SELECT * 
                    FROM {assign_submission} as_s
                    WHERE as_s.userid = $user 
                    AND
                    as_s.assignment = $id_tar";



            $exist = $DB->record_exists_sql($sql);


            return $exist;
        }

        $objetivos = lista_objetivos();


        $nombres_tareas = tarea_objetivo_n('Superar a todos mis compañeros');



        $data['objetivos'] = $objetivos;






        $this->content->text .= $OUTPUT->render_from_template($templatename, $data);
        return $this->content;
    }
}

