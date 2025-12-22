<?php

namespace Masso\Excel;

// Compatible con PHPExcel (maatwebsite/excel ^2.1)
class SafeValueBinder extends \PHPExcel_Cell_DefaultValueBinder
{
    /**
     * En PHP 8+, PHPExcel puede intentar acceder a offsets de string sobre ints (ej: $value[0]),
     * lo que produce "Trying to access array offset on value of type int".
     *
     * Para lectura de Excel en este proyecto solo necesitamos valores "stringables",
     * así que forzamos escalares numéricos a string antes de delegar.
     */
    public function bindValue(\PHPExcel_Cell $cell, $value = null)
    {
        if (is_int($value) || is_float($value)) {
            $value = (string) $value;
        }

        return parent::bindValue($cell, $value);
    }
}
