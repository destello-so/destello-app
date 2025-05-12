<link rel="stylesheet" href="css/selected_product.css">

<div id="productModal" class="modal">
    <div class="modal-content" style="position:relative;">
        <button id="closeModalBtn" aria-label="Cerrar" style="position:absolute;top:18px;right:18px;background:none;border:none;font-size:1.7rem;color:#bbb;cursor:pointer;z-index:2;transition:color 0.2s;">&times;</button>
        <img id="modalImg" src="" alt="" style="width:180px;height:180px;object-fit:cover;border-radius:18px;box-shadow:0 2px 16px #eee;margin-bottom:1.2rem;">
        <h2 id="modalNombre" style="color:#6b7ad6;font-size:1.7rem;font-weight:800;margin-bottom:0.6rem;line-height:1.2;"></h2>
        <p id="modalDescripcion" style="color:#888;font-size:1.08rem;margin-bottom:1.1rem;"></p>
        <div id="modalPrecio" style="font-size:1.5rem;font-weight:800;color:#e57373;margin-bottom:1.2rem;"></div>
        <div id="modalDescripcionLarga" style="color:#666;font-size:1rem;margin-bottom:2rem;max-width:340px;margin-left:auto;margin-right:auto;"></div>
        <button id="addToCartBtn" style="padding:0.9rem 2.2rem;background:linear-gradient(90deg,#7b8dfb 0%,#feaa9d 100%);color:white;border:none;border-radius:30px;font-weight:700;font-size:1.15rem;box-shadow:0 4px 16px #feaa9d33;transition:all 0.3s;margin-bottom:1.2rem;">Añadir al carrito</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal de producto
    const productModal = document.getElementById('productModal');
    const modalNombre = document.getElementById('modalNombre');
    const modalDescripcion = document.getElementById('modalDescripcion');
    const modalPrecio = document.getElementById('modalPrecio');
    const modalImg = document.getElementById('modalImg');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const addToCartBtn = document.getElementById('addToCartBtn');
    const modalDescripcionLarga = document.getElementById('modalDescripcionLarga');
    let selectedProductId = null;

    // Selección de productos
    const productos = document.querySelectorAll('.product');
    productos.forEach(prod => {
        prod.addEventListener('click', function() {
            selectedProductId = prod.getAttribute('data-id');
            modalNombre.textContent = prod.getAttribute('data-nombre-prod');
            modalDescripcion.textContent = prod.getAttribute('data-descripcion');
            modalPrecio.textContent = 'S/ ' + prod.getAttribute('data-precio');
            modalImg.src = prod.getAttribute('data-img');
            // Si tienes una descripción larga, usa data-descripcion-larga, si no, deja vacío
            modalDescripcionLarga.textContent = prod.getAttribute('data-descripcion-larga') || '';
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
        // Aquí puedes agregar la lógica para añadir al carrito usando selectedProductId
        alert('Producto añadido al carrito (simulado)');
        productModal.classList.remove('show');
    });
});
</script>
