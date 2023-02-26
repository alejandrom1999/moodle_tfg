<?php

require_once("$CFG->libdir/formslib.php");

class form_tarea extends moodleform {



    //Add elements to form
    public function definition()
    {

        $mform = $this->_form; // Don't forget the underscore!


        function get_actividades($id_curso): array
        {
            global $DB;
            $sql2 = "SELECT a.name
                 FROM {assign} a
                 WHERE a.course = $id_curso";

            $tareas = $DB->get_records_sql($sql2);
            $nombres = array();
            $i = 0;

            foreach ($tareas as $t)
            {
                $nombres[$i++] = $t->name;
            }
            return $nombres;
        }
        $id = optional_param('id_curso', 'No hay valor', PARAM_TEXT);
        $nombre_objetivo = optional_param('nombre_objetivo', 'No hay valor', PARAM_TEXT);

        $tareas = get_actividades($id);

        $mform->addElement('select', 'tarea', 'Selecciona tarea para asignar el objetivo', $tareas);
        $mform->setDefault('tarea', '0');

        $this->add_action_buttons($cancel = true, $submitlabel = 'Finalizar');
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }

}