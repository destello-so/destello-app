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
    <link rel="stylesheet" href="css/checkout.css">
    <title>Destello - Pago</title>   
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="checkout-container">
        <div class="checkout-header">
            <div class="header-decoration">
                <i class="fas fa-sparkles"></i>
            </div>
            <h1>Finalizar Compra</h1>
            <p>Estás a un paso de disfrutar tus productos Destello</p>
            
            <div class="checkout-steps">
                <div class="step completed">
                    <div class="step-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="step-label">Carrito</div>
                </div>
                <div class="step-connector completed"></div>
                <div class="step active">
                    <div class="step-icon"><i class="fas fa-credit-card"></i></div>
                    <div class="step-label">Pago</div>
                </div>
                <div class="step-connector"></div>
                <div class="step">
                    <div class="step-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="step-label">Confirmación</div>
                </div>
            </div>
        </div>
        
        <div class="checkout-content">
            <!-- Sección principal de pago -->
            <div class="checkout-main">
                <!-- Datos de envío -->
                <div class="checkout-section shipping-details">
                    <h2><i class="fas fa-map-marker-alt"></i> Dirección de Envío</h2>
                    
                    <div class="form-group">
                        <div class="form-row">
                            <div class="form-field">
                                <label for="firstName">Nombre</label>
                                <input type="text" id="firstName" placeholder="Tu nombre" value="María">
                            </div>
                            <div class="form-field">
                                <label for="lastName">Apellido</label>
                                <input type="text" id="lastName" placeholder="Tu apellido" value="García">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-field">
                                <label for="email">Correo Electrónico</label>
                                <input type="email" id="email" placeholder="ejemplo@correo.com" value="maria@ejemplo.com">
                            </div>
                            <div class="form-field">
                                <label for="phone">Teléfono</label>
                                <input type="tel" id="phone" placeholder="Tu número" value="987-654-321">
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="address">Dirección</label>
                            <input type="text" id="address" placeholder="Calle, número, etc." value="Av. Primavera 456, Depto. 302">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-field">
                                <label for="city">Ciudad</label>
                                <input type="text" id="city" placeholder="Tu ciudad" value="Lima">
                            </div>
                            <div class="form-field">
                                <label for="postalCode">Código Postal</label>
                                <input type="text" id="postalCode" placeholder="Código postal" value="15023">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Método de pago -->
                <div class="checkout-section payment-method">
                    <h2><i class="fas fa-credit-card"></i> Método de Pago</h2>
                    
                    <div class="payment-options">
                        <div class="payment-option selected">
                            <input type="radio" id="credit-card" name="payment" value="credit-card" checked>
                            <label for="credit-card">
                                <i class="far fa-credit-card"></i>
                                <span>Tarjeta de Crédito/Débito</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="credit-card-form">
                        <div class="card-container">
                            <div class="card-preview">
                                <div class="card-front">
                                    <div class="card-type">
                                        <i class="fab fa-cc-visa"></i>
                                    </div>
                                    <div class="card-number">
                                        <span id="cardNumberPreview">•••• •••• •••• ••••</span>
                                    </div>
                                    <div class="card-details">
                                        <div class="card-holder">
                                            <label>Titular</label>
                                            <div id="cardHolderPreview">NOMBRE DEL TITULAR</div>
                                        </div>
                                        <div class="card-expires">
                                            <label>Expira</label>
                                            <div><span id="monthPreview">MM</span>/<span id="yearPreview">AA</span></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-back">
                                    <div class="card-stripe"></div>
                                    <div class="card-signature">
                                        <span id="cvvPreview">CVV</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-field">
                                <label for="cardNumber">Número de Tarjeta</label>
                                <div class="input-icon">
                                    <input type="text" id="cardNumber" placeholder="1234 5678 9012 3456">
                                    <i class="fab fa-cc-visa"></i>
                                </div>
                            </div>
                            
                            <div class="form-field">
                                <label for="cardHolder">Nombre del Titular</label>
                                <input type="text" id="cardHolder" placeholder="Como aparece en la tarjeta">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-field">
                                    <label>Fecha de Expiración</label>
                                    <div class="expiry-inputs">
                                        <select id="expiryMonth">
                                            <option value="" disabled selected>MM</option>
                                            <option value="01">01</option>
                                            <option value="02">02</option>
                                            <option value="03">03</option>
                                            <option value="04">04</option>
                                            <option value="05">05</option>
                                            <option value="06">06</option>
                                            <option value="07">07</option>
                                            <option value="08">08</option>
                                            <option value="09">09</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                        </select>
                                        <span>/</span>
                                        <select id="expiryYear">
                                            <option value="" disabled selected>AA</option>
                                            <option value="24">24</option>
                                            <option value="25">25</option>
                                            <option value="26">26</option>
                                            <option value="27">27</option>
                                            <option value="28">28</option>
                                            <option value="29">29</option>
                                            <option value="30">30</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-field cvv-field">
                                    <label for="cvv">CVV</label>
                                    <div class="input-icon">
                                        <input type="text" id="cvv" placeholder="123" maxlength="3">
                                        <i class="fas fa-question-circle" title="El código de seguridad de 3 dígitos ubicado al reverso de tu tarjeta"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Resumen del pedido -->
            <div class="checkout-sidebar">
                <div class="checkout-section order-summary">
                    <h2>Resumen del Pedido</h2>
                    
                    <div class="order-items">
                        <div class="order-item">
                            <img src="https://via.placeholder.com/60x70/ffb6c1/ffffff" alt="Producto 1">
                            <div class="item-info">
                                <h4>Collar Destello Rosa</h4>
                                <div class="item-qty">Cantidad: 1</div>
                            </div>
                            <div class="item-price">S/ 29.99</div>
                        </div>
                        
                        <div class="order-item">
                            <img src="https://via.placeholder.com/60x70/ffcccb/ffffff" alt="Producto 2">
                            <div class="item-info">
                                <h4>Pendientes Flor Cristal</h4>
                                <div class="item-qty">Cantidad: 2</div>
                            </div>
                            <div class="item-price">S/ 24.99</div>
                        </div>
                        
                        <div class="order-item">
                            <img src="https://via.placeholder.com/60x70/fadadd/ffffff" alt="Producto 3">
                            <div class="item-info">
                                <h4>Pulsera Perlas Delicada</h4>
                                <div class="item-qty">Cantidad: 1</div>
                            </div>
                            <div class="item-price">S/ 34.99</div>
                        </div>
                    </div>
                    
                    <div class="order-totals">
                        <div class="total-row">
                            <span>Subtotal</span>
                            <span>S/ 89.97</span>
                        </div>
                        <div class="total-row">
                            <span>Descuento</span>
                            <span>-S/ 20.00</span>
                        </div>
                        <div class="total-row">
                            <span>Envío</span>
                            <span>Gratis</span>
                        </div>
                        <div class="total-row grand-total">
                            <span>Total</span>
                            <span>S/ 69.97</span>
                        </div>
                    </div>
                    
                    <button id="completeOrder" class="complete-order-btn">
                        <i class="fas fa-lock"></i> Finalizar Pedido
                    </button>
                    
                    <div class="secure-checkout">
                        <i class="fas fa-shield-alt"></i> Pago 100% seguro
                    </div>
                    
                    <div class="back-link">
                        <a href="cart.php"><i class="fas fa-arrow-left"></i> Volver al carrito</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de confirmación -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <div class="success-animation">
                <div class="checkmark-circle">
                    <div class="checkmark"></div>
                </div>
            </div>
            <h2>¡Pedido Realizado con Éxito!</h2>
            <p>Gracias por tu compra en Destello</p>
            <p class="order-number">Número de pedido: <span>#DE-203945</span></p>
            <p class="order-message">Hemos enviado los detalles a tu correo electrónico</p>
            <a href="home.php" class="continue-button">Volver a la Tienda</a>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Simular cambio de tarjeta
            const cardNumber = document.getElementById('cardNumber');
            const cardHolder = document.getElementById('cardHolder');
            const expiryMonth = document.getElementById('expiryMonth');
            const expiryYear = document.getElementById('expiryYear');
            const cvv = document.getElementById('cvv');
            
            const cardNumberPreview = document.getElementById('cardNumberPreview');
            const cardHolderPreview = document.getElementById('cardHolderPreview');
            const monthPreview = document.getElementById('monthPreview');
            const yearPreview = document.getElementById('yearPreview');
            const cvvPreview = document.getElementById('cvvPreview');
            
            // Actualizar número de tarjeta
            cardNumber.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value) {
                    value = value.match(/.{1,4}/g).join(' ');
                    cardNumberPreview.textContent = value;
                } else {
                    cardNumberPreview.textContent = '•••• •••• •••• ••••';
                }
                
                // Cambiar tipo de tarjeta basado en el primer dígito
                const firstDigit = value.charAt(0);
                const cardTypeIcon = document.querySelector('.card-type i');
                
                if (firstDigit === '4') {
                    cardTypeIcon.className = 'fab fa-cc-visa';
                } else if (firstDigit === '5') {
                    cardTypeIcon.className = 'fab fa-cc-mastercard';
                } else if (firstDigit === '3') {
                    cardTypeIcon.className = 'fab fa-cc-amex';
                } else {
                    cardTypeIcon.className = 'fab fa-cc-visa';
                }
            });
            
            // Actualizar nombre del titular
            cardHolder.addEventListener('input', function() {
                if (this.value) {
                    cardHolderPreview.textContent = this.value.toUpperCase();
                } else {
                    cardHolderPreview.textContent = 'NOMBRE DEL TITULAR';
                }
            });
            
            // Actualizar mes de expiración
            expiryMonth.addEventListener('change', function() {
                if (this.value) {
                    monthPreview.textContent = this.value;
                } else {
                    monthPreview.textContent = 'MM';
                }
            });
            
            // Actualizar año de expiración
            expiryYear.addEventListener('change', function() {
                if (this.value) {
                    yearPreview.textContent = this.value;
                } else {
                    yearPreview.textContent = 'AA';
                }
            });
            
            // Actualizar CVV
            cvv.addEventListener('focus', function() {
                document.querySelector('.card-preview').classList.add('flipped');
            });
            
            cvv.addEventListener('blur', function() {
                document.querySelector('.card-preview').classList.remove('flipped');
            });
            
            cvv.addEventListener('input', function() {
                if (this.value) {
                    cvvPreview.textContent = this.value;
                } else {
                    cvvPreview.textContent = 'CVV';
                }
            });
            
            // Modal de confirmación
            const completeOrderBtn = document.getElementById('completeOrder');
            const confirmationModal = document.getElementById('confirmationModal');
            
            completeOrderBtn.addEventListener('click', function() {
                // Simular procesamiento de pago
                completeOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                completeOrderBtn.disabled = true;
                
                // Mostrar modal después de simular procesamiento
                setTimeout(function() {
                    confirmationModal.style.display = 'flex';
                }, 1500);
            });
            
            // Cerrar modal al hacer clic fuera de él
            window.addEventListener('click', function(event) {
                if (event.target === confirmationModal) {
                    confirmationModal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>