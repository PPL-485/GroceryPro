<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
    laravelVersion: { type: String, required: true },
    phpVersion: { type: String, required: true },
});

const features = [
    { icon: 'mdi-chart-bar', title: 'Smart Dashboard', description: 'Real-time insights into your revenue, transactions, and inventory levels at a glance.' },
    { icon: 'mdi-cart-outline', title: 'Point of Sale', description: 'Lightning-fast POS system with QRIS payment support for seamless checkout experiences.' },
    { icon: 'mdi-package-variant-closed', title: 'Stock Management', description: 'Track inventory levels, get low stock alerts, and manage your products effortlessly.' },
    { icon: 'mdi-trending-up', title: 'Detailed Reports', description: 'Comprehensive transaction history and analytics to make data-driven decisions.' },
    { icon: 'mdi-account-group-outline', title: 'User Management', description: 'Control access with Owner and Cashier roles, keeping your operations secure.' },
    { icon: 'mdi-shield-check-outline', title: 'Secure & Reliable', description: 'Built with security in mind, protecting your business data around the clock.' },
];

const testimonials = [
    { name: 'Budi Santoso', store: 'Toko Budi Jaya', text: '"GroceryPro has completely transformed how I manage my inventory. The low stock alerts are a lifesaver!"', rating: 5 },
    { name: 'Siti Aminah', store: 'Warung Barokah', text: '"The POS system is incredibly fast. My customers are happier because checkout is so smooth now."', rating: 5 },
    { name: 'Ahmad Faisal', store: 'Faisal Minimarket', text: '"I love the detailed reports. I finally understand which products are my best sellers and where my money is going."', rating: 4 },
];

const faqs = ref([
    { question: 'Is GroceryPro suitable for a small warung?', answer: 'Absolutely! GroceryPro is designed specifically for small to medium grocery stores and warungs. It\'s easy to use and doesn\'t require expensive hardware.', open: false },
    { question: 'Do I need a special device for the POS?', answer: 'No, you can use GroceryPro on any modern web browser, tablet, or smartphone. We also support standard barcode scanners if you have one.', open: false },
    { question: 'How secure is my store data?', answer: 'We take security very seriously. All your data is encrypted and backed up daily. Only authorized users with roles you assign can access sensitive information.', open: false },
    { question: 'Can I try it before I commit?', answer: 'Yes! We offer a fully-featured free trial so you can experience how GroceryPro can help your business before making a decision.', open: false },
]);

const toggleFaq = (index) => {
    faqs.value[index].open = !faqs.value[index].open;
};
</script>

<template>
    <Head title="Welcome to GroceryPro" />

    <v-app>
        <div class="lp-root">

            <!-- ═══════════════════ HEADER ═══════════════════ -->
            <header class="lp-header">
                <div class="lp-logo" v-motion-fade>
                    <div class="lp-logo-icon">
                        <v-icon icon="mdi-package-variant-closed" size="22" color="white"></v-icon>
                    </div>
                    <span class="lp-logo-text">GroceryPro</span>
                </div>

                <div v-if="canLogin" v-motion-fade>
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')">
                        <v-btn color="#2E6B3B" variant="flat" class="lp-btn-signin">Dashboard</v-btn>
                    </Link>
                    <template v-else>
                        <Link :href="route('login')">
                            <v-btn color="#2E6B3B" variant="flat" class="lp-btn-signin">Sign In</v-btn>
                        </Link>
                    </template>
                </div>
            </header>

            <!-- ═══════════════════ HERO ═══════════════════ -->
            <main class="lp-hero">
                <div class="lp-hero-blob lp-blob-1"></div>
                <div class="lp-hero-blob lp-blob-2"></div>

                <div class="lp-hero-grid">
                    <!-- Left: Text -->
                    <div class="lp-hero-text" v-motion-slide-visible-left>
                        <span class="lp-badge">Built for Small Grocery Stores</span>
                        <h1 class="lp-hero-title">
                            Fresh Approach to<br/>
                            <span class="lp-accent">Inventory</span>
                        </h1>
                        <p class="lp-hero-sub">
                            Streamline your grocery store operations with our intuitive inventory management system.
                            From point-of-sale to analytics, everything you need in one place.
                        </p>
                        <div style="margin-top: 2rem;">
                            <Link v-if="canRegister" :href="route('register')">
                                <v-btn color="#D38865" variant="flat" class="lp-btn-cta">
                                    Get Started
                                    <v-icon icon="mdi-arrow-right" size="small" style="margin-left:6px;"></v-icon>
                                </v-btn>
                            </Link>
                        </div>
                    </div>

                    <!-- Right: Dashboard Mockup -->
                    <div class="lp-hero-graphic" v-motion-slide-visible-right>
                        <div class="lp-mockup-bg-rotate"></div>
                        <div class="lp-blob-circle lp-bc-right"></div>
                        <div class="lp-blob-circle lp-bc-left"></div>

                        <div class="lp-mockup-card">
                            <div class="lp-mockup-inner">
                                <div class="lp-mockup-header-row">
                                    <div>
                                        <p class="lp-mockup-label">Today's Revenue</p>
                                        <h3 class="lp-mockup-revenue">Rp 2,450,000</h3>
                                        <p class="lp-mockup-growth">
                                            <v-icon icon="mdi-arrow-up" size="x-small"></v-icon>
                                            +12.5% <span class="lp-mockup-growth-sub">from yesterday</span>
                                        </p>
                                    </div>
                                    <div class="lp-mockup-icon-box">
                                        <v-icon icon="mdi-trending-up" color="#D38865"></v-icon>
                                    </div>
                                </div>
                                <div class="lp-mockup-row lp-mockup-row-neutral">
                                    <span>Transactions</span><span class="lp-mockup-val">158</span>
                                </div>
                                <div class="lp-mockup-row lp-mockup-row-warn">
                                    <span>Low Stock Items</span><span class="lp-mockup-val lp-accent">8</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- ═══════════════════ FEATURES ═══════════════════ -->
            <section class="lp-features">
                <div class="lp-container">
                    <div class="lp-section-head" v-motion-slide-visible-bottom>
                        <h2 class="lp-section-title">Everything You Need</h2>
                        <p class="lp-section-sub">Powerful features designed for grocery store owners</p>
                    </div>

                    <div class="lp-grid-3">
                        <div
                            v-for="(f, i) in features"
                            :key="i"
                            class="lp-feature-card lp-fade-up"
                            :style="{ animationDelay: (i * 100) + 'ms' }"
                        >
                            <div class="lp-feature-icon">
                                <v-icon :icon="f.icon"></v-icon>
                            </div>
                            <h3 class="lp-feature-title">{{ f.title }}</h3>
                            <p class="lp-feature-desc">{{ f.description }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══════════════════ STATS ═══════════════════ -->
            <section class="lp-stats">
                <div class="lp-stats-overlay"></div>
                <div class="lp-container lp-stats-grid">
                    <div class="lp-stat-item" v-motion-pop-visible>
                        <span class="lp-stat-num">99.9%</span>
                        <span class="lp-stat-label">Uptime</span>
                    </div>
                    <div class="lp-stat-item lp-stat-divider" v-motion-pop-visible :delay="200">
                        <span class="lp-stat-num">500+</span>
                        <span class="lp-stat-label">Stores Powered</span>
                    </div>
                    <div class="lp-stat-item lp-stat-divider" v-motion-pop-visible :delay="400">
                        <span class="lp-stat-num">24/7</span>
                        <span class="lp-stat-label">Support</span>
                    </div>
                </div>
            </section>

            <!-- ═══════════════════ TESTIMONIALS ═══════════════════ -->
            <section class="lp-testimonials">
                <div class="lp-container">
                    <div class="lp-section-head" v-motion-slide-visible-bottom>
                        <h2 class="lp-section-title">What Our Users Say</h2>
                        <p class="lp-section-sub">Join thousands of happy store owners</p>
                    </div>

                    <div class="lp-grid-3">
                        <div
                            v-for="(t, i) in testimonials"
                            :key="i"
                            class="lp-testimonial-card lp-fade-up"
                            :style="{ animationDelay: (i * 150) + 'ms' }"
                        >
                            <div class="lp-testimonial-rating">
                                <v-icon v-for="n in t.rating" :key="n" icon="mdi-star" color="#F59E0B" size="small"></v-icon>
                                <v-icon v-for="n in 5 - t.rating" :key="'e'+n" icon="mdi-star-outline" color="#F59E0B" size="small"></v-icon>
                            </div>
                            <p class="lp-testimonial-text">{{ t.text }}</p>
                            <div class="lp-testimonial-author">
                                <div class="lp-author-avatar">
                                    {{ t.name.charAt(0) }}
                                </div>
                                <div>
                                    <h4 class="lp-author-name">{{ t.name }}</h4>
                                    <span class="lp-author-store">{{ t.store }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══════════════════ FAQ ═══════════════════ -->
            <section class="lp-faq">
                <div class="lp-container">
                    <div class="lp-section-head" v-motion-slide-visible-bottom>
                        <h2 class="lp-section-title">Frequently Asked Questions</h2>
                        <p class="lp-section-sub">Got questions? We've got answers.</p>
                    </div>

                    <div class="lp-faq-list">
                        <div
                            v-for="(faq, i) in faqs"
                            :key="i"
                            class="lp-faq-item"
                            :class="{ 'lp-faq-open': faq.open }"
                            @click="toggleFaq(i)"
                            v-motion-slide-visible-bottom
                        >
                            <div class="lp-faq-question">
                                <h3>{{ faq.question }}</h3>
                                <v-icon icon="mdi-chevron-down" class="lp-faq-icon"></v-icon>
                            </div>
                            <div class="lp-faq-answer-wrapper">
                                <div class="lp-faq-answer">
                                    <div class="lp-faq-answer-inner">
                                        <p>{{ faq.answer }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══════════════════ CTA ═══════════════════ -->
            <section class="lp-cta">
                <div class="lp-container lp-cta-inner" v-motion-slide-visible-bottom>
                    <h2 class="lp-cta-title">Ready to Transform Your Store?</h2>
                    <p class="lp-cta-sub">Join hundreds of grocery stores already using GroceryPro to streamline their operations.</p>
                    <Link v-if="canRegister" :href="route('register')">
                        <v-btn color="#D38865" variant="flat" class="lp-btn-cta" style="font-size:1rem; padding: 0 2.5rem; height:52px;">
                            Start Your Journey
                            <v-icon icon="mdi-arrow-right" size="small" style="margin-left:6px;"></v-icon>
                        </v-btn>
                    </Link>
                </div>
            </section>

            <!-- ═══════════════════ FOOTER ═══════════════════ -->
            <footer class="lp-footer">
                <div class="lp-container lp-footer-inner">
                    <div class="lp-footer-brand">
                        <v-icon icon="mdi-package-variant-closed" color="#D38865" size="20"></v-icon>
                        <span class="lp-footer-name">GroceryPro</span>
                    </div>
                    <p class="lp-footer-copy">&copy; 2026 GroceryPro. All rights reserved.</p>
                </div>
            </footer>

        </div>
    </v-app>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');

/* ── Root ─────────────────────────────────────────── */
.lp-root {
    min-height: 100vh;
    background: #FCFBF8;
    color: #1a1a1a;
    font-family: 'Plus Jakarta Sans', sans-serif;
    overflow-x: hidden;
}

/* ── Container ────────────────────────────────────── */
.lp-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

/* ── Header ───────────────────────────────────────── */
.lp-header {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1.5rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.lp-logo {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.lp-logo-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: #2E6B3B;
    display: flex;
    align-items: center;
    justify-content: center;
}

.lp-logo-text {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1B5E20;
    letter-spacing: -0.02em;
}

.lp-btn-signin {
    text-transform: none !important;
    letter-spacing: 0 !important;
    font-weight: 600;
    padding: 0 1.5rem;
    color: white !important;
}

/* ── Hero ─────────────────────────────────────────── */
.lp-hero {
    position: relative;
    max-width: 1200px;
    margin: 0 auto;
    padding: 4rem 2rem 6rem;
}

.lp-hero-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.5;
    pointer-events: none;
}
.lp-blob-1 {
    width: 300px; height: 300px;
    background: #E8F5E9;
    top: 0; left: -80px;
}
.lp-blob-2 {
    width: 250px; height: 250px;
    background: #FFF3E0;
    top: 40px; right: 60px;
}

.lp-hero-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
    position: relative;
    z-index: 1;
}
@media (max-width: 768px) {
    .lp-hero-grid { grid-template-columns: 1fr; gap: 3rem; }
    .lp-grid-3 { grid-template-columns: 1fr; }
    .lp-stats-grid { grid-template-columns: 1fr; }
    .lp-stat-divider { border-left: none; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 2rem; margin-top: 2rem; }
}

.lp-badge {
    display: inline-block;
    background: #E8F5E9;
    color: #2E6B3B;
    border: 1px solid #C8E6C9;
    border-radius: 999px;
    padding: 0.4rem 1rem;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.lp-hero-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 700;
    color: #1B5E20;
    line-height: 1.15;
    margin: 0 0 1.25rem;
}

.lp-accent {
    color: #D38865;
}

.lp-hero-sub {
    font-size: 1.05rem;
    color: #555;
    line-height: 1.7;
    max-width: 480px;
    margin: 0;
}

.lp-btn-cta {
    text-transform: none !important;
    letter-spacing: 0 !important;
    font-weight: 700;
    padding: 0 2rem;
    height: 48px;
    color: white !important;
    border-radius: 10px !important;
}

/* ── Hero Mockup ──────────────────────────────────── */
.lp-hero-graphic {
    position: relative;
}

.lp-mockup-bg-rotate {
    position: absolute;
    inset: -16px;
    background: #E8F5E9;
    border-radius: 24px;
    transform: rotate(3deg) scale(1.05);
    z-index: 0;
    transition: transform 0.5s ease;
}
.lp-hero-graphic:hover .lp-mockup-bg-rotate {
    transform: rotate(6deg) scale(1.05);
}

.lp-blob-circle {
    position: absolute;
    border-radius: 50%;
    z-index: 0;
    pointer-events: none;
}
.lp-bc-right {
    width: 80px; height: 80px;
    background: #EED8C9;
    top: -24px; right: -24px;
    opacity: 0.9;
}
.lp-bc-left {
    width: 100px; height: 100px;
    background: #E8F5E9;
    bottom: -30px; left: -30px;
    opacity: 0.8;
}

.lp-mockup-card {
    position: relative;
    background: #427A45;
    border-radius: 24px;
    padding: 1rem;
    box-shadow: 0 25px 60px rgba(0,0,0,0.2);
    z-index: 1;
    border: 1px solid #2d5e30;
}

.lp-mockup-inner {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
}

.lp-mockup-header-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.25rem;
}

.lp-mockup-label {
    font-size: 0.8rem;
    color: #888;
    font-weight: 500;
    margin: 0 0 0.25rem;
}

.lp-mockup-revenue {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1B5E20;
    margin: 0 0 0.35rem;
}

.lp-mockup-growth {
    font-size: 0.75rem;
    color: #4CAF50;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 2px;
}
.lp-mockup-growth-sub {
    color: #aaa;
    font-weight: 400;
    margin-left: 4px;
}

.lp-mockup-icon-box {
    background: #FFF5F0;
    padding: 8px;
    border-radius: 10px;
}

.lp-mockup-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.85rem 1rem;
    border-radius: 12px;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}
.lp-mockup-row-neutral {
    background: #F5F7F5;
    border: 1px solid #f0f0f0;
    color: #444;
}
.lp-mockup-row-warn {
    background: #FFF5F2;
    border: 1px solid #ffe4da;
    color: #444;
}
.lp-mockup-val {
    font-weight: 700;
    color: #333;
}

/* ── Fade-up animation ────────────────────────────── */
@keyframes lp-fade-up-in {
    from { opacity: 0; transform: translateY(32px); }
    to   { opacity: 1; transform: translateY(0); }
}
.lp-fade-up {
    animation: lp-fade-up-in 0.6s ease both;
}

/* ── Features ─────────────────────────────────────── */
.lp-features {
    background: #FCFBF8;
    padding: 6rem 0;
}

.lp-section-head {
    text-align: center;
    max-width: 640px;
    margin: 0 auto 3.5rem;
}

.lp-section-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.8rem, 3vw, 2.5rem);
    font-weight: 700;
    color: #1B5E20;
    margin: 0 0 0.75rem;
}

.lp-section-sub {
    font-size: 1rem;
    color: #888;
    margin: 0;
}

.lp-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}

.lp-feature-card {
    background: white;
    border-radius: 18px;
    padding: 2rem;
    border: 1px solid #f0f0f0;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    transition: box-shadow 0.3s ease, transform 0.3s ease;
    cursor: default;
}
.lp-feature-card:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.10);
    transform: translateY(-4px);
}
.lp-feature-card:hover .lp-feature-icon {
    background: #2E6B3B;
    color: white;
}

.lp-feature-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #E8F5E9;
    color: #2E6B3B;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.25rem;
    transition: background 0.3s, color 0.3s;
}

.lp-feature-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #111;
    margin: 0 0 0.5rem;
}

.lp-feature-desc {
    font-size: 0.875rem;
    color: #666;
    line-height: 1.65;
    margin: 0;
}

/* ── Stats ────────────────────────────────────────── */
.lp-stats {
    position: relative;
    background: linear-gradient(135deg, #2E6B3B, #1B5E20);
    padding: 5rem 0;
    overflow: hidden;
}

.lp-stats-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.08);
    pointer-events: none;
}

.lp-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    text-align: center;
    position: relative;
    z-index: 1;
}

.lp-stat-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 1rem 0;
}

.lp-stat-divider {
    border-left: 1px solid rgba(255,255,255,0.2);
}

.lp-stat-num {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.5rem, 5vw, 3.75rem);
    font-weight: 700;
    color: white;
    line-height: 1;
}

.lp-stat-label {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.7);
    font-weight: 500;
    letter-spacing: 0.05em;
}

/* ── Testimonials ─────────────────────────────────── */
.lp-testimonials {
    padding: 6rem 0;
    background: #FFF;
}
.lp-testimonial-card {
    background: #FCFBF8;
    border-radius: 16px;
    padding: 2rem;
    border: 1px solid #f0f0f0;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.lp-testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}
.lp-testimonial-rating {
    margin-bottom: 1rem;
}
.lp-testimonial-text {
    font-size: 1rem;
    color: #444;
    font-style: italic;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    flex-grow: 1;
}
.lp-testimonial-author {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.lp-author-avatar {
    width: 40px; height: 40px;
    background: #E8F5E9;
    color: #2E6B3B;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
}
.lp-author-name {
    font-weight: 700;
    color: #111;
    margin: 0 0 0.1rem;
    font-size: 0.95rem;
}
.lp-author-store {
    font-size: 0.8rem;
    color: #777;
}

/* ── FAQ ──────────────────────────────────────────── */
.lp-faq {
    padding: 6rem 0;
    background: #FCFBF8;
}
.lp-faq-list {
    max-width: 800px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.lp-faq-item {
    background: white;
    border-radius: 12px;
    border: 1px solid #eee;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
}
.lp-faq-item:hover {
    border-color: #C8E6C9;
}
.lp-faq-open {
    border-color: #2E6B3B;
    box-shadow: 0 4px 15px rgba(46,107,59,0.08);
}
.lp-faq-question {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
}
.lp-faq-question h3 {
    font-size: 1.05rem;
    font-weight: 600;
    color: #111;
    margin: 0;
}
.lp-faq-icon {
    transition: transform 0.3s ease;
}
.lp-faq-open .lp-faq-icon {
    transform: rotate(180deg);
}
.lp-faq-answer-wrapper {
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows 0.3s ease-in-out;
}
.lp-faq-open .lp-faq-answer-wrapper {
    grid-template-rows: 1fr;
}
.lp-faq-answer {
    overflow: hidden;
}
.lp-faq-answer-inner {
    padding: 0 1.5rem 1.25rem;
    font-size: 0.95rem;
    color: #555;
    line-height: 1.6;
}
.lp-faq-answer-inner p { margin: 0; }

/* ── CTA ──────────────────────────────────────────── */
.lp-cta {
    padding: 6rem 0;
    background: #FCFBF8;
}

.lp-cta-inner {
    text-align: center;
}

.lp-cta-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.8rem, 3.5vw, 2.8rem);
    font-weight: 700;
    color: #1B5E20;
    margin: 0 0 1rem;
}

.lp-cta-sub {
    font-size: 1rem;
    color: #777;
    margin: 0 0 2.5rem;
    max-width: 520px;
    margin-left: auto;
    margin-right: auto;
}

/* ── Footer ───────────────────────────────────────── */
.lp-footer {
    background: #0F3511;
    padding: 3rem 0;
    border-top: 1px solid #1a4a1c;
}

.lp-footer-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.lp-footer-brand {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.lp-footer-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: white;
}

.lp-footer-copy {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.4);
    margin: 0;
}
</style>
