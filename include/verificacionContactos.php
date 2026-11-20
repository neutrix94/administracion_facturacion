<?php
	session_start();
	$_SESSION['current_view'] = $_POST['action'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificacion Contactos</title>
</head>
<body>
    <div id="emergent">
        <div id="emergent_content">
            <h2 class="text-center"><br><br>Cargando ...</h2>
            <div class="text-center">
                <img src="img/load.gif" alt="">
            </div>
        </div>
    </div>
    <div>
        <button
            type="button"
            class="btn btn-success form-control m-2"
            onclick="exportaTabla();"
        >
            <i>Exportar</i>
        </button>
        <table class="table table-striped table-bordered">
            <thead class="bg-white" style="position : sticky; top :0;">
                <tr>
                    <th>Razon Social</th>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Celular</th>
                    <th>Correo</th>
                    <th>Folio Único</th>
                </tr>
            </thead>
            <tbody id="contactos_faltantes"></tbody>
        </table>
    </div>
</body>
</html>

<script>
    async function enviarDatos() {
        $(`#emergent`).css('display', 'block');
        try {
            const respuesta = await fetch('rest/comprobacion_contactos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (!respuesta.ok) {
                throw new Error(`Error HTTP: ${respuesta.status}`);
            }

            const datos = await respuesta.json();
            if(datos.status == 200){
                build_rows(datos.registros);
            }
            console.log('Respuesta:', datos);
        } catch (error) {
            console.error('Falló la petición:', error);
        }
        $(`#emergent`).css('display', 'none');
    }

    function build_rows(registros){
        var content = ``;
        for (const key in registros) {
            for (const key2 in registros[key]['no_existen']) {
                content += `<tr>
                    <td>${registros[key].nombre_db}</td>
                    <td>${registros[key]['no_existen'][key2].nombre}</td>
                    <td>${registros[key]['no_existen'][key2].telefono}</td>
                    <td>${registros[key]['no_existen'][key2].celular}</td>
                    <td>${registros[key]['no_existen'][key2].correo}</td>
                    <td>${registros[key]['no_existen'][key2].folio_unico}</td>
                </tr>`;
            }
        }
        $('#contactos_faltantes').html(content);
    }

    function exportaTabla() {
        const tabla = document.querySelector('table');
        const filas = tabla.querySelectorAll('tr');

        if (filas.length <= 1) {
            alert('No hay datos para exportar');
            return;
        }

        const lineas = [];
        filas.forEach(fila => {
            const celdas = fila.querySelectorAll('th, td');
            const valores = Array.from(celdas).map(celda => {
                let texto = celda.innerText.trim();
                texto = texto.replace(/"/g, '""');   // escapa comillas dobles
                return `"${texto}"`;                 // encierra cada valor entre comillas
            });
            lineas.push(valores.join(','));
        });

        const csv = '\uFEFF' + lineas.join('\r\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);

        const enlace = document.createElement('a');
        enlace.href = url;
        enlace.download = `contactos_faltantes_RS_${new Date().toISOString().slice(0, 10)}.csv`;
        document.body.appendChild(enlace);
        enlace.click();
        document.body.removeChild(enlace);
        URL.revokeObjectURL(url);
    }

    enviarDatos();
</script>

<style>
    #emergent{
        position : fixed;
        top : 0;
        left : 0;
        width: 100%;
        height: 100%;
        background-color: rgba( 0,0,0,.5 );
        z-index:10;
        display : none;
    }

    #emergent_content{
        position : fixed;
        top : 10%;
        left : 10%;
        width: 80%;
        min-height: 30%;
        max-height: 70%;
        background-color: white;
        box-shadow: 1px 1px 10px rgba( 0,0,0,.5 );
    }
    .hidden{
        display : none;
    }
    #table_content{
        max-height: 500px !important;
        overflow: auto;
    }
</style>

