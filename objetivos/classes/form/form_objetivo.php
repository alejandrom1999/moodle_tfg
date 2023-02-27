<?php

require_once("$CFG->libdir/formslib.php");
class form_objetivo extends moodleform {

    public function definition() {
        $mform = $this->_form;


        // Cabecera
        $mform->addElement('header', 'general', 'Creacion de un objetivo');

        // Texto objetivo
        $mform->addElement('text', 'nombre', 'Introduce el nombre del objetivo');
        $mform->addRule('nombre', null, 'required', null, 'client');

        // Campo Cursos
        $mform->addElement('select', 'curso', 'Selecciona curso para asignar el objetivo', get_nombres_curso());
        $mform->setDefault('curso', '0');

        $this->add_action_buttons($cancel = true, 'Insertar objetivo');
    }

    public function validation($data, $files) {

        return array();
    }

}


