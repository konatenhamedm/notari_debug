<?php


namespace App\Service;


use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Cell;

class Util
{
    static $cssColorNames = array(
        "aliceblue" => "F0F8FF",
        "antiquewhite" => "FAEBD7",
        "aqua" => "00FFFF",
        "aquamarine" => "7FFFD4",
        //"azure" => "F0FFFF",
        //"beige" => "F5F5DC",
        "bisque" => "FFE4C4",
        "black" => "000000",
        "blanchedalmond" => "FFEBCD",
        "blue" => "0000FF",
        "blueviolet" => "8A2BE2",
        "brown" => "A52A2A",
        "burlywood" => "DEB887",
        "cadetblue" => "5F9EA0",
        "chartreuse" => "7FFF00",
        "chocolate" => "D2691E",
        "coral" => "FF7F50",
        "cornflowerblue" => "6495ED",
        //"cornsilk" => "FFF8DC",
        "crimson" => "DC143C",
        "cyan" => "00FFFF",
        "darkblue" => "00008B",
        "darkcyan" => "008B8B",
        "darkgoldenrod" => "B8860B",
        //"darkgray" => "A9A9A9",
        "darkgreen" => "006400",
        //"darkgrey" => "A9A9A9",
        "darkkhaki" => "BDB76B",
        "darkmagenta" => "8B008B",
        "darkolivegreen" => "556B2F",
        "darkorange" => "FF8C00",
        "darkorchid" => "9932CC",
        "darkred" => "8B0000",
        "darksalmon" => "E9967A",
        "darkseagreen" => "8FBC8F",
        "darkslateblue" => "483D8B",
        "darkslategray" => "2F4F4F",
        "darkslategrey" => "2F4F4F",
        "darkturquoise" => "00CED1",
        "darkviolet" => "9400D3",
        "deeppink" => "FF1493",
        "deepskyblue" => "00BFFF",
        /*"dimgray" => "696969",
        "dimgrey" => "696969",*/
        "dodgerblue" => "1E90FF",
        "firebrick" => "B22222",
        "floralwhite" => "FFFAF0",
        "forestgreen" => "228B22",
        "fuchsia" => "FF00FF",
        "gainsboro" => "DCDCDC",
        //"ghostwhite" => "F8F8FF",
        "gold" => "FFD700",
        "goldenrod" => "DAA520",
        //"gray" => "808080",
        "green" => "008000",
        "greenyellow" => "ADFF2F",
        "grey" => "808080",
        "honeydew" => "F0FFF0",
        "hotpink" => "FF69B4",
        "indianred" => "CD5C5C",
        "indigo" => "4B0082",
        "ivory" => "FFFFF0",
        "khaki" => "F0E68C",
        "lavender" => "E6E6FA",
        "lavenderblush" => "FFF0F5",
        "lawngreen" => "7CFC00",
        "lemonchiffon" => "FFFACD",
        "lightblue" => "ADD8E6",
        "lightcoral" => "F08080",
        "lightcyan" => "E0FFFF",
        "lightgoldenrodyellow" => "FAFAD2",
        //"lightgray" => "D3D3D3",
        "lightgreen" => "90EE90",
        "lightgrey" => "D3D3D3",
        "lightpink" => "FFB6C1",
        "lightsalmon" => "FFA07A",
        "lightseagreen" => "20B2AA",
        "lightskyblue" => "87CEFA",
        "lightslategray" => "778899",
        "lightslategrey" => "778899",
        "lightsteelblue" => "B0C4DE",
        "lightyellow" => "FFFFE0",
        "lime" => "00FF00",
        "limegreen" => "32CD32",
        "linen" => "FAF0E6",
        "magenta" => "FF00FF",
        "maroon" => "800000",
        "mediumaquamarine" => "66CDAA",
        "mediumblue" => "0000CD",
        "mediumorchid" => "BA55D3",
        "mediumpurple" => "9370DB",
        "mediumseagreen" => "3CB371",
        "mediumslateblue" => "7B68EE",
        "mediumspringgreen" => "00FA9A",
        "mediumturquoise" => "48D1CC",
        "mediumvioletred" => "C71585",
        "midnightblue" => "191970",
        "mintcream" => "F5FFFA",
        "mistyrose" => "FFE4E1",
        "moccasin" => "FFE4B5",
        "navajowhite" => "FFDEAD",
        "navy" => "000080",
        "oldlace" => "FDF5E6",
        "olive" => "808000",
        "olivedrab" => "6B8E23",
        "orange" => "FFA500",
        "orangered" => "FF4500",
        "orchid" => "DA70D6",
        "palegoldenrod" => "EEE8AA",
        "palegreen" => "98FB98",
        "paleturquoise" => "AFEEEE",
        "palevioletred" => "DB7093",
        "papayawhip" => "FFEFD5",
        "peachpuff" => "FFDAB9",
        "peru" => "CD853F",
        "pink" => "FFC0CB",
        "plum" => "DDA0DD",
        "powderblue" => "B0E0E6",
        "purple" => "800080",
        "red" => "FF0000",
        "rosybrown" => "BC8F8F",
        "royalblue" => "4169E1",
        "saddlebrown" => "8B4513",
        "salmon" => "FA8072",
        "sandybrown" => "F4A460",
        "seagreen" => "2E8B57",
        "seashell" => "FFF5EE",
        "sienna" => "A0522D",
        "silver" => "C0C0C0",
        "skyblue" => "87CEEB",
        "slateblue" => "6A5ACD",
        "slategray" => "708090",
        "slategrey" => "708090",
        "snow" => "FFFAFA",
        "springgreen" => "00FF7F",
        "steelblue" => "4682B4",
        "tan" => "D2B48C",
        "teal" => "008080",
        "thistle" => "D8BFD8",
        "tomato" => "FF6347",
        "turquoise" => "40E0D0",
        "violet" => "EE82EE",
        "wheat" => "F5DEB3",

        "yellow" => "FFFF00",
        "yellowgreen" => "9ACD32",
    );


    /**
     * @var mixed
     */
    private $em;

    /**
     * @var mixed
     */
    private $uploadsPath;


    private $targetDir;


    const MOIS = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

    const MOIS_SHORT = [1 => "Jan","Fév","Mar","Avr","Mai","Jui","Juil","Aoû","Sep","Oct","Nov","Déc"];

    /**
     * @param EntityManagerInterface $em
     * @param string $uploadsPath
     * @param string $targetDir
     */
    public function __construct(EntityManagerInterface $em, string $uploadsPath , string $targetDir)
    {
        $this->em        = $em;
        $this->uploadsPath = $uploadsPath;
        $this->targetDir = $targetDir;
    }

    /**
     * @param PhpWord $phpWord
     * @param $width
     */
    public function wordHeaderCOURRIER(PhpWord $phpWord, &$section, $mode = 'portrait', $type = 'auto')
    {
        /*$header = $section->addHeader();
        $header->setType($type);
        $width  = $mode == 'portrait' ? 450 : 650;
        $height = $mode == 'portrait' ? 45 : 70;
        $header->addImage($this->uploadDir . '/img/doc_header.png', ['height' => $height, 'align' => 'center', 'width' => $width, 'positioning' => \PhpOffice\PhpWord\Style\Image::POSITION_ABSOLUTE]);*/
        $header = $section->addHeader();
        $header->setType($type);
        $fontName = 'Cambria';
        $cell1W   = ['portrait' => 1000, 'landscape' => 2000];
        $cell2W   = ['portrait' => 6000, 'landscape' => 20000];
        $cell3W   = ['portrait' => 1000, 'landscape' => 2000];
        $cell4W   = ['portrait' => 1000, 'landscape' => 2000];

        $img1W = [
            'portrait' => ['width' => 1.72, 'height' => 1.31, 'align' => 'center', 'unit' => 'cm']
            , 'landscape' => ['width' => 2.12, 'height' => 1.61, 'align' => 'center', 'unit' => 'cm'],
        ];
        $img2W = [
            'portrait'  => ['width' => 7.65, 'height' => 1.46, 'align' => 'center', 'unit' => 'cm'],
            'landscape' => ['width' => 9.75, 'height' => 1.86, 'align' => 'center', 'unit' => 'cm'],
        ];

        $headerFonts = [
            'portrait' => ['size' => 8, 'italic' => false, 'bold' => true, 'name' => $fontName]
            , 'landscape' => ['size' => 9, 'italic' => false, 'bold' => true, 'name' => $fontName],
        ];

        $textParagraphStyle = ['align' => 'center', 'spaceAfter' => 0, 'spaceBefore' => 0];

        $headerFont = $headerFonts[$mode];

        $w1 = $cell1W[$mode];
        $w2 = $cell2W[$mode];
        $w3 = $cell3W[$mode];
        $w4 = $cell4W[$mode];

        //$imageWidth = $logoWidths[$mode];

        $styleTable = ['borderSize' => 0, 'borderColor' => 'ffffff', 'cellPadding' => 100, 'cellMargin' => 100];
        $phpWord->addTableStyle('headerTable', $styleTable);
        $table = $header->addTable('headerTable');
        $table->addRow();
        $cell = $table->addCell();
        /*$cell->addImage($this->uploadsPath . '/img/logo-ampmn.png', $img1W[$mode]);*/

        $w = $w1+$w2+$w2+$w4;

        $table->addRow();
        //$headerFont = ['size' => 8, 'italic' => true, 'name' => 'Arial Narrow'];
        $cell = $table->addCell($w);
        $cell->addText('Renée Claire KASSY', $headerFont);
        $cell->addText('Notaire', $headerFont);

        //$table->addRow();
        //$headerFont = ['size' => 8, 'italic' => true, 'name' => 'Arial Narrow'];
        //$cell = $table->addCell($w);
        //$cell->addText(' Secrétariat Exécutif du Conseil National pour la Nutrition,  l’Alimentation et le DPE', $headerFont, $textParagraphStyle);
        //$cell->addText('--------------', $headerFont, $textParagraphStyle);

        $table->addRow();

        $cell = $table->addCell($w);
       /* $cell->addImage($this->uploadsPath . '/img/logo-pmnprint.png', $img2W[$mode]);*/
        $headerFonts[$mode]['size'] = 13;
        $cell->addText('-----------------------------------------------------------------------------------------------------------------------------------------------', $headerFont, $textParagraphStyle);

        /*$cell = $table->addCell($w4);
        $cell->addImage($this->uploadDir . '/img/header_wb.jpg', $img3W[$mode]);*/

        return $phpWord;

    }
    /**
     * @param PhpWord $phpWord
     * @param $section
     * @param $mode
     * @param $fontName
     * @return mixed
     */
    public function wordHeaderPSNDEA(PhpWord $phpWord, &$section, $mode = 'portrait', $fontName = 'Arial Narrow', $type = 'auto')
    {
        $header = $section->addHeader();
        $header->setType($type);
        $fontName       = $fontName ?: 'Arial Narrow';
        $logoCellWidths = ['portrait' => 1000, 'landscape' => 2000];
        $textCellWidths = ['portrait' => 6000, 'landscape' => 20000];
        $lastCellWidths = ['portrait' => 1000, 'landscape' => 2000];
        $logoWidths     = ['portrait' => 80, 'landscape' => 100];
        $headerFonts    = [
            'portrait' => ['size' => 6, 'italic' => true, 'name' => $fontName]
            , 'landscape' => ['size' => 8, 'italic' => true, 'name' => $fontName],
        ];

        $textParagraphStyle = ['align' => Cell::VALIGN_CENTER, 'spaceAfter' => 0];

        $headerFont = $headerFonts[$mode];

        $w1 = $logoCellWidths[$mode];
        $w2 = $textCellWidths[$mode];
        $w3 = $lastCellWidths[$mode];

        $imageWidth = $logoWidths[$mode];

        $styleTable = ['borderSize' => 0, 'borderColor' => 'ffffff', 'cellPadding' => 100, 'cellMargin' => 100];
        $phpWord->addTableStyle('headerTable', $styleTable);
        $table = $header->addTable('headerTable');
        $table->addRow();
        $cell = $table->addCell($w1);
        $cell->addImage($this->uploadsPath . '/img/logo-ampmn.png', ['width' => $imageWidth, 'align' => 'left']);
        //$headerFont = ['size' => 8, 'italic' => true, 'name' => 'Arial Narrow'];
        $cell = $table->addCell($w2);
        $cell->addText('République de Côte d\'Ivoire', $headerFont, $textParagraphStyle);
        $cell->addText('Union-Discipline-Travail', $headerFont, $textParagraphStyle);
        $cell->addText('-------------------------', $headerFont, $textParagraphStyle);
        $cell->addText('MINISTERE DE L\'ECONOMIE NUMERIQUE ET DE LA POSTE', $headerFont, $textParagraphStyle);
        $cell->addText('-------------------------', $headerFont, $textParagraphStyle);
        $cell->addText('DIRECTION DES PROJETS DES SYSTEMES D\'INFORMATIONS ET DES STATISTIQUES (DPSIS)', $headerFont, $textParagraphStyle);
        $cell->addText('-------------------------', $headerFont, $textParagraphStyle);
        $cell->addText('PROJET DE SOLUTIONS NUMERIQUES POUR LE DESENCLAVEMENT DES ZONES RURALES ET L\'e-AGRICULTURE', $headerFont, $textParagraphStyle);
        $cell->addText('-------------------------', $headerFont, $textParagraphStyle);
        $headerFont['italic'] = false;
        $cell->addText('FINANCEMENT GROUPE BANQUE MONDIALE: CREDIT IDA N° 6224-CI', $headerFont, $textParagraphStyle);

        $cell = $table->addCell($w3);
        /*$cell->addImage($this->uploadsPath . '/img/logo-ampmn.png', [
            'width'         => 80
            , 'align' => 'right',
            'wrappingStyle' => 'square',
            'top'           => 22,
        ]);*/

        return $phpWord;

    }

    /**
     * @param PhpWord $word
     * @param $section
     */
    public function wordFooter(PhpWord $word, &$section, $mode = 'portrait', $client = 'pmn')
    {

        $client = 'pmn';
        $footer = $section->addFooter();

        $name = $client == 'pmn' ? 'Monotype Corsiva': 'Calibri';

        $footerStyle = ['size' => 8, 'color' => '333333', 'italic' => true, 'name' => $name, 'bold' => true];
        $center      = ['align' => 'center', 'spaceAfter' => 0];

        //PhpOffice\PhpWord\Style\TextBox;

        $textbox = $footer->addTextBox(
            [
                //'align'       => 'center',
                'width'       => $mode == 'portrait' ? 500 : 700,
                'height'      => 25,
                'borderSize'  => 0,

                'borderColor' => '#FFFFFF',

                //'positioning' => \PhpOffice\PhpWord\Style\TextBox::POSITION_RELATIVE_TO_LMARGIN,
                //'marginLeft' => 5000,

                'innerMargin' => 10,
            ]
        );

        $data = [

            'pmn' => [
                'SE-CONNAPE -  PMNDPE - Cocody Angré 8ème Tranche, non loin de la Résidence Niable - 01 BP 1533 Abidjan 01 –', 'Tél. : (225) 22 54 94 50 / 07 31 59 41 – Adresse Mail : pmndpe2019@gmail.com'
            ],
        ];

        $current = $data[$client];




        $paragraphStyle = array_merge($center, ['borderTopSize' => 20, 'borderTopColor' => '#000000']);
        $textbox->addText($current[0], $footerStyle, $paragraphStyle);
        $textbox->addText($current[1], $footerStyle, $center);
    }

}