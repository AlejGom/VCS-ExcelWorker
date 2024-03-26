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
<div class="downloadContainer">
    <p><b>Descargar</b></p>
    <a class="fileName" href="{{ route('downloadFile', $file->id) }}"><b>{{ $file->name }}</b></a>
</div>
<div class="buttonContainer">
    <a href="{{ route('goBack') }}"><button class="backButton">Volver</button></a>
</div>
<div class="searchContainer">
    <form action="{{ route('filterData', ['id' => $file->id]) }}" method="POST">
        @csrf
        <!-- <label for="search_text">Texto de Búsqueda:</label> -->
        <input class="searchInput" type="text" name="search_text" id="search_text" placeholder="Texto de Búsqueda" @if (isset($inputText)) value="{{ $inputText }}" @endif>
        <button  class="searchButton" type="submit">Búsqueda</button>
    </form>
    @if ($filteredData)
        <a href="{{ route('readFile', $file->id) }}"><button class="cleanButton">Limpiar filtro</button></a>
    @endif
</div>
<button class="confirmChangesButton"><img src="{{ asset('../resources/images/cheque.png') }}"></button>
<a class="deleteButton" href="{{ route('readFile', $file->id) }}"><img src="{{ asset('../resources/images/borrar.png') }}"></a>
@if ($filteredData)
    <table class="fileTable">
        <tbody>
            <tr>
                <td class="firstItem"></td>
                @foreach ($firstLane as $cell)
                    <td class="firstItem">{{ $cell }}</td>
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
                <td class="firstItem">{{ $cell }}</td>
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
@if ($maxRows > $currentRows)
    <a href="{{ route('increaseRows') }}"><button class="moreButton">Ver más</button></a>
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
        
        // Habilitar la edición de celdas al hacer doble clic
        $('.fileTable tbody').on('dblclick', 'td', function() {
            var currentValue = $(this).text();
            $(this).html('<input type="text" class="editCell" value="' + currentValue + '">');
            $(this).find('.editCell').focus();
        });

        // Capturar los cambios y enviar al servidor al hacer clic en el botón de confirmación
        $('.fileTable tbody').on('click', '.confirmButton', function() {
            var $cell    = $(this).closest('td');
            var newValue = $cell.find('.editCell').val();
            var rowIndex = $(this).closest('tr').index();
            var colIndex = $(this).closest('td').index();
            // Enviar los datos al servidor
            updateCellValue(rowIndex, colIndex, newValue);
        });

        // Función para actualizar el valor de la celda en el servidor
        function updateCellValue(rowIndex, colIndex, newValue) {
            // Enviar los datos al servidor
            $.ajax({
                url: '{{ route('updateCell') }}', // Ruta para manejar la actualización en el servidor
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    fileId: {{ $file->id }},
                    rowIndex: rowIndex,
                    colIndex: colIndex,
                    newValue: newValue
                },
                success: function(response) {
                    // Manejar la respuesta del servidor si es necesario
                    console.log(response);
                },
                complete: function() {
                    // Restaurar la celda a su estado original
                    $('.fileTable tbody td.editing').each(function() {
                        var newValue = $(this).find('.editCell').val();
                        $(this).removeClass('editing').text(newValue);
                    });
                }
            });
        }

        // Capturar los cambios y enviar al servidor al presionar Enter
        /* $(document).on('keyup', '.editCell', function(event) {
            if (event.keyCode === 13) { // Enter key
                var $cell = $(this).closest('td');
                var newValue = $(this).val();
                var rowIndex = $cell.closest('tr').index();
                var colIndex = $cell.index();
                // Enviar los datos al servidor
                updateCellValue(rowIndex, colIndex, newValue);
            }
        }); */

        // Capturar los cambios y enviar al servidor al hacer clic en el botón "Confirmar Cambios"
        $('.confirmChangesButton').on('click', function() {
            $('.fileTable tbody .editCell').each(function() {
                var $cell    = $(this).closest('td');
                var newValue = $(this).val();
                var rowIndex = $cell.closest('tr').index();
                var colIndex = $cell.index();
                // Enviar los datos al servidor
                updateCellValue(rowIndex, colIndex, newValue);
            });
        });
    });
</script>

@endsection
