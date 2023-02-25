<?php

require_once("$CFG->libdir/formslib.php");

class form_objetivo extends moodleform {

    //Add elements to form
    public function definition()
    {
        global $CFG;

        $mform = $this->_form; // Don't forget the underscore!

        function get_cursos(){
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
        $curso_actual = optional_param('curso_actual', 'No hay valor', PARAM_TEXT);
        $cursos = array();
        $cursos[0] = $curso_actual;

        $mform->addElement('header', 'header', 'Nuevo objetivo');
        $mform->addElement('text', 'nombre', 'Nombre del objetivo');
        $mform->setType('nombre', PARAM_NOTAGS);


        $mform->addElement('select', 'curso', 'Curso', $cursos);
        $mform->setDefault('curso', '0');


        $this->add_action_buttons($cancel = true, $submitlabel = 'Insertar objetivo');
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }

}