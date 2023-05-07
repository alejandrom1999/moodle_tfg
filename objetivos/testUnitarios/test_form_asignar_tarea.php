<?php declare(strict_types=1);
define('CLI_SCRIPT', true);
use PHPUnit\Framework\TestCase;

require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/blocks/objetivos/form_asignar_tarea.php');

class test_form_asignar_tarea extends TestCase
{
    public function test_check_if_quiz()
    {
        $this->assertTrue(check_if_quiz('Quiz Numero 3'));
        $this->assertTrue(check_if_quiz('Quiz seguridad u2'));
        $this->assertTrue(check_if_quiz('Quiz final examen'));
    }

    public function test_get_id_actividad()
    {
        $this->assertEquals(12, get_id_actividad('Actividad Moviles 1'));
        $this->assertEquals(14, get_id_actividad('Antenas parabolicas'));
        $this->assertEquals(13, get_id_actividad('Actividad Moviles 2'));
        $this->assertEquals(3, get_id_actividad('Quiz Numero 3'));
        $this->assertEquals(2, get_id_actividad('Quiz seguridad u2'));
        $this->assertEquals(1, get_id_actividad('Quiz final examen'));
    }

    public function test_get_tareas()
    {
 
        $this->assertEquals(['Quiz final examen'], get_tareas(2));
        $this->assertEquals(['Quiz seguridad u2'], get_tareas(3));
        $this->assertEqualsCanonicalizing(['Quiz Numero 3', 'Actividad Moviles 1',
         'Actividad Moviles 2', 'Antenas parabolicas'], get_tareas(4));
    }

    public function test_asignar_tarea()
    {
        global $DB;

        $id_objetivo = 1;
        $id_tarea = 2;
        $nombre_tarea = 'Tarea 1';
        $peso = 50;

        asignar_tarea($id_objetivo, $id_tarea, $nombre_tarea, $peso);
        
        $tarea = $DB->get_record('tarea', array('id_tarea' => $id_tarea, 'id_objetivo' => $id_objetivo));

        $this->assertEquals($id_tarea, $tarea->id_tarea);
        $this->assertEquals($id_objetivo, $tarea->id_objetivo);
        $this->assertEquals($nombre_tarea, $tarea->nombre);
        $this->assertEquals($peso, $tarea->peso);

        $DB->delete_records('tarea', array('id_tarea' => $id_tarea, 'id_objetivo' => $id_objetivo));
    }

    public function test_get_id_objetivo()
    {
        global $DB;
        $curso_id = 2;
        $nombre_obj = 'Objetivo 1';

        // Creamos un objetivo de prueba
        $obj = new stdClass();
        $obj->id_course = $curso_id;
        $obj->nombre = $nombre_obj;
        $obj->id = $DB->insert_record('objetivo', $obj);

        $id_obj = get_id_objetivo($curso_id, $nombre_obj);

        // Comprobamos que el id devuelto es el correcto
        $this->assertEquals($obj->id, $id_obj);

        // Borramos el objetivo de prueba
        $DB->delete_records('objetivo', array('id' => $obj->id));
    }

    public function test_esta_asignada_tarea()
    {
        global $DB;
        $id_tarea = 1;

        // Creamos una tarea de prueba
        $tarea = new stdClass();
        $tarea->id_tarea = $id_tarea;
        $tarea->tipo_tarea = 'actividad';
        $tarea->nombre = 'Tarea de prueba';
        $tarea->id = $DB->insert_record('tarea', $tarea);

        $esta_asignada = esta_asignada_tarea($id_tarea);

        // Comprobamos que la tarea fue asignada
        $this->assertTrue($esta_asignada);

        // Borramos la tarea de prueba
        $DB->delete_records('tarea', array('id' => $tarea->id));
    }

    public function test_esta_asignado_quiz()
    {
        global $DB;
        $id_quiz = 1;

        // Creamos un quiz de prueba
        $quiz = new stdClass();
        $quiz->id_quiz = $id_quiz;
        $quiz->id_objetivo = 1;
        $quiz->nombre = 'Quiz de prueba';
        $quiz->peso = 2;
        $quiz->id = $DB->insert_record('quiz_asignados', $quiz);

        $esta_asignado = esta_asignado_quiz($id_quiz);

        // Comprobamos que el quiz fue asignado
        $this->assertTrue($esta_asignado);

        // Borramos el quiz de prueba
        $DB->delete_records('quiz_asignados', array('id' => $quiz->id));
    }
}
