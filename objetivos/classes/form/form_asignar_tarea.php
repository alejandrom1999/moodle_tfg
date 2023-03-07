<?php

require_once("$CFG->libdir/formslib.php");
class form_asignar_tarea extends moodleform {
    function get_tareas($curso_id)
    {
        global $DB;

        // 1. Obtener lista de tareas de la BBDD.
        $nombres = $DB->get_records('assign', ['course' => $curso_id]);
        $quizes = $DB->get_records('quiz', ['course' => $curso_id]);

        $tareas = array();
        $i = 0;
        // 2. Meterlas en un array y devolverlas.
        foreach($nombres as $nom)
        {
            $tareas[$i++] = $nom->name;
        }

        foreach($quizes as $q)
        {
            $tareas[$i++] = $q->name;
        }

        return $tareas;
    }
    function get_objetivos_curso($curso_id)
    {
        global $DB;

        $objetivos = $DB->get_records('objetivo', ['id_course' =>  $curso_id]);
        $nombres = array();
        $i = 0;
        foreach ($objetivos as $obj)
        {
            $nombres[$i++] = $obj->nombre;
        }
        return $nombres;
    }

    public function definition() {
        $mform = $this->_form;
        $id_curso = optional_param('id_curso',' ', PARAM_TEXT);

        $objetivos_curso = $this->get_objetivos_curso($id_curso);

        $tareas_curso = $this->get_tareas($id_curso);

        // Si no hay objetivos en el curso...
        if(empty($objetivos_curso)){
            // Si no hay tareas...
            if(!empty($tareas_curso)){
                unset($tareas_curso);
                $tareas_curso[0] = "No se puede asignar";
            }
            $objetivos_curso[0] = "No hay objetivos";
        }

        // Si no hay tareas...
        if(empty($tareas_curso)){
            $tareas_curso[0] = "No hay tareas";
            // Si hay objetivos...
            if(!empty($objetivos_curso)){
                unset($objetivos_curso);
                $objetivos_curso[0] = "No se puede asignar";
            }
        }

        // Cabecera
        $mform->addElement('header', 'general', 'Asignacion de una tarea a un objetivo');

        // Texto objetivo
        $mform->addElement('select', 'objetivo', 'Selecciona objetivo: ', $objetivos_curso);
        $mform->setDefault('objetivo', '0');

        // Campo Cursos
        $mform->addElement('select', 'tarea', 'Selecciona tarea: ', $tareas_curso);
        $mform->setDefault('tarea', '0');

        // Texto objetivo
        $mform->addElement('text', 'peso', 'Introduce el peso del objetivo');
        $mform->setDefault('peso', 'Ejemplo: 20,30 (porcentajes) ');


        $mform->addElement('hidden', 'id_curso', 'campo oculto');
        $mform->setDefault('id_curso', $id_curso);


        if(!in_array('No', $objetivos_curso) && !in_array('No', $tareas_curso))
            $this->add_action_buttons($cancel = true, 'Asignar tarea a objetivo');
    }


    public function validation($data, $files) {
        return array();
    }

}
