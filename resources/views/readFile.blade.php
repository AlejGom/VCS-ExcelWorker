@extends('templates.header')

@section('body')
@if (auth()->user()->name !== 'admin')
    @if (auth()->user()->id !== $file->user_id)
        @php
            header('Location: ' . route('goBack'), true, 302);
            exit();
        @endphp
    @endif
@endif
<link rel="stylesheet" href="{{ asset('../resources/css/file.css') }}">

<!-- Container to manage submenu -->
<div class="manageButtonsContainer">
    <!-- Convert dates to specific date -->
    <a class="dateButton" onclick="showLoading()" href="{{ route('readAndConvertDates', $file->id) }}"><button>Cambiar fechas a dd/mm/yyyy</button></a>
    <div class="spaceBetween"></div>
    <!-- Edit file name -->
    <form class="editFileNameForm" action="{{ route('editFileName', $file->id) }}" method="POST">
        @csrf
        <input class="searchInput" type="text" name="newFileName" id="newFileName" value="{{ $file->name }}">
        <a class="editFileNameButton" href="{{ route('editFileName', $file->id) }}"><button>Editar Nombre</button></a>        
    </form>
    <div class="spaceBetween"></div>
    <!-- Filter data by text -->
    <form class="filterDataForm" action="{{ route('filterData', ['id' => $file->id]) }}" method="POST">
        @csrf
        <!-- <label for="search_text">Texto de Búsqueda:</label> -->
        <input class="searchInput" type="text" name="search_text" id="search_text" placeholder="Texto de Búsqueda" @if (isset($inputText)) value="{{ $inputText }}" @endif>
        <button onclick="showLoading()" class="searchButton" type="submit">Búsqueda</button>
    </form>
    <!-- Clean filter -->
    @if ($filteredData)
        <a onclick="showLoading()" href="{{ route('readFile', $file->id) }}"><button class="cleanButton">Limpiar filtro</button></a>
    @endif
</div>
<div class="downloadContainer">
    <p><b>Descargar</b></p>
    <a class="fileName" href="{{ route('downloadFile', $file->id) }}"><b>{{ $file->name }}</b></a>
</div>
<div class="buttonContainer">
    <a href="{{ route('goBack') }}"><button class="backButton">Volver</button></a>
</div>

<!-- --------------------Edit buttons--------------------- -->
<button style="display: none;" class="confirmChangesButton"><img src="{{ asset('../resources/images/cheque.png') }}"></button>
<a style="display: none;" class="deleteButton" href="{{ route('readFile', $file->id) }}"><img src="{{ asset('../resources/images/borrar.png') }}"></a>
<!-- ----------------------------------------------------- -->

<!-- -------------------------gif------------------------- -->
<div id="loadingGif" style="display: none;">
    <img src="{{ asset('../resources/images/loading.gif') }}">
</div>
<!-- ----------------------------------------------------- -->

@if ($filteredData)
    <table class="fileTable">
        <tbody>
            <tr>
                <td class="firstItem"></td>
                @foreach ($firstLane as $cell)
                    <th class="firstItem">{{ $cell }}</th>
                @endforeach
            </tr>
            @foreach ($filteredData as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td class="item">{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
            </tr>
        </tbody>
    </table>
    @else
<table class="fileTable">
    <!-- <thead>
        <tr>
            @foreach ($data[0] as $cell)
                <th>{{ $cell }}</th>
            @endforeach
        </tr>
    </thead> -->
    <tbody>
        <tr>
            <td class="firstItem"></td>
            @foreach ($firstLane as $cell)
                <th class="firstItem">{{ $cell }}</th>
            @endforeach
        </tr>

    @foreach (array_slice($data, 2) as $row)
        <tr>
            @foreach ($row as $cell)
                <td class="item">{{ $cell }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
@endif
<!-- pages buttons -->
@if ($maxRows > $currentRows)
    <!-- <a href="{{ route('increaseRows') }}"><button class="moreButton">Ver más</button></a> -->
    <div class="pagination">
        <a href="{{ route('readFile', ['id' => $file->id, 'page' => 1]) }}" class="arrow">&lt;&lt;</a>
        <a href="{{ route('readFile', ['id' => $file->id, 'page' => max(1, $currentPage - 1)]) }}" class="arrow">&lt;</a>
            @for ($i = max(1, $currentPage - 2); $i <= min($currentPage + 2, ceil($maxRows / $currentRows)); $i++)
                <a href="{{ route('readFile', ['id' => $file->id, 'page' => $i]) }}" class="{{ $currentPage == $i ? 'active' : '' }}">{{ $i }}</a>        
            @endfor
        <a href="{{ route('readFile', ['id' => $file->id, 'page' => min(ceil($maxRows / $currentRows), $currentPage + 1)]) }}" class="arrow">&gt;</a>
        <a href="{{ route('readFile', ['id' => $file->id, 'page' => ceil($maxRows / $currentRows)]) }}" class="arrow">&gt;&gt;</a>
    </div>
    <div class="selectPages">
        <label for="rows">Seleccionar página:</label>
        <select name="rows" id="rows">
            <option value="" disabled selected></option>
            @for ($i = 1; $i <= ceil($maxRows / $currentRows); $i++)
                <option value="{{ $i }}" {{ $currentRows == $i ? 'selected' : '' }}>{{ $i }}</option>
            @endfor
        </select>
    </div>
@else
    <div class="finalSpace">
        <br><br><br>
    </div>
@endif

<!-- ----------------------------------------------------- -->
<!-- ----------------------------------------------------- -->
<!-- -----------------------SCRIPTS----------------------- -->
<!-- ----------------------------------------------------- -->
<!-- ----------------------------------------------------- -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        
        // available edit mode at double click
        $('.fileTable tbody').on('dblclick', 'td', function() {
            var currentValue = $(this).text();
            // currentValue has the value of the cell

            $(this).html('<input type="text" class="editCell" style="color: black" value="' + currentValue + '">');
            $(this).find('.editCell').focus();

            // show buttons
            $('.confirmChangesButton').show();
            $('.deleteButton').show();
        });

        // update cell value on server
        function updateCellValue(rowIndex, colIndex, newValue) {
            
            // sum 100 per page to rowIndex 
            var currentPage = parseInt({{ $currentPage }});
            console.log(currentPage);

            rowIndex += (currentPage - 1) * 100;
            
            console.log(rowIndex, colIndex, newValue);
            // send data to server
            $.ajax({
                url: '{{ route('updateCell') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    fileId: {{ $file->id }},
                    rowIndex: rowIndex,
                    colIndex: colIndex,
                    newValue: newValue
                },
                success: function(response) {
                    console.log(response);
                },
                complete: function() {
                    // restore cell
                    $('.fileTable tbody td.editing').each(function() {
                        var newValue = $(this).find('.editCell').val();
                        $(this).removeClass('editing').text(newValue);
                    });
                    location.reload();
                }
            });
        }

        // deprecated
        // send data at press enter key
        /* $(document).on('keyup', '.editCell', function(event) {
            if (event.keyCode === 13) { // Enter key
                var $cell = $(this).closest('td');
                var newValue = $(this).val();
                var rowIndex = $cell.closest('tr').index();
                var colIndex = $cell.index();
                // send data to server
                updateCellValue(rowIndex, colIndex, newValue);
            }
        }); */

        // send data at click confirm button
        $('.confirmChangesButton').on('click', function() {
            /* console.log('clicked'); */
            $('.fileTable tbody .editCell').each(function() {
                showLoading();
                /* var $cell    = $(this).closest('td');
                var newValue = $(this).val();
                var rowIndex = $cell.closest('tr').index();
                var colIndex = $cell.index(); */
                var $cell    = $(this).closest('td');
                var rowIndex = $cell.closest('tr').index() + $('.fileTable tbody').scrollTop() / $('.fileTable tbody tr').outerHeight();
                var colIndex = $cell.index();
                var newValue = $(this).val();
                // send data to server
                updateCellValue(Math.floor(rowIndex), colIndex, newValue);
            });
        });
    });

    // select pages controll
    document.getElementById("rows").addEventListener("change", function() {
        var selectedPage = this.value; 
        var fileId = {{ $file->id }}; 
        var url = "{{ url('/files/:fileId?page=:page') }}";
        url = url.replace(':fileId', fileId).replace(':page', selectedPage); 
        window.location.href = url;
    });

    // loading gif
    function showLoading() {
        document.getElementById('loadingGif').style.display = 'block';
    }
</script>

@endsection
