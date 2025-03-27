<?php
session_start();

// Générer un texte CAPTCHA aléatoire
$captcha_text = '';
$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
$characters_length = strlen($characters);
$captcha_length = 6;
for ($i = 0; $i < $captcha_length; $i++) {
    $captcha_text .= $characters[rand(0, $characters_length - 1)];
}

// Stocker le texte CAPTCHA dans la session
$_SESSION['captcha_text'] = $captcha_text;

// Créer une image CAPTCHA
$image = imagecreate(200, 50);
$background_color = imagecolorallocate($image, 255, 255, 255); // Blanc
$text_color = imagecolorallocate($image, 0, 0, 0); // Noir
$font_size = 20;
$font = __DIR__ . '/arial.ttf'; // Assurez-vous d'avoir une police TrueType (TTF) dans le même répertoire

// Ajouter le texte à l'image
imagettftext($image, $font_size, 0, 30, 35, $text_color, $font, $captcha_text);

// Définir l'en-tête de type de contenu
header('Content-Type: image/png');

// Afficher l'image
imagepng($image);

// Libérer l'image de la mémoire
imagedestroy($image);
?>