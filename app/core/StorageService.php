<?php
// app/core/StorageService.php
// ¡NUEVO! (Fase 2.2 - SRP)
// Maneja toda la lógica de interacción con el sistema de archivos.

class StorageService {
    
    private $empresa_id;
    private $base_upload_dir;

    public function __construct($empresa_id) {
        $this->empresa_id = $empresa_id;
        // Define el directorio base de subidas
        $this->base_upload_dir = dirname(APP_ROOT) . '/public/uploads/';
    }

    /**
     * Obtiene la ruta de subida para un módulo, específica de la empresa.
     * Ej: /.../public/uploads/empresa_1/puntos/
     */
    private function getUploadPath($module) {
        // Crea un directorio por empresa para aislar archivos
        $path = $this->base_upload_dir . 'empresa_' . $this->empresa_id . '/' . $module . '/';
        
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return $path;
    }

    /**
     * Guarda una foto para un punto y devuelve el nombre del archivo.
     *
     * @param array $file El array $_FILES['foto'].
     * @param int $puntoId El ID del punto para nombrar el archivo.
     * @return string|false El nombre del archivo guardado o false si falla.
     */
    public function guardarFotoPunto($fileArray, $puntoId) {
        if ($fileArray['error'] !== UPLOAD_ERR_OK) {
            return false; // No se subió o hubo error
        }

        // Validación de tipo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $fileArray['tmp_name']);
        finfo_close($finfo);
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($mime_type, $allowed_types)) {
            // Podríamos lanzar una excepción, pero por ahora devolvemos false
            return false; 
        }

        // Generar nombre de archivo único
        $extension = pathinfo($fileArray['name'], PATHINFO_EXTENSION);
        $nombre_archivo = $puntoId . '_' . time() . '.' . $extension;
        
        $ruta_destino = $this->getUploadPath('puntos') . $nombre_archivo;

        // Mover el archivo
        if (move_uploaded_file($fileArray['tmp_name'], $ruta_destino)) {
            return $nombre_archivo;
        }
        
        return false;
    }

    /**
     * Elimina una foto de un punto del sistema de archivos.
     *
     * @param string $nombreArchivo El nombre del archivo a eliminar.
     * @return bool True si se eliminó, false si no existía o falló.
     */
    public function eliminarFotoPunto($nombreArchivo) {
        if (empty($nombreArchivo)) {
            return false;
        }
        
        $ruta_completa = $this->getUploadPath('puntos') . $nombreArchivo;
        
        if (file_exists($ruta_completa)) {
            return unlink($ruta_completa);
        }
        return false;
    }

    /**
     * Obtiene la URL pública de una foto de punto.
     */
    public static function getUrlFotoPunto($nombreArchivo, $empresaId) {
        if (empty($nombreArchivo) || empty($empresaId)) {
            return URL_ROOT . '/img/placeholder.png'; // Una imagen genérica
        }
        return URL_ROOT . "/uploads/empresa_{$empresaId}/puntos/{$nombreArchivo}";
    }
}
?>