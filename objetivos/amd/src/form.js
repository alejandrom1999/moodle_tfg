$(document).ready(function() {
    $('#id_my_form').submit(function(event) {
        event.preventDefault(); // Evita la recarga de la página
        $.ajax({
            url: '/blocks/objetivos/block_objetivos.php',
            data: {action: 'my_form', formdata: $(this).serialize()},
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                if (data.status == 'ok') {
                    alert(data.message); // Muestra un mensaje de confirmación
                }
            }
        })
    }
}