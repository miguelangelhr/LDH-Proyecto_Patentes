<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="content-language" content="es" />
	<meta name="language" content="es" />
	<meta name="keywords" content="Buscador, Patentes, Europa, Nacional, Internacional" />
	<meta name="description" content="Buscador de Patentes Nacional, Europea e Internacional" />
	<meta name="author" content="Miguel Hernández - Maurizio Rendón" />
	<meta name="robots" content="all" />
	
	<title>Buscador de Patentes (Nacional-Europea-Internacional)</title>
	
	<link rel="stylesheet" href="../css/style.css" type="text/css" media="all" />
	<!--[if lte IE 6]><link rel="stylesheet" href="../css/ie6.css" type="text/css" media="all" /><![endif]-->
	<!--[if IE]><style type="text/css" media="screen"> #navigation ul li a em { top:32px; } </style><![endif]-->
	
</head>

<body>

	<div id="header">

		<div class="shell">
			
			<h1 id="logo">
				<a href="index.html">Buscador de Patentes.</a>
			</h1>
			<!--/ Logo -->
			
			<div id="navigation">
				<ul>
					<li class="selected"><a href="index.html">Home</a></li>
					<li><a href="acerca-de.html">Acerca de</a></li>
					<li><a href="quienes-somos.html">Quienes Somos</a></li>
					<li class="last"><a href="contacto.html">Contacto</a></li>
				</ul>
			</div>
			<!--/ Navigation -->
			
		</div>
		<!-- shell -->
		
	</div>
	<!--/ Header -->

	<div id="content">	

		<div class="shell">
			
			<div class="content">

<?php 

$a = $_POST['patente']; 

/**
 * funcion para extraer codigo html entre 2 limites
 */
function cortar($beg, $end, $str) {
   $a = explode($beg, $str, 2);
   $b = explode($end, $a[1]);
   return $beg . $b[0] . $end;
}

/**
 * direcciones url para realizar las busquedas
 */
$url1 = "http://worldwide.espacenet.com/searchResults?compact=false&AB=";
$url2 = "http://www.oepm.es/es/signos_distintivos/resultados.html?denominacion=Contenga&texto=";
$url3 = "http://ep.espacenet.com/searchResults?compact=false&AB=";
$url3end = "&ST=quick&locale=en_EP&submitted=true&DB=ep.espacenet.com";

/**
 * Definimos las urls finales con la cadena a buscar
 */
$f1 = $url1.$a;
$f2 = $url2.$a;
$f3 = $url3.$a.$url3end;


//-----------------------------------------
//----Parte de patentes espa�olas----
//-----------------------------------------

/**
 * creamos un fichero html temporal para extraer las descripciones de las patentes
 */
$matriz = file($f2);
file_put_contents("tmp.html","");
file_put_contents("tmp.html",$matriz[400],FILE_APPEND);

/**
 * url de la pagina para arreglar los enlaces relativos
 */
$oldSetting = libxml_use_internal_errors( true );
libxml_clear_errors();

$html = new DOMDocument();
$html -> loadHtmlFile("tmp.html");
$xpath = new DOMXPath( $html );


/**
 * extraemos todos los enlaces para poder corregirlos
 */
$links = $xpath->query( '//a[starts-with(@href,"/es/signos_distintivos/detalle.html?")] | //td ');

$tabladef  = '<table> <tr> <td>';
$tablacont = '</td> </tr> <tr> <td>';
$tablaend  = '</td> </tr> </table>';

echo "				<div class='search-spain'>";
echo "				<h2>Resultados en Base de Datos de España:</h2>";

$salida = "";
$contenido = "";

$urloe = "http://www.oepm.es";

/**
 * recorremos todos los enlaces extraidos
 */
foreach ( $links as $link )
{

	/**
	 * corregimos los enlaces
	 */
	$clink = $urloe.$link->getAttribute( 'href' );
	$clink = str_replace(" ", "%20",$clink);
	$mat = file($clink);

	/**
	 * escribimos el enlace corregido mas la descripcion concreta de la patente
	 * (se encuentra en la linea 404 del html generado)
	 */
	$contenido = utf8_encode($mat[404]);

	/**
	 * reparamos las url relativas
	 */
	$url_repair = '/es/signos_distintivos/';
	$contenido = str_replace($url_repair, $urloe.'/es/signos_distintivos/' ,$contenido);
	
	$salida = $salida.$contenido;
	
	/*foreach ($mat as $num_linea => $linea) {
		echo "Línea #<b>{$num_linea}</b> : " . htmlspecialchars($linea) . "<br />\n";
	}*/
	
}

/**
 * escribimos los resultados
 */
echo $salida;
echo '					<p class="more">
							<a href='.$f2.' title="Enlace" target="new">>> Más información en la página original</a>
						</p>';

echo "				</div>
					<!--/ search-spain -->";

libxml_clear_errors();
libxml_use_internal_errors( $oldSetting );



//---------------------------------------
//----Parte de patentes europeas----
//---------------------------------------

$urleur = file_get_contents($f3);

/**
 * extraemos la tabla donde se encuentra el contenido que nos interesa del html obtenido
 */
$urleur= cortar('<table class="application">', '</table>', $urleur);

/**
 * eliminamos los checkbox innecesarios
 */
$limpiar_checkbox='%<input type="checkbox"[^>]*>%';
$urleur = preg_replace($limpiar_checkbox,'',$urleur);


/**
 * arreglamos las url relativas
 */
$urlpe = 'http://worldwide.espacenet.com/';
$url_repair = '/publicationDetails/biblio';
$urleur = str_replace($url_repair, $urlpe.'publicationDetails/biblio', $urleur);

$reemplazar ='     <tr>
        <th colspan="7">';
		
$urleur = str_replace($reemplazar, "<tr class='title'><th colspan='7'>", $urleur);


/**
 * Escribimos todos los resultados a fichero y con su estilo, cabecera con la codificacion
 */

echo "				<div class='search-europe'>";
echo "				<h2>Resultados en Base de Datos de Europa:</h2>";
echo $tabladef;
echo $urleur;
echo $tablacont;
echo $tablaend;
echo '					<p class="more">
							<a href='.$f3.' title="Enlace" target="new">>> Más información en la página original</a>
						</p>';

echo "				</div>
					<!--/ search-europe -->";



//-------------------------------------------
//----Parte de patentes de 90+ paises----
//-------------------------------------------
/*$url90 = file_get_contents($f1);
$url90 = cortar('<table class="application">', '</table>', $url90);



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


?>

			</div>
			<!--/ content -->
			
		</div>

	</div>
	<!--/ content -->

	<div id="footer">
	
		<div class="shell">
			
			<a href="#" class="notext footer-logo">Buscador de Patentes</a>
			
			<div class="right">
				<p class="footer-menu">
					<a class="selected" href="index.html">Home</a>
					<span>|</span>
					<a href="acerca-de.html">Acerca de</a>
					<span>|</span>
					<a href="quienes-somos.html">Quienes Somos</a>
					<span>|</span>
					<a href="contacto.html">Contacto</a>
				</p>
				<p class="copyright">Copyright &copy; 2013. (Miguel-Hernández y Maurizio-Rendón).</p>
			</div>
			<!--/ Footer Nav -->
			
		</div>
		<!--/ Shell -->
		
	</div>
	<!--/ Footer -->
	
</body>
</html>

