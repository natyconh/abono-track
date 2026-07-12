<?php
// app/core/StorageService.php — Abono Track
// Upload de archivos simplificado (sin subdirectorio por empresa).

class StorageService {

    private $baseUploadDir;

    public function __construct() {
        // Directorio: public/uploads/
        $this->baseUploadDir = dirname(APP_ROOT) . '/public/uploads/';
    }

    private function getUploadPath($module) {
        $path = $this->baseUploadDir . $module . '/';
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return $path;
    }

    /**
     * Guarda un archivo de imagen y devuelve su nombre.
     * @param array $file    $_FILES['campo']
     * @param int   $itemId  ID del elemento para nombrar el archivo
     * @param string $module Subcarpeta destino (ej. 'fertilizantes')
     */
    public function guardarImagen($file, $itemId, $module = 'general') {
        if ($file['error'] !== UPLOAD_ERR_OK) return false;

        $finfo     = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) return false;

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $itemId . '_' . time() . '.' . $ext;
        $dest     = $this->getUploadPath($module) . $filename;

        return move_uploaded_file($file['tmp_name'], $dest) ? $filename : false;
    }

    public function eliminarImagen($filename, $module = 'general') {
        if (empty($filename)) return false;
        $ruta = $this->getUploadPath($module) . $filename;
        return (file_exists($ruta)) ? unlink($ruta) : false;
    }

    public static function getUrl($filename, $module = 'general') {
        if (empty($filename)) return URL_ROOT . '/img/placeholder.png';
        return URL_ROOT . "/uploads/{$module}/{$filename}";
    }
}
?>
