<?php

namespace App\Controllers;

use TCPDF;
use FPDI;

class Api extends BaseController {

    public function __construct() {
        
    }

    public function incripcion() {
        $pdf = new TCPDF("PORTRAIT", "pt", "letter");
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        // debug($pdf);

        // Ruta al archivo JSON
        $personas = './data.json';
        $delegacion = './delegaciones.json';
        $directores = './directores.json';

        // Verifica si el archivo existe
        if (file_exists($personas)) {
            // Lee el contenido del archivo
            $jsonContent = file_get_contents($personas);

            // Decodifica el contenido JSON a un array asociativo
            $personas = json_decode($jsonContent, true);
        }
        // Verifica si el archivo existe
        if (file_exists($delegacion)) {
            // Lee el contenido del archivo
            $jsonContent = file_get_contents($delegacion);

            // Decodifica el contenido JSON a un array asociativo
            $delegacion = json_decode($jsonContent, true);
        }
        // Verifica si el archivo existe
        if (file_exists($directores)) {
            // Lee el contenido del archivo
            $jsonContent = file_get_contents($directores);

            // Decodifica el contenido JSON a un array asociativo
            $directores = json_decode($jsonContent, true);
        }

        // Selecciona una clave aleatoria
        $keyDelegacion = array_rand($delegacion);
        // Obtén la delegación usando la clave aleatoria
        $delegacion = $delegacion[$keyDelegacion];

        // Filtrar los objetos que tengan la delegación aleatoria seleccionada
        $directores = array_filter($directores, function ($unDirector) use ($delegacion) {
            // debug([
            //     'director' => $unDirector['delegation'],
            //     'delegacion' => $delegacion['alias'],
            //     "comparacion" => ($unDirector['delegation'] === $delegacion['alias']),
            // ], false);
            return $unDirector['delegation'] === $delegacion['alias'];
        });

        $personas = array_filter($personas, function ($persona) use ($delegacion) {
            return $persona['delegation'] === $delegacion['alias'] && $persona['typePerson'] == "atleta";
        });

        // Verifica que el array filtrado tenga al menos 15 elementos
        if (count($personas) >= 15) {
            // Obtén 15 claves aleatorias del array filtrado
            $keyPersonas = array_rand($personas, 15);

            // Si sólo se seleccionó una clave, conviértela en un array
            if (!is_array($keyPersonas)) {
                $keyPersonas = [$keyPersonas];
            }

            // Obtén los elementos correspondientes a las claves aleatorias
            $personas = array_map(function ($clave) use ($personas) {
                return $personas[$clave];
            }, $keyPersonas);
        }

        /* debug([
            // 'equipo' => $delegacion,
            // 'jueces' => $directores,
            'personas' => $personas,
        ]); */

        // Agregar Página
        $pdf->AddPage();
        $this->header($pdf);
        $this->equipo($pdf, $delegacion, $directores);
        $this->categoria($pdf);
        $this->atletas($pdf, $personas);
        $this->footer($pdf, $delegacion);


        /* imprecion del archivo va al final */
        $filename = "pruebasPDF";
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '.pdf"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        $pdf->Output($filename . '.pdf', 'I');
        $pdf->Output($filename . '.pdf', 'D');
    }

    private function header($pdf) {
        $pdf->SetFont($txtFont = '', $txtFormat = 'I', $txtSize = 11);
        $pdf->Cell($width = 0, $height = 10, $texto = "Asociación de Fútbol de Salón del Distrito Capital", $borde = 0, $saltoLD = 1, $alineacion = 'L');
        $pdf->Cell($width = 0, $height = 10, $texto = "Liga Distrital de Fútbol de Salón", $borde = 0, $saltoLD = 1, $alineacion = 'L');
        $pdf->SetFont($txtFont = '', $txtFormat = '', $txtSize = 15);
        $pdf->Cell($width = 400, $height = 35, $texto = "XV CAMPEONATO", $borde = 0, $saltoLD = 1, $alineacion = 'C');
        $pdf->SetFont($txtFont = '', $txtFormat = 'U', $txtSize = 15);
        $pdf->Cell($width = 400, $height = 0, $texto = "'FICHAJE- TORNEO APERTURA - 2024", $borde = 0, $saltoLD = 1, $alineacion = 'C');
        $pdf->Ln($lineas = 10);

        // Imprimir Imagén
        $tamanoimg = 110;
        $pdf->Image($ruta = './img/Logo.png',  $x = $pdf->getPageWidth() - PDF_MARGIN_RIGHT - $tamanoimg, $y = 0, $w = $tamanoimg, $h = $tamanoimg, '', '', '', false, 300, '', false, false, 0);
    }

    private function equipo($pdf, $equipo, $jueces) {
        $incre = 1;
        $delegado = [];
        $entrenadores = [];

        foreach ($jueces as $key => $value) {
            if ($incre == 1) {
                $delegado = $value;
            } else {
                $entrenadores[] = $value;
            }
            $incre++;
        }

        $entrenadoresTxt = "";
        foreach ($entrenadores as $key => $value) {
            // Divide el nombre y apellido por espacios
            $nombres = explode(" ", $value["name"]);
            $apellidos = explode(" ", $value["lastname"]);

            // Obtén el primer nombre y el primer apellido
            $primerNombre = $nombres[0];
            $primerApellido = $apellidos[0];

            // Añade el separador " | " después del primer entrenador
            if ($key > 0) {
                $entrenadoresTxt .= " | ";
            }

            // Concatena el primer nombre y el primer apellido al texto
            $entrenadoresTxt .= $primerNombre . " " . $primerApellido;
        }

        // debug([
        //     "entrenadores" => $entrenadoresTxt,
        // ]);


        $pdf->SetFont($txtFont = 'kanitb', $txtFormat = 'B', $txtSize = 10);

        $anchoCuadro = 52;
        $anchoTabla = 165;
        // Primera celda: un cuadro vacío de 52x52
        $pdf->SetDrawColor($rojoBorde = 221, $verdeBorde = 221, $azulBorde = 221);
        // $pdf->Image($ruta = './img/ECR.png',  $x = PDF_MARGIN_LEFT + 12.5, $y = 120, $w = $anchoCuadro, $h = $anchoCuadro, '', '', '', false, 300, '', false, false, 0);
        $pdf->Image($ruta = './img/' . $equipo["alias"] . '.png',  $x = $pdf->GetX(), $y = $pdf->GetY(), $w = $anchoCuadro, $h = $anchoCuadro, '', '', '', false, 300, '', false, false, 0);
        $pdf->Cell($width = $anchoCuadro, $height = $anchoCuadro, $texto = "", $borde = 1, $saltoLD = 0, $alineacion = 'L');

        // Segunda y tercera celdas (ubicadas a la derecha de la primera celda)
        $pdf->SetFont($txtFont = 'kanitlight', $txtFormat = '', $txtSize = 8);
        $pdf->SetFillColor($rojoFondo = 236, $verdeFondo = 236, $azulFondo = 236);
        $pdf->Cell($width = $anchoTabla, $height = 13, $texto = "Club:", $borde = 1, $saltoLD = 0, $alineacion = 'L', $confondo = true);
        $pdf->Cell($width = $anchoTabla, $height = 13, $texto = "Delegado:", $borde = 1, $saltoLD = 1, $alineacion = 'L', $confondo = true);

        // Cuarta y quinta celdas (ubicadas debajo de la segunda y tercera celdas)
        $pdf->SetFont($txtFont = 'kanitb', $txtFormat = 'B', $txtSize = 10);
        $pdf->Cell($width = $anchoCuadro, $height = 13, $texto = "", $borde = 0, $saltoLD = 0, $alineacion = 'L'); // Celda vacía para alineación
        $pdf->Cell($width = $anchoTabla, $height = 13, $texto = $equipo["name"], $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $pdf->Cell($width = $anchoTabla, $height = 13, $texto = $delegado["name"] . " " . $delegado["lastname"], $borde = 1, $saltoLD = 1, $alineacion = 'L');

        // Sexta celda (una celda que ocupa el ancho total bajo la primera fila)
        $pdf->SetFont($txtFont = 'kanitlight', $txtFormat = '', $txtSize = 8);
        $pdf->Cell($width = $anchoCuadro, $height = 13, $texto = "", $borde = 0, $saltoLD = 0, $alineacion = 'L'); // Celda vacía para alineación
        $pdf->Cell($width = $anchoTabla * 2, $height = 13, $texto = "Entrenador (es):", $borde = 1, $saltoLD = 1, $alineacion = 'L', $confondo = true);

        // Séptima celda (otra celda ocupando el ancho total bajo la segunda fila)
        $pdf->Cell($width = $anchoCuadro, $height = 13, $texto = "", $borde = 0, $saltoLD = 0, $alineacion = 'L'); // Celda vacía para alineación
        $pdf->SetFont($txtFont = 'kanitb', $txtFormat = 'B', $txtSize = 10);
        $pdf->Cell($width = $anchoTabla * 2, $height = 13, $texto = $entrenadoresTxt, $borde = 1, $saltoLD = 1, $alineacion = 'L');
    }

    private function categoria($pdf) {

        $ancho = $pdf->getPageWidth() * 0.65;
        $pdf->LN(5);
        $pdf->SetY($posY = 117.28);

        // $pdf->SetXY($posX =  $ancho , $posY = 110);

        $pdf->SetFont($txtFont = 'kanit', $txtFormat = '', $txtSize = 12);
        $pdf->Cell($width = $ancho, $height = 13, $texto = "", $borde = 0, $saltoLD = 0, $alineacion = 'L');
        $pdf->Cell($width = 120, $height = 10, $texto = "CATEGORIA", $borde = 0, $saltoLD = 1, $alineacion = 'C');
        $pdf->SetFont($txtFont = 'kanitb', $txtFormat = 'U', $txtSize = 15);
        $pdf->Cell($width = $ancho, $height = 25, $texto = "", $borde = 0, $saltoLD = 0, $alineacion = 'L');
        $pdf->Cell($width = 120, $height = 25, $texto = "PRE-INFANTIL", $borde = 0, $saltoLD = 0, $alineacion = 'C', true);
        $pdf->SetFont($txtFont = 'kanitb', $txtFormat = '', $txtSize = 15);
        $pdf->Cell($width = 120, $height = 25, $texto = "1", $borde = 0, $saltoLD = 1, $alineacion = 'L');
        $pdf->SetFont($txtFont = 'kanit', $txtFormat = '', $txtSize = 12);
        $pdf->Cell($width = $ancho, $height = 13, $texto = "", $borde = 0, $saltoLD = 0, $alineacion = 'L');
        $pdf->Cell($width = 120, $height = 10, $texto = "2015-2014", $borde = 0, $saltoLD = 1, $alineacion = 'C', true);
    }

    private function atletas($pdf, $personas) {
        /* $vari = [
            "ancho" => $pdf->getPageWidth(),
            "margenIzq" => PDF_MARGIN_LEFT,
            "margenDere" => PDF_MARGIN_RIGHT,
            "calculo" => $tamTotal,
            "calculos" => [
                " " => $tamTotal * 0.05,
                "carnet" => $tamTotal * 0.08,
                "APELLIDOS" => $tamTotal * 0.40,
                "FECHA" => $tamTotal * 0.11,
                "EDAD" => $tamTotal * 0.07,
                "CEDULA" => $tamTotal * 0.12,
                "Nº" => $tamTotal * 0.05,
                "REFUERZA" => $tamTotal * 0.12,
            ]
        ];

        $total = 0;
        foreach ($vari["calculos"] as $key => $value) {
            $total += $value;
        }

        $vari["total"] = $total;
        debug($vari); 
        */

        $pdf->LN(15);
        $tamTotal = $pdf->getPageWidth() - PDF_MARGIN_LEFT - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT - PDF_MARGIN_RIGHT;
        $anchoCol = [
            0.05, // id
            0.13, // carnet
            0.38, // nombre
            0.10, // fecha nac
            0.05, // edad 
            0.12, // cedula
            0.05, // numero camiseta
            0.12, // refurza
        ];

        $aux = 0;
        // $pdf->SetX(0);
        $pdf->SetFont($txtFont = 'kanitlight', $txtFormat = '', $txtSize = 8);
        $pdf->Cell($width = $tamTotal * $anchoCol[($aux)], $height = 12, $texto = "", $borde = 0, $saltoLD = 0, $alineacion = 'C');
        $aux++;
        $pdf->Cell($width = $tamTotal * $anchoCol[($aux)], $height = 12, $texto = "CARNET", $borde = 1, $saltoLD = 0, $alineacion = 'C', true);
        $aux++;
        $pdf->Cell($width = $tamTotal * $anchoCol[($aux)], $height = 12, $texto = "APELLIDOS Y NOMBRES", $borde = 1, $saltoLD = 0, $alineacion = 'C', true);
        $aux++;
        $pdf->Cell($width = $tamTotal * $anchoCol[($aux)], $height = 12, $texto = "FECHA NAC.", $borde = 1, $saltoLD = 0, $alineacion = 'C', true);
        $aux++;
        $pdf->Cell($width = $tamTotal * $anchoCol[($aux)], $height = 12, $texto = "EDAD:", $borde = 1, $saltoLD = 0, $alineacion = 'C', true);
        $aux++;
        $pdf->Cell($width = $tamTotal * $anchoCol[($aux)], $height = 12, $texto = "CEDULA", $borde = 1, $saltoLD = 0, $alineacion = 'C', true);
        $aux++;
        $pdf->Cell($width = $tamTotal * $anchoCol[($aux)], $height = 12, $texto = "Nº", $borde = 1, $saltoLD = 0, $alineacion = 'C', true);
        $aux++;
        $pdf->Cell($width = $tamTotal * $anchoCol[($aux)], $height = 12, $texto = "REFUERZA AL:", $borde = 1, $saltoLD = 1, $alineacion = 'C', true);

        $anchoCuadro = 26;

        foreach ($personas as $key => $value) {
            $age = 0;
            if (isset($value['birth_date']) && !empty($value['birth_date'])) {

                $birthDate = $value['birth_date']; // Fecha de nacimiento en formato 'dd/mm/yyyy'

                // Crear un objeto DateTime a partir de la fecha de nacimiento
                $birthDateObj = \DateTime::createFromFormat('d/m/Y', $birthDate);

                if (!$birthDateObj) {
                    // Si falla, intenta con el formato 'Y-m-d'
                    $birthDateObj = \DateTime::createFromFormat('Y-m-d', $birthDate);
                }

                // Crear un objeto DateTime para la fecha actual
                $currentDateObj = new \DateTime();

                // Calcular la diferencia entre la fecha actual y la fecha de nacimiento
                $ageDiff = $currentDateObj->diff($birthDateObj);

                // Obtener la edad en años
                $age = $ageDiff->y;
            }

            // $value['gender'] = ($key % 2 == 0) ? "male" : "female"; esto no sirve, solamente fue un ejemplo para ver female tmb
            // $pdf->SetX(0);
            $aux = 0;
            $pdf->SetFont($txtFont = 'kanitlight', $txtFormat = 'I', $txtSize = 8);
            $pdf->Cell($width = $tamTotal * $anchoCol[($aux)], $height = $anchoCuadro, $texto = $key + 1, $borde = 'B', $saltoLD = 0, $alineacion = 'C');
            $aux++;
            $pdf->Image($ruta = './img/' . $value['gender'] . '.png',  $x = $pdf->GetX(), $y = $pdf->GetY(), $w = $anchoCuadro, $h = $anchoCuadro, '', '', '', false, 300, '', false, false, 0);
            $pdf->SetFont($txtFont = 'kanitb', $txtFormat = 'B', $txtSize = 9);
            $pdf->Cell($width = $tamTotal * $anchoCol[($aux)], $height = $anchoCuadro, $texto = $value['carnet'], $borde = 'B', $saltoLD = 0, $alineacion = 'R');
            $aux++;
            $pdf->SetFont($txtFont = 'kanit', $txtFormat = '', $txtSize = 9);
            $pdf->Cell($width = $tamTotal * $anchoCol[($aux)], $height = $anchoCuadro, $texto =  explode(" ", $value["name"])[0] . " " .  explode(" ", $value["lastname"])[0], $borde = 'B', $saltoLD = 0, $alineacion = 'L');
            $aux++;
            $pdf->Cell($width = $tamTotal * $anchoCol[($aux)], $height = $anchoCuadro, $texto = $value['birth_date'], $borde = 'B', $saltoLD = 0, $alineacion = 'C');
            $aux++;
            $pdf->Cell($width = $tamTotal * $anchoCol[($aux)], $height = $anchoCuadro, $texto = $age, $borde = 'B', $saltoLD = 0, $alineacion = 'C');
            $aux++;
            $pdf->Cell($width = $tamTotal * $anchoCol[($aux)], $height = $anchoCuadro, $texto = $value['dni'], $borde = 'B', $saltoLD = 0, $alineacion = 'C');
            $aux++;
            $pdf->Cell($width = $tamTotal * $anchoCol[($aux)], $height = $anchoCuadro, $texto = "", $borde = 'B', $saltoLD = 0, $alineacion = 'C');
            $aux++;
            $pdf->Cell($width = $tamTotal * $anchoCol[($aux)], $height = $anchoCuadro, $texto = "", $borde = 'B', $saltoLD = 1, $alineacion = 'C');
        }
    }

    private function footer($pdf, $equipo) {
        $currentDateObj = new \DateTime();

        $pdf->SetAutoPageBreak(true, 0);
        $pdf->SetY($pdf->getPageHeight() - 75);
        $tamTotal = $pdf->getPageWidth() - PDF_MARGIN_LEFT - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT - PDF_MARGIN_RIGHT;
        $celdas = $tamTotal / 3;

        $pdf->SetDrawColor($rojoBorde = 0, $verdeBorde = 0, $azulBorde = 0);

        $pdf->Image($ruta = './img/' . $equipo["alias"] . '.png', $x = $pdf->GetX(), $y = $pdf->GetY() - 40, $ancho = 55, $alto = 55, '', '', '', $borde = false, $resolucion = 300, '', $saltoAuto = false, $alineacion = false, $redimensionar = 0);
        $pdf->Image($ruta = './img/Logo.png', $x =  $tamTotal - $pdf->GetX(), $y = $pdf->GetY() - 40, $ancho = 55, $alto = 55, '', '', '', $borde = false, $resolucion = 300, '', $saltoAuto = false, $alineacion = false, $redimensionar = 0);

        $pdf->Cell($width = $celdas, $height = 12, $texto = "", $borde = 'B', $saltoLD = 0, $alineacion = 'L');
        $pdf->Cell($width = $celdas, $height = 12, $texto = "", $borde = 0, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $celdas, $height = 12, $texto = "", $borde = 'B', $saltoLD = 1, $alineacion = 'C');
        $pdf->Cell($width = $celdas, $height = 12, $texto = "DELEGADO DEL EQUIPO:", $borde = 0, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $celdas, $height = 12, $texto = $currentDateObj->format('d/m/Y'), $borde = 0, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $celdas, $height = 12, $texto = "POR ASOFUTSAL:", $borde = 0, $saltoLD = 1, $alineacion = 'C');
        $pdf->LN(10);
        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 8);
        $pdf->Write($altoTexto = 10, $textoLink = "Esta ficha solo puede ser emitida por la Asofutsal y debe ser presentada en la mesa tecnica por el Delegado o Entrenador antes de iniciar el encuentro antes de iniciar el encuentro.", $enlace = '', $borde = 0, $alineacion = 'C', $llenado = true, $saltoLinea = 0, $url = '', $marcarEnlace = 0);
    }

    private function helper() {
        // Crear un nuevo objeto TCPDF
        $pdf = new TCPDF($orientacion = "PORTRAIT", $unidad = "pt", $formato = "letter");

        // aqui obtenemos ancho y alto de la pagina
        $pdf->getPageWidth();
        $pdf->getPageHeight();

        // Variables: obtenemos los valores de los diferntes margenes
        PDF_MARGIN_TOP;
        PDF_MARGIN_LEFT;
        PDF_MARGIN_RIGHT;
        PDF_MARGIN_BOTTOM;

        // Desactivar la impresión de la cabecera y el pie de página
        $pdf->SetPrintHeader($imprimirCabecera = false);
        $pdf->SetPrintFooter($imprimirPie = false);

        // Establecer márgenes: Izquierda, Arriba, Derecha
        $pdf->SetMargins($margenIzq = 10, $margenSup = 20, $margenDer = 10);

        // Establecer salto de página automático
        $pdf->SetAutoPageBreak($activar = true, $margenInf = 20);

        // Agregar una nueva página
        $pdf->AddPage();

        // Agregamos un salto de 10 líneas
        $pdf->Ln($lineas = 10);

        // Establecer la posición x y y
        $pdf->SetX($posX = 10);
        $pdf->SetY($posY = 10);
        $pdf->SetXY($posX = 10, $posY = 5);

        // Establecer fuente
        $pdf->SetFont($fuente = 'kanitb', $estilo = 'B', $tamano = 10);

        // Imprimir Celda
        $pdf->Cell($width = 110, $height = 10, $texto = "Mundo", $borde = 0, $saltoLD = 1, $alineacion = 'L');

        // Cambiar color de texto y fondo
        $pdf->SetTextColor($rojo = 255, $verde = 0, $azul = 0);
        $pdf->SetFillColor($rojoFondo = 255, $verdeFondo = 255, $azulFondo = 0);
        $pdf->SetDrawColor($rojoBorde = 0, $verdeBorde = 0, $azulBorde = 255);

        // Agregar texto con diferentes fuentes
        $pdf->SetFont($fuenteTitulo = 'helvetica', $estiloTitulo = 'B', $tamanoTitulo = 12);
        $pdf->Cell($width = 0, $height = 10, $textoTitulo = 'Título', $borde = 0, $saltoLD = 1, $alineacion = 'C');
        $pdf->SetFont($fuenteParrafo = 'helvetica', $estiloParrafo = '', $tamanoParrafo = 10);
        $pdf->MultiCell($width = 0, $height = 10, $textoParrafo = 'Este es un párrafo de texto con una fuente diferente.');

        // Imprimir Imagen
        $pdf->Image($ruta = './imagen.png', $x = 227.5, $y = 102, $ancho = 55, $alto = 55, '', '', '', $borde = false, $resolucion = 300, '', $saltoAuto = false, $alineacion = false, $redimensionar = 0);

        // Crear una tabla simple
        $pdf->SetFont($fuente = 'kanitb', $estilo = 'B', $tamano = 10);
        $pdf->Cell($anchoCol1 = 30, $altoCol1 = 10, $textoCol1 = 'Columna 1', $borde = 1);
        $pdf->Cell($anchoCol2 = 50, $altoCol2 = 10, $textoCol2 = 'Columna 2', $borde = 1);
        $pdf->Ln();
        $pdf->SetFont($fuente = 'kanitb', $estilo = '', $tamano = 10);
        $pdf->Cell($anchoCol1 = 30, $altoCol1 = 10, $textoDato1 = 'Dato 1', $borde = 1);
        $pdf->Cell($anchoCol2 = 50, $altoCol2 = 10, $textoDato2 = 'Dato 2', $borde = 1);

        // Agregar un link
        $pdf->Write($altoTexto = 10, $textoLink = 'Click aquí', $enlace = '', $borde = 0, $alineacion = 'L', $llenado = true, $saltoLinea = 0, $url = 'http://www.ejemplo.com', $marcarEnlace = 0);

        // Agregar un pie de página
        $pdf->SetY($posY = -15);
        $pdf->SetFont($fuentePie = 'helvetica', $estiloPie = 'I', $tamanoPie = 8);
        $pdf->Cell($anchoPie = 0, $altoPie = 10, $textoPie = 'Página ' . $pdf->PageNo(), $borde = 0, $saltoLD = 0, $alineacion = 'C');

        // Dibujar una línea y un rectángulo
        $pdf->Line($xInicio = 10, $yInicio = 20, $xFin = 200, $yFin = 20); // Línea horizontal
        $pdf->Rect($x = 10, $y = 25, $anchoRect = 100, $altoRect = 50, $tipoBorde = 'D'); // Rectángulo

        // Agregar un código QR
        $pdf->write2DBarcode($contenidoQR = 'http://www.ejemplo.com', $tipoQR = 'QRCODE,H', $x = 150, $y = 50, $anchoQR = 50, $altoQR = 50, $estiloQR = '', $ajusteQR = 'N');

        // Guardar archivo en el servidor
        $rutaArchivo = _DIR_ . '/nombreArchivo.pdf';
        $pdf->Output($rutaArchivo, $destino = 'F');

        // Impresión del archivo en el navegador
        $filename = "pruebasPDF";
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '.pdf"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        // Mostrar en el navegador
        $pdf->Output($filename . '.pdf', $destino = 'I');

        // Descargar archivo
        $pdf->Output($filename . '.pdf', $destino = 'D');
    }
}
