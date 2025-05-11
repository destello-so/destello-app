<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Furukawashiko Flake Stickers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; background: #f8f8f8; }
        header { background: #b7d3c6; padding: 40px 0 0 0; text-align: center; position: relative; }
        nav { background: #fff; padding: 10px 0; box-shadow: 0 2px 4px #0001; }
        nav ul { list-style: none; margin: 0; padding: 0; display: flex; justify-content: center; gap: 30px; }
        nav a { text-decoration: none; color: #333; font-weight: 500; }
        .hero { padding: 40px 0 20px 0; }
        .hero h1 { font-size: 2.5em; margin: 0 0 10px 0; color: #fff; }
        .hero p { color: #fff; margin-bottom: 20px; }
        .hero button { background: #ff7f6b; color: #fff; border: none; padding: 12px 30px; border-radius: 20px; font-size: 1em; cursor: pointer; }
        .section { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .section h2 { margin-bottom: 20px; }
        .products, .brands { display: flex; flex-wrap: wrap; gap: 24px; }
        .product, .brand { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #0001; padding: 16px; flex: 1 1 180px; min-width: 180px; max-width: 220px; text-align: center; }
        .product img { width: 100%; border-radius: 8px; }
        .product-title { font-weight: 600; margin: 10px 0 5px 0; }
        .product-price { color: #ff7f6b; font-weight: 500; }
        .out-of-stock { color: #aaa; font-size: 0.9em; }
        .brands { justify-content: space-around; align-items: center; gap: 40px; }
        .brand img { width: 80px; }
        .testimonial { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #0001; padding: 32px; margin: 40px auto; max-width: 700px; text-align: center; }
        @media (max-width: 900px) {
            .products, .brands { flex-direction: column; align-items: center; }
        }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li><a href="#">Inicio</a></li>
            <li><a href="#">Tienda</a></li>
            <li><a href="#">Productos</a></li>
            <li><a href="#">Blog</a></li>
            <li><a href="#">Página</a></li>
        </ul>
    </nav>
    <header>
        <div class="hero">
            <h1>Furukawashiko Flake Stickers</h1>
            <p>Inspirado por Sakura</p>
            <button>Comprar ahora</button>
        </div>
        <img src="https://i.imgur.com/0y8Ftya.png" alt="Stickers" style="position:absolute; right:10%; top:30px; width:260px; border-radius:12px;">
    </header>
    <div class="section">
        <h2>Best Sellers</h2>
        <div class="products">
            <div class="product">
                <img src="https://i.imgur.com/1.png" alt="Stapler set">
                <div class="product-title">Stapler set</div>
                <div class="product-price">150.00₩</div>
            </div>
            <div class="product">
                <img src="https://i.imgur.com/2.png" alt="Zipper Pen Pouch">
                <div class="product-title">Zipper Pen Pouch</div>
                <div class="product-price">110.00₩ - 150.00₩</div>
            </div>
            <div class="product">
                <img src="https://i.imgur.com/3.png" alt="Weekly Planner">
                <div class="product-title">Weekly Planner</div>
                <div class="product-price">150.00₩</div>
            </div>
            <div class="product">
                <img src="https://i.imgur.com/4.png" alt="Large Desk Calendar">
                <div class="product-title">Large Desk Calendar</div>
                <div class="product-price">150.00₩ <span class="out-of-stock">Agotado</span></div>
            </div>
        </div>
    </div>
    <div class="section">
        <div class="products">
            <div class="product">
                <img src="https://i.imgur.com/5.png" alt="Notebook">
                <div class="product-title">Notebook</div>
            </div>
            <div class="product">
                <img src="https://i.imgur.com/6.png" alt="Sticker">
                <div class="product-title">Sticker</div>
            </div>
            <div class="product">
                <img src="https://i.imgur.com/7.png" alt="Pencil Pocket">
                <div class="product-title">Pencil Pocket</div>
            </div>
            <div class="product">
                <img src="https://i.imgur.com/8.png" alt="Pencil">
                <div class="product-title">Pencil</div>
            </div>
        </div>
    </div>
    <div class="section">
        <h2>New Arrival</h2>
        <div class="products">
            <div class="product">
                <img src="https://i.imgur.com/9.png" alt="Campus Key Ring">
                <div class="product-title">Campus Key Ring</div>
                <div class="product-price">200.00₩</div>
            </div>
            <div class="product">
                <img src="https://i.imgur.com/10.png" alt="BT21 Dream Pencil">
                <div class="product-title">BT21 Dream Pencil</div>
                <div class="product-price">150.00₩</div>
            </div>
            <div class="product">
                <img src="https://i.imgur.com/11.png" alt="Peanuts Schedule Book">
                <div class="product-title">Peanuts Schedule Book</div>
                <div class="product-price">250.00₩</div>
            </div>
            <div class="product">
                <img src="https://i.imgur.com/12.png" alt="Night Sky">
                <div class="product-title">Night Sky</div>
                <div class="product-price">150.00₩ <span style="color:#ff7f6b;">-10%</span></div>
            </div>
            <div class="product">
                <img src="https://i.imgur.com/3.png" alt="Weekly Planner">
                <div class="product-title">Weekly Planner</div>
                <div class="product-price">150.00₩ <span class="out-of-stock">Agotado</span></div>
            </div>
        </div>
    </div>
    <div class="section">
        <h2>Shop By Brands</h2>
        <div class="brands">
            <div class="brand"><img src="https://i.imgur.com/brand1.png" alt="Book Shop"></div>
            <div class="brand"><img src="https://i.imgur.com/brand2.png" alt="Education"></div>
            <div class="brand"><img src="https://i.imgur.com/brand3.png" alt="Kids Education"></div>
            <div class="brand"><img src="https://i.imgur.com/brand4.png" alt="Stationery"></div>
        </div>
    </div>
    <div class="testimonial">
        <h2>Testimonial</h2>
        <p>"¡Me encantan estos productos! Son de excelente calidad y muy bonitos para mi escritorio."</p>
        <p>- Cliente satisfecho</p>
    </div>
</body>
</html>
