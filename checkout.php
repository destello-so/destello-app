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
        
        <!-- Loading state mientras se carga la información -->
        <div id="loading-state" class="loading-checkout">
            <div class="spinner"></div>
            <p>Cargando información de pago...</p>
        </div>
        
        <div id="checkout-content" class="checkout-content" style="display: none;">
            <!-- Sección principal de pago -->
            <div class="checkout-main">
                <!-- Datos de envío -->
                <div class="checkout-section shipping-details">
                    <h2><i class="fas fa-map-marker-alt"></i> Dirección de Envío</h2>
                    
                    <div id="address-selection" class="address-selection" style="display: none;">
                        <label>Selecciona una dirección guardada</label>
                        <select id="saved-addresses">
                            <option value="new">Agregar nueva dirección</option>
                        </select>
                    </div>
                    
                    <div class="shipping-form">
                        <!-- Nombre y Apellido -->
                        <div class="form-field half required">
                            <label for="firstName">Nombre</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="firstName" placeholder="Tu nombre" required>
                            </div>
                        </div>
                        
                        <div class="form-field half required">
                            <label for="lastName">Apellido</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="lastName" placeholder="Tu apellido" required>
                            </div>
                        </div>
                        
                        <!-- Email y Teléfono -->
                        <div class="form-field half required">
                            <label for="email">Correo Electrónico</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" placeholder="ejemplo@correo.com" required>
                            </div>
                        </div>
                        
                        <div class="form-field half">
                            <label for="phone">Teléfono</label>
                            <div class="input-with-icon">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="phone" placeholder="Tu número">
                            </div>
                        </div>
                        
                        <!-- Dirección -->
                        <div class="form-field full required">
                            <label for="address">Dirección</label>
                            <div class="input-with-icon">
                                <i class="fas fa-map-marker-alt"></i>
                                <input type="text" id="address" placeholder="Calle, número, etc." required>
                            </div>
                        </div>
                        
                        <!-- Ciudad y Estado/Provincia -->
                        <div class="form-field half required">
                            <label for="city">Ciudad</label>
                            <div class="input-with-icon">
                                <i class="fas fa-city"></i>
                                <input type="text" id="city" placeholder="Tu ciudad" required>
                            </div>
                        </div>
                        
                        <div class="form-field half">
                            <label for="state">Estado/Provincia</label>
                            <div class="input-with-icon">
                                <i class="fas fa-map"></i>
                                <input type="text" id="state" placeholder="Estado o provincia">
                            </div>
                        </div>
                        
                        <!-- País y Código Postal -->
                        <div class="form-field half required">
                            <label for="country">País</label>
                            <div class="input-with-icon">
                                <i class="fas fa-globe"></i>
                                <input type="text" id="country" placeholder="País" required>
                            </div>
                        </div>
                        
                        <div class="form-field half">
                            <label for="postalCode">Código Postal</label>
                            <div class="input-with-icon">
                                <i class="fas fa-key"></i>
                                <input type="text" id="postalCode" placeholder="Código postal">
                            </div>
                        </div>
                        
                        <!-- Checkbox para guardar dirección -->
                        <div class="save-address-container">
                            <label class="checkbox-container">
                                <input type="checkbox" id="saveAddress">
                                <span class="checkmark"></span>
                                Guardar esta dirección para futuras compras
                            </label>
                            <div class="save-address-info">
                                <i class="fas fa-shield-alt"></i> Tus datos serán guardados de forma segura
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
                                <div class="input-with-icon">
                                    <i class="fab fa-cc-visa"></i>
                                    <input type="text" id="cardNumber" placeholder="1234 5678 9012 3456">
                                </div>
                            </div>
                            
                            <div class="form-field">
                                <label for="cardHolder">Nombre del Titular</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="cardHolder" placeholder="Como aparece en la tarjeta">
                                </div>
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
                                    <div class="input-with-icon">
                                        <i class="fas fa-lock"></i>
                                        <input type="text" id="cvv" placeholder="123" maxlength="3">
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
                    
                    <div id="order-items" class="order-items">
                        <!-- Los items del carrito se cargarán dinámicamente aquí -->
                        <div class="loading-items">
                            <div class="spinner-small"></div>
                            <p>Cargando productos...</p>
                        </div>
                    </div>
                    
                    <div class="order-totals">
                        <div class="total-row">
                            <span>Subtotal</span>
                            <span id="subtotal-amount">S/ 0.00</span>
                        </div>
                        <div class="total-row">
                            <span>Descuento</span>
                            <span id="discount-amount">S/ 0.00</span>
                        </div>
                        <div class="total-row">
                            <span>Envío</span>
                            <span>Gratis</span>
                        </div>
                        <div class="total-row grand-total">
                            <span>Total</span>
                            <span id="total-amount">S/ 0.00</span>
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
            <p class="order-number">Número de pedido: <span id="order-number">#DE-XXXXX</span></p>
            <p class="order-message">Hemos enviado los detalles a tu correo electrónico</p>
            <a href="home.php" class="continue-button">Volver a la Tienda</a>
        </div>
    </div>
    
    <!-- Toast para notificaciones -->
    <div id="toast" class="toast"></div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar si hay un usuario logueado
            const userId = localStorage.getItem('userId');
            if (!userId) {
                window.location.href = 'login.php?redirect=checkout';
                return;
            }
            
            // Variables globales
            let cartData = null;
            let userData = null;
            let appliedDiscount = null;
            
            // Funciones para mostrar/ocultar notificaciones
            function showToast(message, type = 'info') {
                const toast = document.getElementById('toast');
                toast.innerHTML = message;
                toast.className = `toast show ${type}`;
                
                // Añadir sonido de notificación (opcional)
                const audio = new Audio('assets/sounds/notification.mp3');
                audio.volume = 0.2;
                audio.play().catch(e => console.log('No se pudo reproducir el sonido'));
                
                // Animación de entrada y salida
                setTimeout(() => {
                    toast.classList.add('fade-out');
                    setTimeout(() => {
                        toast.className = toast.className.replace('show', '').replace('fade-out', '');
                    }, 300);
                }, 3000);
            }
            
            // Cargar datos del usuario
            function loadUserData() {
                return fetch(`api/get_user.php?userId=${userId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error al cargar datos del usuario');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'Error al cargar datos del usuario');
                        }
                        userData = data.user;
                        
                        // Completar formulario con datos del usuario
                        document.getElementById('firstName').value = userData.first_name || '';
                        document.getElementById('lastName').value = userData.last_name || '';
                        document.getElementById('email').value = userData.email || '';
                        document.getElementById('phone').value = userData.phone || '';
                        document.getElementById('cardHolder').value = 
                            (userData.first_name || '') + ' ' + (userData.last_name || '');
                        document.getElementById('cardHolderPreview').textContent = 
                            ((userData.first_name || '') + ' ' + (userData.last_name || '')).toUpperCase();
                        
                        return loadUserAddresses();
                    });
            }
            
            // Cargar direcciones del usuario
            function loadUserAddresses() {
                return fetch(`api/get_addresses.php?userId=${userId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error al cargar direcciones');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            // Si no hay direcciones, no es un error crítico
                            return;
                        }
                        
                        const addresses = data.addresses;
                        if (addresses && addresses.length > 0) {
                            const selectElement = document.getElementById('saved-addresses');
                            document.getElementById('address-selection').style.display = 'block';
                            
                            // Añadir las direcciones guardadas al select
                            addresses.forEach(address => {
                                const option = document.createElement('option');
                                option.value = address.id;
                                option.textContent = `${address.street}, ${address.city}, ${address.country}`;
                                if (address.is_default) {
                                    option.selected = true;
                                    fillAddressForm(address);
                                }
                                selectElement.appendChild(option);
                            });
                            
                            // Si no hay dirección por defecto, seleccionar la primera
                            if (!addresses.some(addr => addr.is_default) && addresses.length > 0) {
                                fillAddressForm(addresses[0]);
                                selectElement.value = addresses[0].id;
                            }
                            
                            // Evento para cambiar el formulario cuando se selecciona otra dirección
                            selectElement.addEventListener('change', function() {
                                if (this.value === 'new') {
                                    // Limpiar el formulario para una nueva dirección
                                    document.getElementById('address').value = '';
                                    document.getElementById('city').value = '';
                                    document.getElementById('state').value = '';
                                    document.getElementById('country').value = '';
                                    document.getElementById('postalCode').value = '';
                                } else {
                                    // Buscar la dirección seleccionada
                                    const selectedAddress = addresses.find(addr => addr.id == this.value);
                                    if (selectedAddress) {
                                        fillAddressForm(selectedAddress);
                                    }
                                }
                            });
                        }
                    });
            }
            
            // Rellenar formulario con dirección seleccionada
            function fillAddressForm(address) {
                document.getElementById('address').value = address.street || '';
                document.getElementById('city').value = address.city || '';
                document.getElementById('state').value = address.state || '';
                document.getElementById('country').value = address.country || '';
                document.getElementById('postalCode').value = address.zip_code || '';
            }
            
            // Cargar items del carrito
            function loadCartItems() {
                return fetch(`api/get_cart_items.php?userId=${userId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error al cargar el carrito');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'Error al cargar el carrito');
                        }
                        
                        cartData = data;
                        
                        // Verificar si el carrito está vacío
                        if (!data.items || data.items.length === 0) {
                            // Redireccionar al carrito si está vacío
                            showToast('Tu carrito está vacío', 'warning');
                            setTimeout(() => {
                                window.location.href = 'cart.php';
                            }, 2000);
                            throw new Error('Carrito vacío');
                        }
                        
                        // Mostrar items en el resumen
                        const orderItemsContainer = document.getElementById('order-items');
                        orderItemsContainer.innerHTML = ''; // Limpiar loader
                        
                        data.items.forEach(item => {
                            const itemElement = document.createElement('div');
                            itemElement.className = 'order-item';
                            itemElement.innerHTML = `
                                <img src="${item.image_url || 'https://via.placeholder.com/60x70/ffb6c1/ffffff'}" alt="${item.name}">
                                <div class="item-info">
                                    <h4>${item.name}</h4>
                                    <div class="item-qty">Cantidad: ${item.quantity}</div>
                                </div>
                                <div class="item-price">S/ ${parseFloat(item.price).toFixed(2)}</div>
                            `;
                            orderItemsContainer.appendChild(itemElement);
                        });
                        
                        // Actualizar totales
                        updateOrderSummary();
                    });
            }
            
            // Actualizar resumen del pedido
            function updateOrderSummary() {
                if (!cartData) return;
                
                const subtotalElement = document.getElementById('subtotal-amount');
                const discountElement = document.getElementById('discount-amount');
                const totalElement = document.getElementById('total-amount');
                
                let subtotal = 0;
                cartData.items.forEach(item => {
                    subtotal += parseFloat(item.price) * parseInt(item.quantity);
                });
                
                let discount = 0;
                if (appliedDiscount) {
                    if (appliedDiscount.percentage_off) {
                        discount = subtotal * (parseFloat(appliedDiscount.percentage_off) / 100);
                    } else if (appliedDiscount.fixed_amount_off) {
                        discount = parseFloat(appliedDiscount.fixed_amount_off);
                    }
                }
                
                const total = subtotal - discount;
                
                subtotalElement.textContent = `S/ ${subtotal.toFixed(2)}`;
                discountElement.textContent = discount > 0 ? `-S/ ${discount.toFixed(2)}` : `S/ ${discount.toFixed(2)}`;
                totalElement.textContent = `S/ ${total.toFixed(2)}`;
                
                // Guardar en cartData para usar en el proceso de pago
                cartData.subtotal = subtotal;
                cartData.discount = discount;
                cartData.total = total;
            }
            
            // Aplicar código de descuento
            function applyCouponCode() {
                const couponCode = document.getElementById('couponCode').value.trim();
                if (!couponCode) {
                    showToast('Por favor, ingresa un código de descuento', 'warning');
                    return;
                }
                
                fetch('api/apply_discount.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        userId: userId,
                        couponCode: couponCode,
                        subtotal: cartData.subtotal
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        appliedDiscount = data.discount;
                        showToast(`Código aplicado: ${data.message}`, 'success');
                        updateOrderSummary();
                    } else {
                        showToast(data.message || 'Código inválido', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error al aplicar el código de descuento', 'error');
                });
            }
            
            // Iniciar carga de datos
            Promise.all([loadUserData(), loadCartItems()])
                .then(() => {
                    // Ocultar loader y mostrar contenido
                    document.getElementById('loading-state').style.display = 'none';
                    document.getElementById('checkout-content').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error al cargar datos:', error);
                    showToast('Error al cargar la información. Por favor, intenta nuevamente.', 'error');
                });
            
            // Event listeners
            
            // Aplicar código de descuento
            const applyCouponBtn = document.getElementById('applyCoupon');
            if (applyCouponBtn) {
                applyCouponBtn.addEventListener('click', applyCouponCode);
            }
            
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
            
            // Agregar después de la variable cvvPreview
            const cardValidationMessage = document.createElement('div');
            cardValidationMessage.className = 'card-validation-message';
            document.querySelector('.credit-card-form').appendChild(cardValidationMessage);
            
            // Actualizar número de tarjeta con validación
            cardNumber.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                this.value = value.match(/.{1,4}/g)?.join(' ') || '';
                
                if (value) {
                    cardNumberPreview.textContent = this.value;
                    
                    // Validar longitud de la tarjeta
                    if (value.length >= 13 && value.length <= 19) {
                        cardValidationMessage.className = 'card-validation-message show valid';
                        cardValidationMessage.innerHTML = '<i class="fas fa-check-circle"></i> Número de tarjeta válido';
                        setTimeout(() => {
                            cardValidationMessage.className = 'card-validation-message';
                        }, 2000);
                    } else if (value.length > 0) {
                        cardValidationMessage.className = 'card-validation-message show invalid';
                        cardValidationMessage.innerHTML = '<i class="fas fa-exclamation-circle"></i> El número debe tener entre 13 y 19 dígitos';
                    }
                } else {
                    cardNumberPreview.textContent = '•••• •••• •••• ••••';
                    cardValidationMessage.className = 'card-validation-message';
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
            
            // Actualizar CVV con validación mejorada
            cvv.addEventListener('focus', function() {
                document.querySelector('.card-preview').classList.add('flipped');
            });
            
            cvv.addEventListener('blur', function() {
                document.querySelector('.card-preview').classList.remove('flipped');
            });
            
            cvv.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').substring(0, 3);
                if (this.value) {
                    cvvPreview.textContent = this.value;
                    
                    // Validar longitud del CVV
                    if (this.value.length === 3) {
                        cardValidationMessage.className = 'card-validation-message show valid';
                        cardValidationMessage.innerHTML = '<i class="fas fa-check-circle"></i> CVV válido';
                        setTimeout(() => {
                            cardValidationMessage.className = 'card-validation-message';
                        }, 2000);
                    }
                } else {
                    cvvPreview.textContent = 'CVV';
                }
            });
            
            // Finalizar pedido
            const completeOrderBtn = document.getElementById('completeOrder');
            const confirmationModal = document.getElementById('confirmationModal');
            
            completeOrderBtn.addEventListener('click', function() {
                // Validación básica del formulario
                const firstName = document.getElementById('firstName').value.trim();
                const lastName = document.getElementById('lastName').value.trim();
                const email = document.getElementById('email').value.trim();
                const phone = document.getElementById('phone').value.trim();
                const address = document.getElementById('address').value.trim();
                const city = document.getElementById('city').value.trim();
                const state = document.getElementById('state').value.trim();
                const country = document.getElementById('country').value.trim();
                const postalCode = document.getElementById('postalCode').value.trim();
                
                if (!firstName || !lastName || !email || !address || !city || !country) {
                    showToast('Por favor, completa todos los campos requeridos', 'error');
                    return;
                }
                
                // Validación de tarjeta
                const cardNumberValue = cardNumber.value.replace(/\s/g, '');
                const cardHolderValue = cardHolder.value.trim();
                const expiryMonthValue = expiryMonth.value;
                const expiryYearValue = expiryYear.value;
                const cvvValue = cvv.value;
                
                if (!cardNumberValue || cardNumberValue.length < 13 || !cardHolderValue || 
                    !expiryMonthValue || !expiryYearValue || !cvvValue || cvvValue.length < 3) {
                    showToast('Por favor, completa correctamente los datos de la tarjeta', 'error');
                    return;
                }
                
                // Simular procesamiento de pago
                completeOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                completeOrderBtn.disabled = true;
                
                // Datos para crear la orden
                const addressData = {
                    street: address,
                    city: city,
                    state: state,
                    country: country,
                    zip_code: postalCode,
                    save_address: document.getElementById('saveAddress').checked
                };
                
                // Crear la orden
                fetch('api/create_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        userId: userId,
                        address: addressData,
                        cart: cartData,
                        paymentInfo: {
                            method: 'credit_card',
                            cardNumber: cardNumberValue,
                            cardHolder: cardHolderValue,
                            expiryMonth: expiryMonthValue,
                            expiryYear: expiryYearValue,
                            cvv: cvvValue
                        },
                        discountCode: appliedDiscount ? appliedDiscount.code : null
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar número de orden en el modal
                        document.getElementById('order-number').textContent = `#${data.orderNumber || 'DE-' + Math.floor(100000 + Math.random() * 900000)}`;
                        
                        // Mostrar mensaje de éxito y modal de confirmación
                        showToast('Pedido realizado con éxito', 'success');
                        
                        setTimeout(() => {
                            confirmationModal.style.display = 'flex';
                        }, 1500);
                    } else {
                        completeOrderBtn.innerHTML = '<i class="fas fa-lock"></i> Finalizar Pedido';
                        completeOrderBtn.disabled = false;
                        showToast(data.message || 'Error al procesar el pedido', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    completeOrderBtn.innerHTML = '<i class="fas fa-lock"></i> Finalizar Pedido';
                    completeOrderBtn.disabled = false;
                    showToast('Error al procesar el pedido', 'error');
                });
            });
            
            // Cerrar modal al hacer clic fuera de él
            window.addEventListener('click', function(event) {
                if (event.target === confirmationModal) {
                    confirmationModal.style.display = 'none';
                    // Redireccionar a la página de inicio después de cerrar el modal
                    window.location.href = 'home.php';
                }
            });
        });
    </script>
</body>
</html>