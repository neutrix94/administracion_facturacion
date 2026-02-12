    
    function show_new_db_config_form(){
        var content = `<div class="row">
            <div class="col-4 text-end">
            </div>
            <div class="col-4">
                <br>
                <h3 class="text-center">Tipo de Módulo :</h3>
                <br>
                <select id="new_module_type_select" class="form-select" onchange="enable_new_module_btn(this);">
                    <option value="0">--SELECCIONAR--</option>
                    <option value="BASE_DATOS">Base de Datos</option>
                    <option value="ARCHIVO">Archivo de Configuración</option>
                </select>
                <br>
                <button
                    type="button"
                    id="new_module_continue_btn"
                    class="btn btn-secondary form-control"
                    onclick=""
                    disabled
                >
                    <i class="icon-right-big">Continuar</i>
                </button>
                <br>
                <br>
                <button
                    type="button"
                    id=""
                    class="btn btn-danger form-control"
                    onclick="close_emergent();"
                >
                    <i class="icon-cancel">Cancelar y cerrar</i>
                </button>
            </div>
        </div>`;
        show_emergent(content, false);
    }

    function enable_new_module_btn(obj){
        var new_module_type = $(obj).val();
        if(new_module_type == 0 || new_module_type == '0' || new_module_type == ''){
            $('#new_module_continue_btn').attr('disabled', true);
            $('#new_module_continue_btn').attr('onclick', '');
            $('#new_module_continue_btn').removeClass('btn-success');
            $('#new_module_continue_btn').addClass('btn-secondary');
        }else{
            $('#new_module_continue_btn').removeAttr('disabled');
            $('#new_module_continue_btn').attr('onclick', `show_module_form('${new_module_type}', false)`);
            $('#new_module_continue_btn').removeClass('btn-secondary');
            $('#new_module_continue_btn').addClass('btn-success');
        }
    }

    function show_module_form(module_type, module_id){
        if(module_type == "BASE_DATOS"){
            $('#emergent_content').load('./include/specials/configuracion_ambiente/ajax/db_form.php', {module_id: module_id});
        }else if(module_type == "ARCHIVO"){
            $('#emergent_content').load('./include/specials/configuracion_ambiente/ajax/file_form.php', {module_id: module_id});
        }
        $('#emergent').css('display', 'block');
    }

    function show_tables_seeker_response(data_json){
        if(data_json.status == 200 || data_json.status == '200'){
            var content = ``;
            for (const key in data_json.tables) {
                content += `<div class="seeker_row" onclick="setTable('${data_json.tables[key].table_name}');">${data_json.tables[key].table_name}</div>`;
            }
            $('#table_seeker_response').empty();
            $('#table_seeker_response').html(content);
            $('#table_seeker_response').css('display', 'block');
        }
    }

    function build_table_rows(data_json){
        var content = ``, options = `<option value="-1">--SELECCIONAR--</option>`;
        for(const key in data_json.columns){
            content += `<tr id="row_${key}">
                <td class="text-center">
                    <input type="checkbox" id="use_${key}" ${(data_json.columns[key].column_key == "PRI" ?  "checked disabled " : "")}>
                </td>
                <td class="text-end">${data_json.columns[key].column_order}</td>
                <td column_key="${data_json.columns[key].column_key}" id="name_${key}">${data_json.columns[key].column_name}</td>
                <td id="data_type_${key}">${data_json.columns[key].column_data_type}</td>
                <td class="text-center">
                    <input type="checkbox" id="is_required_${key}" checked>
                </td>
                <td>
                    <textarea style="position:relative;width:100%;" id="help_${key}"></textarea>
                </td>
                <td class="text-center">
                    <!--button
                        type="button"
                        class="btn btn-secondary"
                    >
                    </button-->
                    <input type="file" id="file_${key}">
                </td>
            </tr>`;
            options += `<option value="${data_json.columns[key].column_name}">${data_json.columns[key].column_name}</option>`;
        }
        $('#fields_content').empty();
        $('#fields_content').html(content);
        $('#title_field').empty();
        $('#title_field').html(options);
    }

    function build_modules_forms(modules){
        var content = ``;
        tables_metadata = modules;//variable global para exportar / guardar datos

        for (const key0 in modules) {
            content += `<div class="">
                <hr>
                <h3 class="text-center">${modules[key0].module_name}</h3>
                <hr>`;
            for (const key2 in modules[key0].module_tables) {
                var title_key = modules[key0].module_tables[key2].table_title_field;
                var counter = 0;
                for (const key3 in modules[key0].module_tables[key2].values) {
                    content += `<h5 class="text-start">${modules[key0].module_tables[key2].values[key3][title_key]} 
                        <button type="button" class="btn-warning" onclick="module_table_edit(${modules[key0].module_tables[key2].module_table_config_id});"><i class="icon-edit"></i></button></h5>
                        <div class="row">`;
                    var counter_2 = 0;
                    var new_row_disabled = "";
                    var new_row_value = "";
                    var comprobation_button_class = "";
                    for (const key4 in modules[key0].module_tables[key2].values[key3]) {
                        disabled_row = (counter_2 == 0 ? "disabled" : "");
                        new_row_value = (counter_2 == 0 ? modules[key0].module_tables[key2].values[key3][key4] : "");
                        comprobation_button_class = (counter_2 == 0 ? "btn-success icon-ok" : "btn-secondary icon-cancel");
                        content += `<div class="col-2 p-1 text-center" id="${modules[key0].module_tables[key2].table_name}-${key4}-field_name-${counter}">
                            ${key4}
                        </div>
                        <div class="col-4 p-1 text-center">
                            <input type="text" class="form-control" id="${modules[key0].module_tables[key2].table_name}-${key4}-old-${counter}" value="${modules[key0].module_tables[key2].values[key3][key4]}" readonly>
                        </div>
                        <div class="col-6 p-1 text-center">
                            <div class="input-group">
                                <input type="text" class="form-control" id="${modules[key0].module_tables[key2].table_name}-${key4}-new-${counter}" value="${new_row_value}" ${disabled_row}>
                                <button
                                    type="button"
                                    id="${modules[key0].module_tables[key2].table_name}-${key4}-button-${counter}"
                                    class="btn ${comprobation_button_class}"
                                >
                                </button>
                            </div>
                        </div>`;
                        counter_2 ++;
                    }
                    counter ++;
                    content += `<div>
                        <br><br>`;
                }
            }
            content += `</div>`;
        }
        $('#tables_module_container').html(content);
    }

    function build_files_modules_forms(files){
        var content = ``;
        var counter = 0;
        files_metadata = files;//variable global para exportar / guardar datos

        for (const key0 in files) {
            var counter_2 = 0;
            var new_row_disabled = "";
            var new_row_value = "";
            var comprobation_button_class = "";
            content += `<div class="">
                <hr>
                <h3 class="text-center">${files[key0].file_module_name} <button type="button" class="btn-warning" onclick="file_module_edit(${files[key0].file_module_config_id});"><i class="icon-edit"></i></button></h3>
                <div class="text-center"><button type="button" class="btn btn-info" onclick="file_module_download(${counter});"><i class="icon-download">Descargar</i></button></div>
                <hr>
                <div class="row">`;
            for (const key1 in files[key0].file_vars) {
                disabled_row = (counter_2 < 0 ? "disabled" : "");
                new_row_value = (counter_2 == 0 ? files[key0].file_vars[key1].var_value : "");
                comprobation_button_class = (counter_2 == 0 ? "btn-success icon-ok" : "btn-secondary icon-cancel");
                    content += `<div class="col-2 p-1 text-center" id="">
                            ${files[key0].file_vars[key1].var_name}
                        </div>
                        <div class="col-4 p-1 text-center">
                            <input type="text" class="form-control" id="file_${files[key0].file_module_config_id}-old-${counter_2}" value="${files[key0].file_vars[key1].var_value}" readonly>
                        </div>
                        <div class="col-6 p-1 text-center">
                            <div class="input-group">
                                <input type="text" class="form-control" id="file_${files[key0].file_module_config_id}-new-${counter_2}" ${disabled_row}>
                                <!--button
                                    type="button"
                                    id="file_${files[key0].file_module_config_id}-button-${counter_2}"
                                    class="btn ${comprobation_button_class}"
                                >
                                </button-->
                            </div>
                        </div>`;
                //}
                //content += `<div>
                //    <br><br>`;
                counter_2 ++;
            }
            counter ++;

            content += `</div>
                </div>`;
        }
        $('.config_content_2').html(content);
    }

    function show_emergent(content, show_close_btn = false){
        $('#emergent_content').html(content);
        $('#emergent').css("display", "block");
        $('#emergent_close_btn').css("display", (show_close_btn ? "block" : "none"));
    }
    
    function close_emergent(){
        $('#emergent_content').html('');
        $('#emergent').css("display", "none");
        $('#emergent_close_btn').css("display", "none");
    }