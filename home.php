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
    <link rel="stylesheet" href="css/home.css">

    <title>Destello - Inicio</title>
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <!-- Banner Slider -->
    <div class="slider-container">
        <div class="slider">
            <div class="slide active">
                <img src="assets/img/banner summer.png" alt="Promo de Verano">
            </div>
            <div class="slide">
                <img src="assets/img/banner welcome.png" alt="Bienvenidos Destello">
            </div>
            <button class="prev">&#10094;</button>
            <button class="next">&#10095;</button>
        </div>
    </div>
   
    <div class="section">
        <h2>Catálogo</h2>
        <?php
        // Obtener categorías
        $cat_stmt = $conn->query("SELECT * FROM categories");
        $categorias = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="filtros-bar">
            <span style="position:relative;display:inline-block;">
                <i class="fa fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#feaa9d;font-size:1.1em;"></i>
                <input id="buscador" type="text" placeholder="Buscar producto..." style="padding-left:36px;">
            </span>
            <select id="filtro-categoria">
                <option value="">Todas las categorías</option>
                <?php foreach($categorias as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="products" id="productos-lista">
            <?php
            // Consulta para obtener los productos, su imagen principal y todas sus categorías (compatible con ONLY_FULL_GROUP_BY)
            $stmt = $conn->query("SELECT 
    p.id,
    p.name,
    p.sku,
    p.description,
    MAX(pi.url) as image_url,
    MAX(pi.alt_text) as alt_text,
    p.price,
    GROUP_CONCAT(pc.category_id) as categorias
FROM products p
LEFT JOIN (SELECT * FROM product_images WHERE is_primary = 1) pi ON p.id = pi.product_id
LEFT JOIN product_categories pc ON p.id = pc.product_id
GROUP BY p.id, p.name, p.sku, p.description, p.price");
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($productos as $producto):
                $img = !empty($producto['image_url']) ? $producto['image_url'] : 'img/Agenda.png';
                $alt = !empty($producto['alt_text']) ? $producto['alt_text'] : htmlspecialchars($producto['name']);
                $categorias = $producto['categorias'] ?? '';
            ?>
            <div class="product" data-nombre="<?php echo strtolower(htmlspecialchars($producto['name'])); ?>" data-categoria="<?php echo $categorias; ?>">
                <img src="<?php echo $img; ?>" alt="<?php echo $alt; ?>">
                <div class="product-title"><?php echo htmlspecialchars($producto['name']); ?></div>
                <?php if (!empty($producto['price'])): ?>
                <div class="product-price">s/.<?php echo number_format($producto['price'], 2); ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let slideIndex = 0;
            const slides = document.querySelectorAll('.slide');
            const nextBtn = document.querySelector('.next');
            const prevBtn = document.querySelector('.prev');
            let slideInterval;

            function showSlide(index) {
                slides.forEach((slide, i) => {
                    slide.classList.toggle('active', i === index);
                });
            }

            function nextSlide() {
                slideIndex = (slideIndex + 1) % slides.length;
                showSlide(slideIndex);
            }

            function prevSlide() {
                slideIndex = (slideIndex - 1 + slides.length) % slides.length;
                showSlide(slideIndex);
            }

            function startSlider() {
                slideInterval = setInterval(nextSlide, 3000);
            }

            function stopSlider() {
                clearInterval(slideInterval);
            }

            nextBtn.addEventListener('click', () => {
                nextSlide();
                stopSlider();
                startSlider();
            });
            prevBtn.addEventListener('click', () => {
                prevSlide();
                stopSlider();
                startSlider();
            });

            // Iniciar slider automático
            showSlide(slideIndex);
            startSlider();
        });

        // Filtro y buscador de productos (soporta múltiples categorías)
        const buscador = document.getElementById('buscador');
        const filtroCat = document.getElementById('filtro-categoria');
        const productos = document.querySelectorAll('.product');

        function filtrarProductos() {
            const texto = buscador.value.toLowerCase();
            const cat = filtroCat.value;
            productos.forEach(prod => {
                const nombre = prod.getAttribute('data-nombre');
                const categorias = (prod.getAttribute('data-categoria') || '').split(',');
                const coincideNombre = nombre.includes(texto);
                const coincideCat = !cat || categorias.includes(cat);
                prod.style.display = (coincideNombre && coincideCat) ? '' : 'none';
            });
        }
        buscador.addEventListener('input', filtrarProductos);
        filtroCat.addEventListener('change', filtrarProductos);
    </script>

</body>
</html>


