<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<script language="JavaScript">
		function popup(URL, L, T) {
			day = new Date();
			id = day.getTime();
			eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=1,scrollbars=1,location=1,statusbar=1,menubar=1,resizable=1,width=800,height=600,left =L ,top = T');");
		}
		function paginas() {
		   popup("esp.html",524,210);
		   popup("eur.html",624,230);
		   popup("90p.html",724,250);
		}
	</script>

	<title>Busqueda de patentes</title>

</head>

<body onload="paginas()">
</body>
</html>

<?php 

$a = $_POST['nomb']; 

//funcion para extraer codigo html entre 2 límites
function cortar($beg, $end, $str) {
   $a = explode($beg, $str, 2);
   $b = explode($end, $a[1]);
   return $beg . $b[0] . $end;
}

//direcciones url para realizar las busquedas
$url1 = "http://worldwide.espacenet.com/searchResults?compact=false&AB=";
$url2 = "http://www.oepm.es/es/signos_distintivos/resultados.html?denominacion=Contenga&texto=";
$url3 = "http://ep.espacenet.com/searchResults?compact=false&AB=";
$url3end = "&ST=quick&locale=en_EP&submitted=true&DB=ep.espacenet.com";

//Definimos las urls finales con la cadena a buscar
$f1 = $url1.$a;
$f2 = $url2.$a;
$f3 = $url3.$a.$url3end;

echo $f1."<br />";
echo $f2."<br />";
echo $f3."<br />";

//-----------------------------------------
//----Parte de patentes españolas----
//-----------------------------------------

//creamos un fichero html temporal para extraer las descripciones de las patentes

$matriz = file($f2);
file_put_contents("tmp.html","");
file_put_contents("tmp.html",$matriz[400],FILE_APPEND);

//url de la pagina para arreglar los enlaces relativos
$oldSetting = libxml_use_internal_errors( true );
libxml_clear_errors();

$html = new DOMDocument();
$html -> loadHtmlFile("tmp.html");
$xpath = new DOMXPath( $html );


//extraemos todos los enlaces para poder corregirlos
$links = $xpath->query( '//a[starts-with(@href,"/es/signos_distintivos/detalle.html?")] | //td ');

$tabladef='<table width="700" border="1"> <tr> <td>';
$tablacont='</td> </tr> <tr> <td>';
$tablaend='</td> </tr> </table>';
$estilo='<style type="text/css"> body,td,th { font-family: "Trebuchet MS", Helvetica, sans-serif; } </style>';

file_put_contents("esp.html", $estilo);
file_put_contents("esp.html", '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />', FILE_APPEND);
file_put_contents("esp.html", '<h1><strong>Búsqueda en base de datos de España</strong></h1>', FILE_APPEND);
file_put_contents("esp.html", $tabladef, FILE_APPEND);

$salida = "";
$contenido = "";

$urloe = "http://www.oepm.es";

//recorremos todos los enlaces extraidos
foreach ( $links as $link )
{

	//corregimos los enlaces
	$clink = $urloe.$link->getAttribute( 'href' );
	$clink = str_replace(" ", "%20",$clink);
	$mat = file($clink);

	//echo $clink."<br />";   

	//escribimos el enlace corregido mas la descripcion concreta de la patente (se encuentra en la linea 404 del html generado)
	$clink = '<p>'.'<a href='.$clink.'title="Enlace" target="new">Enlace original</a>'.'</p>';
	$salida = $salida.$clink;
	$contenido = utf8_encode($mat[404]);

	//reparamos las url relativas
	$url_repair = '/es/signos_distintivos/';
	$contenido = str_replace($url_repair, $urloe.'/es/signos_distintivos/' ,$contenido);
	
	$salida = $salida.$contenido;
	
	/*foreach ($mat as $num_linea => $linea) {
		echo "Línea #<b>{$num_linea}</b> : " . htmlspecialchars($linea) . "<br />\n";
	}*/
	
}

//escribimos los resultados en el fichero
file_put_contents("esp.html", $salida, FILE_APPEND);
file_put_contents("esp.html", $tablacont, FILE_APPEND);
file_put_contents("esp.html", $tablaend, FILE_APPEND);

$ref = '<p>'.'<a href='.$f2.' title="Enlace" target="new">Mas informacion en la pagina original</a>'.'</p>';
file_put_contents("esp.html", $ref, FILE_APPEND);
libxml_clear_errors();
libxml_use_internal_errors( $oldSetting );



//---------------------------------------
//----Parte de patentes europeas----
//---------------------------------------

$urleur = file_get_contents($f3);

//extraemos la tabla donde se encuentra el contenido que nos interesa del html obtenido
$urleur= cortar('<table class="application">', '</table>', $urleur);

//eliminamos los checkbox innecesarios
$limpiar_checkbox='%<input type="checkbox"[^>]*>%';
$urleur = preg_replace($limpiar_checkbox,'',$urleur);


//arreglamos las url relativas
$urlpe = 'http://worldwide.espacenet.com/';
$url_repair = '/publicationDetails/biblio';
$urleur = str_replace($url_repair, $urlpe.'publicationDetails/biblio', $urleur);


//añadimos el estilo de la fuente
$estilo='<style type="text/css"> body,td,th { font-family: "Trebuchet MS", Helvetica, sans-serif; } </style>';

//Escribimos todos los resultados a fichero y con su estilo, cabecera con la codificacion
file_put_contents("eur.html",$estilo);
file_put_contents("eur.html",'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />',FILE_APPEND);
file_put_contents("eur.html",'<h1><strong>Búsqueda en base de datos de Europa</strong></h1>',FILE_APPEND);
file_put_contents("eur.html",$tabladef,FILE_APPEND);
file_put_contents("eur.html",$urleur,FILE_APPEND);
file_put_contents("eur.html",$tablacont,FILE_APPEND);
file_put_contents("eur.html",$tablaend,FILE_APPEND);
$ref = '<p>'.'<a href='.$f3.' title="Enlace" target="new">Mas informacion en la pagina original</a>'.'</p>';
file_put_contents("eur.html",$ref,FILE_APPEND);



//-------------------------------------------
//----Parte de patentes de 90+ paises----
//-------------------------------------------
/*$url90 = file_get_contents($f1);
$url90 = cortar('<table class="application">', '</table>', $url90);


/*
//quitamos las checkbox innecesarias para nuestro html
$limpiar_checkbox='%<input type="checkbox"[^>]*>%';
$url90 = preg_replace($limpiar_checkbox,'',$url90);

//areglamos los enlaces relativos referidos a la web original
$url_repair='%<a  href="/publicationDetails/[^>]*biblio%';
$url90 = preg_replace($url_repair,'<a  href="http://worldwide.espacenet.com/publicationDetails/biblio', $url90);


$tabladef='<table width="857" border="1"> <tr> <td>';
$tablacont='</td> </tr> <tr> <td>';
$tablaend='</td> </tr> </table>';
$estilo='<style type="text/css"> body,td,th { font-family: "Trebuchet MS", Helvetica, sans-serif; } </style>';
file_put_contents("90p.html",$estilo);
file_put_contents("90p.html",'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />',FILE_APPEND);
file_put_contents("90p.html",'<h1><strong>Búsqueda en base de datos de todo el mundo</strong></h1>',FILE_APPEND);
file_put_contents("90p.html",$tabladef,FILE_APPEND);
file_put_contents("90p.html",$url90,FILE_APPEND);
file_put_contents("90p.html",$tablacont,FILE_APPEND);
file_put_contents("90p.html",$tablaend,FILE_APPEND);

$ref = '<p>'.'<a href='.$f1.' title="Enlace" target="new">Mas informacion en la pagina original</a>'.'</p>';
file_put_contents("90p.html",$ref,FILE_APPEND);
*/

echo '<p>'.'Enlaces a las búsquedas:'.'</p>';
echo '<p>'.'<a href="./90p.html" title="Enlace" target="new">Búsqueda en más de 90 países</a>'.'</p>';
echo '<p>'.'<a href="./esp.html" title="Enlace" target="new">Búsqueda de patentes en España</a>'.'</p>';
echo '<p>'.'<a href="./eur.html" title="Enlace" target="new">Búsqueda de patentes en Europa</a>'.'</p>';

?>


