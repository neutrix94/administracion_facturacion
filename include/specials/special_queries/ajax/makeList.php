<?php
	function build_accordeon( $link, $tipo_lista = 'Consulta', $numero = 1 ){
		$cont = 0;
		$resp = "<div class=\"accordion\" id=\"accordionExample\">";
		$sql="SELECT 
				id_herramienta AS tool_id,
				titulo AS query_title,
				descripcion AS query_description
			FROM sys_herramientas
			WHERE tipo_herramienta = '{$tipo_lista}' 
			ORDER BY titulo ASC";
		$eje = $link->query($sql)or die("Error al consultar las herramientas!!!<br>".$link->error."<br>".$sql);
		
		while( $r = $eje->fetch(PDO::FETCH_ASSOC) ){
			$resp .= '<div class="accordion-item">';
		    	$resp .= '<h2 class="accordion-header" id="heading_'.$numero .'_'.$cont.'">';
			    	$resp .= '<button class="btn btn-light form-control accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_'.$numero .'_'.$cont.'"'
			    	. ' aria-expanded="true" aria-controls="collapse_'.$numero .'_'.$cont.'" onclick="carga_filtros('.$r['tool_id'].',\'busc_prod\');"'
			    	. 'id="herramienta_'.$numero .'_' . $cont . '" class="opc_btn">';
			        $resp .= $r['query_title'];
			      	$resp .= '</button>';
		    	$resp .= '</h2>';
		    	$resp .= '<div id="collapse_'.$numero .'_'.$cont.'" class="accordion-collapse collapse description" aria-labelledby="heading_'.$numero .'_' . $cont . '" data-bs-parent="#accordionExample">';
			    	$resp .= '<div class="accordion-body">';
			    	$resp .= $r['query_description'];
			    	$resp .= '</div>';
		    	$resp .= '</div>';
		  	$resp .= '</div>';
			$cont ++;
		}
		$resp.= '<input type="hidden" id="contador_herramientas_' . $numero . '" value="' . $cont . '">';
		$resp .= '</div>';
		return $resp;
	}
?>