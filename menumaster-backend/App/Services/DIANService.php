<?php
namespace App\Services;

use Exception;

class DIANService
{
    private $outputDir;
    private $nitEmisor;
    private $codigoSucursal;
    private $claveTecnica; // Usada para CUFE/CUDE en stub

    public function __construct()
    {
        $this->outputDir = BASE_PATH . '/storage/facturacion';
        if (!is_dir($this->outputDir)) {
            @mkdir($this->outputDir, 0777, true);
        }
        // Variables de entorno/configuración (ajustar en producción)
        $this->nitEmisor      = getenv('MM_NIT_EMISOR') ?: '900000000';
        $this->codigoSucursal = getenv('MM_COD_SUCURSAL') ?: '001';
        $this->claveTecnica   = getenv('MM_CLAVE_TECNICA') ?: 'CLAVE-PRUEBA-123';
    }

    /**
     * Genera UBL 2.1 XML y CUFE/CUDE (stub), y devuelve paths.
     */
    public function generarFacturaElectronica(array $pedido): array
    {
        $numero = $this->generarNumeroConsecutivo($pedido);
        $cufe   = $this->generarCufeStub($pedido, $numero);
        $xml    = $this->construirXML($pedido, $numero, $cufe);

        $xmlPath = $this->outputDir . "/factura_{$pedido['id']}_{$numero}.xml";
        file_put_contents($xmlPath, $xml);

        // En producción: generar PDF de representación gráfica
        $pdfPath = null; // Opcional: integración con librería de PDF

        return [
            'numero'   => $numero,
            'cufe'     => $cufe,
            'xml_path' => $xmlPath,
            'pdf_path' => $pdfPath,
        ];
    }

    public function obtenerRutasComprobante(array $pedido): array
    {
        // Busca por patrón; en prod se debe guardar referencia en BD
        $pattern = $this->outputDir . "/factura_{$pedido['id']}_*.xml";
        $matches = glob($pattern);
        $xmlPath = $matches[0] ?? null;
        $pdfPath = null;
        return [ 'xml_path' => $xmlPath, 'pdf_path' => $pdfPath ];
    }

    private function generarNumeroConsecutivo(array $pedido): string
    {
        // En producción: usar resolución de numeración DIAN
        $prefijo = 'SETPR'; // Prefijo de prueba
        $consecutivo = str_pad((string)($pedido['id'] ?? rand(1, 99999)), 6, '0', STR_PAD_LEFT);
        return $prefijo . $consecutivo;
    }

    private function generarCufeStub(array $pedido, string $numero): string
    {
        // CUFE/CUDE real requiere campos y clave técnica DIAN; aquí stub
        $cadena = implode('|', [
            $numero,
            $pedido['total'] ?? 0,
            $pedido['cliente']['documento'] ?? '00000000',
            $pedido['fecha'] ?? date('Y-m-d'),
            $this->nitEmisor,
            $this->claveTecnica
        ]);
        return hash('sha256', $cadena);
    }

    private function construirXML(array $pedido, string $numero, string $cufe): string
    {
        $clienteNombre = $pedido['cliente']['nombre'] ?? 'Cliente';
        $clienteDoc    = $pedido['cliente']['documento'] ?? '00000000';
        $total         = number_format((float)($pedido['total'] ?? 0), 2, '.', '');
        $fecha         = $pedido['fecha'] ?? date('Y-m-d');

        // UBL 2.1 simplificado (no firmado); para pruebas y desarrollo
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
            "<Invoice xmlns=\"urn:oasis:names:specification:ubl:schema:xsd:Invoice-2\"\n" .
            "         xmlns:cac=\"urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2\"\n" .
            "         xmlns:cbc=\"urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2\">\n" .
            "  <cbc:ID>{$numero}</cbc:ID>\n" .
            "  <cbc:IssueDate>{$fecha}</cbc:IssueDate>\n" .
            "  <cbc:UUID>{$cufe}</cbc:UUID>\n" .
            "  <cac:AccountingSupplierParty>\n" .
            "    <cac:Party>\n" .
            "      <cac:PartyIdentification><cbc:ID>{$this->nitEmisor}</cbc:ID></cac:PartyIdentification>\n" .
            "    </cac:Party>\n" .
            "  </cac:AccountingSupplierParty>\n" .
            "  <cac:AccountingCustomerParty>\n" .
            "    <cac:Party>\n" .
            "      <cac:PartyIdentification><cbc:ID>{$clienteDoc}</cbc:PartyIdentification>\n" .
            "      <cac:PartyName><cbc:Name>{$clienteNombre}</cbc:Name></cac:PartyName>\n" .
            "    </cac:Party>\n" .
            "  </cac:AccountingCustomerParty>\n" .
            "  <cac:LegalMonetaryTotal>\n" .
            "    <cbc:PayableAmount currencyID=\"COP\">{$total}</cbc:PayableAmount>\n" .
            "  </cac:LegalMonetaryTotal>\n" .
            "</Invoice>\n";
        return $xml;
    }
}