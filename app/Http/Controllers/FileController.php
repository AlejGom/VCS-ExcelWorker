<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\Session;

class FileController extends Controller
{   
    // *************************************************************
    // Show views functions

    public function showUploadForm() {
        return view('upload');
    }
    public function mainPage() {
        $files    = $this->loadFiles();
        $filtered = false;
        /* return view('mainpage'); */
        return view('mainpage', [
            'files' => $files,
            'filtered' => $filtered
        ]);
    }

    // *************************************************************
    // File manager functions

    // Funcion para subir un archivo
    public function uploadFile(Request $request) {
        $this->validate($request, [
            'file' => 'required|file',
        ]);
    
        $uploadFile  = $request->file('file');
        $fileName    = $uploadFile->getClientOriginalName();
        $filePath    = $uploadFile->store('public/files');
        $fileSize    = $uploadFile->getSize();

        // deprecated
        /* $fileContent = file_get_contents($uploadFile->getPathname()); */


        $file            = new File();
        $file->name      = $fileName;
        $file->file_path = $filePath;
        $file->size      = $fileSize;
        $file->user_id   = auth()->id();
        
        // deprecated
        /* $file->name    = $uploadFile->getClientOriginalName(); */
        /* $file->content = $fileContent; */
        $this->readAndConvertDates($file);

        $file->save();
        

        /* return back()->with('success','Archivo subido con exito'); */
        return redirect('/mainPage')->with('success','Archivo subido con exito');
    }
    
    // Funcion para leer un archivo
    public function readFile($fileId) {

        $file     = File::find($fileId);
        $filePath = storage_path('app/' . $file->file_path);

        // deprecated
        /* $fileContent = $file->content; */
        /* $localFilePath = storage_path('app/public/files/' . $file->name);
        Storage::put('public/files/' . $file->name, $fileContent);
        file_put_contents($localFilePath, $fileContent); */

        /* $reader = new ReaderXlsx(); */
        /* $extension = pathinfo($file, PATHINFO_EXTENSION); */
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        /* dd($extension); */

        switch ($extension) {
            case 'xlsx': $extensionCode = 'Xlsx'; break;
            case 'xls':  $extensionCode = 'Xls'; break;
            case 'ods':  $extensionCode = 'Ods'; break;
            case 'txt':  $extensionCode = 'Csv'; break; // PATHINFO_EXTENSION detected by txt from csv
            default: return redirect('/mainPage')->with('extensionError','Extension no soportada');
        }
        /* dd($extension); */
        /* dd($extensionCode); */

        $reader      = IOFactory::createReader($extensionCode);
        $spreadsheed = $reader->load($filePath);
        $sheet       = $spreadsheed->getActiveSheet();

        $data     = [];
        $rowCount = -2;

        $firstLane = [];
        $firstRow  = $sheet->getRowIterator(2)->current();
        foreach ($firstRow->getCellIterator() as $cell) {
            $firstLane[] = $cell->getValue();
        }
        
        // read file
        foreach($sheet->getRowIterator() as $row) {
            $rowData = [$rowCount + 1];
            foreach($row->getCellIterator() as $cell) {
                $rowData[] = $cell->getValue();        
                /* dd($rowData); */
            }
            $data[] = $rowData;
            $rowCount++;
            
            $currentRows = Session::get('currentRows', 500);
    
            if($rowCount >= $currentRows) {
                break;
            }
        }
        
        $maxRows = count($data);
        
        /* dd($maxRows); */
        // reverse array to descendent
        /* $data = array_reverse($data); */

        // deprecated
        /* foreach($sheet->getRowIterator() as $row) {
            $rowData = [];
            foreach($row->getCellIterator() as $cell) {
                $rowData[] = $cell->getValue();
            }
            $data[] = $rowData;
        } */

        $filteredData = null;
        
        return view('readFile', [
            'data'         => $data,
            'maxRows'      => $maxRows,
            'currentRows'  => $currentRows,
            'file'         => $file,
            'filteredData' => $filteredData,
            'firstLane'    => $firstLane]);
    
    }
    
    public function loadFiles() {
        if(auth()->user()->name === 'admin') {
            $files = File::orderBy('created_at', 'desc')->get();
        } else {
            $files = File::where('user_id', auth()->id())->orderBy('created_at', 'desc')->get();    
        }

        
        return $files;
    }

    public function deleteFile($fileId) {
        $file = File::find($fileId);
        Storage::delete($file->file_path);
        $file->delete();

        return redirect('/mainPage');
    }

    public function updateCell(Request $request) {

        $fileId   = $request->input('fileId');
        $rowIndex = $request->input('rowIndex');
        $colIndex = $request->input('colIndex');
        $newValue = $request->input('newValue');
        
        $file     = File::find($fileId);
        $filePath = storage_path('app/' . $file->file_path);

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        /* dd($extension); */

        switch ($extension) {
            case 'xlsx': $extensionCode = 'Xlsx'; break;
            case 'xls':  $extensionCode = 'Xls'; break;
            case 'ods':  $extensionCode = 'Ods'; break;
            case 'txt':  $extensionCode = 'Csv'; break; // PATHINFO_EXTENSION detected by txt from csv
            default: return redirect('/mainPage')->with('extensionError','Extension no soportada');
        }
        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();

        $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex) . ($rowIndex + 2);
        /* dd($cellCoordinate); */
        $sheet->setCellValue($cellCoordinate, $newValue);

        $writer = IOFactory::createWriter($spreadsheet, $extensionCode);
        /* dd($writer); */
        $writer->save($filePath);

        return response()->json(['success' => true]);
    }
    // *************************************************************
    // Other functions

    public function increaseRowsInView() {
        $currentRows = session()->get('currentRows', 500);
        $newRows     = $currentRows + 500;
        Session::put('currentRows', $newRows);
        return redirect()->back();
    }

    public function goBack() {
        Session::forget('currentRows');
        return redirect('/mainPage');
    }

    public function downloadFile($fileId) {
        $file     = File::find($fileId);
        $filePath = storage_path('app/' . $file->file_path);
        return response()->download($filePath);
    }

    private function readAndConvertDates($file) {
     
        $filePath = storage_path('app/' . $file->file_path);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));   

        switch ($extension) {
            case 'xlsx': $extensionCode = 'Xlsx'; break;
            case 'xls':  $extensionCode = 'Xls'; break;
            case 'ods':  $extensionCode = 'Ods'; break;
            case 'txt':  $extensionCode = 'Csv'; break; // PATHINFO_EXTENSION detected by txt from csv
            default: return redirect('/mainPage')->with('extensionError','Extension no soportada');
        }

        $reader      = IOFactory::createReader($extensionCode);
        $spreadsheet = $reader->load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();

        foreach ($sheet->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $value = $this->parseDate($cell->getValue());
                $cell->setValue($value);
            }
        }

        $writer = IOFactory::createWriter($spreadsheet, $extensionCode);
        $writer->save($filePath);
        
    }
    private function parseDate($value) {
        $formats = ['d/m/Y', 'm/d/Y', 'Y-m-d', 'Y/m/d', 'd-m-Y', 'd/m/Y H:i:s'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date !== false) {
                return $date->format('d/m/Y');
            }
        }
        return $value;
    }
}
