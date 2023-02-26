<?php

require_once("$CFG->libdir/formslib.php");
class form_objetivo extends moodleform {

    public function definition() {
        $mform = $this->_form;

        // Hay que  mostrar opciones adicionales en
        // un menú desplegable según la selección de un usuario en otro campo del formulario.
        // Para ello hay que utilizar AJAX.

        $mform->addElement('header', 'general', 'Creacion de un objetivo');

        $mform->addElement('text', 'nombre', 'Introduce el nombre del objetivo');
        $mform->setType('nombre', PARAM_TEXT);

        $cursos = array("Curso 1", "Curso 2");

        $mform->addElement('select', 'curso', 'Selecciona curso para asignar el objetivo', $cursos);
        $mform->setDefault('curso', '3');


        $tareas = array("Tarea 1", "Tarea 2");

        $mform->addElement('select', 'tarea', 'Selecciona tarea para asignar el objetivo', $tareas);
        $mform->setDefault('tarea', '0');

        //$this->page->requires->js_call_amd('core/moodle-forms', 'init_form_autosubmit', array($this->_form->getAttribute('id')));

        $this->add_action_buttons($cancel = true, 'Insertar objetivo');
    }

    public function ajax_my_form_callback($data) {
        // Procesa los datos enviados en la solicitud AJAX y devuelve una respuesta
        return array('status' => 'ok', 'message' => 'Datos recibidos');
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // add custom validation rules here

        return $errors;
    }
}

//require_js('/blocks/objetivos/my_form.js');
