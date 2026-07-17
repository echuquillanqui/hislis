<?php

namespace Database\Seeders;

use App\Models\AnalyticalPrinciple;
use App\Models\ContainerType;
use App\Models\FinancialCategory;
use App\Models\InventoryCategory;
use App\Models\InventoryMovementType;
use App\Models\MeasurementUnit;
use App\Models\PaymentMethod;
use App\Models\SampleType;
use Illuminate\Database\Seeder;

class LisCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCatalog(SampleType::class, [
            ['SANGRE', 'Sangre'],
            ['SUERO', 'Suero'],
            ['PLASMA', 'Plasma'],
            ['ORINA', 'Orina'],
            ['HECES', 'Heces'],
            ['HISOPADO', 'Hisopado'],
            ['ESPUTO', 'Esputo'],
            ['LCR', 'Líquido cefalorraquídeo'],
        ]);

        $this->seedCatalog(ContainerType::class, [
            ['TUBO_ROJO', 'Tubo tapa roja', ['color' => 'Rojo']],
            ['TUBO_LILA', 'Tubo EDTA tapa lila', ['color' => 'Lila']],
            ['TUBO_AZUL', 'Tubo citrato tapa azul', ['color' => 'Azul']],
            ['FRASCO_ESTERIL', 'Frasco estéril'],
            ['RECIPIENTE_ORINA', 'Recipiente para orina'],
        ]);

        $this->seedCatalog(MeasurementUnit::class, [
            ['MG_DL', 'Miligramos por decilitro', ['symbol' => 'mg/dL', 'unit_type' => 'concentración']],
            ['G_DL', 'Gramos por decilitro', ['symbol' => 'g/dL', 'unit_type' => 'concentración']],
            ['UL', 'Microlitro', ['symbol' => 'µL', 'unit_type' => 'volumen']],
            ['ML', 'Mililitro', ['symbol' => 'mL', 'unit_type' => 'volumen']],
            ['U_L', 'Unidades por litro', ['symbol' => 'U/L', 'unit_type' => 'actividad']],
            ['UI_ML', 'Unidades internacionales por mililitro', ['symbol' => 'UI/mL', 'unit_type' => 'concentración']],
            ['PORCENTAJE', 'Porcentaje', ['symbol' => '%', 'unit_type' => 'relación']],
            ['UNIDAD', 'Unidad', ['symbol' => 'und', 'unit_type' => 'conteo']],
        ]);

        $this->seedCatalog(AnalyticalPrinciple::class, [
            ['CLIA', 'CLIA'],
            ['ECLIA', 'ECLIA'],
            ['CMIA', 'CMIA'],
            ['ELISA', 'ELISA'],
            ['FIA', 'FIA'],
            ['ELFA', 'ELFA'],
            ['INMUNOCROMATOGRAFIA', 'Inmunocromatografía'],
            ['AGLUTINACION', 'Aglutinación'],
            ['NEFELOMETRIA', 'Nefelometría'],
            ['INMUNOTURBIDIMETRIA', 'Inmunoturbidimetría'],
            ['ESPECTROFOTOMETRIA', 'Espectrofotometría'],
            ['COLORIMETRIA', 'Colorimetría'],
            ['MICROSCOPIA', 'Microscopía'],
            ['CULTIVO', 'Cultivo'],
            ['PCR', 'PCR'],
            ['OTRO', 'Otro'],
        ]);

        $this->seedCatalog(InventoryCategory::class, [
            ['REACTIVOS', 'Reactivos'],
            ['CALIBRADORES', 'Calibradores'],
            ['CONTROLES', 'Controles'],
            ['TOMA_MUESTRA', 'Material de toma de muestra'],
            ['TUBOS', 'Tubos'],
            ['AGUJAS', 'Agujas'],
            ['GUANTES', 'Guantes'],
            ['PLACAS', 'Placas'],
            ['MEDIOS_CULTIVO', 'Medios de cultivo'],
            ['DESCARTABLE', 'Material descartable'],
            ['PAPELERIA', 'Papelería'],
            ['LIMPIEZA', 'Limpieza'],
            ['OTROS', 'Otros'],
        ]);

        $this->seedCatalog(InventoryMovementType::class, [
            ['ENTRADA_COMPRA', 'Entrada por compra', ['direction' => 'entry']],
            ['ENTRADA_MANUAL', 'Entrada manual', ['direction' => 'entry']],
            ['DONACION', 'Donación', ['direction' => 'entry']],
            ['DEVOLUCION', 'Devolución', ['direction' => 'entry']],
            ['SALIDA_MANUAL', 'Salida manual', ['direction' => 'exit']],
            ['CONSUMO_EXAMEN', 'Consumo por examen', ['direction' => 'exit']],
            ['CONSUMO_CONTROL', 'Consumo por control', ['direction' => 'exit']],
            ['CONSUMO_CALIBRACION', 'Consumo por calibración', ['direction' => 'exit']],
            ['MERMA', 'Merma', ['direction' => 'exit']],
            ['VENCIMIENTO', 'Vencimiento', ['direction' => 'exit']],
            ['ROTURA', 'Rotura', ['direction' => 'exit']],
            ['AJUSTE_POSITIVO', 'Ajuste positivo', ['direction' => 'entry', 'requires_approval' => true]],
            ['AJUSTE_NEGATIVO', 'Ajuste negativo', ['direction' => 'exit', 'requires_approval' => true]],
            ['TRANSFERENCIA_SALIDA', 'Transferencia de salida', ['direction' => 'exit']],
            ['TRANSFERENCIA_ENTRADA', 'Transferencia de entrada', ['direction' => 'entry']],
            ['REVERSION', 'Reversión', ['direction' => 'entry', 'requires_approval' => true]],
        ]);

        $this->seedCatalog(PaymentMethod::class, [
            ['EFECTIVO', 'Efectivo'],
            ['TARJETA', 'Tarjeta', ['requires_reference' => true]],
            ['TRANSFERENCIA', 'Transferencia', ['requires_reference' => true]],
            ['BILLETERA_DIGITAL', 'Billetera digital', ['requires_reference' => true]],
            ['CREDITO', 'Crédito'],
            ['MIXTO', 'Pago mixto'],
            ['OTRO', 'Otro'],
        ]);

        $this->seedCatalog(FinancialCategory::class, [
            ['ING_ORDENES', 'Cobros de órdenes', ['type' => 'income']],
            ['ING_ABONOS_CLIENTES', 'Abonos de clientes', ['type' => 'income']],
            ['ING_OTROS', 'Otros ingresos', ['type' => 'income']],
            ['EGR_COMPRAS', 'Compras pagadas', ['type' => 'expense']],
            ['EGR_GASTOS_OPERATIVOS', 'Gastos operativos', ['type' => 'expense']],
            ['EGR_MOVILIDAD', 'Movilidad', ['type' => 'expense']],
            ['EGR_SERVICIOS', 'Servicios', ['type' => 'expense']],
            ['EGR_MANTENIMIENTO', 'Mantenimiento', ['type' => 'expense']],
            ['EGR_CAJA_CHICA', 'Caja chica', ['type' => 'expense']],
            ['EGR_OTROS', 'Otros egresos', ['type' => 'expense']],
        ]);
    }

    private function seedCatalog(string $modelClass, array $items): void
    {
        foreach ($items as $index => $item) {
            [$code, $name] = $item;
            $extra = $item[2] ?? [];

            $modelClass::updateOrCreate(
                ['code' => $code],
                array_merge([
                    'name' => $name,
                    'status' => true,
                    'sort_order' => $index + 1,
                ], $extra)
            );
        }
    }
}
