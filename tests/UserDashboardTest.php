<?php
/**
 * TESTS UNITARIOS - DASHBOARD USUARIO
 * Validaciones de endpoints del panel de usuario
 */

use PHPUnit\Framework\TestCase;

class UserDashboardTest extends TestCase
{
    /**
     * CAMBIAR_CONTRASENA.PHP
     * Valida: id_usuario, contrasena_actual, contrasena_nueva obligatorios
     */
    
    private function validarDatosCambiarContrasena($datos)
    {
        $id = intval($datos['id_usuario'] ?? 0);
        $actual = $datos['contrasena_actual'] ?? "";
        $nueva = $datos['contrasena_nueva'] ?? "";
        
        return $id > 0 && !empty($actual) && !empty($nueva);
    }
    
    public function testCambiarContrasenaDatosCompletos()
    {
        $datos = [
            'id_usuario' => 5,
            'contrasena_actual' => 'oldpass123',
            'contrasena_nueva' => 'newpass456'
        ];
        $this->assertTrue($this->validarDatosCambiarContrasena($datos));
    }
    
    public function testCambiarContrasenaSinId()
    {
        $datos = [
            'contrasena_actual' => 'oldpass123',
            'contrasena_nueva' => 'newpass456'
        ];
        $this->assertFalse($this->validarDatosCambiarContrasena($datos));
    }
    
    public function testCambiarContrasenaSinActual()
    {
        $datos = [
            'id_usuario' => 5,
            'contrasena_nueva' => 'newpass456'
        ];
        $this->assertFalse($this->validarDatosCambiarContrasena($datos));
    }
    
    public function testCambiarContrasenaSinNueva()
    {
        $datos = [
            'id_usuario' => 5,
            'contrasena_actual' => 'oldpass123'
        ];
        $this->assertFalse($this->validarDatosCambiarContrasena($datos));
    }
    
    public function testCambiarContrasenaIdCero()
    {
        $datos = [
            'id_usuario' => 0,
            'contrasena_actual' => 'oldpass123',
            'contrasena_nueva' => 'newpass456'
        ];
        $this->assertFalse($this->validarDatosCambiarContrasena($datos));
    }
    
    public function testCambiarContrasenaActualVacia()
    {
        $datos = [
            'id_usuario' => 5,
            'contrasena_actual' => '',
            'contrasena_nueva' => 'newpass456'
        ];
        $this->assertFalse($this->validarDatosCambiarContrasena($datos));
    }
    
    public function testCambiarContrasenaNuevaVacia()
    {
        $datos = [
            'id_usuario' => 5,
            'contrasena_actual' => 'oldpass123',
            'contrasena_nueva' => ''
        ];
        $this->assertFalse($this->validarDatosCambiarContrasena($datos));
    }
    
    private function verificarContrasenaActual($inputPass, $dbPass)
    {
        return $inputPass === $dbPass;
    }
    
    public function testVerificarContrasenaActualCorrecta()
    {
        $this->assertTrue($this->verificarContrasenaActual("pass123", "pass123"));
    }
    
    public function testVerificarContrasenaActualIncorrecta()
    {
        $this->assertFalse($this->verificarContrasenaActual("pass123", "wrongpass"));
    }
    
    /**
     * DESCARGAR_COMPROBANTE.PHP
     * Valida: id numérico
     */
    
    private function validarIdComprobante($id)
    {
        return is_numeric($id) && intval($id) > 0;
    }
    
    public function testIdComprobanteValido()
    {
        $this->assertTrue($this->validarIdComprobante("10"));
    }
    
    public function testIdComprobanteInvalido()
    {
        $this->assertFalse($this->validarIdComprobante("abc"));
    }
    
    public function testIdComprobanteCero()
    {
        $this->assertFalse($this->validarIdComprobante("0"));
    }
    
    public function testIdComprobanteNegativo()
    {
        $this->assertFalse($this->validarIdComprobante("-5"));
    }
    
    public function testIdComprobanteNull()
    {
        $this->assertFalse($this->validarIdComprobante(null));
    }
    
    /**
     * HORAS.PHP
     * Valida: id_usuario, fecha, horas_trabajadas obligatorios
     */
    
    private function validarDatosRegistrarHoras($datos)
    {
        $id = $datos['id_usuario'] ?? null;
        $fecha = $datos['fecha'] ?? null;
        $horas = $datos['horas_trabajadas'] ?? null;
        
        return !empty($id) && !empty($fecha) && !empty($horas);
    }
    
    public function testRegistrarHorasDatosCompletos()
    {
        $datos = [
            'id_usuario' => 5,
            'fecha' => '2025-01-15',
            'horas_trabajadas' => 8
        ];
        $this->assertTrue($this->validarDatosRegistrarHoras($datos));
    }
    
    public function testRegistrarHorasSinId()
    {
        $datos = [
            'fecha' => '2025-01-15',
            'horas_trabajadas' => 8
        ];
        $this->assertFalse($this->validarDatosRegistrarHoras($datos));
    }
    
    public function testRegistrarHorasSinFecha()
    {
        $datos = [
            'id_usuario' => 5,
            'horas_trabajadas' => 8
        ];
        $this->assertFalse($this->validarDatosRegistrarHoras($datos));
    }
    
    public function testRegistrarHorasSinHoras()
    {
        $datos = [
            'id_usuario' => 5,
            'fecha' => '2025-01-15'
        ];
        $this->assertFalse($this->validarDatosRegistrarHoras($datos));
    }
    
    public function testRegistrarHorasIdVacio()
    {
        $datos = [
            'id_usuario' => '',
            'fecha' => '2025-01-15',
            'horas_trabajadas' => 8
        ];
        $this->assertFalse($this->validarDatosRegistrarHoras($datos));
    }
    
    public function testRegistrarHorasFechaVacia()
    {
        $datos = [
            'id_usuario' => 5,
            'fecha' => '',
            'horas_trabajadas' => 8
        ];
        $this->assertFalse($this->validarDatosRegistrarHoras($datos));
    }
    
    public function testRegistrarHorasHorasVacio()
    {
        $datos = [
            'id_usuario' => 5,
            'fecha' => '2025-01-15',
            'horas_trabajadas' => ''
        ];
        $this->assertFalse($this->validarDatosRegistrarHoras($datos));
    }
    
    /**
     * LISTAR_HORAS.PHP
     * Valida: id_usuario requerido
     */
    
    private function validarIdUsuarioRequerido($id)
    {
        return !empty($id);
    }
    
    public function testListarHorasIdValido()
    {
        $this->assertTrue($this->validarIdUsuarioRequerido(5));
    }
    
    public function testListarHorasIdVacio()
    {
        $this->assertFalse($this->validarIdUsuarioRequerido(""));
    }
    
    public function testListarHorasIdNull()
    {
        $this->assertFalse($this->validarIdUsuarioRequerido(null));
    }
    
    public function testListarHorasIdCero()
    {
        $this->assertFalse($this->validarIdUsuarioRequerido(0));
    }
    
    /**
     * PAGOS.PHP
     * Valida: id_usuario requerido, acción
     */
    
    private function validarDatosPagos($datos)
    {
        $id = intval($datos['id_usuario'] ?? 0);
        return $id > 0;
    }
    
    public function testPagosIdUsuarioValido()
    {
        $datos = ['id_usuario' => 5];
        $this->assertTrue($this->validarDatosPagos($datos));
    }
    
    public function testPagosIdUsuarioCero()
    {
        $datos = ['id_usuario' => 0];
        $this->assertFalse($this->validarDatosPagos($datos));
    }
    
    public function testPagosSinIdUsuario()
    {
        $datos = [];
        $this->assertFalse($this->validarDatosPagos($datos));
    }
    
    private function validarAccionPagos($accion)
    {
        $accionesValidas = ['listar', 'registrar_pago', 'ver_comprobante'];
        return in_array($accion, $accionesValidas);
    }
    
    public function testAccionPagosListar()
    {
        $this->assertTrue($this->validarAccionPagos('listar'));
    }
    
    public function testAccionPagosRegistrar()
    {
        $this->assertTrue($this->validarAccionPagos('registrar_pago'));
    }
    
    public function testAccionPagosVerComprobante()
    {
        $this->assertTrue($this->validarAccionPagos('ver_comprobante'));
    }
    
    public function testAccionPagosInvalida()
    {
        $this->assertFalse($this->validarAccionPagos('eliminar'));
    }
    
    public function testAccionPagosVacia()
    {
        $this->assertFalse($this->validarAccionPagos(''));
    }
    
    private function validarDatosRegistrarPago($datos)
    {
        $idPago = $datos['id_pago'] ?? "";
        return !empty($idPago);
    }
    
    public function testRegistrarPagoIdValido()
    {
        $datos = ['id_pago' => '123'];
        $this->assertTrue($this->validarDatosRegistrarPago($datos));
    }
    
    public function testRegistrarPagoIdVacio()
    {
        $datos = ['id_pago' => ''];
        $this->assertFalse($this->validarDatosRegistrarPago($datos));
    }
    
    public function testRegistrarPagoSinId()
    {
        $datos = [];
        $this->assertFalse($this->validarDatosRegistrarPago($datos));
    }
    
    private function validarTipoPago($tipo)
    {
        return $tipo === 'mensual' || $tipo === 'aporte_inicial';
    }
    
    public function testTipoPagoMensual()
    {
        $this->assertTrue($this->validarTipoPago('mensual'));
    }
    
    public function testTipoPagoAporteInicial()
    {
        $this->assertTrue($this->validarTipoPago('aporte_inicial'));
    }
    
    public function testTipoPagoInvalido()
    {
        $this->assertFalse($this->validarTipoPago('otro'));
    }
    
    private function validarDatosVerComprobante($datos)
    {
        $idPago = intval($datos['id_pago'] ?? 0);
        $idAporte = intval($datos['id_aporte'] ?? 0);
        
        return $idPago > 0 || $idAporte > 0;
    }
    
    public function testVerComprobanteConIdPago()
    {
        $datos = ['id_pago' => 5];
        $this->assertTrue($this->validarDatosVerComprobante($datos));
    }
    
    public function testVerComprobanteConIdAporte()
    {
        $datos = ['id_aporte' => 3];
        $this->assertTrue($this->validarDatosVerComprobante($datos));
    }
    
    public function testVerComprobanteSinIds()
    {
        $datos = [];
        $this->assertFalse($this->validarDatosVerComprobante($datos));
    }
    
    public function testVerComprobanteIdsCero()
    {
        $datos = ['id_pago' => 0, 'id_aporte' => 0];
        $this->assertFalse($this->validarDatosVerComprobante($datos));
    }
    
    /**
     * PERFIL.PHP
     * Valida: id_usuario obligatorio
     */
    
    private function validarDatosPerfil($datos)
    {
        $id = intval($datos['id_usuario'] ?? 0);
        return $id > 0;
    }
    
    public function testPerfilIdUsuarioValido()
    {
        $datos = ['id_usuario' => 5];
        $this->assertTrue($this->validarDatosPerfil($datos));
    }
    
    public function testPerfilIdUsuarioCero()
    {
        $datos = ['id_usuario' => 0];
        $this->assertFalse($this->validarDatosPerfil($datos));
    }
    
    public function testPerfilSinIdUsuario()
    {
        $datos = [];
        $this->assertFalse($this->validarDatosPerfil($datos));
    }
    
    public function testPerfilIdUsuarioNegativo()
    {
        $datos = ['id_usuario' => -5];
        $this->assertFalse($this->validarDatosPerfil($datos));
    }
}
