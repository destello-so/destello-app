<?php
session_start();
require 'config/db.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/global.css">
    <title>Destello - Inicio</title>
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <h1>Inicio - Catálogo</h1>
    <!-- Banner Slider -->
    <div class="slider-container">
        <div class="slider">
            <div class="slide active">
                <img src="img/banner summer.png" alt="Promo de Verano">
            </div>
            <div class="slide">
                <img src="img/banner welcome.png" alt="Bienvenidos Destello">
            </div>
            <button class="prev">&#10094;</button>
            <button class="next">&#10095;</button>
        </div>
    </div>
   
    <div class="section">
        <h2>Catálogo</h2>
        <div class="products">
            <div class="product">
                <img src="img/Agenda.png" alt="Agenda 2024">
                <div class="product-title">Agenda 2024</div>
                <div class="product-price">250.00₲</div>
            </div>
            <div class="product">
                <img src="img/Agenda1.png" alt="Agenda Interior">
                <div class="product-title">Agenda Interior</div>
                <div class="product-price">250.00₲</div>
            </div>
            <div class="product">
                <img src="img/organizador.jpg" alt="Organizador">
                <div class="product-title">Organizador</div>
                <div class="product-price">200.00₲</div>
            </div>
            <div class="product">
                <img src="img/Planner.png" alt="Planner">
                <div class="product-title">Planner</div>
                <div class="product-price">180.00₲</div>
            </div>
        </div>
    </div>
    <div class="section">
        <div class="products">
            <div class="product">
                <img src="img/Cuaderno1.png" alt="Cuaderno Notas 1">
                <div class="product-title">Cuaderno Notas 1</div>
            </div>
            <div class="product">
                <img src="img/Cuaderno2.png" alt="Cuaderno Notas 2">
                <div class="product-title">Cuaderno Notas 2</div>
            </div>
            <div class="product">
                <img src="img/Planner2.png" alt="Planner Interior">
                <div class="product-title">Planner Interior</div>
            </div>
            <div class="product">
                <img src="img/Agenda (2).png" alt="Agenda Portada">
                <div class="product-title">Agenda Portada</div>
            </div>
        </div>
    </div>


</body>
</html>


