<?php
namespace App\Libs;
use Maatwebsite\Excel\Files\NewExcelFile;
class CustomerExcelExport extends NewExcelFile {
    public function getFilename()
    {
        return 'tk';
    }
}