<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class FilterController extends Controller {
    /**
     * Filtrar datos del archivo Excel por texto.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $fileId
     * @return \Illuminate\Http\Response
     */
    public function filterData(Request $request, $id)
    {
        // Obtain file
        $file     = File::find($id);
        $filePath = storage_path('app/' . $file->file_path);

        // verify file
        if (!file_exists($filePath)) {
            return redirect('/mainPage')->with('error', 'El archivo no existe');
        }

        // Obtain the text to search from the form
        /* $searchText = $request->input('search_text'); */
        if (isset($request->search_text)) {
            $searchText = $request->search_text;
        } else {
            $searchText = '';
        }

        // verify search text
        if (!$searchText) {
            return redirect()->back()->with('error', 'Por favor, proporcione un texto de búsqueda');
        }

        // load file
        $reader      = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();

        $data        = [];
        $rowCount    = 0;
        $currentPage = Session::get('currentPage', 1);

        $firstLane   = [];
        $firstRow    = $sheet->getRowIterator(2)->current();
        
        foreach ($firstRow->getCellIterator() as $cell) {
            $firstLane[] = $cell->getValue();
        }

        // read data from second row
        foreach ($sheet->getRowIterator(3) as $row) {
            $rowData = [$rowCount + 1];
            foreach ($row->getCellIterator() as $cell) {
                $rowData[] = $cell->getValue();
            }
            $data[] = $rowData;
            $rowCount++;
        }

        // apply filters
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
        $users       = User::all();
        /* dd($filteredData); */
        // return view with data
        return view('readFile', [
            'filteredData' => $filteredData,
            'file'         => $file, 
            'currentRows'  => $currentRows, 
            'maxRows'      => $maxRows, 
            'data'         => $data, 
            'inputText'    => $request->input('search_text'), 
            'firstLane'    => $firstLane,
            'currentPage'  => $currentPage,
            'users'        => $users,
        ]);
    }

    // -----------------------------------------------------------------
    // ------------------funcion para filtrar archivos------------------
    // -----------------------------------------------------------------
    public function filterFiles(Request $request) {

        $searchText = $request->input('search_text');
        $startDate  = $request->input('start_date');
        $endDate    = $request->input('end_date');
        $searchUser = $request->input('search_user');

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

        if ($searchUser) {
            $query->where('user_id', $searchUser);
        }

        $files    = $query->orderBy('created_at','desc')->get();
        $filtered = true;
        $users    = User::all();

        return view('mainPage', [
            'files'      => $files,
            'inputText'  => $searchText,
            'filtered'   => $filtered,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
            'searchUser' => $searchUser,
            'users'      => $users,
        ]);
    }

    // --------------------------------------------------------
    // -----------------------deprecated-----------------------
    // --------------------------------------------------------
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

    // filter by date
    $userId = auth()->user()->id;
    if (auth()->user()->name === 'admin') {
        $files  = File::whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->get();   
    } else {
        $files  = File::where('user_id', $userId)->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->get();
    }

    $filtered = true;

    // return view with data
    return view('mainPage', [
        'files'     => $files,
        'filtered'  => $filtered,
        'startDate' => $startDate,
        'endDate'   => $endDate,
    ]);
    } */
}
