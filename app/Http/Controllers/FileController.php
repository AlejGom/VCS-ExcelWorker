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
use App\Models\User;
use App\Models\SharedFile;
use PhpOffice\PhpSpreadsheet\Shared\Date;


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
        $users    = User::all();

        /* return view('mainpage'); */
        return view('mainpage', [
            'files'    => $files,
            'filtered' => $filtered,
            'users'    => $users,
        ]);
    }
    // Mostrar formulario para compartir un archivo
    public function showShareFile($id) {
        $file  = File::find($id);
        $users = User::all();

        return view('shareFile', [
            'file'  => $file,
            'users' => $users
        ]);
    }

    // Mostrar archivos compartidos
    public function mySharedFiles() {
        $sharedFiles   = $this->loadSharedFiles();
        $mySharedFiles = $this->loadMySharedFiles();
        $filtered      = false;
        $users         = User::all();


        $files   = collect();
        $myFiles = collect();

        foreach($sharedFiles as $sharedFile) {
            $file = File::find($sharedFile->id_file);

            if ($file) {
                $files->push([
                    'idShared' => $sharedFile->id,
                    'id'       => $file->id,
                    'name'     => $file->name,
                    'created'  => $file->created_at,
                    'size'     => $file->size,
                    'user'     => $file->user->name,
                ]);
            }
        }

        foreach($mySharedFiles as $mySharedFile) {
            $file = File::find($mySharedFile->id_file);
            $user = User::find($mySharedFile->shared);

            if ($file) {
                $myFiles->push([
                    'idShared'    => $mySharedFile->id,
                    'id'          => $file->id,
                    'name'        => $file->name,
                    'created'     => $file->created_at,
                    'size'        => $file->size,
                    'user'        => $file->user->name,
                    'destinatary' => $user->name
                ]);
            }
        }
        return view('mySharedFiles', [
                'files'    => $files,
                'myFiles'  => $myFiles,
                'filtered' => $filtered,
                'users'    => $users,
            ]);
    }

    // *************************************************************
    // File manager functions

    // -------------------------------------------------------------
    // ----------------Funcion para subir un archivo----------------
    // -------------------------------------------------------------
    public function uploadFile(Request $request) {
        $this->validate($request, [
            'file'     => 'required|file',
            'fileName' => 'required',
        ]);
    
        $uploadFile  = $request->file('file');
        /* $fileName    = $uploadFile->getClientOriginalName(); */
        $fileName    = $request->input('fileName');
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

        // MANDAR A LEER EL ARCHIVO Y CAMBIAR FECHAS
        /* $this->readAndConvertDates($file); */
    
        $file->save();
        

        /* return back()->with('success','Archivo subido con exito'); */
        return redirect('/mainPage')->with('success','Archivo subido con exito');
    }

    // ---------------------------------------------------------------------
    // --------------Funcion para compartir archivos------------------------
    // ---------------------------------------------------------------------

    public function shareFile(Request $request) {
        $this->validate($request, [
            'fileId' => 'required',
            'user'   => 'required',
        ]);

        $userId       = auth()->id();
        $sharedUserId = $request->input('user');
        $fileId       = $request->input('fileId');

        $sharedFile = new SharedFile();

        $sharedFile->shared  = $sharedUserId;
        $sharedFile->id_user = $userId;
        $sharedFile->id_file = $fileId;

        $sharedFile->save();
        return redirect('mainPage')->with('success','success');


    }

    // ------------------------------------------------------------
    // ----------------Funcion para leer un archivo----------------
    // ------------------------------------------------------------
    public function readFile($fileId, Request $request) {

        // search file with id and save in $file 
        $file     = File::find($fileId);
        // save file path of $file
        $filePath = storage_path('app/' . $file->file_path);

        // deprecated
        /* $fileContent = $file->content; */
        /* $localFilePath = storage_path('app/public/files/' . $file->name);
        Storage::put('public/files/' . $file->name, $fileContent);
        file_put_contents($localFilePath, $fileContent); */

        /* $reader = new ReaderXlsx(); */
        /* $extension = pathinfo($file, PATHINFO_EXTENSION); */
        
        // read file extension and save in $extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // switch returned data into data to IOFactory
        switch ($extension) {
            case 'xlsx': $extensionCode = 'Xlsx'; break;
            case 'xls':  $extensionCode = 'Xls'; break;
            case 'ods':  $extensionCode = 'Ods'; break;
            case 'txt':  $extensionCode = 'Csv'; break; // PATHINFO_EXTENSION detected by txt from csv
            case 'jpeg': return view('image', ['image' => $file]);
            case 'png':  return view('image', ['image' => $file]);
            case 'jpg':  return view('image', ['image' => $file]);
            case 'gif':  return view('image', ['image' => $file]);
            case 'webp': return view('image', ['image' => $file]);
            default: return redirect('/mainPage')->with('extensionError','Extension no soportada');
        }

        // read file with extension
        $reader      = IOFactory::createReader($extensionCode);
        // load file
        $spreadsheed = $reader->load($filePath);
        $sheet       = $spreadsheed->getActiveSheet();

        // declare variables
        $data      = [];
        $rowCount  = -2;
        $firstLane = [];

        // read first row "tittles of columns"
        $firstRow  = $sheet->getRowIterator(2)->current();
        foreach ($firstRow->getCellIterator() as $cell) {
            $firstLane[] = $cell->getValue();
        }
        
        // read file rows and save in $data[]
        foreach($sheet->getRowIterator() as $row) {
            $rowData = [$rowCount + 1];
            foreach($row->getCellIterator() as $cell) {
                $rowData[] = $cell->getValue();        
                /* dd($rowData); */
            }
            $data[] = $rowData;

            // aux to add number of  the file
            $rowCount++;
            
            // save current rows in session
            $currentRows = Session::get('currentRows', 100);
    
        }
        
        $maxRows = count($data);
        $perPage = 100;
        $page    = $request->query('page', 1);
        $offset  = ($page - 1) * $perPage;

        $pagedData = array_slice($data, $offset, $perPage);
        
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
        $users = User::all();

        return view('readFile', [
            'data'         => $pagedData,
            'maxRows'      => $maxRows,
            'currentRows'  => $currentRows,
            'file'         => $file,
            'filteredData' => $filteredData,
            'firstLane'    => $firstLane,
            'currentPage'  => $page,
            'users'        => $users,
        ]);
    
    }
    
    // --------------------------------------------------------------------
    // ----------------Funcion para mostrar archivos en main---------------
    // --------------------------------------------------------------------
    public function loadFiles() {
        if(auth()->user()->name === 'admin') {
            $files = File::orderBy('updated_at', 'desc')->get();
        } else {
            $files = File::where('user_id', auth()->id())->orderBy('updated_at', 'desc')->get();    
        }

        return $files;
    }

    // --------------------------------------------------------------------
    // ------------Funcion para mostrar archivos en compartidos------------
    // --------------------------------------------------------------------
    public function loadSharedFiles() {
        if(auth()->user()->name === 'admin') {
            $files = SharedFile::with('file.user')->orderBy('updated_at', 'desc')->get();
        } else {
            $files = SharedFile::with('file.user')->where('shared', auth()->id())->orderBy('updated_at', 'desc')->get();    
        }

        return $files;
    }

    // -------------------------------------------------------------------------
    // ----Funcion para mostrar archivos en compartidos por el mismo usuario----
    // -------------------------------------------------------------------------
    public function loadMySharedFiles() {
        if(auth()->user()->name === 'admin') {
            $myFiles = SharedFile::with('file.user')->orderBy('updated_at', 'desc')->get();
        } else {
            $myFiles = SharedFile::with('file.user')->where('id_user', auth()->id())->orderBy('updated_at', 'desc')->get();    
        }

        return $myFiles;
    }

    // -------------------------------------------------------------------
    // ----------------Funcion para borrar archivos-----------------------
    // -------------------------------------------------------------------
    public function deleteFile($fileId) {
        $sharedFiles = SharedFile::where('id_file', $fileId)->get();

        foreach ($sharedFiles as $sharedFile) {
            $sharedFile->delete();
        }

        $file = File::find($fileId);

        if ($file) {
            Storage::delete($file->file_path);
            $file->delete();
        }

        return redirect('/mainPage');
    }

    // -------------------------------------------------------------------
    // ----------Funcion para borrar archivos compartidos-----------------
    // -------------------------------------------------------------------
    public function deleteSharedFile($fileId) {
        $file = SharedFile::find($fileId);
        $file->delete();

        return redirect('/mySharedFiles');
    }

    // -------------------------------------------------------------------
    // ----------------Funcion para editar archivos-----------------------
    // -------------------------------------------------------------------
    public function editFileName(Request $request, $id) {
        $file = File::findOrFail($id);
        $file->name = $request->input('newFileName');
        $file->save();

        return redirect('/files/' . $file->id);
    }

    // -------------------------------------------------------------------
    // ----------------Funcion para actualizar celda----------------------
    // -------------------------------------------------------------------
    public function updateCell(Request $request) {

        $fileId    = $request->input('fileId');
        $rowIndex  = $request->input('rowIndex');
        $colIndex  = $request->input('colIndex');
        $newValue  = $request->input('newValue');
        
        $file      = File::find($fileId);
        $filePath  = storage_path('app/' . $file->file_path);

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        /* dd($extension); */

        switch ($extension) {
            case 'xlsx': $extensionCode = 'Xlsx'; break;
            case 'xls':  $extensionCode = 'Xls';  break;
            case 'ods':  $extensionCode = 'Ods';  break;
            case 'txt':  $extensionCode = 'Csv';  break; // PATHINFO_EXTENSION detected by txt from csv
            default: return redirect('/mainPage')->with('extensionError','Extension no soportada');
        }
        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();

        $backupFilePath = storage_path('app/backup/' . $file->name);
        copy($filePath, $backupFilePath);

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

    // ---------------------------------------------------------------------
    // -----------Funcion para aumentar numero de filas---------------------
    // ---------------------------------------------------------------------
    // deprecated
    /* public function increaseRowsInView() {
        $currentRows = session()->get('currentRows', 500);
        $newRows     = $currentRows + 500;
        Session::put('currentRows', $newRows);
        return redirect()->back();
    } */

    // ----------------------------------------------------------------
    // ----------------Funcion para volver al main---------------------
    // ----------------------------------------------------------------
    public function goBack() {
        Session::forget('currentRows');
        return redirect('/mainPage');
    }

    // ----------------------------------------------------------------
    // ----------------Funcion para descargar archivo------------------
    // ----------------------------------------------------------------
    public function downloadFile($fileId) {
        $file     = File::find($fileId);
        $filePath = storage_path('app/' . $file->file_path);
        $fileName = User::find($file->user_id)->name . $file->name;
        return response()->download($filePath, $fileName);
    }

    // ----------------------------------------------------------------------
    // ----------------Funcion para leer y convertir fechas------------------
    // ----------------------------------------------------------------------
    public function readAndConvertDates($fileId) {

        $file = File::find($fileId);

        $filePath  = storage_path('app/' . $file->file_path);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'xlsx': $extensionCode = 'Xlsx'; break;
            case 'xls':  $extensionCode = 'Xls';  break;
            case 'ods':  $extensionCode = 'Ods';  break;
            case 'txt':  $extensionCode = 'Csv';  break; // PATHINFO_EXTENSION detected by txt from csv
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

        return redirect('/files/' . $fileId);
        
    }   

    // -----------------------------------------------------------------
    // --------------Funcion para transformar fechas--------------------
    // -----------------------------------------------------------------
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

    // -----------------------------------------------------------------
    // -------------Funcion para reemplazar columnas--------------------
    // -----------------------------------------------------------------

    public function replaceColumn(Request $request, $id) {
        
        $this->validate($request, [
            'selectedColumn'  => 'required',
            'toReplaceText'   => 'required',
            'replacementText' => 'required',
        ]);

        $selectedColumn  = $request->selectedColumn;
        $selectedText    = $request->toReplaceText;
        $replacementText = $request->replacementText;
        
        $file      = File::findOrFail($id);
        $filePath  = storage_path('app/' . $file->file_path);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    
        switch ($extension) {
            case 'xlsx': $extensionCode  = 'Xlsx'; break;
            case 'xls':  $extensionCode  = 'Xls'; break;
            case 'ods':  $extensionCode  = 'Ods'; break;
            default: return redirect('/mainPage')->with('extensionError', 'Extension no soportada');
        }
    
        $reader      = IOFactory::createReader($extensionCode);
        $spreadsheet = $reader->load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        
        $columnIndex = array_search($selectedColumn, $sheet->toArray()[1]);

        $startRow = 3;

        $backupFilePath = storage_path('app/backup/' . $file->name);
        copy($filePath, $backupFilePath);
        
        foreach ($sheet->getRowIterator() as $row) {
            $rowIndex = $row->getRowIndex();
        
            if ($rowIndex >= $startRow) {
                $cellCoordinate = Coordinate::stringFromColumnIndex($columnIndex + 1) . $rowIndex;
                $cell           = $sheet->getCell($cellCoordinate);
                $cellValue      = $cell->getValue();
        
                if ($cellValue == $selectedText) {
                    
                    $sheet->setCellValue($cellCoordinate, $replacementText);
                }
            }
        }
    
        $writer = IOFactory::createWriter($spreadsheet, $extensionCode);
        $writer->save($filePath);
    
        return redirect('/files/' . $id);
    }
    
    // --------------------------------------------------------
    // -------------------Change Excel date--------------------
    // --------------------------------------------------------
    
    public function replaceExcelDate(Request $request, $id) {
        
        $this->validate($request, [
            'selectedColumn' => 'required',
        ]);
        
        $selectedColumn = $request->selectedColumn;

        $file      = File::findOrFail($id);
        $filePath  = storage_path('app/' . $file->file_path);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $reader      = IOFactory::createReader(ucfirst($extension));
        $spreadsheet = $reader->load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        /* dd($sheet->toArray()[1]); */
        $columnIndex = array_search($selectedColumn, $sheet->toArray()[1]);
        /* dd($columnIndex); */
        /* $lastRow = $sheet->getHighestDataRow($columnIndex); */

        $startRow = 3;

        $backupFilePath = storage_path('app/backup/' . $file->name);
        copy($filePath, $backupFilePath);

        foreach ($sheet->getRowIterator() as $row) {
            $rowIndex = $row->getRowIndex();

            if ($rowIndex >= $startRow) {
                $cellCoordinate = Coordinate::stringFromColumnIndex($columnIndex + 1) . $rowIndex;
                $cell           = $sheet->getCell($cellCoordinate);
                $cellValue      = $cell->getValue();

                if (is_numeric($cellValue)) {

                    $date = Date::excelToDateTimeObject($cellValue);
                    $formattedDate = $date->format('d/m/Y');

                    $sheet->setCellValue($cellCoordinate, $formattedDate);
                }
            }
        }
        /* for ($row = 2; $row <= $lastRow; $row++) {
            $cell  = $sheet->getCell($selectedColumn . $row);
            $value = $cell->getValue();
        
            if (is_numeric($value)) {
                $date = Date::excelToDateTimeObject($value);
                $formattedDate = $date->format('d/m/Y');

                $cell->setValue($formattedDate);
            }
        } */

        $writer = IOFactory::createWriter($spreadsheet, ucfirst($extension));
        $writer->save($filePath);

        return redirect()->back()->with('success', 'Fechas reemplazadas exitosamente.');

    }

    // --------------------------------------------------------
    // ------------------Revert file content-------------------
    // --------------------------------------------------------

    public function revertChanges($id) {
        
        $file = File::findOrFail($id);
        
        $originalFilePath = storage_path('app/' . $file->file_path);
    
        $backupFilePath = storage_path('app/backup/' . $file->name);
        
    
        if (file_exists($backupFilePath)) {
            copy($backupFilePath, $originalFilePath);
            unlink($backupFilePath);
    
            /* dd('Cambios revertidos exitosamente.'); */
            return redirect('/files/' . $file->id);
        } else {
            /* dd('No hay cambios para revertir'); */
            return redirect('/files/' . $file->id);
        }
    }
    
    
    
}
