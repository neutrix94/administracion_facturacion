<?php
	session_start();
	$_SESSION['current_view'] = $_POST['action'];
    include( "../../db.php" );
	$db = new db();
	$link = $db->conectDB();
    include('./ajax/ConfiguracionAmbiente.php');
    $ConfiguracionAmbiente = new ConfiguracionAmbiente($link);
    $current_git_branch = $ConfiguracionAmbiente->getCurrentBranch();
    //echo $ConfiguracionAmbiente->getConfigModules();
    //return '';
?>
<!--DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../../../../css/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../../../../css/icons/css/fontello.css">
    <script src="../../../../js/jquery-1.10.2.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de versión</title>
</head>
<body-->
<div class="row" style="height : 1000px !important; overflow:auto;left: -5% !important;">
    <script src="./include/specials/configuracion_ambiente/js/functions.js"></script>
    <script src="./include/specials/configuracion_ambiente/js/builder.js"></script>
    <div id="emergent">
        <div id="emergent_close_btn" class="text-end p-3">
            <button
                type="button"
                class="btn btn-danger"
                onclick="close_emergent();"
            >
                <i>X</i>
            </button>
        </div>
        <div id="emergent_content"></div>
    </div>
    <div class="">
        <div class="row header bg-primary">
            <div class="row">
                <div class="col-2 p-1">
                    <button
                        type="btn btn-success"
                        class="btn btn-success form-control"
                        onclick="show_new_db_config_form();"
                    >
                        <i class="icon-plus">Agregar modulo</i>
                    </button>
                </div>
                <div class="col-8">
                    <h2 class="text-center text-white">Configuración de Ambiente (<?php echo $current_git_branch;?>)</h2>
                </div>
                <div class="col-2 p-1">
                    <button
                        type="button"
                        class="btn btn-secondary form-control"
                        onclick="saveConfigurations();"
                    >
                        <i class="icon-floppy-1">Guardar</i>
                    </button>
                </div>
            </div>
        </div>
        <div class="config_content">
            <div class="config_content_1">
                <div class="row">
                    <div class="col-6">
                        <h6 class="text-center">Base de datos actual</h6>
                        <div class="input-group">
                            <input type="text" id="current_db_name" class="form-control" value="<?php echo isset($dbName) ? : '';?>" placeholder="Base de datos a la que apunta el sistema" readonly>
                            <button
                                type="button"
                                class="btn btn-info"
                                onclick="json_export();"
                            >
                                <i class="icon-download">Exportar JSON</i>
                            </button>
                        </div>
                        <input type="text" id="current_git_branch" class="form-control" value="<?php echo $current_git_branch;?>" readonly>
                    </div>
                    <div class="col-6">
                        <h6 class="text-center">Base de datos (comprobación)</h6>
                        <div class="input-group">
                            <input type="text" id="db_name_comprobation" class="form-control" onpaste="bloquearPegar(event)" placeholder="Escribe la base de datos a la que estas apuntando">
                            <button
                                type="button"
                                class="btn btn-warning"
                                onclick="json_import();"
                            >
                                <i class="icon-upload">Importar JSON</i>
                            </button>
                        </div>
                        <input type="text" id="git_branch_comprobation" class="form-control" onpaste="bloquearPegar(event)" placeholder="Escribe la rama de GIT a la que estas apuntando">
                    </div>
                </div>
                <div id="tables_module_container"></div>
            </div>            
            <div class="config_content_2">
            <?php
                include('./ajax/ejemplo_modulo_archivos.php');
            ?>
            </div>
        </div>
        <!--div class="footer bg-primary text-center">
            <button
                type="button"
                class="btn btn-light"
                onclick="location.href='../../../../index.php';"
            >
                <i class="icon-home-1">Regresar al panel</i>
            </button>
        </div>
    </div-->
    <input type="file" id="json_file_import" accept=".json" class="hidden">
<!--/body>
</html-->
</div>
<script>
    getConfigModules();
    document.getElementById('json_file_import').addEventListener('change', function (e) {
        const archivo = e.target.files[0];
        if (!archivo) return;
        
        if (archivo.type !== "application/json") {// Validar que sea JSON
            alert("Por favor sube un archivo JSON");
            return;
        }
        const reader = new FileReader();
        reader.onload = function () {
            try {
            const data = JSON.parse(reader.result);
                modulesTablesImport(data.modules_tables);
                fileModulesImport(data.files_modules);
            } catch (error) {
                alert("El archivo no es un JSON válido : " + error);
                return false;
            }
        };
        reader.readAsText(archivo);
    });
</script>

<style>
    .header{
        position: absolute;
        top:0;
        width: 100%;
        height: 80px;
        right: 12px;
    }
    .footer{
        position: absolute;
        top:calc( 100% - 50px);
        width: 100%;
        right:0;
        height: 50px;
    }
    .config_content{
        position:absolute;
        top : calc(80px);
        height: calc(100% - 100px);
        width: 100%;
        border : 1px solid black;
    }
    .config_content_1, .config_content_2{
        position: absolute;
        height: calc(50%);
        overflow: auto;
        width: 100%;
    }
    .config_content_2{
        top: 50% !important;
    }
    .hidden{
        display : none;
    }
/**/
    #emergent{
        position: absolute;
        width: 100%;
        left:0;
        height: 100%;
        top:0;
        background-color: rgba(0, 0, 0, .5);
        z-index: 100;
        display: none;
    }
    #emergent_content{
        position: relative;
        width: 90%;
        left : 5%;
        background-color: white;
        min-height: 30%;
        max-height: 80%;
        overflow: auto;
        top : 5%;
    }

    #table_seeker_response{
        position :relative;
        width : 100%;
        max-height : 300px;
        background: white;
        box-shadow: 1px 1px 10px rgba(0, 0, 0, .5);
        display: none;
        overflow: auto;
    }
    .seeker_row{
        position: relative;
        width: 100%;
        padding: 8px;
    }
    .seeker_row:hover{
        background: rgba(225, 225, 0, .5);
    }
    #cont_carga{
        left : 0 !important;
    }
</style>

<script>
    //show_module_form('ARCHIVO', false);
</script>

