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
    <link rel="stylesheet" href="css/selected_product.css">

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
            <button class="prev"><i class="fas fa-chevron-left"></i></button>
            <button class="next"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
   
    <div class="section">
        <br>
        <h2>Catálogo</h2>
        <br>
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
    p.stock_quantity,
    p.dimensions,
    MAX(pi.url) as image_url,
    MAX(pi.alt_text) as alt_text,
    p.price,
    GROUP_CONCAT(pc.category_id) as categorias
FROM products p
LEFT JOIN (SELECT * FROM product_images WHERE is_primary = 1) pi ON p.id = pi.product_id
LEFT JOIN product_categories pc ON p.id = pc.product_id
GROUP BY p.id, p.name, p.sku, p.description, p.price, p.stock_quantity, p.dimensions");
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($productos as $producto):
                $img = !empty($producto['image_url']) ? $producto['image_url'] : 'img/Agenda.png';
                $alt = !empty($producto['alt_text']) ? $producto['alt_text'] : htmlspecialchars($producto['name']);
                $categorias = $producto['categorias'] ?? '';
            ?>
            <div class="product" data-nombre="<?php echo strtolower(htmlspecialchars($producto['name'])); ?>" data-categoria="<?php echo $categorias; ?>" 
                data-id="<?php echo $producto['id']; ?>" 
                data-nombre-prod="<?php echo htmlspecialchars($producto['name']); ?>" 
                data-descripcion="<?php echo htmlspecialchars($producto['description']); ?>" 
                data-precio="<?php echo number_format($producto['price'], 2); ?>" 
                data-img="<?php echo $img; ?>"
                data-sku="<?php echo htmlspecialchars($producto['sku']); ?>"
                data-dimensiones="<?php echo !empty($producto['dimensions']) ? htmlspecialchars($producto['dimensions']) : 'N/A'; ?>"
                data-stock="<?php echo !empty($producto['stock_quantity']) ? $producto['stock_quantity'] : '0'; ?>">
                <img src="<?php echo $img; ?>" alt="<?php echo $alt; ?>">
                <div class="product-title"><?php echo htmlspecialchars($producto['name']); ?></div>
                <?php if (!empty($producto['price'])): ?>
                <div class="product-price">s/.<?php echo number_format($producto['price'], 2); ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <br>            <br>


    </div>

    <div id="productModal" class="modal">
        <div class="modal-content">
            <button id="closeModalBtn" aria-label="Cerrar">&times;</button>
            
            <!-- Contenido principal del producto -->
            <div style="display: flex; flex-wrap: wrap; padding: 30px;">
                <!-- Columna izquierda: imagen del producto -->
                <div style="flex: 1; min-width: 300px; margin-right: 30px; margin-bottom: 20px;">
                    <img id="modalImg" src="" alt="" style="width:100%; max-width: 400px; height:auto; object-fit:contain;">
                </div>
                
                <!-- Columna derecha: información del producto -->
                <div style="flex: 1; min-width: 300px;">
                    <!-- Título y subtítulo -->
                    <h2 id="modalNombre"></h2>
                    <h3 id="modalSubtitulo"></h3>
                    
                    <!-- Precio -->
                    <div id="modalPrecio"></div>
                    
                    <!-- Descripción del producto -->
                    <div id="modalDescripcionContainer" style="margin-bottom:20px;">
                        <p id="modalDescripcion"></p>
                    </div>
                    
                    <!-- Cantidad -->
                    <div style="margin-bottom:25px;">
                        <label class="cantidad-label">Cantidad</label>
                        <div style="display:flex;align-items:center;">
                            <div style="display:flex;align-items:center;overflow:hidden;border-radius:16px;border:1px solid #eee;padding:2px;">
                                <button id="decrementBtn">-</button>
                                <input type="number" id="cantidadInput" value="1" min="1">
                                <button id="incrementBtn">+</button>
                            </div>
                            <div id="stockInfo">
                                <span id="modalStock">0</span> unidades disponibles
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:30px;">
                        <button id="addToCartBtn">
                            Agregar al carrito
                        </button>
                        <button id="addToListBtn">
                            <i class="far fa-heart" style="margin-right:8px;"></i> Añadir a Mi Lista de Deseos
                        </button>
                    </div>
                    
                    <!-- Detalles del producto -->
                    <div style="margin-bottom:15px;display:flex;gap:15px;">
                        <div style="display:flex;flex-direction:column;align-items:center;flex:1;padding:10px;border-radius:10px;background:#f9f9f9;">
                            <span style="color:#999;font-size:0.85rem;margin-bottom:5px;">SKU</span>
                            <div id="modalSku"></div>
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:center;flex:1;padding:10px;border-radius:10px;background:#f9f9f9;">
                            <span style="color:#999;font-size:0.85rem;margin-bottom:5px;">Dimensiones</span>
                            <div id="modalDimensiones"></div>
                        </div>
                    </div>
                </div>
            </div>
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

        document.addEventListener('DOMContentLoaded', function() {
            // Modal de producto
            const productModal = document.getElementById('productModal');
            const modalNombre = document.getElementById('modalNombre');
            const modalSubtitulo = document.getElementById('modalSubtitulo');
            const modalDescripcion = document.getElementById('modalDescripcion');
            const modalPrecio = document.getElementById('modalPrecio');
            const modalImg = document.getElementById('modalImg');
            const modalSku = document.getElementById('modalSku');
            const modalDimensiones = document.getElementById('modalDimensiones');
            const modalStock = document.getElementById('modalStock');
            const closeModalBtn = document.getElementById('closeModalBtn');
            const addToCartBtn = document.getElementById('addToCartBtn');
            const addToListBtn = document.getElementById('addToListBtn');
            const decrementBtn = document.getElementById('decrementBtn');
            const incrementBtn = document.getElementById('incrementBtn');
            const cantidadInput = document.getElementById('cantidadInput');
            let selectedProductId = null;

            // Manejar cantidad
            decrementBtn.addEventListener('click', function() {
                let value = parseInt(cantidadInput.value);
                if (value > 1) {
                    cantidadInput.value = value - 1;
                }
            });
            
            incrementBtn.addEventListener('click', function() {
                let value = parseInt(cantidadInput.value);
                let stock = parseInt(modalStock.textContent);
                if (value < stock) {
                    cantidadInput.value = value + 1;
                } else {
                    alert('No puede exceder el stock disponible');
                }
            });

            // Selección de productos
            const productos = document.querySelectorAll('.product');
            productos.forEach(prod => {
                prod.addEventListener('click', function() {
                    selectedProductId = prod.getAttribute('data-id');
                    const nombre = prod.getAttribute('data-nombre-prod');
                    // Dividir el nombre en título y subtítulo si contiene espacio
                    const nombreParts = nombre.split(' ');
                    if (nombreParts.length > 2) {
                        modalNombre.textContent = nombreParts.slice(0, 2).join(' ');
                        modalSubtitulo.textContent = nombreParts.slice(2).join(' ');
                    } else {
                        modalNombre.textContent = nombre;
                        modalSubtitulo.textContent = '';
                    }
                    
                    modalPrecio.textContent = 'S/. ' + prod.getAttribute('data-precio');
                    modalImg.src = prod.getAttribute('data-img');
                    
                    // Obtener los datos de stock y dimensiones
                    const stock = prod.getAttribute('data-stock');
                    const dimensiones = prod.getAttribute('data-dimensiones');
                    
                    // Asignar los valores a los elementos del modal
                    modalSku.textContent = prod.getAttribute('data-sku') || 'N/A';
                    modalDimensiones.textContent = dimensiones || 'N/A';
                    modalStock.textContent = stock || '0';
                    
                    // Mostrar la descripción del producto
                    modalDescripcion.textContent = prod.getAttribute('data-descripcion') || 'Explora nuestra colección. Productos de alta calidad para satisfacer tus necesidades.';
                    
                    // Establecer valores de cantidad
                    cantidadInput.value = 1;
                    cantidadInput.max = stock || 999;
                    
                    // Verificar stock para actualizar el botón de compra
                    const stockInt = parseInt(stock || '0');
                    if (stockInt <= 0) {
                        addToCartBtn.disabled = true;
                        addToCartBtn.textContent = 'Agotado';
                    } else {
                        addToCartBtn.disabled = false;
                        addToCartBtn.textContent = 'Agregar al carrito';
                    }
                    
                    // Mostrar el modal
                    productModal.classList.add('show');
                });
            });
            
            closeModalBtn.addEventListener('click', function() {
                productModal.classList.remove('show');
            });
            
            window.addEventListener('click', function(event) {
                if (event.target === productModal) {
                    productModal.classList.remove('show');
                }
            });
            
            addToCartBtn.addEventListener('click', function() {
                const cantidad = parseInt(cantidadInput.value);
                alert(`Producto (ID: ${selectedProductId}) añadido al carrito - Cantidad: ${cantidad}`);
                productModal.classList.remove('show');
            });
            
            addToListBtn.addEventListener('click', function() {
                alert('Producto añadido a tu lista de deseos');
                this.innerHTML = '<i class="fas fa-heart" style="margin-right: 8px; color: #F25C76;"></i> En tu lista';
            });
        });
    </script>

    <!-- Footer elegante -->
    <footer style="background: linear-gradient(90deg, #6b7ad6, #feaa9d); color: white; padding: 50px 0 30px; margin-top: 80px;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 40px;">
            <div style="flex: 1 1 300px;">
                <h3 style="font-size: 1.8rem; margin-bottom: 20px; font-weight: 800;">Destello</h3>
                <p style="line-height: 1.6; margin-bottom: 20px;">Iluminamos tu estilo con joyas y accesorios que destacan tu belleza única. Encuentra artículos elegantes para cada ocasión.</p>
                <div style="display: flex; gap: 15px;">
                    <a href="#" style="color: white; background: rgba(255,255,255,0.2); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" style="color: white; background: rgba(255,255,255,0.2); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" style="color: white; background: rgba(255,255,255,0.2); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">
                        <i class="fab fa-twitter"></i>
                    </a>
                </div>
            </div>
            
            <div style="flex: 1 1 200px;">
                <h4 style="font-size: 1.2rem; margin-bottom: 20px;">Enlaces rápidos</h4>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="margin-bottom: 12px;"><a href="#" style="color: white; text-decoration: none; transition: all 0.2s;">Inicio</a></li>
                    <li style="margin-bottom: 12px;"><a href="#" style="color: white; text-decoration: none; transition: all 0.2s;">Categorías</a></li>
                    <li style="margin-bottom: 12px;"><a href="#" style="color: white; text-decoration: none; transition: all 0.2s;">Productos</a></li>
                    <li style="margin-bottom: 12px;"><a href="#" style="color: white; text-decoration: none; transition: all 0.2s;">Sobre nosotros</a></li>
                </ul>
            </div>
            
            <div style="flex: 1 1 250px;">
                <h4 style="font-size: 1.2rem; margin-bottom: 20px;">Contáctanos</h4>
                <div style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 12px;">
                    <i class="fas fa-map-marker-alt" style="margin-top: 4px;"></i>
                    <span>Av. Javier Prado Este 123, Lima, Perú</span>
                </div>
                <div style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 12px;">
                    <i class="fas fa-phone-alt" style="margin-top: 4px;"></i>
                    <span>+51 987 654 321</span>
                </div>
                <div style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 12px;">
                    <i class="fas fa-envelope" style="margin-top: 4px;"></i>
                    <span>info@destello.com</span>
                </div>
            </div>
        </div>
        
        <div style="max-width: 1200px; margin: 40px auto 0; padding: 20px 20px 0; border-top: 1px solid rgba(255,255,255,0.2); text-align: center; font-size: 0.9rem;">
            <p>© 2023 Destello. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Botón flotante WhatsApp -->
    <a href="#" style="position: fixed; bottom: 30px; right: 30px; background: #25d366; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.2); z-index: 99; font-size: 1.8rem; transition: all 0.3s;">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Indicador de carga de página -->
    <div id="pageLoader" style="position: fixed; top: 0; left: 0; width: 100%; height: 5px; background: #f0f0f0; z-index: 9999; overflow: hidden;">
        <div style="height: 100%; width: 0; background: linear-gradient(90deg, #7b8dfb, #feaa9d); transition: width 0.5s ease;"></div>
    </div>

    <script>
        // Indicador de carga de página
        document.addEventListener('DOMContentLoaded', function() {
            const loader = document.querySelector('#pageLoader div');
            loader.style.width = '70%';
            
            window.addEventListener('load', function() {
                loader.style.width = '100%';
                setTimeout(() => {
                    document.getElementById('pageLoader').style.opacity = '0';
                }, 500);
            });
        });
    </script>

    <style>
        .section h2 {
            text-align: center;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .section h2::after {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: -8px;
            width: 100px;
            height: 4px;
            background: linear-gradient(to right, #feaa9d, #f8f8f8);
            border-radius: 4px;
        }
    </style>

</body>
</html>


