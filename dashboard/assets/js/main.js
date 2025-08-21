// Autozentrum Kiener – main.js (Vercel ready)
(function(){
  const $ = (sel, ctx=document)=>ctx.querySelector(sel);
  const $$ = (sel, ctx=document)=>Array.from(ctx.querySelectorAll(sel));

  // Year
  const yEl = $("#year"); if (yEl) yEl.textContent = new Date().getFullYear();

  // Theme toggle (Tailwind CDN in class-mode; stores in localStorage)
  const root = document.documentElement;
  const THEME_KEY = "ak-theme";
  const applyTheme = (mode)=>{
    if(mode==="dark") root.classList.add("dark"); else root.classList.remove("dark");
    const icon = $("#themeIcon"); if(icon) icon.textContent = mode==="dark" ? "☀️" : "🌙";
    const tBtns = [$("#themeToggle"), $("#themeToggleMobile")].filter(Boolean);
    tBtns.forEach(b=>b.setAttribute("aria-pressed", String(mode==="dark")));
  };
  const saved = localStorage.getItem(THEME_KEY);
  applyTheme(saved || "light");
  $$("#themeToggle, #themeToggleMobile").forEach(btn=>btn.addEventListener("click", ()=>{
    const isDark = root.classList.toggle("dark");
    localStorage.setItem(THEME_KEY, isDark ? "dark" : "light");
    applyTheme(isDark ? "dark" : "light");
  }));

  // Mobile menu
  const menuBtn = $("#mobileMenuBtn");
  const menu = $("#mobileMenu");
  if(menuBtn && menu){
    menuBtn.addEventListener("click", ()=>{
      const expanded = menuBtn.getAttribute("aria-expanded")==="true";
      menuBtn.setAttribute("aria-expanded", String(!expanded));
      menu.classList.toggle("open");
    });
  }

  // Back to top
  const back = $("#backToTop");
  if(back){
    window.addEventListener("scroll", ()=>{
      if(window.scrollY > 500) back.classList.add("show");
      else back.classList.remove("show");
    });
    back.addEventListener("click", ()=>window.scrollTo({top:0, behavior:"smooth"}));
  }

  // Reveal animations
  const io = new IntersectionObserver(entries=>{
    for(const e of entries){
      if(e.isIntersecting){ e.target.classList.add("in"); if(e.target.dataset.revealOnce!=="false") io.unobserve(e.target); }
    }
  }, {threshold:.15});
  $$(".reveal,[data-reveal-group]").forEach(el=>io.observe(el));

  // Cookie banner (functional-only)
  const COOKIE_KEY="ak-consent";
  const banner=$("#cookieBanner");
  if(banner && !localStorage.getItem(COOKIE_KEY)){
    banner.style.display="block";
    $("#cookieAcceptAll").addEventListener("click", ()=>{ localStorage.setItem(COOKIE_KEY,"all"); banner.remove(); });
    $("#cookieReject").addEventListener("click", ()=>{ localStorage.setItem(COOKIE_KEY,"necessary"); banner.remove(); });
    const open=$("#cookieSettingsBtn"); if(open) open.addEventListener("click", ()=>{ banner.style.display="block"; });
  }

  // VEHICLES: fetch & render
  const grid = $("#vehiclesGrid");
  if(grid){
    const searchInput = $("#searchInput");
    const fuelFilter = $("#fuelFilter");
    const sortSelect = $("#sortSelect");
    const resetBtn = $("#resetFilters");
    const loadMoreBtn = $("#loadMoreBtn");

    let page=1, size=12, items=[], total=0, loading=false, lastQuery="";
    function buildQuery(){
      const params = new URLSearchParams();
      params.set("page", String(page));
      params.set("size", String(size));
      const q = (searchInput?.value||"").trim();
      if(q) params.set("q", q);
      const fuel = fuelFilter?.value||"";
      if(fuel) params.set("fuel", fuel);
      params.set("sort", sortSelect?.value||"price-asc");
      return params.toString();
    }

    function el(tag, cls, html){ const e=document.createElement(tag); if(cls) e.className=cls; if(html!=null) e.innerHTML=html; return e; }

    function render(){
      grid.innerHTML="";
      if(!items.length){ grid.innerHTML = '<div class="text-sm text-gray-600">Keine Fahrzeuge gefunden.</div>'; return; }
      for(const v of items){
        const card = el("article","vehicle-card");
        const media = el("div","vehicle-media");
        const img = new Image(); img.loading="lazy"; img.alt = v.title || "Fahrzeug"; img.src = v.image || "https://placehold.co/640x480?text=Fahrzeug";
        media.appendChild(img);
        const body = el("div","vehicle-body");
        body.appendChild(el("h3","vehicle-title", v.title||"Ohne Titel"));
        const meta = el("div","vehicle-meta",
          `<span>${(v.year||"—")}</span> · <span>${(v.mileageFormatted||"— km")}</span> · <span>${v.fuel||"—"}</span> · <span>${v.gearbox||"—"}</span>`
        );
        body.appendChild(meta);
        const foot = el("div","vehicle-footer");
        foot.appendChild(el("div","price-chip", v.priceFormatted || "Preis auf Anfrage"));
        const more = el("a","more-btn", "Details");
        more.href = v.url || "#"; more.target = "_blank"; more.rel="noopener";
        foot.appendChild(more);
        card.append(media, body, foot);
        grid.appendChild(card);
      }
    }

    async function load(reset=false){
      if(loading) return;
      loading=true;
      if(reset){ page=1; items=[]; }
      const qs = buildQuery(); lastQuery = qs;
      try{
        const res = await fetch(`/api/vehicles.php?${qs}`, {headers:{Accept:"application/json"}});
        if(!res.ok) throw new Error("Bad response");
        const data = await res.json();
        total = data.total ?? 0;
        const newItems = (data.items||[]);
        items = reset ? newItems : items.concat(newItems);
        render();
        // hide "Mehr laden" if we are at end
        if(loadMoreBtn){
          const maxPages = Math.ceil((data.total||0) / size);
          loadMoreBtn.style.display = page >= maxPages ? "none" : "inline-flex";
        }
      }catch(err){
        console.error(err);
        grid.innerHTML = '<div class="text-sm text-gray-600">Fehler beim Laden der Fahrzeuge.</div>';
      }finally{ loading=false; }
    }

    load(true);

    if(searchInput) searchInput.addEventListener("input", ()=>{ page=1; load(true); });
    if(fuelFilter) fuelFilter.addEventListener("change", ()=>{ page=1; load(true); });
    if(sortSelect) sortSelect.addEventListener("change", ()=>{ page=1; load(true); });
    if(resetBtn) resetBtn.addEventListener("click", ()=>{
      if(searchInput) searchInput.value="";
      if(fuelFilter) fuelFilter.value="";
      if(sortSelect) sortSelect.value="price-asc";
      page=1; load(true);
    });
    if(loadMoreBtn) loadMoreBtn.addEventListener("click", ()=>{ page++; load(); });
  }

  // Newsletter form demo (frontend only)
  const newsletterForm = $("#newsletterForm");
  if(newsletterForm){
    newsletterForm.addEventListener("submit", (e)=>{
      e.preventDefault();
      const email = $("#newsletterEmail")?.value?.trim();
      const out = $("#newsletterStatus");
      if(!email || !email.includes("@")){ if(out) out.textContent="Bitte gültige E-Mail eingeben."; return; }
      if(out) out.textContent="Danke! Wir melden uns.";
      newsletterForm.reset();
    });
  }

  // Contact form client-side validation only (send via mailto or backend of your choice)
  const contactForm = $("#contactForm");
  if(contactForm){
    contactForm.addEventListener("submit", (e)=>{
      e.preventDefault();
      const name = $("#name")?.value?.trim();
      const email = $("#email")?.value?.trim();
      const message = $("#message")?.value?.trim();
      const status = $("#formStatus");
      if(!name||!email||!message){ status.textContent="Bitte füllen Sie alle Pflichtfelder aus."; return; }
      status.textContent="Danke! Wir melden uns kurzfristig.";
      contactForm.reset();
    });
  }
})();
