<?php

namespace App\Controllers;

use TCPDF;
use FPDI;

class newPDF extends BaseController {

    public function __construct() {
    }

    public function newpdf() {
        // $pdf = new TCPDF("LEGAL", "pt", "letter");
        $pdf = new TCPDF('p', 'pt', 'A3');
        // $pdf->getPathPaintOperator();

        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);

        $pdf->SetDrawColor($rojoBorde = 52, $verdeBorde = 52, $azulBorde = 52);

        $match = './asofutsal.matches_asofutsal.json';

        // Verifica si el archivo existe
        if (file_exists($match)) {
            // Lee el contenido del archivo
            $jsonContent = file_get_contents($match);

            // Decodifica el contenido JSON a un array asociativo
            $match = json_decode($jsonContent, true);
        }

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

        // Agregar Página
        $pdf->AddPage();
        $this->headerNewPDF($pdf, $match);

        foreach ($match['delegation'] as $key => $value) {
            $deporteEnUso = [];
            foreach ($delegacion as $key2 => $value2) {
                if ($value2['alias'] == $value) {
                    $deporteEnUso = $value2;
                }
            }
            $this->dataNewPDF($pdf, $deporteEnUso);
            $this->tablaPDF($pdf, $deporteEnUso);
        }

        // $this->observacionesPDF($pdf);
        // $this->footerPDF($pdf);


        /* imprecion del archivo va al final */
        $filename = "newPDF";
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '.pdf"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        $pdf->Output($filename . '.pdf', 'I');
        $pdf->Output($filename . '.pdf', 'D');
    }

    private function headerNewPDF($pdf, $match) {

        $tamTotal = $pdf->getPageWidth() - PDF_MARGIN_LEFT - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT - PDF_MARGIN_RIGHT;
        $tamImg = 70;
        $celdas = ($tamTotal - ($tamImg * 2));

        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 8);

        // $CAMPEONATO = 'LIGA DISTRITAL FUTSAL';
        $CAMPEONATO = $match['name'];

        $FECHA = $match['date']['$date'];
        $dateObject = new \DateTime($FECHA);
        $FECHA = $dateObject->format('d/m/Y');

        $SEDE = $match['sede'];
        $col1 = [
            $pdf->GetStringWidth('CAMPEONATO:') + 2, // campeonato
            $pdf->GetStringWidth($CAMPEONATO) + 2, // variable: campeonato
            $pdf->GetStringWidth('FECHA:') + 2, // fecha
            $pdf->GetStringWidth($FECHA) + 2, // variable: fecha
            $pdf->GetStringWidth('SEDE:') + 2, // sede
            $pdf->GetStringWidth($SEDE) + 2, // variable: sede
        ];

        $separacion = ($celdas - array_sum($col1)) / 4;
        // debug([
        //     "separacion" => $separacion,
        //     "celdas" => $celdas,
        //     "columnas" => $col1,
        // ]);

        $pdf->RoundedRect($pdf->getX(), $pdf->GetY(), $tamTotal, 90, 20, '1001', "D"); // Aquí "13" significa esquina superior izquierda y derecha
        $pdf->Ln($lineas = 15);

        $aux = 0;

        $pdf->Image($ruta = './img/Logo.png', $x = $pdf->getX(), $y = $pdf->GetY(), $ancho = $tamImg, $alto = $tamImg, '', '', '', $borde = false, $resolucion = 300, '', $saltoAuto = false, $alineacion = false, $redimensionar = 0);
        $pdf->Image($ruta = './img/ECR.png', $x = $pdf->getPageWidth() - $tamImg - $pdf->getX(), $y = $pdf->GetY(), $ancho = $tamImg, $alto = $tamImg, '', '', '', $borde = false, $resolucion = 300, '', $saltoAuto = false, $alineacion = false, $redimensionar = 0);

        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 13);
        $pdf->Cell($width = 0, $height = 14, $textoTitulo = 'COLEGIO NACIONAL DE ÁRBITROS Y ANOTADORES DE FUTBOL SALA', $borde = 0, $saltoLD = 1, $alineacion = 'C');
        $pdf->Ln($lineas = 10);

        $pdf->SetX($posX = $pdf->GetX() + $tamImg);

        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 8);
        $pdf->Cell($width =  $separacion, $height = 8, $textoTitulo = '', $borde = 0, $saltoLD = 0, $alineacion = '');

        $pdf->Cell($width =  $col1[($aux)], $height = 8, $textoTitulo = 'CAMPEONATO:', $borde = 0, $saltoLD = 0, $alineacion = 'l');
        $aux++;
        $pdf->SetFont($fuente = 'kanitextralight', $estilo = '', $tamano = 8);
        $pdf->Cell($width =  $col1[($aux)] + $separacion, $height = 8, $textoTitulo = $CAMPEONATO, $borde = 0, $saltoLD = 0, $alineacion = 'l');
        $aux++;
        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 8);
        $pdf->Cell($width =  $col1[($aux)], $height = 8, $textoTitulo = 'FECHA:', $borde = 0, $saltoLD = 0, $alineacion = 'l');
        $aux++;
        $pdf->SetFont($fuente = 'kanitextralight', $estilo = '', $tamano = 8);
        $pdf->Cell($width =  $col1[($aux)] + $separacion, $height = 8, $textoTitulo = $FECHA, $borde = 0, $saltoLD = 0, $alineacion = 'l');
        $aux++;
        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 8);
        $pdf->Cell($width =  $col1[($aux)], $height = 8, $textoTitulo = 'SEDE:', $borde = 0, $saltoLD = 0, $alineacion = 'l');
        $aux++;
        $pdf->SetFont($fuente = 'kanitextralight', $estilo = '', $tamano = 8);
        $pdf->Cell($width =  $col1[($aux)], $height = 8, $textoTitulo = $SEDE, $borde = 0, $saltoLD = 1, $alineacion = 'l');

        $pdf->Ln($lineas = 10);

        $pdf->SetX($posX = $pdf->GetX() + $tamImg);

        $CATEGORIA = $match['category'];
        $FASE = $match['group'];

        $HORA = $match['date']['$date'];
        $dateObject = new \DateTime($HORA);
        $HORA = $dateObject->format('h:i A');
        $CIUDAD = 'CIUDAD BOLIVAR';
        $col2 = [
            $pdf->GetStringWidth('CATEGORIA:') + 2, // categoria
            $pdf->GetStringWidth($CATEGORIA) + 2, // variable: categoria
            $pdf->GetStringWidth('FASE:') + 2, // fase
            $pdf->GetStringWidth($FASE) + 2, // variable: fase
            $pdf->GetStringWidth('HORA:') + 2, // hora
            $pdf->GetStringWidth($HORA) + 2, // variable: hora
            $pdf->GetStringWidth('CIUDAD:') + 2, // ciudad
            $pdf->GetStringWidth($CIUDAD) + 2, // variable: ciudad
        ];

        $separacion = ($celdas - array_sum($col2)) / 5;
        $aux = 0; // lo reiniciamos de nuevo

        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 8);
        $pdf->Cell($width =  $separacion, $height = 8, $textoTitulo = '', $borde = 0, $saltoLD = 0, $alineacion = '');

        $pdf->Cell($width = $col2[($aux)], $height = 8, $textoTitulo = 'CATEGORIA:', $borde = 0, $saltoLD = 0, $alineacion = 'l');
        $aux++;
        $pdf->SetFont($fuente = 'kanitextralight', $estilo = '', $tamano = 8);
        $pdf->Cell($width = $col2[($aux)] + $separacion, $height = 8, $textoTitulo = $CATEGORIA, $borde = 0, $saltoLD = 0, $alineacion = 'l');
        $aux++;
        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 8);
        $pdf->Cell($width = $col2[($aux)], $height = 8, $textoTitulo = 'FASE:', $borde = 0, $saltoLD = 0, $alineacion = 'l');
        $aux++;
        $pdf->SetFont($fuente = 'kanitextralight', $estilo = '', $tamano = 8);
        $pdf->Cell($width = $col2[($aux)] + $separacion, $height = 8, $textoTitulo = $FASE, $borde = 0, $saltoLD = 0, $alineacion = 'l');
        $aux++;
        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 8);
        $pdf->Cell($width = $col2[($aux)], $height = 8, $textoTitulo = 'HORA:', $borde = 0, $saltoLD = 0, $alineacion = 'l');
        $aux++;
        $pdf->SetFont($fuente = 'kanitextralight', $estilo = '', $tamano = 8);
        $pdf->Cell($width = $col2[($aux)] + $separacion, $height = 8, $textoTitulo = $HORA, $borde = 0, $saltoLD = 0, $alineacion = 'l');
        $aux++;
        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 8);
        $pdf->Cell($width = $col2[($aux)], $height = 8, $textoTitulo = 'CIUDAD:', $borde = 0, $saltoLD = 0, $alineacion = 'l');
        $aux++;
        $pdf->SetFont($fuente = 'kanitextralight', $estilo = '', $tamano = 8);
        $pdf->Cell($width = $col2[($aux)], $height = 8, $textoTitulo = $CIUDAD, $borde = 0, $saltoLD = 1, $alineacion = 'l');
        $aux++;

        $pdf->Ln($lineas = 10);
    }

    private function dataNewPDF($pdf, $team) {

        $tamTotal = $pdf->getPageWidth() - PDF_MARGIN_LEFT - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT - PDF_MARGIN_RIGHT;
        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 10);

        $equipo = $team['name'];
        $uniforme = '--';
        $asistente = 'MARCOS AROCHA';

        $col1 = [
            $pdf->GetStringWidth('EQUIPO A:') + 2, // equpo
            $pdf->GetStringWidth($equipo) + 2, // variable de equipo
            $pdf->GetStringWidth('UNIFORME: ') + 2, // uniforme color
            12, // circulo color 1
            12, // circulo color 2
            $pdf->GetStringWidth('ASISTENTE TÉCNICO: ') + 2, // asistente
            $pdf->GetStringWidth($asistente) + 2, // variable asiestente
        ];

        $separacion = ($tamTotal - array_sum($col1)) / 3;

        $aux = 0;
        $pdf->Ln($lineas = 20);

        $pdf->Cell($width = $col1[($aux)], $height = 12, $textoTitulo = 'EQUIPO A:', $borde = 0, $saltoLD = 0, $alineacion = '');
        $aux++;

        $pdf->SetFont($fuente = 'kanitextralight', $estilo = '', $tamano = 10);
        $pdf->Cell($width = $col1[($aux)] + $separacion, $height = 12, $textoTitulo = $equipo, $borde = 0, $saltoLD = 0, $alineacion = '');
        $aux++;

        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 10);
        $pdf->Cell($width = $col1[($aux)], $height = 12, $textoTitulo = 'UNIFORME:', $borde = 0, $saltoLD = 0, $alineacion = '');
        $aux++;

        $pdf->SetFont($fuente = 'kanitextralight', $estilo = '', $tamano = 10);

        $color1 = hexToRgb($team['color']);
        $pdf->SetFillColor($rojoFondo = $color1['r'], $verdeFondo = $color1['g'], $azulFondo = $color1['b']);

        $pdf->RoundedRect($x = $pdf->GetX(), $y = $pdf->GetY(), $w = $col1[($aux)], $h = $col1[($aux)], 6, '1111', 'DF');
        $pdf->Cell($width = $col1[($aux)], $height = 12, $textoTitulo = '', $borde = 0, $saltoLD = 0, $alineacion = '');
        $aux++;

        $color1 = hexToRgb($team['color2']);
        $pdf->SetFillColor($rojoFondo = $color1['r'], $verdeFondo = $color1['g'], $azulFondo = $color1['b']);

        $pdf->RoundedRect($x = $pdf->GetX() + 1, $y = $pdf->GetY(), $w = $col1[($aux)], $h = $col1[($aux)], 6, '1111', 'DF');
        $pdf->Cell($width = $col1[($aux)]  + $separacion, $height = 12, $textoTitulo = '', $borde = 0, $saltoLD = 0, $alineacion = '');
        $aux++;

        $posicion = $pdf->GetX();

        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 10);
        $pdf->Cell($width = $col1[($aux)], $height = 12, $textoTitulo = 'ASISTENTE TÉCNICO:', $borde = 1, $saltoLD = 0, $alineacion = '');
        $aux++;

        $pdf->SetFont($fuente = 'kanitextralight', $estilo = '', $tamano = 10);
        $pdf->Cell($width = $col1[($aux)] + $separacion, $height = 12, $textoTitulo = $asistente, $borde = 1, $saltoLD = 1, $alineacion = '');

        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 10);
        $pdf->SetX($posicion);
        $calc = $pdf->GetStringWidth('CAPITAN: ') + 2;
        $pdf->Cell($width = $calc, $height = 12, $textoTitulo = 'CAPITAN:', $borde = 1, $saltoLD = 0, $alineacion = '');

        $tamanoMarcos = ($col1[5] + $col1[6] + $separacion) - $calc;
        $pdf->SetFont($fuente = 'kanitextralight', $estilo = '', $tamano = 10);
        $pdf->Cell($width = $tamanoMarcos, $height = 12, $textoTitulo = 'MARCOS AROCHA', $borde = 1, $saltoLD = 1, $alineacion = '');

        $pdf->Ln($lineas = -12);

        // debug([
        //     'posicion' => $posicion,
        //     'separacion' => $separacion,
        //     'calculo' => $calc,
        //     'tamano total' => $tamTotal,
        //     'suma' => $tamTotal  - ($posicion + $calc),
        // ]);

        // Dimensiones de la imagen
        $anchoaltoImagen = 65;

        // Margen adicional alrededor de la imagen dentro del rectángulo
        $margenRectangulo = 5;

        // Ancho y alto del rectángulo redondeado
        $anchoRect = $anchoaltoImagen + (2 * $margenRectangulo);
        $altoRect = $anchoaltoImagen + (2 * $margenRectangulo);

        // Dibujar el rectángulo redondeado centrado
        $pdf->RoundedRect($pdf->GetX(), $pdf->GetY(), $anchoRect, $altoRect, 20, '1111', 'D');

        $posicionXImagen = $pdf->GetX() + $margenRectangulo;
        $posicionYImagen = $pdf->GetY() + $margenRectangulo;

        // Colocar la imagen centrada dentro del rectángulo
        $pdf->Image('./img/' . $team['alias'] . '.png', $posicionXImagen, $posicionYImagen, $anchoaltoImagen, $anchoaltoImagen, '', '', '', false, 300, '', false, false, 0);

        $nuevoX = $pdf->GetX() + $anchoaltoImagen +  (2 * $margenRectangulo) + 10;
        $nuevoY = $pdf->GetY() + 12;
        $pdf->SetXY($nuevoX, $nuevoY);

        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 10);
        $anchotecnico = $pdf->GetStringWidth('TECNICO: ') + 2;

        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 12);
        $anchoiniciales = $pdf->GetStringWidth('INICIALES: ') + 2;

        $ancho = $anchoiniciales + (30 * 4) - $anchotecnico;

        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 10);
        $pdf->Cell($width = $anchotecnico, $height = 12, $textoTitulo = 'TECNICO:', $borde = 1, $saltoLD = 0, $alineacion = '');

        $pdf->SetFont($fuente = 'kanitextralight', $estilo = '', $tamano = 10);
        $pdf->Cell($width = $ancho, $height = 12, $textoTitulo = 'Matias', $borde = 1, $saltoLD = 0, $alineacion = '');

        $pdf->SetFillColor($rojoFondo = 255, $verdeFondo = 211, $azulFondo = 0);
        $pdf->Cell($width = 10, $height = 12, $textoTitulo = '', $borde = 1, $saltoLD = 0, $alineacion = '', true);

        $pdf->SetFillColor($rojoFondo = 5, $verdeFondo = 14, $azulFondo = 155);
        $pdf->Cell($width = 10, $height = 12, $textoTitulo = '', $borde = 1, $saltoLD = 0, $alineacion = '', true);

        $pdf->SetFillColor($rojoFondo = 254, $verdeFondo = 0, $azulFondo = 0);
        $pdf->Cell($width = 10, $height = 12, $textoTitulo = '', $borde = 1, $saltoLD = 1, $alineacion = '', true);

        $pdf->SetX($nuevoX);
        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 12);

        $pdf->Cell($width = $anchoiniciales, $height = 14, $textoTitulo = 'INICIALES:', $borde = 1, $saltoLD = 0, $alineacion = '');

        $pdf->SetTextColor($rojo = 255, $verde = 255, $azul = 255);
        $pdf->SetFillColor($rojoFondo = 0, $verdeFondo = 132, $azulFondo = 214);
        $nuevoAncho = 30;
        // debug($nuevoAncho);

        $pdf->Cell($width = $nuevoAncho, $height = 14, $textoTitulo = '01', $borde = 1, $saltoLD = 0, $alineacion = 'C', true);
        $pdf->Cell($width = $nuevoAncho, $height = 14, $textoTitulo = '02', $borde = 1, $saltoLD = 0, $alineacion = 'C', true);
        $pdf->Cell($width = $nuevoAncho, $height = 14, $textoTitulo = '03', $borde = 1, $saltoLD = 0, $alineacion = 'C', true);
        $pdf->Cell($width = $nuevoAncho, $height = 14, $textoTitulo = '04', $borde = 1, $saltoLD = 0, $alineacion = 'C', true);
        $pdf->Cell($width = $nuevoAncho, $height = 14, $textoTitulo = '05', $borde = 1, $saltoLD = 1, $alineacion = 'C', true);

        $nuevoY = $pdf->GetY() + 10;
        $pdf->SetXY($nuevoX, $nuevoY);
        $pdf->SetTextColor($rojo = 6, $verde = 0, $azul = 0);

        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 12);
        $pdf->Cell($width = $pdf->GetStringWidth('TIEMPOS: ') + 2, $height = 16, $textoTitulo = 'TIEMPOS:', $borde = 1, $saltoLD = 0, $alineacion = 'C');

        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 8);
        $pdf->Cell($width = $pdf->GetStringWidth('1ER ') + 2, $height = 16, $textoTitulo = '1ER', $borde = 'TBL', $saltoLD = 0, $alineacion = 'L');

        $pdf->SetFont($fuente = 'kanitextralight', $estilo = '', $tamano = 8);
        $pdf->Cell($width = $pdf->GetStringWidth('PERIODO ') + 2, $height = 16, $textoTitulo = 'PERIODO', $borde = 'TBR', $saltoLD = 0, $alineacion = 'R');

        $pdf->SetTextColor($rojo = 255, $verde = 255, $azul = 255);
        $pdf->SetFont($fuente = 'kanit', $estilo = 'B', $tamano = 12);
        $pdf->Cell($width = $nuevoAncho, $height = 16, $textoTitulo = "12'", $borde = 1, $saltoLD = 0, $alineacion = 'C', true);
        $pdf->Cell($width = $nuevoAncho, $height = 16, $textoTitulo = 'X', $borde = 1, $saltoLD = 0, $alineacion = 'C', true);

        $pdf->SetTextColor($rojo = 6, $verde = 0, $azul = 0);
        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 8);
        $pdf->Cell($width = $pdf->GetStringWidth(" 2DO") + 2, $height = 16, $textoTitulo = '2DO', $borde = 'TBL', $saltoLD = 0, $alineacion = 'L');

        $pdf->SetFont($fuente = 'kanitextralight', $estilo = '', $tamano = 8);
        $pdf->Cell($width = $pdf->GetStringWidth("PERIODO ") + 2, $height = 16, $textoTitulo = 'PERIODO', $borde = 'TBR', $saltoLD = 0, $alineacion = 'R');

        $pdf->SetTextColor($rojo = 255, $verde = 255, $azul = 255);
        $pdf->SetFont($fuente = 'kanit', $estilo = 'B', $tamano = 12);
        $pdf->Cell($width = $nuevoAncho, $height = 16, $textoTitulo = "22'", $borde = 1, $saltoLD = 0, $alineacion = 'C', true);
        $pdf->Cell($width = $nuevoAncho, $height = 16, $textoTitulo = "31'", $borde = 1, $saltoLD = 1, $alineacion = 'C', true);
    }

    private function tablaPDF($pdf) {
        $tamTotal = $pdf->getPageWidth() - PDF_MARGIN_LEFT - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT - PDF_MARGIN_RIGHT;

        $varNum = $pdf->GetStringWidth(" N° ");
        $ancho = ($tamTotal - $varNum) / 5;

        $pdf->SetTextColor($rojo = 6, $verde = 0, $azul = 0);

        $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 10);
        // $pdf->SetFont($fuente = 'kanitextralight', $estilo = '', $tamano = 10);
        $pdf->Ln($lineas = 15);

        $pdf->SetFont($fuente = 'kanitb', $estilo = '', $tamano = 10);

        $numFicha = $ancho - 35;
        $nombreApe = $ancho + 35;
        $pdf->Cell($width = $numFicha, $height = 26, $textoTitulo = "N° FICHA", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $nombreApe, $height = 26, $textoTitulo = "NOMBRE Y APELLIDO", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $varNum, $height = 26, $textoTitulo = "N°", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $posicion = $pdf->GetX();

        $pdf->Cell($width = $ancho, $height = 13, $textoTitulo = "FALTAS PERS", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $ancho, $height = 13, $textoTitulo = "TARJETAS", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $ancho, $height = 13, $textoTitulo = "GOLES", $borde = 1, $saltoLD = 1, $alineacion = 'C');


        $pdf->SetX($posicion);
        $calcNums = $ancho / 5;
        $pdf->Cell($width = $calcNums, $height = 13, $textoTitulo = "1", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $calcNums, $height = 13, $textoTitulo = "2", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $calcNums, $height = 13, $textoTitulo = "3", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $posicion2 = $pdf->GetX();

        $pdf->Cell($width = $calcNums, $height = 13, $textoTitulo = "4", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $calcNums, $height = 13, $textoTitulo = "5", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $posicion = $pdf->GetX();


        $pdf->SetX($posicion);
        $calcTarjetas = $ancho / 3;

        $pdf->SetFillColor($rojoFondo = 255, $verdeFondo = 211, $azulFondo = 0);
        $pdf->Cell($width = $calcTarjetas, $height = 13, $textoTitulo = "AMA", $borde = 1, $saltoLD = 0, $alineacion = 'C', true);

        $pdf->SetTextColor($rojo = 255, $verde = 255, $azul = 255);
        $pdf->SetFillColor($rojoFondo = 5, $verdeFondo = 14, $azulFondo = 155);
        $pdf->Cell($width = $calcTarjetas, $height = 13, $textoTitulo = "AZUL", $borde = 1, $saltoLD = 0, $alineacion = 'C', true);

        $pdf->SetFillColor($rojoFondo = 254, $verdeFondo = 0, $azulFondo = 0);
        $pdf->Cell($width = $calcTarjetas, $height = 13, $textoTitulo = "ROJA", $borde = 1, $saltoLD = 0, $alineacion = 'C', true);
        $posicion = $pdf->GetX();


        $varTotal = $pdf->GetStringWidth(" TOTAL ");
        $pdf->SetX($posicion);
        $pdf->SetTextColor($rojo = 6, $verde = 0, $azul = 0);
        $calc = ($ancho - $varTotal) / 3;
        $pdf->Cell($width = $calc, $height = 13, $textoTitulo = "AG", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $calc, $height = 13, $textoTitulo = "1P", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $calc, $height = 13, $textoTitulo = "2P", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $varTotal, $height = 13, $textoTitulo = "TOTAL", $borde = 1, $saltoLD = 1, $alineacion = 'C');

        $celdas = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        foreach ($celdas as $key => $value) {
            $pdf->Cell($width = $numFicha, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
            $pdf->Cell($width = $nombreApe, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
            $pdf->Cell($width = $varNum, $height = 13, $textoTitulo = $value, $borde = 1, $saltoLD = 0, $alineacion = 'C');
            $pdf->Cell($width = $calcNums, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
            $pdf->Cell($width = $calcNums, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
            $pdf->Cell($width = $calcNums, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
            $pdf->Cell($width = $calcNums, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
            $pdf->Cell($width = $calcNums, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
            $calcTarjetas2 = ($ancho / 3) / 2;
            $pdf->Cell($width = $calcTarjetas2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
            $pdf->Cell($width = $calcTarjetas2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
            $pdf->Cell($width = $calcTarjetas, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
            $pdf->Cell($width = $calcTarjetas, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
            $pdf->Cell($width = $calc, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
            $pdf->Cell($width = $calc, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
            $pdf->Cell($width = $calc, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
            $pdf->Cell($width = $varTotal, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 1, $alineacion = 'C');
        }


        $tamFaltas = $varNum + $nombreApe + $numFicha + $calcNums * 2;

        $tamDivisor = ($tamTotal - $tamFaltas) / 4;

        $pdf->Cell($width = $tamFaltas, $height = 13, $textoTitulo = "FALTAS ACUMULATIVAS", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $calcNums * 3, $height = 13, $textoTitulo = "TIRO INICIAL", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $calcTarjetas, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $calcTarjetas, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $calcTarjetas + $calc, $height = 13, $textoTitulo = "FINALISTAS", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $calc * 2 + $varTotal, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 1, $alineacion = 'C');


        $tamFaltasmini = $tamFaltas / 14;
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "1°P", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "2°P", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');


        $pdf->Cell($width = $calcNums * 3, $height = 13, $textoTitulo = "INGRESOS", $borde = 1, $saltoLD = 0, $alineacion = 'C');

        $ingresos = ($calcTarjetas * 2 + $calcTarjetas + $calc + $calc * 2 + $varTotal) / 13;

        $pdf->Cell($width = $ingresos, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $ingresos, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $ingresos, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $ingresos, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $ingresos, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $ingresos, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $ingresos, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $ingresos, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $ingresos, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $ingresos, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $ingresos, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $ingresos, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $ingresos, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 1, $alineacion = 'C');


        $tamFaltasmini = ($numFicha +  $nombreApe +  $varNum + $ancho + $calcTarjetas * 2) / 21;

        $pdf->Cell($width = $tamFaltasmini * 6, $height = 13, $textoTitulo = "GOL/MIN", $borde = 1, $saltoLD = 0, $alineacion = 'L');

        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');

        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');

        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');

        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');

        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');

        // $tnmPenales = $calcTarjetas + $calc * 3 + $varTotal;
        $tnmPenales = $tamTotal - $tamFaltasmini * 21;

        $pdf->Cell($width = $tnmPenales, $height = 13, $textoTitulo = "PENALES", $borde = 1, $saltoLD = 1, $alineacion = 'C');

        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');

        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');

        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');

        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');

        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');

        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');

        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');


        $tnmPenales3 = $tnmPenales / 3;
        $pdf->Cell($width = $tnmPenales3, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tnmPenales3, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tnmPenales3, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 1, $alineacion = 'C');



        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');

        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');

        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');

        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');

        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');

        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');

        $pdf->Cell($width = $tamFaltasmini, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tamFaltasmini * 2, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');


        $tnmPenales3 = $tnmPenales / 3;
        $pdf->Cell($width = $tnmPenales3, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tnmPenales3, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'C');
        $pdf->Cell($width = $tnmPenales3, $height = 13, $textoTitulo = "", $borde = 1, $saltoLD = 1, $alineacion = 'C');
    }

    private function observacionesPDF($pdf) {
        $pdf->Ln(30);
        $tamTotal = $pdf->getPageWidth() - PDF_MARGIN_LEFT - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT - PDF_MARGIN_RIGHT;

        $ancho = $tamTotal / 7;

        $col1 = [
            $tamTotal * 0.22,
            $tamTotal * 0.38,
            $tamTotal * 0.10,
            $tamTotal * 0.10,
            $tamTotal * 0.10,
            $tamTotal * 0.05,
            $tamTotal * 0.05,
        ];

        $aux = 0;

        $pdf->SetFont($fuente = 'kanitb', $estilo = '', $tamano = 12);

        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "OFICIALES ÁRBITRAJE", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;
        $pdf->SetFont($fuente = 'kanitb', $estilo = '', $tamano = 10);

        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;
        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;
        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "HORA DE INICIO", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;
        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "HORA FINAL", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;
        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "A", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;
        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "B", $borde = 1, $saltoLD = 1, $alineacion = 'L');
        $aux++;

        $aux = 0;

        $pdf->SetFont($fuente = 'kanitb', $estilo = '', $tamano = 12);

        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "ÁRBITRO 01", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;

        $pdf->SetFont($fuente = 'kanitb', $estilo = '', $tamano = 10);

        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;
        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "1 ° P", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;
        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;
        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;
        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;
        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "", $borde = 1, $saltoLD = 1, $alineacion = 'L');
        $aux++;

        $aux = 0;

        $pdf->SetFont($fuente = 'kanitb', $estilo = '', $tamano = 12);

        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "ÁRBITRO 02", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;

        $pdf->SetFont($fuente = 'kanitb', $estilo = '', $tamano = 10);

        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;
        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "2 ° P", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;
        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;
        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;
        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;
        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "", $borde = 1, $saltoLD = 1, $alineacion = 'L');
        $aux++;

        $aux = 0;
        $pdf->SetFont($fuente = 'kanitb', $estilo = '', $tamano = 12);

        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "ANOTADOR", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;

        $pdf->SetFont($fuente = 'kanitb', $estilo = '', $tamano = 10);
        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;
        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "PENALES", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;

        $va1 = $col1[($aux)];
        $aux++;
        $va2 = $col1[($aux)];
        $aux++;
        $va3 = $col1[($aux)];
        $aux++;

        $pdf->Cell($width = $col1[($aux)] + $va1 + $va2 + $va3, $height = 20, $textoTitulo = "", $borde = 1, $saltoLD = 1, $alineacion = 'L');
        $aux++;


        $aux = 0;
        $pdf->SetFont($fuente = 'kanitb', $estilo = '', $tamano = 12);

        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "CRONOMETRISTA", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;

        $pdf->SetFont($fuente = 'kanitb', $estilo = '', $tamano = 10);

        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;
        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "RES FINAL", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;

        $colChiquita = $col1[($aux)] / 3;

        $pdf->Cell($width = $colChiquita, $height = 20, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $pdf->Cell($width = $colChiquita, $height = 20, $textoTitulo = "", $borde = 1, $saltoLD = 0, $alineacion = 'L');
        $aux++;

        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "GANADOR", $borde = 1, $saltoLD = 0, $alineacion = 'L');

        $subset = array_slice($col1, 0, 3);
        $sum = array_sum($subset) + ($colChiquita * 2) + $col1[($aux)];

        // debug([
        //     "tamano total" => $tamTotal,
        //     "suma" => $sum,
        //     "resta" => $tamTotal - $sum,
        // ]);
        $pdf->Cell($width = $tamTotal - $sum, $height = 20, $textoTitulo = "", $borde = 1, $saltoLD = 1, $alineacion = 'L');

        $aux = 0;
        $pdf->SetFont($fuente = 'kanitb', $estilo = '', $tamano = 12);

        $pdf->Cell($width = $col1[($aux)], $height = 20, $textoTitulo = "OBSERVACIONES", $borde = 1, $saltoLD = 0, $alineacion = 'L');

        $pdf->Cell($width = array_sum($col1) - $col1[($aux)], $height = 20, $textoTitulo = "", $borde = 'LR', $saltoLD = 1, $alineacion = 'L');
        $pdf->Cell($width = $tamTotal, $height = 70, $textoTitulo = "", $borde = 'LRB', $saltoLD = 0, $alineacion = 'L');
    }

    private function footerPDF($pdf) {
        $tamTotal = $pdf->getPageWidth() - PDF_MARGIN_LEFT - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT - PDF_MARGIN_RIGHT;
        $pdf->SetAutoPageBreak(true, 0);

        $valor = $pdf->getPageHeight() - 65;
        $pdf->SetY($valor);
        $pdf->SetFont($fuente = 'kanitextralight', $estilo = '', $tamano = 10);

        $pdf->Write($altoTexto = 10, $textoLink = "Planilla 3001 - 15/07/2024 - Hora de expedición: 3:04 PM (Solicitado porr: Marcos Arocha)", $enlace = '', $borde = 0, $alineacion = 'L', $llenado = true, $saltoLinea = 0, $url = '', $marcarEnlace = 0);
        $pdf->SetY($valor);
        $pdf->Write($altoTexto = 10, $textoLink = "Powered by SHORTAE / SMARPORT", $enlace = '', $borde = 0, $alineacion = 'R', $llenado = true, $saltoLinea = 0, $url = '', $marcarEnlace = 0);


        // $pdf->SetY(200);
        // $pdf->SetFont($fuente = 'kanit', $estilo = 'b', $tamano = 8);
        // $pdf->Write($altoTexto = 10, $textoLink = "N° PLANILLA: ", $enlace = '', $borde = 0, $alineacion = 'R', $llenado = true, $saltoLinea = 0, $url = '', $marcarEnlace = 0);

        // $pdf->SetFont($fuente = 'kanit', $estilo = '', $tamano = 8);
        // $pdf->Write($altoTexto = 10, $textoLink = "3001", $enlace = '', $borde = 0, $alineacion = 'R', $llenado = true, $saltoLinea = 0, $url = '', $marcarEnlace = 0);

        $pdf->SetY(200); // Posiciona en la coordenada Y

        // Primer texto: "N° PLANILLA: "
        $pdf->SetFont('kanit', '', 10);
        $anchoNumero = $pdf->GetStringWidth("3001") + 2;
        $pdf->SetFont('kanit', 'b', 10);
        $pdf->Cell($width = $tamTotal - $anchoNumero, $height = 16, $textoTitulo = "N° PLANILLA:", $borde = 0, $saltoLD = 0, $alineacion = 'R');

        // Segundo texto: "3001"
        $pdf->SetFont('kanit', '', 10);
        $pdf->Cell($width = $anchoNumero, $height = 16, $textoTitulo = "3001", $borde = 0, $saltoLD = 0, $alineacion = 'L');
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
