<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SM Rentals - Camera Rental System</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="auth-screen" id="authScreen">
    <div class="auth-card">
      <div class="logo auth-logo">SM <span>RENTALS</span></div>
      <h2 id="authTitle">Login</h2>
      <p id="authSubtitle">Login to manage your camera rentals.</p>

      <form id="authForm">
        <div id="nameField" class="auth-field hidden">
          <label>Full name</label>
          <input id="authName" type="text" placeholder="Your name">
        </div>
        <label>Email</label>
        <input id="authEmail" type="email" placeholder="you@example.com" required>
        <label>Password</label>
        <input id="authPassword" type="password" placeholder="Password" required minlength="4">
        <button type="submit" class="auth-submit" id="authSubmit">Login</button>
      </form>

      <button class="switch-auth" id="switchAuth">Don't have an account? Register</button>
      <p class="demo-note">Your account and rental orders are stored in MySQL.</p>
    </div>
  </div>

  <div id="appShell" class="hidden">
  <header class="header">
    <div class="logo">SM <span>RENTALS</span></div>
    <div class="header-right">
      <span id="userWelcome" class="user-welcome"></span>
      <input id="searchInput" type="search" placeholder="Search products">
      <button id="cartBtn" class="cart-btn">🛒 <span id="cartCount">0</span></button>
      <button id="logoutBtn" class="logout-btn">Logout</button>
    </div>
  </header>

  <main>
    <section class="rental-bar" id="rentalBar">
      <div class="calendar">▣</div>
      <div>
        <strong>Select a rental period</strong>
        <small>View prices and availability</small>
      </div>
      <span>›</span>
    </section>

    <section class="period-panel hidden" id="periodPanel">
      <div>
        <label>Start date</label>
        <input type="date" id="startDate">
      </div>
      <div>
        <label>End date</label>
        <input type="date" id="endDate">
      </div>
      <button id="applyPeriod">Apply period</button>
    </section>

    <div class="layout">
      <aside class="sidebar">
        <button class="category active" data-category="All">All products</button>
        <button class="category" data-category="Bundle Deals">Bundle Deals</button>
        <button class="category" data-category="Camera">Camera</button>
        <button class="category" data-category="Camera Stabilizer Systems">Camera Stabilizer Systems</button>

        <div class="category-group">
          <button class="category lens-toggle" data-category="Lenses">⌃ &nbsp; Lenses</button>
          <div class="subcategories">
            <button class="category" data-category="Sony E-Mount Lenses">Sony Mirrorless E-Mount Lenses</button>
            <button class="category" data-category="Cine Lenses">Cine Lenses</button>
            <button class="category" data-category="Lens Adapters">Lens Adapters</button>
          </div>
        </div>

        <button class="category" data-category="Monitor And Wireless Video System">Monitor And Wireless Video System</button>
        <button class="category" data-category="Wireless Follow Focus Systems">Wireless Follow Focus Systems</button>
        <button class="category" data-category="Lights">Lights</button>
        <button class="category" data-category="Tripods">Tripods</button>
        <button class="category" data-category="Battery System">Battery System</button>
      </aside>

      <section class="products-area">
        <div class="toolbar">
          <p id="resultCount">All products</p>
          <select id="sortSelect">
            <option value="default">Standard sorting</option>
            <option value="low">Price: low to high</option>
            <option value="high">Price: high to low</option>
            <option value="name">Name: A-Z</option>
          </select>
        </div>
        <div id="products" class="products"></div>
      </section>
    </div>
  </main>

  <div class="drawer-overlay hidden" id="overlay"></div>
  <aside class="cart-drawer" id="cartDrawer">
    <div class="drawer-head">
      <h2>Your Rental Cart</h2>
      <button id="closeCart">×</button>
    </div>
    <div id="cartItems" class="cart-items"></div>
    <div class="cart-summary">
      <div><span>Rental days</span><strong id="summaryDays">1</strong></div>
      <div><span>Subtotal</span><strong id="subtotal">Rs.0.00</strong></div>
      <div><span>Security deposit</span><strong id="deposit">Rs.0.00</strong></div>
      <div class="total"><span>Total</span><strong id="total">Rs.0.00</strong></div>
      <button id="checkoutBtn" class="checkout">Proceed to Checkout</button>
    </div>
  </aside>

  <div class="modal hidden" id="checkoutModal">
    <div class="modal-card">
      <button class="modal-close" id="closeModal">×</button>
      <h2>Complete Rental</h2>
      <p id="checkoutInfo"></p>
      <form id="checkoutForm">
        <input required id="customerName" placeholder="Full name">
        <input required type="tel" id="customerPhone" placeholder="Phone number">
        <input required type="email" id="customerEmail" placeholder="Email address">
        <textarea required id="customerAddress" placeholder="Address"></textarea>
        <button type="submit" class="checkout">Confirm Rental</button>
      </form>
    </div>
  </div>

  <div class="toast" id="toast"></div>
  </div>
  <script src="script.js"></script>
</body>
</html>
