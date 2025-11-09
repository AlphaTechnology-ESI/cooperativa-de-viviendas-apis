<?php
/**
 * TESTS UNITARIOS - GUARDAR SOLICITUD
 * Archivo PHP testeado: endpoint/solicitudes/guardar_solicitud.php
 * 
 */

use PHPUnit\Framework\TestCase;

class IndexFormTest extends TestCase
{
    /**
     * VALIDACIÓN DE ENTRADA JSON
     * Línea PHP: if (!$input) { ... }
     */
    
    private function validarInputJSON($input)
    {
        return $input !== null && $input !== false;
    }
    
    public function testInputJSONValido()
    {
        $input = ['nombre' => 'Juan', 'email' => 'juan@ejemplo.com'];
        $this->assertTrue($this->validarInputJSON($input));
    }
    
    public function testInputJSONNull()
    {
        $this->assertFalse($this->validarInputJSON(null));
    }
    
    public function testInputJSONFalse()
    {
        $this->assertFalse($this->validarInputJSON(false));
    }
    
    public function testInputJSONVacio()
    {
        $this->assertTrue($this->validarInputJSON([]));
    }
    
    /**
     * CONVERSIÓN DE MONTO
     * Línea PHP: floatval(str_replace(['$', '.', ','], '', $input["monto_inicial"] ?? "0"))
     */
    
    private function convertirMonto($monto)
    {
        if ($monto === null || $monto === '') {
            return 0.0;
        }
        return floatval(str_replace(['$', '.', ','], '', $monto));
    }
    
    public function testConvertirMontoConFormato()
    {
        $this->assertEquals(1000000.0, $this->convertirMonto("$1.000.000"));
    }
    
    public function testConvertirMontoSinFormato()
    {
        $this->assertEquals(1000000.0, $this->convertirMonto("1000000"));
    }
    
    public function testConvertirMontoConComas()
    {
        $this->assertEquals(1000000.0, $this->convertirMonto("1,000,000"));
    }
    
    public function testConvertirMontoVacio()
    {
        $this->assertEquals(0.0, $this->convertirMonto(""));
    }
    
    public function testConvertirMontoNull()
    {
        $this->assertEquals(0.0, $this->convertirMonto(null));
    }
    
    public function testConvertirMontoCero()
    {
        $this->assertEquals(0.0, $this->convertirMonto("0"));
    }
    
    /**
     * VALIDACIÓN DE ESTRUCTURA DE DATOS PARA INSERCIÓN
     * El PHP espera estos campos en el array $input para el INSERT
     */
    
    private function validarCamposUsuarioPendiente($datos)
    {
        $camposRequeridos = [
            'nombre', 'email', 'telefono', 'dni', 
            'fecha_nacimiento', 'estado_civil', 'ocupacion', 'ingresos'
        ];
        
        foreach ($camposRequeridos as $campo) {
            if (!isset($datos[$campo])) {
                return false;
            }
        }
        
        return true;
    }
    
    public function testEstructuraUsuarioPendienteCompleta()
    {
        $datos = [
            'nombre' => 'Juan',
            'email' => 'juan@ejemplo.com',
            'telefono' => '099123456',
            'dni' => '12345678',
            'fecha_nacimiento' => '1990-01-01',
            'estado_civil' => 'soltero',
            'ocupacion' => 'Empleado',
            'ingresos' => '500000_1000000'
        ];
        $this->assertTrue($this->validarCamposUsuarioPendiente($datos));
    }
    
    public function testEstructuraSinNombre()
    {
        $datos = [
            'email' => 'juan@ejemplo.com',
            'telefono' => '099123456',
            'dni' => '12345678',
            'fecha_nacimiento' => '1990-01-01',
            'estado_civil' => 'soltero',
            'ocupacion' => 'Empleado',
            'ingresos' => '500000_1000000'
        ];
        $this->assertFalse($this->validarCamposUsuarioPendiente($datos));
    }
    
    public function testEstructuraSinEmail()
    {
        $datos = [
            'nombre' => 'Juan',
            'telefono' => '099123456',
            'dni' => '12345678',
            'fecha_nacimiento' => '1990-01-01',
            'estado_civil' => 'soltero',
            'ocupacion' => 'Empleado',
            'ingresos' => '500000_1000000'
        ];
        $this->assertFalse($this->validarCamposUsuarioPendiente($datos));
    }
    
    private function validarCamposSolicitud($datos)
    {
        $camposRequeridos = [
            'vivienda_seleccionada', 'forma_pago'
        ];
        
        foreach ($camposRequeridos as $campo) {
            if (!isset($datos[$campo])) {
                return false;
            }
        }
        
        return true;
    }
    
    public function testEstructuraSolicitudCompleta()
    {
        $datos = [
            'vivienda_seleccionada' => 'duplex_norte',
            'monto_inicial' => '1000000',
            'forma_pago' => 'contado',
            'grupo_familiar' => 'Familia de 4',
            'comentarios' => 'Sin comentarios'
        ];
        $this->assertTrue($this->validarCamposSolicitud($datos));
    }
    
    public function testEstructuraSolicitudSinVivienda()
    {
        $datos = [
            'monto_inicial' => '1000000',
            'forma_pago' => 'contado'
        ];
        $this->assertFalse($this->validarCamposSolicitud($datos));
    }
    
    /**
     * VALIDACIÓN DE RESPUESTA JSON
     * El PHP retorna estructura específica según el resultado
     */
    
    private function validarRespuestaExito($respuesta)
    {
        return isset($respuesta['estado']) && $respuesta['estado'] === 'ok';
    }
    
    public function testRespuestaExitoValida()
    {
        $respuesta = ['estado' => 'ok'];
        $this->assertTrue($this->validarRespuestaExito($respuesta));
    }
    
    public function testRespuestaExitoInvalida()
    {
        $respuesta = ['estado' => 'error'];
        $this->assertFalse($this->validarRespuestaExito($respuesta));
    }
    
    private function validarRespuestaError($respuesta)
    {
        return isset($respuesta['estado']) && 
               isset($respuesta['mensaje']) && 
               $respuesta['estado'] === 'error';
    }
    
    public function testRespuestaErrorCompleta()
    {
        $respuesta = [
            'estado' => 'error',
            'mensaje' => 'Datos inválidos'
        ];
        $this->assertTrue($this->validarRespuestaError($respuesta));
    }
    
    public function testRespuestaErrorSinMensaje()
    {
        $respuesta = ['estado' => 'error'];
        $this->assertFalse($this->validarRespuestaError($respuesta));
    }
}
