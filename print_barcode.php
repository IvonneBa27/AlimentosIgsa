<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['image'])) {
        // Convertir la ruta relativa en absoluta
        $relativePath = $data['image']; 
        $absolutePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $relativePath;

        // Verificar si el archivo realmente existe
        if (!file_exists($absolutePath)) {
            echo json_encode([
                'success' => false,
                'message' => 'El archivo NO EXISTE en el servidor.',
                'path' => $absolutePath
            ]);
            exit;
        }

        // Nombre de la impresora (Verifica este nombre en "Dispositivos e Impresoras")
        $printerName = "\\\\localhost\\DYMO_LabelWriter_4XL";

        // Comando corregido con doble comilla y sin /B
        $command = "copy \"$absolutePath\" \"$printerName\"";

        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            echo json_encode(['success' => true, 'message' => 'Impresión exitosa.']);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al imprimir. Verifica la impresora.',
                'output' => implode("\n", $output),
                'return_code' => $returnVar
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No se recibió la imagen.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
