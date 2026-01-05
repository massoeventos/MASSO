<?php

namespace Masso\Excel;

use Illuminate\Support\MessageBag;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Facades\Excel;

class ParticipantsExcelImport implements ToModel, WithStartRow, WithEvents
{
    /**
     * Encabezados esperados (SOLO los entregados por el usuario)
     */
    protected $expected = [
        'names' => 'Nombre (s)*',
        'lastname_father' => 'Apellido PATERNO*',
        'lastname_mother' => 'Apellido MATERNO*',
        'phone' => 'NÚM TELÉFONO',
        'document' => 'Cédula Identidad / Pasaporte*',
        'email' => 'Email*',
        'workplace' => 'Lugar_de_trabajo*',
        'city' => 'Ciudad*',
        'country' => 'País*',
        'profession' => 'Profesión*',
        'specialty' => 'Especialidad*',
        'birthdate' => 'Fecha de nacimiento*',
        'inscription_category' => 'Categoría de inscripción',
        'payment_form' => 'Forma de Pago (OC-documento de pago)',
        'company_name' => 'Nombre de la Empresa*',
        'rut' => 'Rut *',
        'business_name' => 'Razón Social*',
        'giro' => 'Giro',
        'amount_to_pay' => 'Monto a pagar',
    ];

    protected $requiredKeys = [];
    protected $expectedNormalized = [];

    /** @var array key => colIndex */
    protected $columns = [];

    protected $errors = [];
    protected $maxErrors = 50;
    protected $participantsCount = 0;

    /** contador de filas reales procesadas (para reportar N de Excel) */
    protected $currentRowNumber = 2;

    public function __construct()
    {
        foreach ($this->expected as $key => $label) {
            $this->expectedNormalized[$key] = $this->normalizeExcelHeader($label);
            if (strpos($label, '*') !== false) {
                $this->requiredKeys[] = $key;
            }
        }
    }

    /**
     * Valida el Excel de participantes y retorna la cantidad de participantes válidos.
     *
     * Retorna un array:
     * - ok: bool
     * - participants_count: int
     * - bag: MessageBag (solo si ok=false)
     */
    public function validateAndCount($fullPath)
    {
        // Reset state in case instance is reused
        $this->columns = [];
        $this->errors = [];
        $this->participantsCount = 0;
        $this->currentRowNumber = $this->startRow();

        try {
            Excel::import($this, $fullPath);
        } catch (ParticipantsExcelValidationException $e) {
            return ['ok' => false, 'participants_count' => 0, 'bag' => $e->getBag()];
        } catch (\Exception $e) {
            $bag = new MessageBag();
            $bag->add('participants_excel', 'No se pudo leer el Excel. Verifica que el archivo sea válido (.xlsx/.xls/.csv) y respete el formato.');
            return ['ok' => false, 'participants_count' => 0, 'bag' => $bag];
        }

        if ($this->participantsCount === 0) {
            $bag = new MessageBag();
            $bag->add('participants_excel', 'El Excel no contiene filas de participantes (o están vacías).');
            return ['ok' => false, 'participants_count' => 0, 'bag' => $bag];
        }

        if (!empty($this->errors)) {
            $bag = new MessageBag();
            foreach ($this->errors as $msg) {
                $bag->add('participants_excel', $msg);
            }
            return ['ok' => false, 'participants_count' => 0, 'bag' => $bag];
        }

        return ['ok' => true, 'participants_count' => $this->participantsCount];
    }

    public function startRow(): int
    {
        // Fila 1 = encabezados; datos parten en fila 2
        return 2;
    }

    /**
     * Se ejecuta por cada fila de datos (desde startRow).
     * No persistimos nada: retornamos null.
     */
    public function model(array $row)
    {
        $excelRowNumber = $this->currentRowNumber;
        $this->currentRowNumber++;

        if (empty($this->columns)) {
            // Si por algún motivo no se detectó header, forzamos un error claro.
            throw new ParticipantsExcelValidationException($this->bagWithSingle('participants_excel', 'No se pudo detectar el encabezado del Excel.'));
        }

        // Determinar si la fila está vacía (en base a columnas requeridas)
        $allEmpty = true;
        foreach ($this->requiredKeys as $key) {
            if (!isset($this->columns[$key])) {
                continue;
            }
            $colIndex = $this->columns[$key];
            $value = isset($row[$colIndex]) ? trim((string) $row[$colIndex]) : '';
            if ($value !== '') {
                $allEmpty = false;
                break;
            }
        }

        if ($allEmpty) {
            return null;
        }

        $this->participantsCount++;

        foreach ($this->requiredKeys as $key) {
            if (!isset($this->columns[$key])) {
                continue;
            }
            $colIndex = $this->columns[$key];
            $value = isset($row[$colIndex]) ? (string) $row[$colIndex] : '';
            if (trim($value) === '') {
                $label = isset($this->expected[$key]) ? $this->expected[$key] : $key;
                $this->errors[] = 'Fila ' . $excelRowNumber . ': falta "' . $label . '".';
            }
        }

        if (count($this->errors) >= $this->maxErrors) {
            $this->errors[] = 'Se encontraron demasiados errores. Corrige el archivo y vuelve a intentar.';
            $bag = new MessageBag();
            foreach ($this->errors as $msg) {
                $bag->add('participants_excel', $msg);
            }
            throw new ParticipantsExcelValidationException($bag);
        }

        return null;
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                // Solo necesitamos leer el header 1 vez (primera hoja)
                if (!empty($this->columns)) {
                    return;
                }
                $this->detectHeaderColumnsOrFail($event);
            },
        ];
    }

    protected function detectHeaderColumnsOrFail(BeforeSheet $event)
    {
        try {
            $sheet = $event->getSheet()->getDelegate();
            $highestColumn = $sheet->getHighestColumn();
            $headerRow = $sheet->rangeToArray('A1:' . $highestColumn . '1', null, true, false);
            $headerRow = isset($headerRow[0]) && is_array($headerRow[0]) ? $headerRow[0] : [];
        } catch (\Exception $e) {
            throw new ParticipantsExcelValidationException($this->bagWithSingle('participants_excel', 'No se pudo leer el encabezado del Excel.'));
        }

        $found = [];
        foreach ($headerRow as $colIndex => $cell) {
            $header = $this->normalizeExcelHeader($cell);
            if ($header === '') {
                continue;
            }
            foreach ($this->expectedNormalized as $key => $expectedHeader) {
                if (isset($found[$key])) {
                    continue;
                }
                if ($header === $expectedHeader) {
                    $found[$key] = $colIndex;
                }
            }
        }

        foreach ($this->requiredKeys as $key) {
            if (!isset($found[$key])) {
                throw new ParticipantsExcelValidationException(
                    $this->bagWithSingle('participants_excel', 'No se pudo detectar el encabezado del Excel. Usa el formato indicado y asegúrate de incluir las columnas obligatorias (*).')
                );
            }
        }

        $this->columns = $found;
    }

    protected function normalizeExcelHeader($value)
    {
        $value = is_null($value) ? '' : (string) $value;
        $value = trim(mb_strtolower($value, 'UTF-8'));
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if ($converted !== false && $converted !== null) {
                $value = $converted;
            }
        }
        $value = preg_replace('/\s+/', ' ', $value);
        $value = preg_replace('/[^a-z0-9 ]+/', '', $value);
        return trim($value);
    }

    protected function bagWithSingle($key, $message)
    {
        $bag = new MessageBag();
        $bag->add($key, $message);
        return $bag;
    }
}
