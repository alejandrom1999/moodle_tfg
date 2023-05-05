<?php declare(strict_types=1);
define('CLI_SCRIPT', true);
use PHPUnit\Framework\TestCase;

require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/blocks/objetivos/form_objetivo.php');

class test_form_objetivo extends TestCase 
{

    public function testInsertarObjetivo() {
        global $DB;
        $form = new form_objetivo();
        $data = (object) ['nombre' => 'Mi nuevo objetivo'];

        $id_curso = 1; // Se debe definir un id de curso válido aquí
        $form->set_data($data);
        $form->set_data(['id_curso' => $id_curso]);

        insertar_objetivo($id_curso, $data->nombre);

        $objetivo = $DB->get_record('objetivo', ['nombre' => $data->nombre]);
        $this->assertEquals($id_curso, $objetivo->id_course);
        $this->assertEquals($data->nombre, $objetivo->nombre);
    }
}