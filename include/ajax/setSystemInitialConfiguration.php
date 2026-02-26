<?php
	header('Content-Type: text/html; charset=utf-8');
//Elimina el archivo anterior si existe
	if(file_exists("../../conf.json")){
		unlink("../../conf.json");
	}
    $data = [
        'system_host' => base64_encode($_POST['host']),
        'system_path' => base64_encode($_POST['path']),
        'system_database_name' => base64_encode($_POST['db_name']),
        'system_database_user' => base64_encode($_POST['db_user']),
        'system_database_password' => base64_encode($_POST['db_password'])
    ];
    $json_data = json_encode($data, JSON_PRETTY_PRINT);

    $file = '../../conf.json';  // Nombre del archivo donde se guardará el JSON
    if(file_put_contents($file, $json_data)){
        //echo "Archivo JSON creado exitosamente!";
    }else{
        die("Error al crear el archivo JSON.");
    }

	if(file_exists("../../config.inc.php")){
		unlink("../../config.inc.php");
	}
    $datos="<?php
        /*************************Definiciones de base de datos**********************/
        \$dbHost = \"{$_POST['host']}\";
        \$dbUser = \"{$_POST['db_user']}\";
        \$dbPassword = \"{$_POST['db_password']}\";
        \$dbName = \"{$_POST['db_name']}\";
?>";    

		$fp2 = fopen("../../config.inc.php", "w");
		fputs($fp2,$datos);
		fclose($fp2);
		chmod("../../config.inc.php", 0777);

    echo 'ok';
?>