<?php
header('Access-Control-Allow-Origin: *');

// Obtiene el path donde guardará la imagen
$path = $_POST['path'];

// Obtiene los detalles de la imagen
$imageName = $_FILES['image']['name'];
$imageType = $_FILES['image']['type'];
$imageSize = $_FILES['image']['size'];
$imageTmpName = $_FILES['image']['tmp_name'];

// Ruta de destino para guardar la imagen
$destination = $path . $imageName;

// Mueve la imagen a la ruta de destino
if (move_uploaded_file($imageTmpName, $destination)) {
  // La imagen se ha guardado correctamente
  echo "Imagen guardada con éxito";
} else {
  // Error al guardar la imagen
  echo "Error al guardar la imagen";
}
?>