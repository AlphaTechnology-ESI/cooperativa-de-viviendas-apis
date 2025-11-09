<?php
/**
 * TESTS UNITARIOS - DASHBOARD ADMIN
 * Validaciones de endpoints del panel administrativo
 */

use PHPUnit\Framework\TestCase;

class AdminDashboardTest extends TestCase
{
    /**
     * CAMBIAR_ESTADO.PHP
     * Valida: id_jornada y estado no vacíos
     */
    
    private function validarDatosCambiarEstado($datos)
    {
        $id = $datos['id_jornada'] ?? null;
        $estado = $datos['estado'] ?? null;
        
        return !empty($id) && !empty($estado);
    }
    
    public function testCambiarEstadoDatosCompletos()
    {
        $datos = ['id_jornada' => 1, 'estado' => 'aprobada'];
        $this->assertTrue($this->validarDatosCambiarEstado($datos));
    }
    
    public function testCambiarEstadoSinId()
    {
        $datos = ['estado' => 'aprobada'];
        $this->assertFalse($this->validarDatosCambiarEstado($datos));
    }
    
    public function testCambiarEstadoSinEstado()
    {
        $datos = ['id_jornada' => 1];
        $this->assertFalse($this->validarDatosCambiarEstado($datos));
    }
    
    public function testCambiarEstadoIdVacio()
    {
        $datos = ['id_jornada' => '', 'estado' => 'aprobada'];
        $this->assertFalse($this->validarDatosCambiarEstado($datos));
    }
    
    public function testCambiarEstadoEstadoVacio()
    {
        $datos = ['id_jornada' => 1, 'estado' => ''];
        $this->assertFalse($this->validarDatosCambiarEstado($datos));
    }
    
    /**
     * DESCARGAR_COMPROBANTE_ADMIN.PHP
     * Valida: id numérico y extensión de archivo
     */
    
    private function validarIdNumerico($id)
    {
        return is_numeric($id) && intval($id) > 0;
    }
    
    public function testIdNumericoValido()
    {
        $this->assertTrue($this->validarIdNumerico("123"));
    }
    
    public function testIdNumericoInvalido()
    {
        $this->assertFalse($this->validarIdNumerico("abc"));
    }
    
    public function testIdNumeroCero()
    {
        $this->assertFalse($this->validarIdNumerico("0"));
    }
    
    public function testIdNumeroNegativo()
    {
        $this->assertFalse($this->validarIdNumerico("-5"));
    }
    
    private function detectarMimeType($nombreArchivo)
    {
        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case "png": 
                return "image/png";
            case "jpg":
            case "jpeg": 
                return "image/jpeg";
            case "pdf": 
                return "application/pdf";
            default: 
                return "application/octet-stream";
        }
    }
    
    public function testMimeTypePng()
    {
        $this->assertEquals("image/png", $this->detectarMimeType("archivo.png"));
    }
    
    public function testMimeTypeJpg()
    {
        $this->assertEquals("image/jpeg", $this->detectarMimeType("archivo.jpg"));
    }
    
    public function testMimeTypeJpeg()
    {
        $this->assertEquals("image/jpeg", $this->detectarMimeType("archivo.jpeg"));
    }
    
    public function testMimeTypePdf()
    {
        $this->assertEquals("application/pdf", $this->detectarMimeType("archivo.pdf"));
    }
    
    public function testMimeTypeDesconocido()
    {
        $this->assertEquals("application/octet-stream", $this->detectarMimeType("archivo.txt"));
    }
    
    public function testMimeTypeMayusculas()
    {
        $this->assertEquals("image/png", $this->detectarMimeType("archivo.PNG"));
    }
    
    /**
     * ELIMINAR_SOCIO.PHP
     * Valida: id_usuario requerido y numérico
     */
    
    private function validarDatosEliminarSocio($datos)
    {
        if (!isset($datos['id_usuario']) || empty($datos['id_usuario'])) {
            return false;
        }
        
        return true;
    }
    
    public function testEliminarSocioIdValido()
    {
        $datos = ['id_usuario' => 5];
        $this->assertTrue($this->validarDatosEliminarSocio($datos));
    }
    
    public function testEliminarSocioSinId()
    {
        $datos = [];
        $this->assertFalse($this->validarDatosEliminarSocio($datos));
    }
    
    public function testEliminarSocioIdVacio()
    {
        $datos = ['id_usuario' => ''];
        $this->assertFalse($this->validarDatosEliminarSocio($datos));
    }
    
    public function testEliminarSocioIdNull()
    {
        $datos = ['id_usuario' => null];
        $this->assertFalse($this->validarDatosEliminarSocio($datos));
    }
    
    /**
     * PAGOS_ADMIN.PHP
     * Valida: acción requerida, tipo válido
     */
    
    private function validarAccionPagos($datos)
    {
        $accion = $datos['accion'] ?? "";
        return !empty($accion);
    }
    
    public function testAccionPagosValida()
    {
        $datos = ['accion' => 'listar'];
        $this->assertTrue($this->validarAccionPagos($datos));
    }
    
    public function testAccionPagosVacia()
    {
        $datos = ['accion' => ''];
        $this->assertFalse($this->validarAccionPagos($datos));
    }
    
    public function testAccionPagosSinAccion()
    {
        $datos = [];
        $this->assertFalse($this->validarAccionPagos($datos));
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
    
    public function testTipoPagoVacio()
    {
        $this->assertFalse($this->validarTipoPago(''));
    }
    
    private function validarDatosActualizarEstadoPago($datos)
    {
        $tipo = $datos['tipo'] ?? "";
        $estado = $datos['estado'] ?? "";
        
        if ($tipo === 'mensual') {
            $id = intval($datos['id_pago'] ?? 0);
            return $id > 0 && !empty($estado);
        } elseif ($tipo === 'aporte_inicial') {
            $id = intval($datos['id_aporte'] ?? 0);
            return $id > 0 && !empty($estado);
        }
        
        return false;
    }
    
    public function testActualizarEstadoPagoMensualValido()
    {
        $datos = ['tipo' => 'mensual', 'id_pago' => 5, 'estado' => 'aprobado'];
        $this->assertTrue($this->validarDatosActualizarEstadoPago($datos));
    }
    
    public function testActualizarEstadoAporteValido()
    {
        $datos = ['tipo' => 'aporte_inicial', 'id_aporte' => 3, 'estado' => 'validado'];
        $this->assertTrue($this->validarDatosActualizarEstadoPago($datos));
    }
    
    public function testActualizarEstadoPagoSinId()
    {
        $datos = ['tipo' => 'mensual', 'estado' => 'aprobado'];
        $this->assertFalse($this->validarDatosActualizarEstadoPago($datos));
    }
    
    public function testActualizarEstadoPagoSinEstado()
    {
        $datos = ['tipo' => 'mensual', 'id_pago' => 5];
        $this->assertFalse($this->validarDatosActualizarEstadoPago($datos));
    }
    
    public function testActualizarEstadoTipoInvalido()
    {
        $datos = ['tipo' => 'invalido', 'id_pago' => 5, 'estado' => 'aprobado'];
        $this->assertFalse($this->validarDatosActualizarEstadoPago($datos));
    }
    
    /**
     * VER_HORA_ADMIN.PHP
     * Valida: id requerido
     */
    
    private function validarIdRequerido($id)
    {
        return !empty($id);
    }
    
    public function testIdRequeridoValido()
    {
        $this->assertTrue($this->validarIdRequerido("10"));
    }
    
    public function testIdRequeridoVacio()
    {
        $this->assertFalse($this->validarIdRequerido(""));
    }
    
    public function testIdRequeridoNull()
    {
        $this->assertFalse($this->validarIdRequerido(null));
    }
}
