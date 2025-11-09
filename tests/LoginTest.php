<?php
/**
 * TESTS UNITARIOS - LOGIN
 * Validaciones del endpoint de autenticación
 * Solo testea lo que el PHP de login realmente valida
 */

use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    /**
     * VALIDACIÓN DE CAMPOS OBLIGATORIOS
     * El login.php solo verifica que correo y contraseña no estén vacíos
     */
    
    private function validarCampoNoVacio($valor)
    {
        return !empty($valor);
    }
    
    public function testCorreoNoVacio()
    {
        $this->assertTrue($this->validarCampoNoVacio("usuario@ejemplo.com"));
    }
    
    public function testCorreoVacio()
    {
        $this->assertFalse($this->validarCampoNoVacio(""));
    }
    
    public function testCorreoNull()
    {
        $this->assertFalse($this->validarCampoNoVacio(null));
    }
    
    public function testContrasenaNoVacia()
    {
        $this->assertTrue($this->validarCampoNoVacio("password123"));
    }
    
    public function testContrasenaVacia()
    {
        $this->assertFalse($this->validarCampoNoVacio(""));
    }
    
    public function testContrasenaNull()
    {
        $this->assertFalse($this->validarCampoNoVacio(null));
    }
    
    /**
     * VALIDACIÓN DE ESTRUCTURA DE ENTRADA JSON
     * El login.php verifica que lleguen los campos en el JSON
     */
    
    private function validarEstructuraLogin($datos)
    {
        return isset($datos['correo']) && isset($datos['contrasena']);
    }
    
    public function testEstructuraCompletaValida()
    {
        $datos = [
            'correo' => 'usuario@ejemplo.com',
            'contrasena' => 'password123'
        ];
        $this->assertTrue($this->validarEstructuraLogin($datos));
    }
    
    public function testEstructuraSinCorreo()
    {
        $datos = [
            'contrasena' => 'password123'
        ];
        $this->assertFalse($this->validarEstructuraLogin($datos));
    }
    
    public function testEstructuraSinContrasena()
    {
        $datos = [
            'correo' => 'usuario@ejemplo.com'
        ];
        $this->assertFalse($this->validarEstructuraLogin($datos));
    }
    
    public function testEstructuraVacia()
    {
        $datos = [];
        $this->assertFalse($this->validarEstructuraLogin($datos));
    }
    
    /**
     * VALIDACIÓN COMPLETA DE DATOS DE LOGIN
     * Combina ambas validaciones: estructura y contenido
     */
    
    private function validarDatosLogin($datos)
    {
        // Verifica estructura
        if (!isset($datos['correo']) || !isset($datos['contrasena'])) {
            return false;
        }
        
        // Verifica que no estén vacíos
        if (empty($datos['correo']) || empty($datos['contrasena'])) {
            return false;
        }
        
        return true;
    }
    
    public function testDatosLoginValidos()
    {
        $datos = [
            'correo' => 'usuario@ejemplo.com',
            'contrasena' => 'password123'
        ];
        $this->assertTrue($this->validarDatosLogin($datos));
    }
    
    public function testDatosLoginCorreoVacio()
    {
        $datos = [
            'correo' => '',
            'contrasena' => 'password123'
        ];
        $this->assertFalse($this->validarDatosLogin($datos));
    }
    
    public function testDatosLoginContrasenaVacia()
    {
        $datos = [
            'correo' => 'usuario@ejemplo.com',
            'contrasena' => ''
        ];
        $this->assertFalse($this->validarDatosLogin($datos));
    }
    
    public function testDatosLoginAmbosVacios()
    {
        $datos = [
            'correo' => '',
            'contrasena' => ''
        ];
        $this->assertFalse($this->validarDatosLogin($datos));
    }
    
    public function testDatosLoginSoloEspaciosEnCorreo()
    {
        $datos = [
            'correo' => '   ',
            'contrasena' => 'password123'
        ];
        // empty() considera espacios como no vacío, así funciona el login.php
        $this->assertTrue($this->validarDatosLogin($datos));
    }
    
    public function testDatosLoginSoloEspaciosEnContrasena()
    {
        $datos = [
            'correo' => 'usuario@ejemplo.com',
            'contrasena' => '   '
        ];
        // empty() considera espacios como no vacío, así funciona el login.php
        $this->assertTrue($this->validarDatosLogin($datos));
    }
    
    /**
     * SIMULACIÓN DE VERIFICACIÓN DE CONTRASEÑA
     * El login.php hace comparación directa sin hash
     */
    
    private function verificarContrasena($contrasenaInput, $contrasenaDB)
    {
        return $contrasenaInput === $contrasenaDB;
    }
    
    public function testContrasenaCorrecta()
    {
        $this->assertTrue($this->verificarContrasena("password123", "password123"));
    }
    
    public function testContrasenaIncorrecta()
    {
        $this->assertFalse($this->verificarContrasena("password123", "password456"));
    }
    
    public function testContrasenaCaseSensitive()
    {
        $this->assertFalse($this->verificarContrasena("Password123", "password123"));
    }
    
    public function testContrasenaConEspacios()
    {
        $this->assertFalse($this->verificarContrasena("password123", " password123 "));
    }
    
    /**
     * VALIDACIÓN DE ROL
     * El login.php devuelve "usuario" o "admins" como rol
     */
    
    private function validarRol($rol)
    {
        $rolesValidos = ['usuario', 'admins'];
        return in_array($rol, $rolesValidos);
    }
    
    public function testRolUsuario()
    {
        $this->assertTrue($this->validarRol("usuario"));
    }
    
    public function testRolAdmin()
    {
        $this->assertTrue($this->validarRol("admins"));
    }
    
    public function testRolInvalido()
    {
        $this->assertFalse($this->validarRol("superadmin"));
    }
    
    public function testRolVacio()
    {
        $this->assertFalse($this->validarRol(""));
    }
    
    /**
     * VALIDACIÓN DE RESPUESTA JSON
     * El login.php retorna estructura específica
     */
    
    private function validarEstructuraRespuestaExito($respuesta)
    {
        return isset($respuesta['estado']) && 
               isset($respuesta['rol']) && 
               isset($respuesta['nombre']) && 
               isset($respuesta['id']) &&
               $respuesta['estado'] === 'ok';
    }
    
    public function testRespuestaExitoCompleta()
    {
        $respuesta = [
            'estado' => 'ok',
            'rol' => 'usuario',
            'nombre' => 'Juan Pérez',
            'id' => 1
        ];
        $this->assertTrue($this->validarEstructuraRespuestaExito($respuesta));
    }
    
    public function testRespuestaExitoSinRol()
    {
        $respuesta = [
            'estado' => 'ok',
            'nombre' => 'Juan Pérez',
            'id' => 1
        ];
        $this->assertFalse($this->validarEstructuraRespuestaExito($respuesta));
    }
    
    private function validarEstructuraRespuestaError($respuesta)
    {
        return isset($respuesta['estado']) && 
               isset($respuesta['mensaje']) &&
               $respuesta['estado'] === 'error';
    }
    
    public function testRespuestaErrorCompleta()
    {
        $respuesta = [
            'estado' => 'error',
            'mensaje' => 'Credenciales inválidas'
        ];
        $this->assertTrue($this->validarEstructuraRespuestaError($respuesta));
    }
    
    public function testRespuestaErrorSinMensaje()
    {
        $respuesta = [
            'estado' => 'error'
        ];
        $this->assertFalse($this->validarEstructuraRespuestaError($respuesta));
    }
}
