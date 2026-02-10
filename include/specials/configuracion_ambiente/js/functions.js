var tables_metadata = null;
var files_metadata = null;

    function bloquearPegar(event) {
        event.preventDefault(); // Evita la acción de pegar
        alert("No puedes pegar texto aquí.");
    }

    function initial_validation(){
        var current_db_name, db_name_comprobation, current_git_branch, git_branch_comprobation;
        current_db_name = $('#current_db_name').val().trim();
        db_name_comprobation = $('#db_name_comprobation').val().trim();
        if(current_db_name != db_name_comprobation){
            alert("la base de datos no coincide en la comprobación.");
            $('#current_db_name').select();
            return false;
        }

        current_git_branch = $('#current_git_branch').val().trim();
        git_branch_comprobation = $('#git_branch_comprobation').val().trim();
        if(current_git_branch != git_branch_comprobation){
            alert("La rama de GIT no coincide en la comprobación.");
            $('#git_branch_comprobation').select();
            return false;
        }
        alert("Válido.");
    }

    async function seekTable(e){
        if(e != 'intro' && e.keyCode != 13){
            return false;
        }
        var text = $('#table_seeker_input').val().trim();
        if(text.length <= 2){
            return false;
        }else{
            var url = `./include/specials/configuracion_ambiente/ajax/ConfiguracionAmbiente.php?config_flag=seekTables&text=${text}`;
            var resp = await ajaxR(url);
            data_json = JSON.parse(resp);
            if(data_json.status && (data_json.status == 200 || data_json.status == '200')){
                show_tables_seeker_response(data_json);
            }else{
                if(data_json.status){
                    $('#table_seeker_response').html(data_json.message);
                    $('#table_seeker_response').css('display', 'block');
                }else{
                    alert("Error : \n" + resp);
                    $('#table_seeker_response').html('');
                    $('#table_seeker_response').css('display', 'none');
                }
            }
        }
    }

    async function setTable(table_name){
        $('#table_seeker_input').val(table_name);
        $('#table_seeker_input').attr('disabled', true);
        $('#table_seeker_response').html('');
        $('#table_seeker_response').css('display', 'none');
        await getTableColumns(table_name);
    }

    async function getTableColumns(table_name){
        var url = `./include/specials/configuracion_ambiente/ajax/ConfiguracionAmbiente.php?config_flag=getTableColumns&table_name=${table_name}`;
        var resp = await ajaxR(url);
        data_json = JSON.parse(resp);
        if(data_json.status && (data_json.status == 200 || data_json.status == '200')){
            build_table_rows(data_json);
        }else{
            alert("Error : \n" + resp);
            location.reload();
        }
    }

    async function saveTableModule(){
        var detail = [];
        const formData = new FormData();
        var field_name_, data_type_, required_, help_text_;
        var content = `<div class="text-center">
            <h2 class="text-center">Guardando...</h2>
            <br>
            <br>
            <img src="../../../../img/img_casadelasluces/load.gif" width="40%">
        </div>`;
        formData.append(`config_flag`, `saveTableModule`);//json de detalle de campos`
        formData.append(`config_module_id`, $(`#config_module_id`).val());//
        formData.append(`table_name`, $(`#table_seeker_input`).val());//nombre de tabla
        formData.append(`where_table_module`, $(`#where_table_module`).val());//condicion
        formData.append(`title_field`, $(`#title_field`).val());//campo de titulo
        formData.append(`line_check`, ($(`#line_check`).prop('checked') ? '1' : '0'));//linea
        formData.append(`local_check`, ($(`#local_check`).prop('checked') ? '1' : '0'));//local
        setTimeout(function(){
            var all_is_valid = true;
            $('#fields_content tr').each(function(index){
                if($(`#use_${index}`).prop('checked')){
                    field_name_ = $(`#name_${index}`).html().trim();
                    if(field_name_ == ""){
                        all_is_valid = false;
                        return false;
                    }
                    data_type_ = $(`#data_type_${index}`).html().trim();
                    if(data_type_ == ""){
                        all_is_valid = false;
                        return false;
                    }
                    required_ = ($(`#is_required_${index}`).prop('checked') ? '1' : '0');
                    if(required_ == null){
                        all_is_valid = false;
                        return false;
                    }
                    help_text_ = $(`#help_${index}`).html().trim();
                    if(required_ == null){
                        all_is_valid = false;
                        return false;
                    }
                    detail.push({
                        field_name : field_name_,
                        data_type : data_type_,
                        required : required_,
                        help_text : help_text_
                    });
                    formData.append(`imagen_${field_name_}`, $(`#file_${index}`).val());
                }
            });
            formData.append(`detail_json`, JSON.stringify(detail));//json de detalle de campos
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

    async function getConfigModules(){
    //tablas
        var url = `./include/specials/configuracion_ambiente/ajax/ConfiguracionAmbiente.php?config_flag=getConfigModules`;
        var resp = await ajaxR(url);
        var json_data = JSON.parse(resp);
        if(json_data.status && (json_data.status == '200' || json_data.status == 200)){

            build_modules_forms(json_data.modules);
        }else{
            alert("Error : \n" + resp);
            return false;
        }
    //archivos
        var url = `./include/specials/configuracion_ambiente/ajax/ConfiguracionAmbiente.php?config_flag=getConfigFiles`;
        var resp = await ajaxR(url);
        var json_data = JSON.parse(resp);
        if(json_data.status && (json_data.status == '200' || json_data.status == 200)){
            console.log(json_data);
            build_files_modules_forms(json_data.modules);
        }else{
            alert("Error : \n" + resp);
            return false;
        }
    }

    function module_table_edit(module_table_id){
        alert("module_table_edit : " + module_table_id);
    }

    function file_module_edit(file_module_id){
        alert("file_module_edit : " + file_module_id);
    }
    function json_export( export_ = false){
        if(export_ == false){
            var content = `<div class="row">
                <div class="col-2"></div>
                <div class="col-8 text-center">
                    <h3 class="text-center">Nombre del archivo</h3>
                    <br>
                    <div class="input-group">
                        <input type="text" id="json_export_name" class="form-control text-end">
                        <button
                            type="button"
                            class="btn"
                        >
                            .json
                        </button>
                    </div>
                    <br>
                    <button
                        type="button"
                        class="btn btn-success form-control"
                        onclick="json_export(true);"
                    >
                        <i class="icon-download">Exportar</i>
                    </button>
                </div>
            </div>`;
            show_emergent(content, true);
        }else{
            json_name = $('#json_export_name').val().trim().replace('.json', '');
            json_name =json_name.replaceAll(' ', '_');
            if(json_name.length <= 0){
                alert("El nombre del archivo no puede ir vacio.");
                $('#json_export_name').select();
                return false;
            }
            json_name += `.json`;
            getBeforeModuleTableData(json_name);
        }
    }

    function getBeforeModuleTableData(nombreArchivo = 'data.json'){
        var file_data = {
            modules_tables : tables_metadata,
            files_modules : files_metadata
        };
        console.log(JSON.stringify(file_data));
        const blob = new Blob(
            [JSON.stringify(file_data, null, 2)],
            { type: "application/json" }
        );
        const a = document.createElement("a");
        a.href = URL.createObjectURL(blob);
        a.download = nombreArchivo;
        a.click();
        URL.revokeObjectURL(a.href);
    }

    function json_import(){
        $('#json_file_import').click();
    }

    function modulesTablesImport(modules) {//aqui es donde se procesa el json
        //console.log("JSON cargado:", modules);
        for (const key0 in modules) {
            //alert(modules[key0].module_name);
            for (const key2 in modules[key0].module_tables) {
                var counter = 0;
                //alert(modules[key0].module_tables[key2].table_name);
                for (const key3 in modules[key0].module_tables[key2].values) {
                    //console.log(modules[key0].module_tables[key2].values[key3]);
                    for (const key4 in modules[key0].module_tables[key2].values[key3]) {
                        console.log(`${key4} : ${modules[key0].module_tables[key2].values[key3][key4]}`);
                        $(`#${modules[key0].module_tables[key2].table_name}-${key4}-new-${counter}`).val(modules[key0].module_tables[key2].values[key3][key4]);
                    //quitar y poner nueva clase a iconos
                        $(`#${modules[key0].module_tables[key2].table_name}-${key4}-button-${counter}`).removeClass('icon-ok');
                        $(`#${modules[key0].module_tables[key2].table_name}-${key4}-button-${counter}`).removeClass('icon-cancel');
                        $(`#${modules[key0].module_tables[key2].table_name}-${key4}-button-${counter}`).removeClass('btn-success icon-ok');
                        $(`#${modules[key0].module_tables[key2].table_name}-${key4}-button-${counter}`).removeClass('btn-secondary');
                        if(modules[key0].module_tables[key2].values[key3][key4] == tables_metadata[key0].module_tables[key2].values[key3][key4]){
                            $(`#${modules[key0].module_tables[key2].table_name}-${key4}-button-${counter}`).addClass('btn-success icon-ok');
                        }else{
                            $(`#${modules[key0].module_tables[key2].table_name}-${key4}-button-${counter}`).addClass('btn-info icon-ok');
                        }
                    }
                    counter ++;
                }
            }
        }
    }

    function fileModulesImport(files_modules){
        var content = ``;
        var counter = 0;
        //files_metadata = files;//variable global para exportar / guardar datos
        for (const key0 in files_modules) {
            var counter_2 = 0;
            for (const key1 in files_modules[key0].file_vars) {
                $(`#file_${files_modules[key0].file_module_config_id}-new-${counter_2}`).val(`${files_modules[key0].file_vars[key1].var_value}`);
                counter_2 ++;
            }
            counter ++;
        }
    }

    function saveConfigurations(){
    //recopilar informacion de acuerdo a la metadata (por modulo y tabla)
        //console.log(tables_metadata);
        var modules_to_save = {};
        var files_to_save = {};
    //obtiene datos de tablas
        for (const key0 in tables_metadata) {
            modules_to_save[tables_metadata[key0].module_name] = [];
            for (const key2 in tables_metadata[key0].module_tables) {
                var counter = 0;
                //var table_name = tables_metadata[key0].module_tables[key2].table_name;
                for (const key3 in tables_metadata[key0].module_tables[key2].values) {
                    var data_array = {};
                    var counter_2 = 0;
                    for (const key4 in tables_metadata[key0].module_tables[key2].values[key3]) {
                        if(counter_2 == 0){
                            data_array.table_primary_key = key4;
                            data_array.table_name_data = tables_metadata[key0].module_tables[key2].table_name;
                        }
                        var new_value = $(`#${tables_metadata[key0].module_tables[key2].table_name}-${key4}-new-${counter}`).val();
                        if(new_value.trim().length <= 0){
                            alert(`El valor '${key4}' del modulo '${tables_metadata[key0].module_name}' no pueden ir vacio`);
                            $(`#${tables_metadata[key0].module_tables[key2].table_name}-${key4}-new-${counter}`).focus();
                            return false;
                        }
                        data_array[key4] = new_value;
                        counter_2 ++;
                    }
                    modules_to_save[tables_metadata[key0].module_name].push(data_array);
                    counter ++;
                }
            }
        }
    //obtiene datos de archivos
        
        var counter = 0;
        /*for (const key0 in files_metadata) {//archivos
            files_to_save[key0] = {};
            files_to_save[key0].name = files_metadata[key0].file_name;
            files_to_save[key0].vars = {};
            var data_array = {};
            var counter_2 = 0;
            for (const key1 in files_metadata[key0].file_vars) {//variables
                data_array.file_var_primary_key = files_metadata[key0].file_vars[key1].file_module_var_id;
                var new_value = $(`#file_${files_metadata[key0].file_module_config_id}-new-${counter_2}`).val();
                data_array.file_var_value = new_value;
                if(new_value.trim().length <= 0){
                    alert(`El valor '${key1}' del modulo '${files_metadata[key0].file_module_name}' no pueden ir vacio`);
                    $(`#file_${files_metadata[key0].file_module_config_id}-new-${counter_2}`).focus();
                    return false;
                }
                counter_2 ++;
                files_to_save[key0].vars.push(data_array);
            }
            counter ++;
        }*/
            for (const key0 in files_metadata) {
                files_to_save[key0] = {};
                files_to_save[key0].name = files_metadata[key0].file_name;
                files_to_save[key0].vars = [];
            
                let counter_2 = 0;
            
                for (const key1 in files_metadata[key0].file_vars) {
                    let data_array = {};
                    data_array.file_var_primary_key = files_metadata[key0].file_vars[key1].file_module_var_id;
            
                    let new_value = $(`#file_${files_metadata[key0].file_module_config_id}-new-${counter_2}`).val();
                    data_array.file_var_value = new_value;
            
                    counter_2++;
                    files_to_save[key0].vars.push(data_array);
                }
            }
        //console.log(JSON.stringify(files_to_save));
        //return false;
        const formData = new FormData();//declara formData que llevara el flag y json
        var content = `<div class="text-center">
            <h2 class="text-center">Guardando datos...</h2>
            <br>
            <br>
            <img src="../../../../img/img_casadelasluces/load.gif" width="40%">
        </div>`;
        show_emergent(content, false);
        formData.append(`config_flag`, `saveConfigurations`);//json de detalle de campos
        formData.append(`json_data`, JSON.stringify(modules_to_save));//json con nuevos valores
        formData.append(`files_data`, JSON.stringify(files_to_save));//json de detalle de archivos
        setTimeout(function(){
            $.ajax({
                url: "./include/specials/configuracion_ambiente/ajax/ConfiguracionAmbiente.php",
                type: "POST",
                data: formData,
                contentType: false, // OBLIGATORIO
                processData: false, // OBLIGATORIO
                success: function (response) {
                    var json_decode = JSON.parse(response);
                    if(json_decode.status && (json_decode.status == "200" || json_decode.status == 200)){
                        alert(json_decode.message);
                        location.reload();
                    }else{
                        console.log();
                        alert("Error : " + response);
                    }
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                }
            });
        }, 300);
    }

    function file_module_download(counter){//alert(counter);
        var content = `{\n\t`;
        var counter_2 = 0;
        var file_name = files_metadata[counter].file_module_name.replaceAll(' ', '_') + `-${files_metadata[counter].file_name}`;
        for (const key1 in files_metadata[counter].file_vars) {//alert();
            //console.log(files_metadata[counter].file_vars[key1]);
            content += (counter_2 > 0 ? `,\n\t` : ``);
            content += `"${files_metadata[counter].file_vars[key1].var_name}" : "${files_metadata[counter].file_vars[key1].var_value}"`;

            counter_2 ++;
        }
        content += `\n}`;
        //alert(content + "\n" +file_name);
        FileDownload(content, file_name )
    }

    function FileDownload(file_data, file_name ){
        //console.log(JSON.stringify(tables_metadata));
        const blob = new Blob(
            [file_data],
            { type: "text/plain;charset=utf-8" }
        );
        const a = document.createElement("a");
        a.href = URL.createObjectURL(blob);
        a.download = file_name;
        a.click();
        URL.revokeObjectURL(a.href);
    }
    
//lamadas asincronas
	async function ajaxR( url ){
		if(window.ActiveXObject)
		{		
			var httpObj = new ActiveXObject("Microsoft.XMLHTTP");
		}
		else if (window.XMLHttpRequest)
		{		
			var httpObj = new XMLHttpRequest();	
		}
		httpObj.open("POST", url , false, "", "");
		httpObj.send(null);
		return httpObj.responseText;
	}
