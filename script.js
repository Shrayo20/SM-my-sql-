
let products = [];
const NO_IMG = "data:image/svg+xml," + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="300" height="200"><rect width="100%" height="100%" fill="#eee"/><text x="50%" y="50%" fill="#999" font-family="Arial" text-anchor="middle">No image</text></svg>');

let activeCategory = "All";
let search = "";
let cart = JSON.parse(localStorage.getItem("rentalCart") || "[]");
let startDate = null, endDate = null;

const $ = id => document.getElementById(id);

// ---------- MySQL Login / Register ----------
let authMode = "login";
let currentUser = null;

async function api(action, payload = null){
  const options = payload ? {method:"POST", headers:{"Content-Type":"application/json"}, body:JSON.stringify(payload)} : {};
  const res = await fetch(`api.php?action=${action}`, options);
  const data = await res.json().catch(()=>({success:false,message:"Server response was not valid JSON."}));
  if(!res.ok && !data.message) data.message = "Request failed.";
  return data;
}

function showApp(){
  $("authScreen").classList.add("hidden");
  $("appShell").classList.remove("hidden");
  $("userWelcome").textContent = currentUser ? `Hi, ${currentUser.name || currentUser.email}` : "";
}
function showAuth(){
  $("authScreen").classList.remove("hidden");
  $("appShell").classList.add("hidden");
}
function updateAuthUI(){
  const isLogin = authMode === "login";
  $("authTitle").textContent = isLogin ? "Sign in" : "Create account";
  $("authSubtitle").textContent = isLogin ? "Sign in to manage your camera rentals." : "Create an account to rent camera equipment.";
  $("authSubmit").textContent = isLogin ? "Sign in" : "Create account";
  $("nameField").classList.toggle("hidden", isLogin);
  $("switchAuth").textContent = isLogin ? "Need an account? Sign up" : "Already have an account? Sign in";
}

$("switchAuth").addEventListener("click",()=>{ authMode = authMode==="login" ? "register" : "login"; updateAuthUI(); });

$("authForm").addEventListener("submit",async e=>{
  e.preventDefault();
  const email=$("authEmail").value.trim().toLowerCase();
  const password=$("authPassword").value;
  const name=$("authName").value.trim();
  const data=await api(authMode==="login" ? "login" : "register", {name,email,password});
  if(!data.success){ toast(data.message || "Authentication failed"); return; }
  currentUser=data.user; sessionStorage.setItem("rentalUser",JSON.stringify(currentUser));
  showApp(); e.target.reset(); toast(authMode==="login" ? "Sign in successful" : "Account created successfully");
});

$("logoutBtn").addEventListener("click",async()=>{ await api("logout"); currentUser=null; sessionStorage.removeItem("rentalUser"); showAuth(); authMode="login"; updateAuthUI(); });

updateAuthUI();
const storedUser=sessionStorage.getItem("rentalUser");
if(storedUser){ try{ currentUser=JSON.parse(storedUser); showApp(); }catch(e){ showAuth(); } } else showAuth();

const money = n => "Rs." + Number(n).toLocaleString("en-IN", {minimumFractionDigits:2});

function rentalDays(){
  if(!startDate || !endDate) return 1;
  const a = new Date(startDate), b = new Date(endDate);
  const days = Math.ceil((b-a)/(1000*60*60*24)) + 1;
  return Math.max(1, days);
}

function saveCart(){ localStorage.setItem("rentalCart", JSON.stringify(cart)); }

function renderProducts(){
  let list = products.filter(p =>
    (activeCategory === "All" || p.category === activeCategory) &&
    p.name.toLowerCase().includes(search.toLowerCase())
  );

  const sort = $("sortSelect").value;
  if(sort==="low") list.sort((a,b)=>a.price-b.price);
  if(sort==="high") list.sort((a,b)=>b.price-a.price);
  if(sort==="name") list.sort((a,b)=>a.name.localeCompare(b.name));

  $("resultCount").textContent = activeCategory === "All" ? `${list.length} products` : `${activeCategory} (${list.length})`;

  $("products").innerHTML = list.length ? list.map(p => `
    <article class="product">
      <div class="product-img"><img src="${p.img}" alt="${p.name}" onerror="this.onerror=null;this.src=NO_IMG"></div>
      <div class="product-info">
        <h3>${p.name}</h3>
        <div class="day">${rentalDays()} day${rentalDays()>1?"s":""}</div>
        <div class="price">${money(p.price * rentalDays())}</div>
        <button class="add-btn" onclick="addToCart(${p.id})">Add to rental</button>
      </div>
    </article>
  `).join("") : `<div class="empty">No products found.</div>`;
}

function addToCart(id){
  const found = cart.find(x=>x.id===id);
  if(found) found.qty++;
  else cart.push({id,qty:1});
  saveCart(); renderCart(); openCart(); toast("Added to rental cart");
}

function changeQty(id, delta){
  const item = cart.find(x=>x.id===id);
  if(!item) return;
  item.qty += delta;
  if(item.qty <= 0) cart = cart.filter(x=>x.id!==id);
  saveCart(); renderCart();
}

function renderCart(){
  $("cartCount").textContent = cart.reduce((sum,x)=>sum+x.qty,0);
  const days = rentalDays();
  $("summaryDays").textContent = days;

  if(!cart.length){
    $("cartItems").innerHTML = `<div class="empty">Your rental cart is empty.</div>`;
  } else {
    $("cartItems").innerHTML = cart.map(item=>{
      const p=products.find(x=>x.id===item.id);
      return `<div class="cart-item">
        <img src="${p.img}" alt="" onerror="this.onerror=null;this.src=NO_IMG">
        <div>
          <h4>${p.name}</h4>
          <small>${money(p.price)} / day</small>
          <div class="qty">
            <button onclick="changeQty(${p.id},-1)">−</button>
            <span>${item.qty}</span>
            <button onclick="changeQty(${p.id},1)">+</button>
            <button class="remove" onclick="changeQty(${p.id},-999)">Remove</button>
          </div>
        </div>
        <strong>${money(p.price*days*item.qty)}</strong>
      </div>`;
    }).join("");
  }

  const subtotal = cart.reduce((sum,item)=>{
    const p=products.find(x=>x.id===item.id);
    return sum+p.price*days*item.qty;
  },0);
  const deposit = cart.reduce((sum,item)=>{
    const p=products.find(x=>x.id===item.id);
    return sum+p.deposit*item.qty;
  },0);
  $("subtotal").textContent=money(subtotal);
  $("deposit").textContent=money(deposit);
  $("total").textContent=money(subtotal+deposit);
  $("checkoutBtn").disabled=!cart.length;
}

function openCart(){ $("cartDrawer").classList.add("open"); $("overlay").classList.remove("hidden"); }
function closeCart(){ $("cartDrawer").classList.remove("open"); $("overlay").classList.add("hidden"); }

function toast(message){
  const t=$("toast"); t.textContent=message; t.classList.add("show");
  setTimeout(()=>t.classList.remove("show"),1800);
}

document.querySelectorAll(".category").forEach(btn=>{
  btn.addEventListener("click",()=>{
    document.querySelectorAll(".category").forEach(x=>x.classList.remove("active"));
    btn.classList.add("active");
    activeCategory=btn.dataset.category;
    renderProducts();
  });
});

$("searchInput").addEventListener("input",e=>{search=e.target.value;renderProducts();});
$("sortSelect").addEventListener("change",renderProducts);

$("rentalBar").addEventListener("click",()=>$("periodPanel").classList.toggle("hidden"));
$("applyPeriod").addEventListener("click",()=>{
  const s=$("startDate").value, e=$("endDate").value;
  if(!s || !e || new Date(e)<new Date(s)){ toast("Please select valid dates"); return; }
  startDate=s; endDate=e;
  $("periodPanel").classList.add("hidden");
  $("rentalBar").querySelector("small").textContent=`${s} to ${e} • ${rentalDays()} days`;
  renderProducts(); renderCart(); toast("Rental period applied");
});

$("cartBtn").addEventListener("click",openCart);
$("closeCart").addEventListener("click",closeCart);
$("overlay").addEventListener("click",closeCart);

$("checkoutBtn").addEventListener("click",()=>{
  if(!cart.length) return;
  if(!startDate || !endDate){toast("Select a rental period first");return;}
  $("checkoutInfo").textContent=`Rental period: ${startDate} to ${endDate} (${rentalDays()} days). Total: ${$("total").textContent}`;
  $("checkoutModal").classList.remove("hidden");
});

$("closeModal").addEventListener("click",()=>$("checkoutModal").classList.add("hidden"));

$("checkoutForm").addEventListener("submit",async e=>{
  e.preventDefault();
  const data=await api("checkout",{
    name:$("customerName").value.trim(), phone:$("customerPhone").value.trim(),
    email:$("customerEmail").value.trim(), address:$("customerAddress").value.trim(),
    startDate,endDate,items:cart
  });
  if(!data.success){ toast(data.message || "Could not save rental"); return; }
  alert(`Rental confirmed!\nOrder ID: ${data.orderId}\nThank you, ${$("customerName").value}.`);
  cart=[]; saveCart(); renderCart();
  $("checkoutModal").classList.add("hidden"); closeCart(); e.target.reset();
});

async function init(){
  const data = await api("products");
  if(!data.success){
    $("products").innerHTML = `<div class="empty">${data.message || "Could not load products."}</div>`;
    return;
  }
  products = data.products.map(p=>({...p,price:Number(p.price),deposit:Number(p.deposit)}));
  cart = cart.filter(i=>products.some(p=>p.id===i.id));
  saveCart(); renderProducts(); renderCart();
}
init();
