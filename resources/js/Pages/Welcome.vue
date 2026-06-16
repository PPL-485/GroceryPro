<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: { type: Boolean },
    laravelVersion: { type: String, required: true },
    phpVersion: { type: String, required: true },
});

const scrollY = ref(0);
const reduceMotion = ref(false);
let frame = 0;

const features = [
    {
        icon: 'mdi-chart-line',
        title: 'Dashboard live',
        description: 'Pantau omzet, transaksi, stok menipis, dan produk terlaris dari satu layar yang mudah dibaca.',
    },
    {
        icon: 'mdi-qrcode-scan',
        title: 'Kasir cepat',
        description: 'Transaksi harian lebih lancar dengan alur POS ringkas, pembayaran QRIS, dan struk yang rapi.',
    },
    {
        icon: 'mdi-package-variant',
        title: 'Stok terkendali',
        description: 'Catat barang masuk-keluar, atur satuan produk, dan dapatkan peringatan sebelum rak kosong.',
    },
    {
        icon: 'mdi-file-chart-outline',
        title: 'Laporan siap pakai',
        description: 'Ringkasan penjualan membantu pemilik toko mengambil keputusan tanpa membuka spreadsheet manual.',
    },
];

const workflow = [
    { stat: '01', title: 'Catat produk', text: 'Masukkan kategori, harga, satuan, dan stok awal.' },
    { stat: '02', title: 'Layani pembeli', text: 'Kasir memilih produk, memproses pembayaran, lalu stok otomatis bergerak.' },
    { stat: '03', title: 'Baca performa', text: 'Pemilik toko melihat tren omzet, stok kritis, dan riwayat transaksi.' },
];

const testimonials = [
    {
        name: 'Budi Santoso',
        store: 'Toko Budi Jaya',
        text: 'Peringatan stok menipis bikin saya tidak lagi telat restock barang harian.',
        rating: 5,
    },
    {
        name: 'Siti Aminah',
        store: 'Warung Barokah',
        text: 'Alur kasirnya cepat. Pelanggan tidak perlu menunggu lama saat jam ramai.',
        rating: 5,
    },
    {
        name: 'Ahmad Faisal',
        store: 'Faisal Minimarket',
        text: 'Laporan penjualan membantu saya tahu produk mana yang benar-benar bergerak.',
        rating: 4,
    },
];

const faqs = ref([
    {
        question: 'Apakah GroceryPro cocok untuk warung kecil?',
        answer: 'Cocok. GroceryPro dirancang untuk toko kelontong, warung, dan minimarket kecil-menengah yang butuh pencatatan stok dan transaksi tanpa sistem yang rumit.',
        open: false,
    },
    {
        question: 'Perlu perangkat khusus untuk kasir?',
        answer: 'Tidak. Aplikasi dapat digunakan dari browser modern di laptop, tablet, atau ponsel. Jika toko punya barcode scanner, alurnya juga bisa dibuat lebih cepat.',
        open: false,
    },
    {
        question: 'Bagaimana data toko diamankan?',
        answer: 'Akses dapat dibatasi berdasarkan peran pengguna seperti owner dan cashier, sehingga data penting hanya terlihat oleh orang yang berwenang.',
        open: false,
    },
    {
        question: 'Apa yang terjadi saat stok menipis?',
        answer: 'Produk dengan stok rendah akan mudah terlihat oleh pemilik toko, sehingga restock bisa dilakukan sebelum barang habis di rak.',
        open: false,
    },
]);

const parallax = (speed = 0) => {
    if (reduceMotion.value) {
        return {};
    }

    return {
        transform: `translate3d(0, ${Math.round(scrollY.value * speed)}px, 0)`,
    };
};

const updateScroll = () => {
    scrollY.value = window.scrollY || window.pageYOffset || 0;
    frame = 0;
};

const onScroll = () => {
    if (!frame) {
        frame = window.requestAnimationFrame(updateScroll);
    }
};

const scrollToSection = (id) => {
    const target = document.getElementById(id);

    if (!target) {
        return;
    }

    const headerHeight = document.querySelector('.lp-header')?.getBoundingClientRect().height || 0;
    const top = target.getBoundingClientRect().top + window.scrollY - headerHeight - 28;

    window.scrollTo({
        top,
        behavior: reduceMotion.value ? 'auto' : 'smooth',
    });

    window.history.replaceState(null, '', `#${id}`);
};

const toggleFaq = (index) => {
    faqs.value[index].open = !faqs.value[index].open;
};

onMounted(() => {
    reduceMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    updateScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);

    if (frame) {
        window.cancelAnimationFrame(frame);
    }
});
</script>

<template>
    <Head title="Welcome to GroceryPro" />

    <v-app>
        <div class="lp-root">
            <header class="lp-header">
                <Link class="lp-logo" href="/">
                    <span class="lp-logo-icon">
                        <v-icon icon="mdi-basket-outline" size="22" color="white"></v-icon>
                    </span>
                    <span class="lp-logo-text">GroceryPro</span>
                </Link>

                <nav class="lp-nav" aria-label="Landing navigation">
                    <a href="#features" @click.prevent="scrollToSection('features')">Fitur</a>
                    <a href="#workflow" @click.prevent="scrollToSection('workflow')">Alur</a>
                    <a href="#faq" @click.prevent="scrollToSection('faq')">FAQ</a>
                </nav>

                <div v-if="canLogin" class="lp-header-actions">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')">
                        <v-btn color="#226D4A" variant="flat" class="lp-btn-signin">Dashboard</v-btn>
                    </Link>
                    <Link v-else :href="route('login')">
                        <v-btn color="#226D4A" variant="flat" class="lp-btn-signin">Sign In</v-btn>
                    </Link>
                </div>
            </header>

            <main>
                <section class="lp-hero">
                    <div class="lp-hero-sky" :style="parallax(-0.08)"></div>
                    <div class="lp-hero-gridline" :style="parallax(-0.05)"></div>
                    <div class="lp-shelf lp-shelf-back" :style="parallax(0.04)">
                        <span v-for="n in 18" :key="'back-' + n"></span>
                    </div>
                    <div class="lp-shelf lp-shelf-front" :style="parallax(0.09)">
                        <span v-for="n in 14" :key="'front-' + n"></span>
                    </div>

                    <div class="lp-hero-inner">
                        <div class="lp-hero-copy" v-motion-slide-visible-left>
                            <span class="lp-badge">
                                <v-icon icon="mdi-storefront-outline" size="16"></v-icon>
                                Built for grocery operations
                            </span>
                            <h1>GroceryPro</h1>
                            <p class="lp-hero-lead">
                                Kelola kasir, stok, dan laporan toko dalam tampilan yang cepat dibaca, terasa modern, dan siap dipakai setiap hari.
                            </p>

                            <div class="lp-hero-actions">
                                <Link v-if="$page.props.auth.user" :href="route('dashboard')">
                                    <v-btn color="#E76F51" variant="flat" class="lp-btn-cta">
                                        Open Dashboard
                                        <v-icon icon="mdi-arrow-right" size="small"></v-icon>
                                    </v-btn>
                                </Link>
                                <Link v-else-if="canLogin" :href="route('login')">
                                    <v-btn color="#E76F51" variant="flat" class="lp-btn-cta">
                                        Sign In
                                        <v-icon icon="mdi-arrow-right" size="small"></v-icon>
                                    </v-btn>
                                </Link>
                                <a class="lp-link-btn" href="#features" @click.prevent="scrollToSection('features')">Lihat fitur</a>
                            </div>

                            <div class="lp-hero-metrics" aria-label="GroceryPro highlights">
                                <div>
                                    <strong>3x</strong>
                                    <span>alur kerja lebih ringkas</span>
                                </div>
                                <div>
                                    <strong>24/7</strong>
                                    <span>monitor stok toko</span>
                                </div>
                                <div>
                                    <strong>QRIS</strong>
                                    <span>pembayaran siap pakai</span>
                                </div>
                            </div>
                        </div>

                        <div class="lp-hero-stage" v-motion-slide-visible-right>
                            <div class="lp-floating-note lp-note-a" :style="parallax(-0.03)">
                                <v-icon icon="mdi-alert-circle-outline" size="18"></v-icon>
                                <span>8 item low stock</span>
                            </div>
                            <div class="lp-floating-note lp-note-b" :style="parallax(0.05)">
                                <v-icon icon="mdi-trending-up" size="18"></v-icon>
                                <span>+12.5% revenue</span>
                            </div>

                            <div class="lp-dashboard-shell" :style="parallax(0.02)">
                                <div class="lp-window-bar">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>

                                <div class="lp-dashboard-top">
                                    <div>
                                        <p>Today's Revenue</p>
                                        <h2>Rp 2.450.000</h2>
                                    </div>
                                    <div class="lp-dashboard-icon">
                                        <v-icon icon="mdi-chart-areaspline" color="#E76F51"></v-icon>
                                    </div>
                                </div>

                                <div class="lp-chart" aria-hidden="true">
                                    <span style="height: 42%"></span>
                                    <span style="height: 58%"></span>
                                    <span style="height: 46%"></span>
                                    <span style="height: 78%"></span>
                                    <span style="height: 62%"></span>
                                    <span style="height: 88%"></span>
                                </div>

                                <div class="lp-pos-strip">
                                    <div>
                                        <span>Transactions</span>
                                        <strong>158</strong>
                                    </div>
                                    <div>
                                        <span>Best seller</span>
                                        <strong>Beras 5kg</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="features" class="lp-features">
                    <div class="lp-container">
                        <div class="lp-section-head" v-motion-slide-visible-bottom>
                            <span class="lp-kicker">Control center</span>
                            <h2>Semua operasi toko dalam satu ritme.</h2>
                            <p>Terinspirasi dari landing page modern yang punya kedalaman visual, setiap bagian dibuat seperti lapisan operasi toko yang bergerak saat pengguna scroll.</p>
                        </div>

                        <div class="lp-feature-grid">
                            <article
                                v-for="(feature, i) in features"
                                :key="feature.title"
                                class="lp-feature-card"
                                v-motion-slide-visible-bottom
                                :delay="i * 120"
                            >
                                <div class="lp-feature-icon">
                                    <v-icon :icon="feature.icon" size="24"></v-icon>
                                </div>
                                <h3>{{ feature.title }}</h3>
                                <p>{{ feature.description }}</p>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="lp-operations">
                    <div class="lp-container lp-operations-grid">
                        <div class="lp-operations-copy" v-motion-slide-visible-left>
                            <span class="lp-kicker">Parallax view</span>
                            <h2>Scroll seperti melihat toko dari depan sampai belakang.</h2>
                            <p>
                                Layer rak, panel kasir, dan kartu laporan bergerak dengan kecepatan berbeda supaya landing page terasa hidup tanpa mengorbankan keterbacaan.
                            </p>
                        </div>

                        <div class="lp-stack-scene" aria-hidden="true">
                            <div class="lp-stack-card lp-stack-1" :style="parallax(-0.04)">
                                <span>Inventory</span>
                                <strong>1.284 produk</strong>
                            </div>
                            <div class="lp-stack-card lp-stack-2" :style="parallax(0.03)">
                                <span>Cashier</span>
                                <strong>Rp 860.000</strong>
                            </div>
                            <div class="lp-stack-card lp-stack-3" :style="parallax(0.07)">
                                <span>Reports</span>
                                <strong>32 transaksi</strong>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="workflow" class="lp-workflow">
                    <div class="lp-container">
                        <div class="lp-section-head lp-section-head-left" v-motion-slide-visible-bottom>
                            <span class="lp-kicker">Daily flow</span>
                            <h2>Dibuat untuk ritme toko yang sibuk.</h2>
                        </div>

                        <div class="lp-workflow-grid">
                            <article
                                v-for="step in workflow"
                                :key="step.stat"
                                class="lp-workflow-card"
                                v-motion-slide-visible-bottom
                            >
                                <span>{{ step.stat }}</span>
                                <h3>{{ step.title }}</h3>
                                <p>{{ step.text }}</p>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="lp-testimonials">
                    <div class="lp-container">
                        <div class="lp-section-head" v-motion-slide-visible-bottom>
                            <span class="lp-kicker">Store stories</span>
                            <h2>Dipakai untuk keputusan harian, bukan hanya pencatatan.</h2>
                        </div>

                        <div class="lp-testimonial-grid">
                            <article
                                v-for="(testimonial, i) in testimonials"
                                :key="testimonial.name"
                                class="lp-testimonial-card"
                                v-motion-slide-visible-bottom
                                :delay="i * 120"
                            >
                                <div class="lp-testimonial-rating">
                                    <v-icon v-for="n in testimonial.rating" :key="n" icon="mdi-star" color="#F4A261" size="small"></v-icon>
                                    <v-icon v-for="n in 5 - testimonial.rating" :key="'empty-' + n" icon="mdi-star-outline" color="#F4A261" size="small"></v-icon>
                                </div>
                                <p>"{{ testimonial.text }}"</p>
                                <div class="lp-testimonial-author">
                                    <span>{{ testimonial.name.charAt(0) }}</span>
                                    <div>
                                        <strong>{{ testimonial.name }}</strong>
                                        <small>{{ testimonial.store }}</small>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>
                </section>

                <section id="faq" class="lp-faq">
                    <div class="lp-container">
                        <div class="lp-section-head" v-motion-slide-visible-bottom>
                            <span class="lp-kicker">FAQ</span>
                            <h2>Pertanyaan yang sering muncul.</h2>
                        </div>

                        <div class="lp-faq-list">
                            <article
                                v-for="(faq, i) in faqs"
                                :key="faq.question"
                                class="lp-faq-item"
                                :class="{ 'lp-faq-open': faq.open }"
                                v-motion-slide-visible-bottom
                            >
                                <button type="button" class="lp-faq-question" @click="toggleFaq(i)">
                                    <span>{{ faq.question }}</span>
                                    <v-icon icon="mdi-chevron-down" class="lp-faq-icon"></v-icon>
                                </button>
                                <div class="lp-faq-answer-wrapper">
                                    <div class="lp-faq-answer">
                                        <p>{{ faq.answer }}</p>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="lp-cta">
                    <div class="lp-container lp-cta-inner" v-motion-slide-visible-bottom>
                        <span class="lp-kicker">Ready</span>
                        <h2>Buat toko terasa lebih ringan dikelola.</h2>
                        <p>Masuk ke GroceryPro dan mulai pantau transaksi, stok, serta laporan dari satu tempat.</p>
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')">
                            <v-btn color="#E76F51" variant="flat" class="lp-btn-cta">
                                Open Dashboard
                                <v-icon icon="mdi-arrow-right" size="small"></v-icon>
                            </v-btn>
                        </Link>
                        <Link v-else-if="canLogin" :href="route('login')">
                            <v-btn color="#E76F51" variant="flat" class="lp-btn-cta">
                                Sign In
                                <v-icon icon="mdi-arrow-right" size="small"></v-icon>
                            </v-btn>
                        </Link>
                    </div>
                </section>
            </main>

            <footer class="lp-footer">
                <div class="lp-container lp-footer-inner">
                    <div class="lp-footer-brand">
                        <v-icon icon="mdi-basket-outline" color="#E76F51" size="20"></v-icon>
                        <span>GroceryPro</span>
                    </div>
                    <p>&copy; 2026 GroceryPro. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </v-app>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

.lp-root {
    min-height: 100vh;
    background: #f8f3ea;
    color: #153027;
    font-family: 'Plus Jakarta Sans', sans-serif;
    overflow-x: hidden;
}

.lp-container {
    width: min(1120px, calc(100% - 32px));
    margin: 0 auto;
}

.lp-header {
    position: fixed;
    top: 16px;
    left: 50%;
    z-index: 30;
    width: min(1120px, calc(100% - 32px));
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding: 10px 12px;
    border: 1px solid rgba(34, 109, 74, 0.18);
    border-radius: 8px;
    background: rgba(255, 252, 246, 0.86);
    box-shadow: 0 16px 40px rgba(21, 48, 39, 0.08);
    backdrop-filter: blur(18px);
}

.lp-logo {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: inherit;
    text-decoration: none;
}

.lp-logo-icon {
    display: inline-flex;
    width: 38px;
    height: 38px;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: #226d4a;
}

.lp-logo-text {
    font-size: 1rem;
    font-weight: 800;
}

.lp-nav {
    display: flex;
    align-items: center;
    gap: 22px;
}

.lp-nav a,
.lp-link-btn {
    color: #3c534a;
    font-size: 0.88rem;
    font-weight: 700;
    text-decoration: none;
}

.lp-link-btn {
    display: inline-flex;
    min-height: 46px;
    align-items: center;
    padding: 0 18px;
    border-radius: 999px;
}

.lp-nav a:hover,
.lp-link-btn:hover {
    color: #e76f51;
}

.lp-btn-signin,
.lp-btn-cta {
    height: 46px !important;
    border-radius: 999px !important;
    color: white !important;
    font-weight: 800 !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
}

.lp-btn-signin {
    padding: 0 20px !important;
}

.lp-btn-cta {
    padding: 0 24px !important;
}

.lp-btn-cta :deep(.v-btn__content) {
    gap: 8px;
}

.lp-hero {
    position: relative;
    min-height: 760px;
    padding: 126px 0 70px;
    overflow: hidden;
    background:
        linear-gradient(180deg, rgba(255, 252, 246, 0.94), rgba(248, 243, 234, 0.72) 62%, #f8f3ea),
        linear-gradient(120deg, #e9f6ed 0%, #fff8ed 45%, #e8f0ff 100%);
}

.lp-hero-sky,
.lp-hero-gridline,
.lp-shelf {
    position: absolute;
    pointer-events: none;
}

.lp-hero-sky {
    inset: -70px -8% auto;
    height: 440px;
    background:
        radial-gradient(circle at 20% 30%, rgba(34, 109, 74, 0.14), transparent 30%),
        radial-gradient(circle at 74% 18%, rgba(231, 111, 81, 0.18), transparent 28%),
        linear-gradient(90deg, rgba(255, 255, 255, 0.42), rgba(255, 255, 255, 0));
}

.lp-hero-gridline {
    inset: 0;
    opacity: 0.45;
    background-image:
        linear-gradient(rgba(34, 109, 74, 0.09) 1px, transparent 1px),
        linear-gradient(90deg, rgba(34, 109, 74, 0.09) 1px, transparent 1px);
    background-size: 72px 72px;
    mask-image: linear-gradient(180deg, #000, transparent 76%);
}

.lp-shelf {
    left: 50%;
    display: flex;
    align-items: flex-end;
    gap: 12px;
    width: 1180px;
    transform: translateX(-50%);
    opacity: 0.72;
}

.lp-shelf span {
    display: block;
    width: 46px;
    border-radius: 8px 8px 0 0;
    background: linear-gradient(180deg, #f4a261, #e76f51);
    box-shadow: inset 0 10px 0 rgba(255, 255, 255, 0.24);
}

.lp-shelf span:nth-child(2n) {
    height: 74px;
    background: linear-gradient(180deg, #86c7a3, #226d4a);
}

.lp-shelf span:nth-child(3n) {
    height: 54px;
    background: linear-gradient(180deg, #90a8d6, #4169a8);
}

.lp-shelf-back {
    top: 160px;
}

.lp-shelf-back span {
    height: 42px;
    opacity: 0.32;
}

.lp-shelf-front {
    right: -250px;
    bottom: 18px;
    left: auto;
    width: 800px;
    justify-content: flex-end;
}

.lp-shelf-front span {
    height: 84px;
    opacity: 0.2;
}

.lp-hero-inner {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: 0.95fr 1.05fr;
    align-items: center;
    gap: 62px;
    width: min(1120px, calc(100% - 32px));
    margin: 0 auto;
}

.lp-hero-copy {
    padding-top: 20px;
}

.lp-badge,
.lp-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #226d4a;
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0;
    text-transform: uppercase;
}

.lp-badge {
    margin-bottom: 24px;
    padding: 9px 12px;
    border: 1px solid rgba(34, 109, 74, 0.2);
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.72);
}

.lp-hero h1,
.lp-section-head h2,
.lp-operations-copy h2,
.lp-cta h2 {
    margin: 0;
    color: #123629;
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    line-height: 1.04;
}

.lp-hero h1 {
    max-width: 540px;
    font-size: 4.55rem;
}

.lp-hero-lead {
    max-width: 560px;
    margin: 22px 0 0;
    color: #52655d;
    font-size: 1.08rem;
    line-height: 1.8;
}

.lp-hero-actions {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-top: 32px;
    flex-wrap: wrap;
}

.lp-hero-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-top: 44px;
}

.lp-hero-metrics div {
    min-height: 92px;
    padding: 16px;
    border: 1px solid rgba(34, 109, 74, 0.12);
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.62);
}

.lp-hero-metrics strong {
    display: block;
    color: #e76f51;
    font-size: 1.25rem;
    font-weight: 800;
}

.lp-hero-metrics span {
    display: block;
    margin-top: 6px;
    color: #607169;
    font-size: 0.78rem;
    line-height: 1.45;
}

.lp-hero-stage {
    position: relative;
    min-height: 560px;
}

.lp-dashboard-shell {
    position: absolute;
    top: 50px;
    right: 0;
    width: min(100%, 560px);
    min-height: 430px;
    padding: 18px;
    border: 1px solid rgba(18, 54, 41, 0.16);
    border-radius: 8px;
    background: #fffdf8;
    box-shadow: 0 34px 90px rgba(21, 48, 39, 0.2);
}

.lp-window-bar {
    display: flex;
    gap: 8px;
    padding-bottom: 18px;
    border-bottom: 1px solid #edf0e9;
}

.lp-window-bar span {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #e76f51;
}

.lp-window-bar span:nth-child(2) {
    background: #f4a261;
}

.lp-window-bar span:nth-child(3) {
    background: #226d4a;
}

.lp-dashboard-top,
.lp-pos-strip {
    display: flex;
    justify-content: space-between;
    gap: 18px;
}

.lp-dashboard-top {
    align-items: flex-start;
    padding: 24px 4px 10px;
}

.lp-dashboard-top p,
.lp-dashboard-top h2 {
    margin: 0;
}

.lp-dashboard-top p {
    color: #708078;
    font-size: 0.88rem;
    font-weight: 700;
}

.lp-dashboard-top h2 {
    margin-top: 8px;
    color: #123629;
    font-size: 2rem;
    font-weight: 800;
}

.lp-dashboard-icon {
    display: inline-flex;
    width: 48px;
    height: 48px;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: #fff2ec;
}

.lp-chart {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    height: 190px;
    margin: 18px 0;
    padding: 16px;
    border-radius: 8px;
    background:
        linear-gradient(180deg, rgba(34, 109, 74, 0.08), rgba(34, 109, 74, 0)),
        repeating-linear-gradient(0deg, transparent, transparent 38px, rgba(34, 109, 74, 0.1) 39px);
}

.lp-chart span {
    flex: 1;
    min-width: 24px;
    border-radius: 6px 6px 0 0;
    background: linear-gradient(180deg, #86c7a3, #226d4a);
    animation: lp-bar-pulse 3.4s ease-in-out infinite;
}

.lp-chart span:nth-child(2n) {
    background: linear-gradient(180deg, #f4a261, #e76f51);
    animation-delay: 0.4s;
}

.lp-pos-strip div {
    flex: 1;
    padding: 16px;
    border: 1px solid #edf0e9;
    border-radius: 8px;
    background: #fbfaf5;
}

.lp-pos-strip span {
    display: block;
    color: #77867e;
    font-size: 0.78rem;
    font-weight: 700;
}

.lp-pos-strip strong {
    display: block;
    margin-top: 8px;
    color: #123629;
    font-size: 1rem;
}

.lp-floating-note {
    position: absolute;
    z-index: 4;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 14px;
    border: 1px solid rgba(18, 54, 41, 0.12);
    border-radius: 8px;
    background: rgba(255, 253, 248, 0.92);
    box-shadow: 0 16px 50px rgba(21, 48, 39, 0.12);
    color: #123629;
    font-size: 0.86rem;
    font-weight: 800;
    backdrop-filter: blur(14px);
}

.lp-note-a {
    top: 24px;
    left: 8px;
}

.lp-note-b {
    top: 112px;
    right: -18px;
}

.lp-features,
.lp-workflow,
.lp-faq,
.lp-cta {
    padding: 92px 0;
}

.lp-section-head {
    max-width: 700px;
    margin: 0 auto 42px;
    text-align: center;
}

.lp-section-head-left {
    margin-left: 0;
    text-align: left;
}

.lp-section-head h2,
.lp-operations-copy h2,
.lp-cta h2 {
    margin-top: 12px;
    font-size: 2.75rem;
}

.lp-section-head p,
.lp-operations-copy p,
.lp-cta p {
    margin: 16px 0 0;
    color: #607169;
    font-size: 1rem;
    line-height: 1.75;
}

.lp-feature-grid,
.lp-testimonial-grid,
.lp-workflow-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

.lp-feature-card,
.lp-testimonial-card,
.lp-workflow-card,
.lp-stack-card,
.lp-faq-item {
    border: 1px solid rgba(18, 54, 41, 0.12);
    border-radius: 8px;
    background: rgba(255, 253, 248, 0.8);
    box-shadow: 0 10px 34px rgba(21, 48, 39, 0.06);
}

.lp-feature-card,
.lp-testimonial-card,
.lp-workflow-card {
    padding: 24px;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
}

.lp-feature-card:hover,
.lp-testimonial-card:hover,
.lp-workflow-card:hover {
    transform: translateY(-5px);
    border-color: rgba(231, 111, 81, 0.3);
    box-shadow: 0 18px 44px rgba(21, 48, 39, 0.1);
}

.lp-feature-icon {
    display: inline-flex;
    width: 48px;
    height: 48px;
    align-items: center;
    justify-content: center;
    margin-bottom: 22px;
    border-radius: 8px;
    background: #e7f3eb;
    color: #226d4a;
}

.lp-feature-card h3,
.lp-workflow-card h3 {
    margin: 0;
    color: #123629;
    font-size: 1.05rem;
    font-weight: 800;
}

.lp-feature-card p,
.lp-workflow-card p,
.lp-testimonial-card p {
    margin: 12px 0 0;
    color: #607169;
    font-size: 0.9rem;
    line-height: 1.68;
}

.lp-operations {
    position: relative;
    padding: 105px 0;
    overflow: hidden;
    background: #123629;
    color: #fffdf8;
}

.lp-operations::before {
    content: '';
    position: absolute;
    inset: 0;
    opacity: 0.18;
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.16) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.16) 1px, transparent 1px);
    background-size: 64px 64px;
}

.lp-operations-grid {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: 0.9fr 1.1fr;
    align-items: center;
    gap: 58px;
}

.lp-operations .lp-kicker {
    color: #91d8ad;
}

.lp-operations-copy h2,
.lp-operations-copy p {
    color: #fffdf8;
}

.lp-operations-copy p {
    color: rgba(255, 253, 248, 0.74);
}

.lp-stack-scene {
    position: relative;
    min-height: 390px;
}

.lp-stack-card {
    position: absolute;
    width: 58%;
    padding: 24px;
    background: rgba(255, 253, 248, 0.92);
}

.lp-stack-card span {
    display: block;
    color: #607169;
    font-size: 0.82rem;
    font-weight: 800;
}

.lp-stack-card strong {
    display: block;
    margin-top: 12px;
    color: #123629;
    font-size: 1.8rem;
}

.lp-stack-1 {
    top: 0;
    left: 8%;
}

.lp-stack-2 {
    top: 118px;
    right: 0;
    background: #fcebdc;
}

.lp-stack-3 {
    left: 0;
    bottom: 0;
    background: #e8f0ff;
}

.lp-workflow {
    background: #fffdf8;
}

.lp-workflow-grid {
    grid-template-columns: repeat(3, 1fr);
}

.lp-workflow-card span {
    color: #e76f51;
    font-size: 0.78rem;
    font-weight: 800;
}

.lp-testimonials {
    padding: 92px 0;
    background: #eef6f0;
}

.lp-testimonial-grid {
    grid-template-columns: repeat(3, 1fr);
}

.lp-testimonial-rating {
    display: flex;
    gap: 3px;
    margin-bottom: 16px;
}

.lp-testimonial-author {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 22px;
}

.lp-testimonial-author > span {
    display: inline-flex;
    width: 42px;
    height: 42px;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #226d4a;
    color: white;
    font-weight: 800;
}

.lp-testimonial-author strong,
.lp-testimonial-author small {
    display: block;
}

.lp-testimonial-author strong {
    color: #123629;
}

.lp-testimonial-author small {
    margin-top: 2px;
    color: #77867e;
}

.lp-faq-list {
    display: grid;
    gap: 12px;
    max-width: 820px;
    margin: 0 auto;
}

.lp-faq-question {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding: 20px 22px;
    border: 0;
    background: transparent;
    color: #123629;
    cursor: pointer;
    font: inherit;
    font-weight: 800;
    text-align: left;
}

.lp-faq-icon {
    flex: 0 0 auto;
    transition: transform 0.25s ease;
}

.lp-faq-open .lp-faq-icon {
    transform: rotate(180deg);
}

.lp-faq-answer-wrapper {
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows 0.25s ease;
}

.lp-faq-open .lp-faq-answer-wrapper {
    grid-template-rows: 1fr;
}

.lp-faq-answer {
    overflow: hidden;
}

.lp-faq-answer p {
    margin: 0;
    padding: 0 22px 20px;
    color: #607169;
    line-height: 1.72;
}

.lp-cta {
    position: relative;
    overflow: hidden;
    background:
        linear-gradient(135deg, rgba(34, 109, 74, 0.1), rgba(231, 111, 81, 0.12)),
        #fffdf8;
}

.lp-cta-inner {
    max-width: 760px;
    text-align: center;
}

.lp-cta .lp-btn-cta {
    margin-top: 30px;
}

.lp-footer {
    padding: 32px 0;
    background: #123629;
}

.lp-footer-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    color: rgba(255, 253, 248, 0.68);
    flex-wrap: wrap;
}

.lp-footer-brand {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #fffdf8;
    font-weight: 800;
}

.lp-footer p {
    margin: 0;
    font-size: 0.86rem;
}

@keyframes lp-bar-pulse {
    0%,
    100% {
        transform: scaleY(1);
    }
    50% {
        transform: scaleY(0.86);
    }
}

@media (max-width: 960px) {
    .lp-nav {
        display: none;
    }

    .lp-hero {
        min-height: auto;
    }

    .lp-hero-inner,
    .lp-operations-grid {
        grid-template-columns: 1fr;
    }

    .lp-hero h1 {
        font-size: 3.4rem;
    }

    .lp-hero-stage {
        min-height: 620px;
    }

    .lp-dashboard-shell {
        left: 0;
        width: 100%;
    }

    .lp-feature-grid,
    .lp-testimonial-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 700px) {
    .lp-header {
        top: 10px;
        width: calc(100% - 20px);
    }

    .lp-logo-text {
        display: none;
    }

    .lp-hero {
        padding-top: 104px;
    }

    .lp-hero h1 {
        font-size: 2.7rem;
    }

    .lp-hero-lead {
        font-size: 1rem;
    }

    .lp-hero-metrics,
    .lp-feature-grid,
    .lp-testimonial-grid,
    .lp-workflow-grid {
        grid-template-columns: 1fr;
    }

    .lp-hero-stage {
        min-height: 740px;
    }

    .lp-dashboard-top,
    .lp-pos-strip {
        flex-direction: column;
    }

    .lp-dashboard-top h2 {
        font-size: 1.55rem;
    }

    .lp-chart {
        gap: 8px;
    }

    .lp-floating-note {
        position: relative;
        top: auto;
        right: auto;
        bottom: auto;
        left: auto;
        margin: 0 0 10px;
    }

    .lp-section-head h2,
    .lp-operations-copy h2,
    .lp-cta h2 {
        font-size: 2.15rem;
    }

    .lp-features,
    .lp-workflow,
    .lp-faq,
    .lp-cta,
    .lp-testimonials {
        padding: 70px 0;
    }

    .lp-stack-scene {
        min-height: 500px;
    }

    .lp-stack-card {
        width: 86%;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        scroll-behavior: auto !important;
        transition-duration: 0.01ms !important;
    }
}
</style>
