<?php

?>
<div class="row p-3">
    <h3>Configuración de Archivo</h3>
    <div class="col-1 p-1 text-center">
        ID :
    </div>
    <div class="col-5 p-1 text-center">
        <input type="text" class="form-control" id="file_id" readonly>
    </div>

    <div class="col-1 p-1 text-center">
        NOMBRE MODULO:
    </div>
    <div class="col-5">
        <input type="text" class="form-control" id="file_title">
    </div>

    <div class="col-1 p-1 text-center">
        NOMBRE ARCHIVO:
    </div>
    <div class="col-5">
        <input type="text" class="form-control" id="file_name">
    </div>

    <div class="col-1 p-1 text-center">
        RUTA :
    </div>
    <div class="col-5 p-1">
        <input type="text" class="form-control" id="file_route">
    </div>
    
    <!--div class="col-1 p-1 text-center">
        ORDEN :
    </div>
    <div class="col-5 p-1">
        <input type="text" class="form-control">
    </div-->

    <div class="col-1 p-1 text-center">
        LINEA :
    </div>
    <div class="col-2 p-1 text-start">
        <input type="checkbox" id="line_check" style="transform:scale(2)" checked>
    </div>
    <div class="col-1 p-1 text-center">
        LOCAL :
    </div>
    <div class="col-2 p-1 text-start">
        <input type="checkbox" id="local_check" style="transform:scale(2)" checked>
    </div>
    <div class="col-3"></div>
    <div class="col-6 p-1 text-start">
        <button type="button" id="save_table_btn" class="btn btn-success form-control" onclick="saveFileModule();">
            <i class="icon-floppy-1">Guardar</i>
        </button>
    </div>
</div>

<div class="row">
    <h3>Variables</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th class="text-center">Nombre</th>
                <th class="text-center">Tipo</th>
                <th class="text-center"></th>
            </tr>
        </thead>

        <tbody id="file_vars_content">
        </tbody>
        
        <tfoot>
            <tr>
                <td class="text-center">
                    <input type="text" id="new_var_name_input" class="form-control">
                </td>
                <td class="text-center">
                    <select id="new_var_type_select" class="form-select">
                        <option value="0">--SELECIONAR--</option>
                        <option value="int">Número</option>
                        <option value="varchar">Texto</option>
                        <option value="boolean">Check</option>
                    </select>
                </td>
                <td class="text-center">
                    <button
                        type="button"
                        class="btn btn-success"
                        onclick="add_file_var();"
                    >
                        <i class="icon-plus"></i>
                    </button>
                </td>
            </tr>
        </tfoot>
    </table>
</div>

<script>
    function add_file_var(){
        var var_name, var_type;
        var_name = $('#new_var_name_input').val().trim();//.toUpperCase()
        if(var_name.length <= 0){
            alert("El nombre de la variable no puede ser vacio.");
            $('#new_var_name_input').focus();
            return false;
        }
        var_type = $('#new_var_type_select').val();
        if(var_name <= 0){
            alert("Selecciona un tipo de variable.");
            $('#new_var_type_select').focus();
            return false;
        }
        var content = `<tr>
            <td>${var_name}</td>
            <td>${var_type}</td>
            <td></td>
        </tr>`;
        $('#file_vars_content').append(content);
        $('#new_var_name_input').val('');
        $('#new_var_type_select').val('');
    }

    async function saveFileModule(){
        
        var detail = [];
        const formData = new FormData();
        var var_name_, var_type_;
        var content = `<div class="text-center">
            <h2 class="text-center">Guardando...</h2>
            <br>
            <br>
            <img src="../../../../img/img_casadelasluces/load.gif" width="40%">
        </div>`;
        formData.append(`config_flag`, `saveFileModule`);//json de detalle de campos
        formData.append(`file_name`, $(`#file_name`).val());//nombre de tabla
        formData.append(`file_route`, $(`#file_route`).val());//condicion
        formData.append(`file_title`, $(`#file_title`).val());//campo de titulo
        formData.append(`line_check`, ($(`#line_check`).prop('checked') ? '1' : '0'));//linea
        formData.append(`local_check`, ($(`#local_check`).prop('checked') ? '1' : '0'));//local
        setTimeout(function(){
            var all_is_valid = true;
            $('#file_vars_content tr').each(function(index){
                $(this).children('td').each(function(index2){
                    if(index2 == 0){
                        var_name_ = $(this).html().trim();
                    }else if(index2 == 1){
                        var_type_ = $(this).html().trim();
                    }
                    
                });
                detail.push({
                    var_name : var_name_,
                    var_type : var_type_
                });
            });
            formData.append(`module_vars`, JSON.stringify(detail));//json de detalle de campos
            if(!all_is_valid){
                alert("Hay datos sin llenar en los campos.");
                return false;
            }
            $.ajax({
                url: "./include/specials/configuracion_ambiente/ajax/ConfiguracionAmbiente.php",
                type: "POST",
                data: formData,
                contentType: false, // OBLIGATORIO
                processData: false, // OBLIGATORIO
                success: function (response) {
                    alert("Resp : " + response);
                    console.log(response);
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                }
            });
        }, 300);
    }
</script>