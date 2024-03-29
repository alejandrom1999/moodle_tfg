<?php

require_once("$CFG->libdir/formslib.php");
class form_objetivo extends moodleform {

    function get_nombre_curso($id_curso)
    {
        global $DB;
        $records_course = $DB->get_records('course');
        foreach ($records_course as $c )
        {
            if($c->id === $id_curso)
            {
                $name = $c->fullname;
            }
        }

        return $name;

    }

    public function definition() {

        $mform = $this->_form;
        $id_curso = optional_param('id_curso',' ', PARAM_TEXT);

        // Cabecera
        $mform->addElement('header', 'general', 'Creacion de un objetivo');

        // Texto objetivo
        $mform->addElement('text', 'nombre', 'Introduce el nombre del objetivo');
        $mform->addRule('nombre', null, 'required', null, 'client');

        // Campo Cursos
        $mform->addElement('select', 'curso', 'Curso para asignar el objetivo', array($this->get_nombre_curso($id_curso)));
        $mform->setDefault('curso', '0');

        $mform->addElement('hidden', 'id_curso', 'campo oculto ');
        $mform->setDefault('id_curso', $id_curso);

        $this->add_action_buttons($cancel = true, 'Insertar objetivo');
    }

    public function validation($data, $files)
    {
        return array();
    }
}