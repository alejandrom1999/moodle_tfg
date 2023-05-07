<?php declare(strict_types=1);
define('CLI_SCRIPT', true);
use PHPUnit\Framework\TestCase;

require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/blocks/objetivos/vista_profesor.php');


class test_vista_profesor extends TestCase 
{
    public function test_numero_tareas() {
        $this->assertEquals(2, numero_tareas(1));
        $this->assertEquals(1, numero_tareas(2));
        $this->assertEquals(1, numero_tareas(3));
        $this->assertEquals(2, numero_tareas(6));
    }
     

    public function test_get_status_tarea_usuario() {
        
        $this->assertEquals(false, get_status_tarea_usuario(1, 'Actividad 1'));
        $this->assertEquals(true, get_status_tarea_usuario(1, 'Quiz 1'));
        $this->assertEquals(false, get_status_tarea_usuario(2, 'Quiz 1'));
    }
    

    public function test_porcentaje_objetivo_usuario() {
        // Usuario del sistema para diferentes objetivos.
        $this->assertEquals(50, porcentaje_objetivo_usuario('Objetivo 1', 3));
        $this->assertEquals(100, porcentaje_objetivo_usuario('Objetivo 2', 3));
        $this->assertEquals(0, porcentaje_objetivo_usuario('Objetivo 3', 3  ));
    }
    

    public function test_nombres_objetivos() {
        $expected = array(
          array('nombre_objetivo' => 'Objetivo 1'),
          array('nombre_objetivo' => 'Objetivo 2'),
          array('nombre_objetivo' => 'Objetivo 3'),
          array('nombre_objetivo' => 'Ganar musculo'),
          array('nombre_objetivo' => 'Aprender base'),
          array('nombre_objetivo' => 'Realizar Quiz'),
          array('nombre_objetivo' => 'Seguridad general'),
          array('nombre_objetivo' => 'Seguridad en moviles'),
          array('nombre_objetivo' => 'Mi nuevo objetivo'),
        );
        $this->assertEquals($expected, nombres_objetivos());
    }


}