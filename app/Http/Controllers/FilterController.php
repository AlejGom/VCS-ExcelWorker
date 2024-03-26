<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Session;

class FilterController extends Controller
{
        /**
     * Filtrar datos del archivo Excel por texto.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $fileId
     * @return \Illuminate\Http\Response
     */
    public function filterData(Request $request, $id)
    {
        // Obtener el archivo
        $file     = File::find($id);
        $filePath = storage_path('app/' . $file->file_path);

        // Verificar la existencia del archivo
        if (!file_exists($filePath)) {
            return redirect('/mainPage')->with('error', 'El archivo no existe');
        }

        // Obtener el texto de búsqueda del formulario
        /* $searchText = $request->input('search_text'); */
        if (isset($request->search_text)) {
            $searchText = $request->search_text;
        } else {
            $searchText = '';
        }

        // Comprobar si se ha proporcionado un texto de búsqueda
        if (!$searchText) {
            return redirect()->back()->with('error', 'Por favor, proporcione un texto de búsqueda');
        }

        // Cargar el archivo
        $reader      = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();

        $data = [];
        $rowCount = 0;

        $firstLane = [];
        $firstRow  = $sheet->getRowIterator(2)->current();
        foreach ($firstRow->getCellIterator() as $cell) {
            $firstLane[] = $cell->getValue();
        }

        // Empezar a leer desde la segunda fila
        foreach ($sheet->getRowIterator(3) as $row) {
            $rowData = [$rowCount + 1];
            foreach ($row->getCellIterator() as $cell) {
                $rowData[] = $cell->getValue();
            }
            $data[] = $rowData;
            $rowCount++;
        }

        // Aplicar el filtro por texto
        $filteredData = array_filter($data, function ($row) use ($searchText) {
            foreach ($row as $cellValue) {
                if (stripos($cellValue, $searchText) !== false) {
                    return true;
                }
            }
            return false;
        });

        $currentRows = Session::get('currentRows', 500);
        $maxRows     = count($data);
        /* dd($filteredData); */
        // Retornar vista con los datos filtrados
        return view('readFile', [
            'filteredData' => $filteredData,
            'file'         => $file, 
            'currentRows'  => $currentRows, 
            'maxRows'      => $maxRows, 
            'data'         => $data, 
            'inputText'    => $request->input('search_text'), 
            'firstLane'    => $firstLane]);
    }

    public function filterFiles(Request $request) {

        $searchText = $request->input('search_text');
        $startDate  = $request->input('start_date');
        $endDate    = $request->input('end_date');

        $userId = auth()->user()->id;
        if (auth()->user()->name === 'admin') {
            $query = File::query();
        } else {
            $query = File::where('user_id', $userId);
        }

        if ($searchText) {
            $query->where('name', 'like', '%' . $searchText . '%');
        }

        if ($startDate && $endDate) {
            $query->whereDate('created_at','>=', $startDate)->whereDate('created_at','<=', $endDate);
        }

        $files = $query->orderBy('created_at','desc')->get();
        $filtered = true;

        return view('mainPage', [
            'files'     => $files,
            'inputText' => $searchText,
            'filtered'  => $filtered,
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ]);
    }

    // deprecated
    /* public function filterFiles(Request $request) {
        
        $searchText = $request->input('search_text');

        $userId = auth()->user()->id;
        if (auth()->user()->name === 'admin') {
            $files  = File::orderBy('created_at', 'desc')->get();
        } else {
            $files  = File::where('user_id', $userId)->orderBy('created_at', 'desc')->get();
        }

        $filteredFiles = $files->filter(function ($file) use ($searchText) {
            if (stripos($file->name, $searchText) !== false) {
                return true;
            }
            return false;
        });

        $filtered = true;

        return view('mainPage', [
            'files'     => $filteredFiles,
            'inputText' => $searchText,
            'filtered'  => $filtered
        ]);
    } */

    // deprecated
    /* public function filterByDate(Request $request) {
        
    $startDate = $request->input('start_date');
    $endDate   = $request->input('end_date');

    // Filtrar archivos por rango de fechas
    $userId = auth()->user()->id;
    if (auth()->user()->name === 'admin') {
        $files  = File::whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->get();   
    } else {
        $files  = File::where('user_id', $userId)->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->get();
    }

    $filtered = true;

    // Retornar la vista con los archivos filtrados
    return view('mainPage', [
        'files'     => $files,
        'filtered'  => $filtered,
        'startDate' => $startDate,
        'endDate'   => $endDate,
    ]);
    } */
}
