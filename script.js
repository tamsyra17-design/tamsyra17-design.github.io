(function(){
    "use strict";

    const translations = {
        ar: {
            heroTitle: 'نبذة تعريفية',
            heroSub: 'محل تمصيرة العز للمستلزمات الرجالية هو أحد الأسماء العريقة في مجال بيع المستلزمات الرجالية، حيث تأسس عام 1986 على يد الوالد خميس الشيدي، واضعًا حجر الأساس لمشروع انطلق ببداية متواضعة، شملت توفير مختلف احتياجات الرجل من منتجات متنوعة، ليكون وجهة متكاملة تخدم المجتمع المحلي. ومع مرور السنوات، شهد المحل تطورًا ملحوظًا، حيث تم في عام 2016 تجديد الهوية التجارية والانطلاق باسم “تمصيرة العز”، ليعكس روح الأصالة مع لمسة من الحداثة، مما ساهم في تعزيز حضوره وانتشاره. وفي عام 2025، تمت إعادة صياغة الهوية مرة أخرى بحلة جديدة وراقية ومميزة، تعكس مستوى أعلى من الجودة والتميز، ليواصل “تمصيرة العز” مسيرته بثقة، محافظًا على إرثه العريق ومواكبًا لتطلعات العملاء في الولاية',
            explore: 'استكشف المجموعة',
            shopByCat: 'تسوق حسب الفئة',
            back: 'رجوع',
            sort: 'ترتيب',
            category: 'الفئة:',
            desc: 'الوصف:',
            material: 'الخامة:',
            status: 'الحالة:',
            inStock: 'متوفر',
            outOfStock: 'نفذ من المخزون',
            limited: 'محدود',
            orderWhatsApp: 'اطلب عبر واتساب',
            quickView: 'نظرة سريعة',
            badgeNew: 'جديد',
            badgeBestseller: 'الأكثر مبيعاً',
            noProducts: 'لا توجد منتجات',
            copyright: '© 2017 تمصيرة العز. جميع الحقوق محفوظة.',
            products: 'منتجات',
            allCat: 'الكل',
            sortDefault: 'الافتراضي',
            sortPriceAsc: 'السعر: من الأقل إلى الأعلى',
            sortPriceDesc: 'السعر: من الأعلى إلى الأقل',
            sortNameAsc: 'الاسم (أ-ي)',
            cartTitle: 'سلة التسوق',
            cartTotal: 'المجموع',
            checkoutWhatsApp: 'إرسال الطلب عبر واتساب',
            emptyCart: 'السلة فارغة',
            addToCart: 'أضف للسلة',
            addedToast: 'تمت الإضافة للسلة',
            remove: 'إزالة'
        },
        en: {
            heroTitle: 'About Us',
            heroSub: 'Tamsirat Al-Ez for Men’s Essentials is a prestigious name in the men’s retail industry. Founded in 1986 by Khamis Al-Shidi, the business began with a humble vision: to provide a comprehensive range of high-quality products for the local community. Over the decades, the establishment has undergone significant evolution. In 2016, the brand identity was renewed as "Tamsirat Al-Ez," successfully blending traditional Omani authenticity with a modern touch. Most recently, in 2025, the brand was further refined with a sophisticated and high-end identity that reflects excellence and distinction. Today, Tamsirat Al-Ez continues its journey with confidence, honoring its deep-rooted heritage while meeting the evolving aspirations of its customers in the Wilayat.',
            explore: 'Explore Collection',
            shopByCat: 'Shop by Category',
            back: 'Back',
            sort: 'Sort',
            category: 'Category:',
            desc: 'Description:',
            material: 'Material:',
            status: 'Status:',
            inStock: 'In Stock',
            outOfStock: 'Out of Stock',
            limited: 'Limited',
            orderWhatsApp: 'Order via WhatsApp',
            quickView: 'Quick View',
            badgeNew: 'New',
            badgeBestseller: 'Bestseller',
            noProducts: 'No products found',
            copyright: '© 2017 Tamsirat Al-Ez. All rights reserved.',
            products: 'Products',
            allCat: 'All',
            sortDefault: 'Default',
            sortPriceAsc: 'Price: Low to High',
            sortPriceDesc: 'Price: High to Low',
            sortNameAsc: 'Name (A-Z)',
            cartTitle: 'Shopping Cart',
            cartTotal: 'Total',
            checkoutWhatsApp: 'Send Order via WhatsApp',
            emptyCart: 'Your cart is empty',
            addToCart: 'Add to Cart',
            addedToast: 'Added to cart',
            remove: 'Remove'
        }
    };

    let currentLang = 'ar';
    let masterData = [];
    let currentCategory = 'all';
    let currentSearchTerm = '';
    let cartItems = []; 

    // --- Core Logic Functions ---
    function saveCart() { localStorage.setItem('tamsirat_cart', JSON.stringify(cartItems)); updateCartBadge(); renderCartDrawer(); }
    function loadCart() { const saved = localStorage.getItem('tamsirat_cart'); cartItems = saved ? JSON.parse(saved) : []; updateCartBadge(); renderCartDrawer(); }
    function updateCartBadge() { const badge = document.getElementById('cartCountBadge'); if(badge) badge.innerText = cartItems.reduce((sum,i)=> sum + i.quantity,0); }
    function showToast(msg, duration=1800) { const toast = document.getElementById('toastMsg'); if(toast) { toast.innerText = msg; toast.style.opacity = '1'; setTimeout(()=> toast.style.opacity = '0', duration); } }

    function addToCart(productId, qty=1) {
        const product = masterData.find(p=> p.id === productId);
        if(!product) return;
        const isOutOfStock = product.status && (product.status.toLowerCase().includes('نفذ') || product.status === 'Out of Stock');
        if(isOutOfStock) {
            showToast(translations[currentLang].outOfStock, 1500); return;
        }
        const existing = cartItems.find(i=> i.id === productId);
        if(existing) existing.quantity += qty;
        else cartItems.push({ id: product.id, name: product.name, price: product.price, quantity: qty, image: product.image, status: product.status });
        saveCart(); renderCartDrawer(); showToast(translations[currentLang].addedToast, 1200);
        openCartDrawer();
    }

    function removeCartItem(id) { cartItems = cartItems.filter(i=> i.id !== id); saveCart(); renderCartDrawer(); }
    
    function updateQuantity(id, delta) {
        const idx = cartItems.findIndex(i=> i.id === id);
        if(idx === -1) return;
        let newQ = cartItems[idx].quantity + delta;
        if(newQ <= 0) removeCartItem(id);
        else { cartItems[idx].quantity = newQ; saveCart(); renderCartDrawer(); }
    }

    function renderCartDrawer() {
        const container = document.getElementById('cartItemsContainer');
        if(!container) return;
        const t = translations[currentLang];
        if(!cartItems.length) {
            container.innerHTML = `<div class="empty-cart-msg">${t.emptyCart}</div>`;
            document.getElementById('cartTotalPrice').innerText = 'OMR 0.000';
            return;
        }
        let total = 0, html = '';
        cartItems.forEach(item => {
            const itemTotal = item.price * item.quantity; total += itemTotal;
            html += `<div class="cart-item"><img class="cart-item-img" src="${item.image}" alt="${item.name}"><div class="cart-item-info"><div class="cart-item-name">${item.name}</div><div class="cart-item-price">OMR ${item.price.toFixed(3)}</div><div class="cart-item-actions"><button class="cart-qty-btn" data-id="${item.id}" data-delta="-1">-</button><span class="cart-item-qty">${item.quantity}</span><button class="cart-qty-btn" data-id="${item.id}" data-delta="1">+</button><button class="remove-item" data-id="${item.id}"><i class="fas fa-trash-alt"></i> ${t.remove}</button></div></div></div>`;
        });
        container.innerHTML = html;
        document.getElementById('cartTotalPrice').innerHTML = `OMR ${total.toFixed(3)}`;
        
        container.querySelectorAll('.cart-qty-btn').forEach(btn => btn.addEventListener('click', (e) => { e.stopPropagation(); updateQuantity(btn.dataset.id, parseInt(btn.dataset.delta)); }));
        container.querySelectorAll('.remove-item').forEach(btn => btn.addEventListener('click', (e) => { e.stopPropagation(); removeCartItem(btn.dataset.id); }));
    }

    function openCartDrawer() { document.getElementById('cartOverlay')?.classList.add('open'); document.getElementById('cartDrawer')?.classList.add('open'); }
    function closeCartDrawer() { document.getElementById('cartOverlay')?.classList.remove('open'); document.getElementById('cartDrawer')?.classList.remove('open'); }

    function sendCartWhatsApp() {
        if(!cartItems.length) { showToast(translations[currentLang].emptyCart, 1500); return; }
        let msg = ''; let total = 0;
        cartItems.forEach(i => { total += i.price * i.quantity; msg += `• ${i.name} × ${i.quantity} = ${(i.price*i.quantity).toFixed(3)} OMR\n`; });
        msg += `\n${translations[currentLang].cartTotal}: ${total.toFixed(3)} OMR\n\n${currentLang === 'ar' ? 'أود تأكيد الطلب' : 'I want to confirm order'}`;
        window.open(`https://api.whatsapp.com/send/?phone=96895149291&text=${encodeURIComponent(msg)}`, '_blank');
    }

    // --- Helper UI Functions ---
    window.addToCartById = (id) => addToCart(id, 1);

    function setHtmlDirection() {
        const dir = currentLang === 'ar' ? 'rtl' : 'ltr';
        document.documentElement.setAttribute('dir', dir);
        document.documentElement.setAttribute('lang', currentLang);
        document.body.classList.toggle('rtl', currentLang === 'ar');
    }

    function updateSortOptions() {
        const select = document.getElementById('sortSelectSaaf');
        if (!select) return;
        const t = translations[currentLang];
        select.innerHTML = `
            <option value="default">${t.sortDefault}</option>
            <option value="price-asc">${t.sortPriceAsc}</option>
            <option value="price-desc">${t.sortPriceDesc}</option>
            <option value="name-asc">${t.sortNameAsc}</option>
        `;
    }

    window.scrollToCategories = function() { document.getElementById('categoryHomeView')?.scrollIntoView({behavior:'smooth'}); };
    window.closeModalSaaf = function() { document.getElementById('productModalSaaf').style.display = 'none'; };
    window.openZoomSaaf = function() { document.getElementById('zoomedImageSaaf').src = document.getElementById('modalProductImgSaaf').src; document.getElementById('zoomLightboxSaaf').style.display = 'flex'; };
    window.closeZoomSaaf = function(event) { if(event) event.stopPropagation(); document.getElementById('zoomLightboxSaaf').style.display = 'none'; };
    
    window.orderWhatsApp = function(name, price) {
        const phone = "96895149291";
        const msg = currentLang === 'ar' ? `السلام عليكم، أرغب في شراء:\nالمنتج: ${name}\nالسعر: ${price} ريال عماني` : `Hello, I'm interested in purchasing:\nItem: ${name}\nPrice: OMR ${price}`;
        window.open(`https://api.whatsapp.com/send/?phone=${phone}&text=${encodeURIComponent(msg)}`, '_blank');
    };

    window.openCategoryView = function(cat) {
        currentCategory = cat;
        document.getElementById('categoryHomeView').style.display = 'none';
        document.getElementById('productSectionView').style.display = 'block';
        document.getElementById('currentCategoryTitleDisplay').textContent = cat === 'all' ? translations[currentLang].allCat : cat;
        filterAndRenderProducts();
        buildNavLinks();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    window.openModalSaaf = function(item) {
        const t = translations[currentLang];
        document.getElementById('modalProductNameSaaf').textContent = item.name;
        document.getElementById('modalProductImgSaaf').src = item.image;
        document.getElementById('modalCategorySaaf').textContent = item.category;
        document.getElementById('modalDescSaaf').textContent = item.description || '-';
        document.getElementById('modalMaterialSaaf').textContent = item.material || '-';
        const priceNum = parseFloat(item.price);
        document.getElementById('modalPriceSaaf').textContent = isNaN(priceNum) ? 'OMR 0.000' : `OMR ${priceNum.toFixed(3)}`;
        
        const out = item.status?.toLowerCase().includes('نفذ') || item.status === 'Out of Stock';
        const statusEl = document.getElementById('modalStatusSaaf');
        statusEl.textContent = out ? t.outOfStock : (item.status || t.inStock);
        statusEl.style.color = out ? 'red' : 'green';
        
        const addBtn = document.getElementById('modalAddToCartBtn');
        if (addBtn) {
            addBtn.querySelector('span').textContent = t.addToCart;
            addBtn.onclick = () => { if(out) showToast(t.outOfStock); else addToCart(item.id, 1); };
        }

        document.getElementById('modalOrderBtnSaaf').onclick = () => {
            if(out) { alert(currentLang === 'ar' ? 'عذراً، المنتج غير متوفر حالياً.' : 'Sorry, this item is out of stock.'); } 
            else { window.orderWhatsApp(item.name, parseFloat(item.price).toFixed(3)); window.closeModalSaaf(); }
        };
        document.getElementById('productModalSaaf').style.display = 'flex';
    };

    function buildNavLinks() {
        const categories = [...new Set(masterData.map(i => i.category))];
        const nav = document.getElementById('dynamicNavLinks');
        if(!nav) return;
        const t = translations[currentLang];
        let html = `<a href="#" class="${currentCategory === 'all' ? 'active' : ''}" data-cat="all">${t.allCat}</a>`;
        categories.forEach(cat => { html += `<a href="#" class="${currentCategory === cat ? 'active' : ''}" data-cat="${cat}">${cat}</a>`; });
        nav.innerHTML = html;
        nav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const cat = link.dataset.cat;
                if (cat === 'all') showHomeView();
                else window.openCategoryView(cat);
            });
        });
    }

    function showHomeView() {
        currentCategory = 'all';
        document.getElementById('categoryHomeView').style.display = 'block';
        document.getElementById('productSectionView').style.display = 'none';
        renderCategoryGrid();
        buildNavLinks();
    }

    function renderCategoryGrid() {
        const t = translations[currentLang];
        const cats = [...new Set(masterData.map(i => i.category))];
        const grid = document.getElementById('categoryGridContainer');
        if(!grid) return;
        let html = '';
        cats.forEach(cat => {
            const cover = masterData.find(p => p.category === cat);
            if (!cover) return;
            const count = masterData.filter(p => p.category === cat).length;
            html += `
            <div class="category-card-saaf" onclick="window.openCategoryView('${cat}')">
                <img src="${cover.image}" alt="${cat}" loading="lazy">
                <div class="category-overlay-saaf">
                    <h3>${cat}</h3>
                    <p>${count} ${t.products}</p>
                </div>
            </div>`;
        });
        grid.innerHTML = html;
    }

    function filterAndRenderProducts() {
        let filtered = [...masterData];
        if (currentCategory && currentCategory !== 'all') { filtered = filtered.filter(p => p.category === currentCategory); }
        if (currentSearchTerm) { filtered = filtered.filter(p => p.name.toLowerCase().includes(currentSearchTerm) || p.category.toLowerCase().includes(currentSearchTerm)); }
        
        const sort = document.getElementById('sortSelectSaaf').value;
        if (sort === 'price-asc') filtered.sort((a,b) => parseFloat(a.price) - parseFloat(b.price));
        else if (sort === 'price-desc') filtered.sort((a,b) => parseFloat(b.price) - parseFloat(a.price));
        else if (sort === 'name-asc') filtered.sort((a,b) => a.name.localeCompare(b.name, 'ar'));

        const container = document.getElementById('productGridContainer');
        const noMsg = document.getElementById('noProductsMessage');
        const t = translations[currentLang];
        
        if (filtered.length === 0) { container.innerHTML = ''; noMsg.style.display = 'block'; noMsg.textContent = t.noProducts; return; }
        noMsg.style.display = 'none';
        
        let html = '';
        filtered.forEach(item => {
            const out = item.status?.toLowerCase().includes('نفذ') || item.status === 'Out of Stock';
            const priceFormatted = parseFloat(item.price).toFixed(3);
            let badge = item.badge;
            
            if (currentLang === 'ar' && badge) {
                if(badge === 'New') badge = t.badgeNew;
                else if(badge === 'Bestseller') badge = t.badgeBestseller;
                else if(badge === 'Limited' || badge === 'Last Stock') badge = t.limited;
            }
            
            const escapedName = item.name.replace(/'/g, "\\'").replace(/"/g, '&quot;');
            const itemStr = JSON.stringify(item).replace(/'/g, "&apos;").replace(/"/g, '&quot;');
            
            html += `
            <div class="product-card-saaf">
                <div class="product-img-saaf" onclick='window.openModalSaaf(${itemStr})'>
                    ${badge ? `<span class="badge-saaf">${badge}</span>` : ''}
                    <img src="${item.image}" alt="${escapedName}" loading="lazy">
                </div>
                <div class="product-info-saaf">
                    <div class="product-name-saaf">${item.name}</div>
                    <div class="product-price-saaf">OMR ${priceFormatted}</div>
                    <div class="product-actions-wrap">
                        <button class="btn-cart" onclick="event.stopPropagation(); window.addToCartById('${item.id}')">
                            <i class="fas fa-cart-plus"></i> ${t.addToCart}
                        </button>
                        <button class="btn-saaf ${out ? 'out-of-stock' : ''}" ${out ? 'disabled' : ''} onclick="event.stopPropagation(); window.orderWhatsApp('${escapedName}','${priceFormatted}')">
                            <i class="fab fa-whatsapp"></i> ${out ? t.outOfStock : t.orderWhatsApp}
                        </button>
                    </div>
                </div>
            </div>`;
        });
        container.innerHTML = html;
    }

    function handleGlobalSearch() {
        const term = document.getElementById('globalSearchInput').value.trim().toLowerCase();
        currentSearchTerm = term;
        if (document.getElementById('categoryHomeView').style.display === 'block' && term !== '') {
            window.openCategoryView('all');
        } else { filterAndRenderProducts(); }
    }

    window.toggleLanguage = function() {
        currentLang = currentLang === 'en' ? 'ar' : 'en';
        setHtmlDirection();
        const langBtnText = document.getElementById('langText');
        if(langBtnText) langBtnText.textContent = currentLang === 'en' ? 'العربية' : 'English';
        updateLanguageUI();
        updateSortOptions();
        buildNavLinks();
        
        if (document.getElementById('productSectionView').style.display === 'block') {
            document.getElementById('currentCategoryTitleDisplay').textContent = currentCategory === 'all' ? translations[currentLang].allCat : currentCategory;
            filterAndRenderProducts();
        } else { renderCategoryGrid(); }
        
        renderCartDrawer();
    };

    function updateLanguageUI() {
        const t = translations[currentLang];
        document.querySelectorAll('[data-lang]').forEach(el => {
            const key = el.dataset.lang;
            if (t[key]) el.textContent = t[key];
        });
        const searchInput = document.getElementById('globalSearchInput');
        if(searchInput) searchInput.placeholder = currentLang === 'ar' ? 'ابحث عن مصار، كمه، خنجر...' : 'Search for mussar, kumma, khanjar...';
        
        const cartTitle = document.getElementById('cartTitle');
        if(cartTitle) cartTitle.innerText = t.cartTitle;

        const checkoutBtnText = document.querySelector('#cartCheckoutBtn span');
        if(checkoutBtnText) checkoutBtnText.innerText = t.checkoutWhatsApp;

        const totalLabel = document.querySelector('.cart-total span:first-child');
        if(totalLabel) totalLabel.innerText = t.cartTotal;
        
        const modalBtn = document.querySelector('#modalOrderBtnSaaf');
        if (modalBtn) modalBtn.innerHTML = `<i class="fab fa-whatsapp"></i> ${t.orderWhatsApp}`;
    }

    function attachEvents() {
        document.getElementById('langToggleBtn').onclick = window.toggleLanguage;
        document.getElementById('backToCategoriesBtn').onclick = (e) => { e.preventDefault(); showHomeView(); };
        document.getElementById('sortSelectSaaf').addEventListener('change', filterAndRenderProducts);
        document.getElementById('globalSearchInput').addEventListener('input', debounce(handleGlobalSearch, 300));
        
        document.getElementById('cartIconBtn').addEventListener('click', openCartDrawer);
        document.getElementById('closeCartBtn').addEventListener('click', closeCartDrawer);
        document.getElementById('cartOverlay').addEventListener('click', closeCartDrawer);
        document.getElementById('cartCheckoutBtn').addEventListener('click', sendCartWhatsApp);
        
        window.addEventListener('click', (e) => { if (e.target === document.getElementById('productModalSaaf')) window.closeModalSaaf(); });
    }

    function debounce(fn, d) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), d); }; }

    function initializeStore(data) {
        const appName = "Untitledspreadsheet-168153147";
        const tableName = "Untitled spreadsheet";
        const baseUrl = "https://www.appsheet.com/image/getimageurl";

        masterData = Array.isArray(data) && data.length ? data : [];
        masterData = masterData.map((item, idx) => {
            let imgUrl = item.image ? String(item.image) : "";
            let finalImageUrl = imgUrl;
            if (imgUrl && !imgUrl.startsWith('http')) {
                finalImageUrl = `${baseUrl}?appName=${appName}&tableName=${encodeURIComponent(tableName)}&fileName=${encodeURIComponent(imgUrl)}&appVersion=1.000005&signature=0d8b1dc9710c0ee701c0f177f7806c4565cd7916e512d6577e52fcf3658928d7`;
            }
            let parsedPrice = parseFloat(String(item.price).replace(/[^0-9.]/g, ''));
            if (isNaN(parsedPrice)) parsedPrice = 0;
            
            return {
                ...item,
                id: item.id || `prod_${idx}_${Date.now()}`,
                name: item.name || "منتج تقليدي",
                category: item.category || "تراثي",
                price: parsedPrice,
                image: finalImageUrl,
                status: item.status || "متوفر",
                description: item.description || "منتج عماني تقليدي أصيل",
                material: item.material || "جودة عالية",
                badge: item.badge || "",
            };
        });

        setHtmlDirection();
        updateSortOptions();
        updateLanguageUI();
        buildNavLinks();
        showHomeView();
        loadCart(); 
        attachEvents();
    }

    async function loadDataFromGoogle() {
        const API_URL = "https://script.google.com/macros/s/AKfycbw7-xTbFIWVCFX66EYwaVLaZw2TA_cosAMBK7xh2JtokQ8A0ljpwAqMtVtSGctRFk_I/exec"; 
        try {
            const response = await fetch(API_URL, { method: 'GET', redirect: 'follow' });
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            initializeStore(data);
        } catch (error) {
            console.warn("استخدام البيانات التجريبية بسبب خطأ في اتصال Google Sheets", error);
            // এখানে আপনি চাইলে আপনার DEMO_DATA ব্যবহার করতে পারেন
            initializeStore([]); 
        }
    }

    window.addEventListener('DOMContentLoaded', loadDataFromGoogle);
})();
