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
    <!-- ctrl + Z -->
    <a class="dateButton" href="{{ route('revertChanges', $file->id) }}" class="btn btn-danger"><button>Deshacer</button></a>
    <div class="spaceBetween"></div>

    <!-- Convert dates to specific date -->
    <a class="dateButton" onclick="toggleReplaceColumnForm()"><button>Reemplazar columna</button></a>
    <div class="spaceBetween"></div>

    <a class="dateButton" onclick="toggleReplaceExcelDate()"><button>Modificar fechas</button></a>
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
<div class="editButtonsContainer">
<button style="display: none;" class="confirmChangesButton"><img src="{{ asset('../resources/images/ahorrar.png') }}"></button>
<a style="display: none;" class="deleteButton" href="{{ route('readFile', $file->id) }}"><img class="deleteButton" src="{{ asset('../resources/images/borrar.png') }}"></a>
</div>
<p class="editInfo" Style="display: none;">Recuerda editar las celdas de una en una, gracias</p>
<!-- ----------------------------------------------------- -->

<!-- Reemplazar datos columnas de excel -->
<div class="replaceContainer" id="replaceColumnFormContainer" style="display: none;">
    <form id="replaceColumnForm" action="{{ route('replaceColumn', ['id' => $file->id]) }}" method="POST">
        @csrf
        <select class="searchInput" name="selectedColumn" id="selectedColumn">
            <option value="" disabled selected>Selecciona una columna</option>
            @foreach ($firstLane as $column)
                <option value="{{ $column }}">{{ $column }}</option>
            @endforeach
        </select><br><br>
    
        <input class="searchInput" type="text" id="toReplaceText" name="toReplaceText" placeholder="Texto a reemplazar..."><br><br>

        <input class="searchInput" type="text" id="replacementText" name="replacementText" placeholder="Reemplazar por..."><br><br>
        <button class="searchButton" type="submit">Reemplazar</button>
    </form>
</div>

<!-- Reemplazar fechas de excel -->
<div class="replaceContainer" id="replaceExcelDates" style="display: none;">
    <form id="replaceColumnForm" action="{{ route('replaceExcelDate', ['id' => $file->id]) }}" method="POST">
        @csrf
        <select class="searchInput" name="selectedColumn" id="selectedColumn">
            <option value="" disabled selected>Selecciona una columna</option>
            @foreach ($firstLane as $column)
                @if ($column && (strpos(strtolower($column), 'fecha') !== false))
                    <option value="{{ $column }}">{{ $column }}</option>
                @endif
            @endforeach
        </select><br><br>

        <button class="searchButton" type="submit">Reemplazar</button>
    </form><br>

    <div class="spaceUnder"></div><br>

    <a class="dateButton" onclick="showLoading()" href="{{ route('readAndConvertDates', $file->id) }}"><button>Cambiar todas las fechas a dd/mm/yyyy</button></a>
</div>

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
    // Array para almacenar las posiciones editadas
    var editedPositions = [];
    var newValues = [];

    // Función para actualizar el valor de la celda en el servidor
    function updateCellValue(rowIndex, colIndex, newValue) {
        $.ajax({
            url: '{{ route('updateCell') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                fileId: {{ $file->id }},
                editedPositions: editedPositions, // Envía el array de posiciones editadas
                newValues: newValues // Envía el array de nuevos valores
            },
            success: function(response) {
                console.log(response);
            },
            complete: function() {
                // Restaura las celdas editadas
                $('.fileTable tbody td.editing').each(function() {
                    var newValue = $(this).find('.editCell').val();
                    $(this).removeClass('editing').text(newValue);
                });
                /* location.reload(); // Recarga la página */
                console.log(editedPositions);
                console.log(newValues);
            }
        });
    }

// Variable para controlar si se permite el doble clic
var doubleClickEnabled = true;

// Manejador de eventos para el evento de doble clic en las celdas
$('.fileTable tbody').on('dblclick', 'td', function() {
    if (doubleClickEnabled) {
        var currentValue = $(this).text();
        $(this).html('<input type="text" class="editCell" style="color: black" value="' + currentValue + '">');
        $(this).find('.editCell').focus();

        // Muestra los botones de confirmación de cambios y eliminación
        $('.confirmChangesButton').show();
        $('.deleteButton').show();
        $('.editInfo').show();

        // Obtiene las coordenadas de la celda editada y las agrega al array de posiciones editadas
        var rowIndex = $(this).closest('tr').index();
        var colIndex = $(this).index();
        var position = { rowIndex: rowIndex, colIndex: colIndex };
        editedPositions.push(position);

        // Desactiva temporalmente el doble clic
        doubleClickEnabled = false;

        // Habilita el doble clic después de un breve retraso
        setTimeout(function() {
            doubleClickEnabled = true;
        }, 1600); // 1600 milisegundos (1.6 segundo)
    }
});


    // Manejador de eventos para el botón de confirmación de cambios
    $('.confirmChangesButton').on('click', function() {
        $('.fileTable tbody .editCell').each(function() {
            showLoading();
            var $cell = $(this).closest('td');
            var newValue = $(this).val();
            var rowIndex = $cell.closest('tr').index();
            var colIndex = $cell.index();
            newValues.push(newValue); // Agrega el nuevo valor al array newValues
            updateCellValue(rowIndex, colIndex, newValue);
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

    /* function openPopupForm() {
        var popup = window.open('', '_blank', 'width=600,height=400');

        var formHtml = `
            <html>
            <head>
                <title>Formulario de Reemplazo de Columna</title>
                <style>
                    body { font-family: Arial, sans-serif; width: 400px; height: 600px; }
                    form { margin: 20px; }
                    label { display: block; margin-bottom: 10px; }
                    input[type="text"] { width: 100%; padding: 8px; margin-bottom: 20px; }
                    select { width: 100%; padding: 8px; margin-bottom: 20px; }
                    button { padding: 10px 20px; background-color: #007bff; color: white; border: none; cursor: pointer; }
                </style>
            </head>
            <body>
                <h2>Formulario de Reemplazo de Columna</h2>
                <form id="replaceColumnForm" action="{{ route('replaceColumn', ['id' => $file->id]) }}" method="POST">
                    <label for="selectedColumn">Selecciona la columna a reemplazar:</label>
                    <select name="selectedColumn" id="selectedColumn">
                        <option value="" disabled selected>Selecciona una columna</option>
                        @foreach ($firstLane as $column)
                            <option value="{{ $column }}">{{ $column }}</option>
                        @endforeach
                    </select>
                    <label for="replacementText">Texto de reemplazo:</label>
                    <input type="text" id="replacementText" name="replacementText" placeholder="Ingrese el texto de reemplazo...">
                    <button type="submit">Reemplazar</button>
                </form>
            </body>
            </html>
        `;

        popup.document.write(formHtml);

        popup.document.getElementById('replaceColumnForm').addEventListener('submit', function(event) {
            event.preventDefault();

            var selectedColumn  = popup.document.getElementById('selectedColumn').value;
            var replacementText = popup.document.getElementById('replacementText').value;
            
            $.ajax({
                url: '{{ route('replaceColumn', ['id' => $file->id]) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    selectedColumn: selectedColumn,
                    replacementText: replacementText
                },
                success: function(response) {
                    
                    if (response.success) {
                        
                        popup.close();
                        
                        location.reload();
                    } else {
                        
                        popup.close();

                        window.location = '{{ route('mainPage') }}';
                        
                    }
                },
                error: function(xhr, status, error) {

                    console.error('Error en la solicitud AJAX:', error);
                }
            });
        });
    } */

    function toggleReplaceColumnForm() {
        var form = document.getElementById('replaceColumnFormContainer');
        if (form.style.display === 'none') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }

    function toggleReplaceExcelDate() {
        var form = document.getElementById('replaceExcelDates');
        if (form.style.display === 'none') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }
</script>
@endsection
