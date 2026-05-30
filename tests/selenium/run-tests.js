import { Builder, By, until, Key } from 'selenium-webdriver';
import chrome from 'selenium-webdriver/chrome.js';

// ============================================================
//  KONFIGURASI — sesuaikan dengan environment Anda
// ============================================================
const BASE_URL = 'http://localhost:8000';
const VALID_EMAIL = 'admin@gmail.com';   // ← ganti email valid
const VALID_PASSWORD = 'password';               // ← ganti password valid
const WAIT_MS = 6000;   // timeout tunggu element (ms)
const PAUSE_MS = 800;    // jeda singkat antar langkah (ms)

// ============================================================
//  HELPER FUNCTIONS
// ============================================================

/** Buat driver Chrome baru */
async function buildDriver() {
    const options = new chrome.Options();
    // options.addArguments('--headless');  // ← hapus comment jika ingin headless
    const driver = await new Builder()
        .forBrowser('chrome')
        .setChromeOptions(options)
        .build();
    await driver.manage().window().maximize();
    return driver;
}

/** Tunggu elemen dengan ID tertentu lalu kembalikan elemennya */
async function waitId(driver, id, ms = WAIT_MS) {
    return driver.wait(until.elementLocated(By.id(id)), ms);
}

/** Tunggu elemen dengan CSS selector */
async function waitCss(driver, selector, ms = WAIT_MS) {
    return driver.wait(until.elementLocated(By.css(selector)), ms);
}

/** Jeda singkat (ms) */
const sleep = (ms) => new Promise(r => setTimeout(r, ms));

/** Cetak hasil test */
function pass(id, desc) { console.log(`  ✅ PASS [${id}] ${desc}`); }
function fail(id, desc, err) { console.log(`  ❌ FAIL [${id}] ${desc}\n       → ${err.message}`); }

/** Login helper — dipakai berulang di banyak test */
async function doLogin(driver, email = VALID_EMAIL, password = VALID_PASSWORD) {
    await driver.get(`${BASE_URL}/login`);
    const emailEl = await waitId(driver, 'input-email');
    await emailEl.clear();
    await emailEl.sendKeys(email);
    const passEl = await waitId(driver, 'input-password');
    await passEl.clear();
    await passEl.sendKeys(password);
    const btnEl = await waitId(driver, 'btn-login');
    await btnEl.click();
}

/** Logout via tombol sidebar */
async function doLogout(driver) {
    // Hover sidebar agar expand (rail mode pakai expand-on-hover)
    const navDrawer = await waitCss(driver, '.v-navigation-drawer');
    await driver.actions().move({ origin: navDrawer }).perform();
    await sleep(PAUSE_MS);
    // Pakai JS click agar tidak gagal karena elemen tertutup animasi
    const btnLogout = await waitId(driver, 'btn-logout');
    await driver.executeScript('arguments[0].click()', btnLogout);
    // Setelah logout, Laravel redirect ke '/' bukan '/login'
    await driver.wait(until.urlMatches(/^http:\/\/[^/]+(\/)?(\?.*)?$/), WAIT_MS);
    await sleep(PAUSE_MS);
}

/** Tunggu snackbar Vuetify dan kembalikan teksnya */
async function getSnackbarText(driver) {
    const snackbar = await driver.wait(
        until.elementLocated(By.css('.v-snackbar__content')),
        WAIT_MS
    );
    await driver.wait(until.elementIsVisible(snackbar), WAIT_MS);
    return snackbar.getText();
}

/** Navigasi ke halaman POS (pastikan sudah login dulu) */
async function goToPOS(driver) {
    await driver.get(`${BASE_URL}/transactions`);
    // Tunggu minimal 1 product card muncul
    await waitCss(driver, '.test-product-card', WAIT_MS);
}

/** Klik product card pertama yang stock_qty > 0 */
async function addFirstAvailableProduct(driver) {
    // Ambil semua product card dengan chip warna 'primary' (stock > 10) atau chip apapun
    const cards = await driver.findElements(By.css('.test-product-card'));
    for (const card of cards) {
        // Cek chip error (stock = 0 → chip berwarna error/merah)
        const errChip = await card.findElements(By.css('.v-chip--variant-flat.bg-error'));
        if (errChip.length === 0) {
            await driver.executeScript('arguments[0].scrollIntoView(true)', card);
            await driver.executeScript('arguments[0].click()', card);
            return true;
        }
    }
    return false;
}

/** Klik product card pertama yang stock = 0 (chip error merah) */
async function clickOutOfStockProduct(driver) {
    const cards = await driver.findElements(By.css('.test-product-card'));
    for (const card of cards) {
        const errChip = await card.findElements(By.css('.v-chip'));
        for (const chip of errChip) {
            const txt = await chip.getText();
            if (txt.trim() === '0 left') {
                await driver.executeScript('arguments[0].scrollIntoView(true)', card);
                await driver.executeScript('arguments[0].click()', card);
                return true;
            }
        }
    }
    return false;
}

// ============================================================
//  TEST CASES
// ============================================================

const results = { pass: 0, fail: 0 };

// ─────────────────────────────────────────────────────────────
// SCN-LOG-001 | TC-LOG-001
// Login berhasil dengan kredensial valid
// ─────────────────────────────────────────────────────────────
async function tcLog001(driver) {
    console.log('\n── TC-LOG-001: Login berhasil dengan kredensial valid ──');
    try {
        await doLogin(driver, VALID_EMAIL, VALID_PASSWORD);
        await driver.wait(until.urlMatches(/\/(dashboard|transactions|goods|report)/), WAIT_MS);
        const url = await driver.getCurrentUrl();
        if (!url.includes('/login')) {
            pass('TC-LOG-001', 'User berhasil masuk dan diarahkan ke halaman utama');
            results.pass++;
        } else {
            throw new Error('URL masih di /login setelah submit');
        }
    } catch (err) {
        fail('TC-LOG-001', 'Login berhasil dengan kredensial valid', err);
        results.fail++;
    }
}

// ─────────────────────────────────────────────────────────────
// SCN-LOG-001 | TC-LOG-002
// User tetap login setelah refresh halaman
// ─────────────────────────────────────────────────────────────
async function tcLog002(driver) {
    console.log('\n── TC-LOG-002: User tetap login setelah refresh ──');
    try {
        // Login dulu dan navigasi ke halaman yang dilindungi (dashboard)
        await doLogin(driver, VALID_EMAIL, VALID_PASSWORD);
        await driver.wait(until.urlMatches(/\/(dashboard|transactions|goods|report)/), WAIT_MS);
        // Simpan URL halaman yang dilindungi, lalu refresh
        const urlBefore = await driver.getCurrentUrl();
        await driver.navigate().refresh();
        await sleep(PAUSE_MS * 2);
        const urlAfter = await driver.getCurrentUrl();
        // Jika URL masih di halaman yang dilindungi (bukan dipaksa ke /login),
        // berarti sesi masih aktif setelah refresh → PASS
        if (!urlAfter.includes('/login')) {
            pass('TC-LOG-002', `Sesi tetap aktif setelah refresh (URL: ${urlAfter})`);
            results.pass++;
        } else {
            throw new Error(`User ter-redirect ke /login setelah refresh dari ${urlBefore}`);
        }
    } catch (err) {
        fail('TC-LOG-002', 'User tetap login setelah refresh halaman', err);
        results.fail++;
    }
}

// ─────────────────────────────────────────────────────────────
// SCN-LOG-002 | TC-LOG-003
// Login gagal dengan password/email tidak terdaftar
// ─────────────────────────────────────────────────────────────
async function tcLog003(driver) {
    console.log('\n── TC-LOG-003: Login gagal dengan credential tidak ada ──');
    try {
        await doLogin(driver, 'userpalsu@gmail.com', 'passwordpalsu123');
        await sleep(PAUSE_MS * 2);
        const url = await driver.getCurrentUrl();
        // Masih di /login = gagal login = test PASS
        if (url.includes('/login')) {
            // Cek pesan error muncul di DOM (Vuetify v-text-field error messages)
            const errEl = await driver.findElements(By.css('.v-messages__message'));
            if (errEl.length > 0) {
                pass('TC-LOG-003', `Muncul pesan error: "${await errEl[0].getText()}"`);
            } else {
                pass('TC-LOG-003', 'Login gagal, user tetap di halaman login');
            }
            results.pass++;
        } else {
            throw new Error('Login berhasil padahal credential tidak valid');
        }
    } catch (err) {
        fail('TC-LOG-003', 'Login gagal dengan credential tidak ada di sistem', err);
        results.fail++;
    }
}

// ─────────────────────────────────────────────────────────────
// SCN-LOG-002 | TC-LOG-004
// Login gagal dengan email kosong
// ─────────────────────────────────────────────────────────────
async function tcLog004(driver) {
    console.log('\n── TC-LOG-004: Login gagal dengan email kosong ──');
    try {
        await driver.get(`${BASE_URL}/login`);
        // Biarkan email kosong, isi password saja
        const passEl = await waitId(driver, 'input-password');
        await passEl.clear();
        await passEl.sendKeys(VALID_PASSWORD);
        const btnEl = await waitId(driver, 'btn-login');
        await btnEl.click();
        await sleep(PAUSE_MS * 2);
        const url = await driver.getCurrentUrl();
        if (url.includes('/login')) {
            pass('TC-LOG-004', 'Login ditolak ketika email kosong');
            results.pass++;
        } else {
            throw new Error('Login berhasil padahal email kosong');
        }
    } catch (err) {
        fail('TC-LOG-004', 'Login gagal dengan email kosong', err);
        results.fail++;
    }
}

// ─────────────────────────────────────────────────────────────
// SCN-LOG-002 | TC-LOG-005
// Login gagal dengan password kosong
// ─────────────────────────────────────────────────────────────
async function tcLog005(driver) {
    console.log('\n── TC-LOG-005: Login gagal dengan password kosong ──');
    try {
        await driver.get(`${BASE_URL}/login`);
        // Isi email, biarkan password kosong
        const emailEl = await waitId(driver, 'input-email');
        await emailEl.clear();
        await emailEl.sendKeys(VALID_EMAIL);
        const btnEl = await waitId(driver, 'btn-login');
        await btnEl.click();
        await sleep(PAUSE_MS * 2);
        const url = await driver.getCurrentUrl();
        if (url.includes('/login')) {
            pass('TC-LOG-005', 'Login ditolak ketika password kosong');
            results.pass++;
        } else {
            throw new Error('Login berhasil padahal password kosong');
        }
    } catch (err) {
        fail('TC-LOG-005', 'Login gagal dengan password kosong', err);
        results.fail++;
    }
}

// ─────────────────────────────────────────────────────────────
// SCN-LOG-003 | TC-LOG-006
// Logout dari sistem
// ─────────────────────────────────────────────────────────────
async function tcLog006(driver) {
    console.log('\n── TC-LOG-006: Logout dari sistem ──');
    try {
        // Pastikan sudah login
        const url = await driver.getCurrentUrl();
        if (url.includes('/login')) {
            await doLogin(driver, VALID_EMAIL, VALID_PASSWORD);
            await driver.wait(until.urlMatches(/\/(dashboard|transactions|goods)/), WAIT_MS);
        }
        await doLogout(driver);

        pass('TC-LOG-006', 'Logout berhasil, diarahkan ke halaman login');
        results.pass++;
    } catch (err) {
        fail('TC-LOG-006', 'Logout dari sistem', err);
        results.fail++;
    }
}

// ─────────────────────────────────────────────────────────────
// SCN-LOG-004 | TC-LOG-007
// Keamanan: akses dashboard tanpa login
// ─────────────────────────────────────────────────────────────
async function tcLog007(driver) {
    console.log('\n── TC-LOG-007: Keamanan akses halaman tanpa login ──');
    try {
        // Pastikan tidak ada sesi aktif — buka tab baru / langsung akses URL
        await driver.get(`${BASE_URL}/dashboard`);
        await driver.wait(until.urlContains('/login'), WAIT_MS);
        pass('TC-LOG-007', 'Akses dashboard tanpa login berhasil di-redirect ke /login');
        results.pass++;
    } catch (err) {
        fail('TC-LOG-007', 'Keamanan akses halaman tanpa login', err);
        results.fail++;
    }
}

// ─────────────────────────────────────────────────────────────
// SCN-POS-001 | TC-PCH-001
// Pembayaran Cash berhasil dengan nominal valid
// ─────────────────────────────────────────────────────────────
async function tcPch001(driver) {
    console.log('\n── TC-PCH-001: Pembayaran cash berhasil ──');
    try {
        await goToPOS(driver);
        await addFirstAvailableProduct(driver);
        await sleep(PAUSE_MS);

        // Buka cart drawer (untuk mobile/layar kecil)
        const btnCart = await waitId(driver, 'btn-toggle-cart');
        await driver.executeScript('arguments[0].click()', btnCart);
        await sleep(PAUSE_MS);

        // Metode cash sudah default — langsung isi Amount Paid
        const amountEl = await waitId(driver, 'input-amount-paid');
        await amountEl.click();
        await amountEl.sendKeys(Key.CONTROL, 'a');
        await amountEl.sendKeys('999999', Key.TAB); // nominal pasti lebih dari total
        await sleep(PAUSE_MS);

        const btnEl = await waitId(driver, 'btn-complete-transaction');
        await driver.executeScript('arguments[0].scrollIntoView(true)', btnEl);
        await driver.executeScript('arguments[0].click()', btnEl);

        const snackText = await getSnackbarText(driver);
        if (snackText.toLowerCase().includes('success') || snackText.toLowerCase().includes('berhasil')) {
            pass('TC-PCH-001', `Transaksi berhasil: "${snackText}"`);
            results.pass++;
        } else {
            throw new Error(`Snackbar tidak mengandung kata sukses: "${snackText}"`);
        }
    } catch (err) {
        fail('TC-PCH-001', 'Pembayaran cash berhasil dengan nominal valid', err);
        results.fail++;
    }
}

// ─────────────────────────────────────────────────────────────
// SCN-POS-001 | TC-PCH-002
// Pembayaran Cash gagal — nominal kurang dari total
// ─────────────────────────────────────────────────────────────
async function tcPch002(driver) {
    console.log('\n── TC-PCH-002: Pembayaran cash gagal (nominal kurang) ──');
    try {
        await goToPOS(driver);
        await addFirstAvailableProduct(driver);
        await sleep(PAUSE_MS);

        const btnCart = await waitId(driver, 'btn-toggle-cart');
        await driver.executeScript('arguments[0].click()', btnCart);
        await sleep(PAUSE_MS);

        const amountEl = await waitId(driver, 'input-amount-paid');
        await amountEl.click();
        await amountEl.sendKeys(Key.CONTROL, 'a');
        await amountEl.sendKeys('1', Key.TAB); // nominal sangat kecil, pasti kurang
        await sleep(PAUSE_MS);

        const btnEl = await waitId(driver, 'btn-complete-transaction');
        await driver.executeScript('arguments[0].scrollIntoView(true)', btnEl);
        await driver.executeScript('arguments[0].click()', btnEl);

        const snackText = await getSnackbarText(driver);
        if (snackText.toLowerCase().includes('less than total') || snackText.toLowerCase().includes('kurang')) {
            pass('TC-PCH-002', `Sistem menolak: "${snackText}"`);
            results.pass++;
        } else {
            throw new Error(`Pesan error tidak sesuai: "${snackText}"`);
        }
    } catch (err) {
        fail('TC-PCH-002', 'Pembayaran cash gagal karena nominal kurang dari total', err);
        results.fail++;
    }
}

// ─────────────────────────────────────────────────────────────
// SCN-POS-001 | TC-PCH-003
// Pembayaran Cash dengan nominal negatif
// ─────────────────────────────────────────────────────────────
async function tcPch003(driver) {
    console.log('\n── TC-PCH-003: Pembayaran cash dengan nominal negatif ──');
    try {
        await goToPOS(driver);
        await addFirstAvailableProduct(driver);
        await sleep(PAUSE_MS);

        const btnCart = await waitId(driver, 'btn-toggle-cart');
        await driver.executeScript('arguments[0].click()', btnCart);
        await sleep(PAUSE_MS);

        const amountEl = await waitId(driver, 'input-amount-paid');
        await amountEl.click();
        await amountEl.sendKeys(Key.CONTROL, 'a');
        await amountEl.sendKeys('-50000', Key.TAB);
        await sleep(PAUSE_MS);

        const btnEl = await waitId(driver, 'btn-complete-transaction');
        await driver.executeScript('arguments[0].scrollIntoView(true)', btnEl);
        await driver.executeScript('arguments[0].click()', btnEl);

        // Apabila sistem menolak, akan ada snackbar error atau user tetap di halaman POS
        const snackText = await getSnackbarText(driver);
        const isRejected =
            snackText.toLowerCase().includes('less than') ||
            snackText.toLowerCase().includes('error') ||
            snackText.toLowerCase().includes('invalid');
        if (isRejected) {
            pass('TC-PCH-003', `Sistem menolak nominal negatif: "${snackText}"`);
            results.pass++;
        } else {
            throw new Error(`Sistem tidak menolak nominal negatif: "${snackText}"`);
        }
    } catch (err) {
        fail('TC-PCH-003', 'Pembayaran cash nominal negatif', err);
        results.fail++;
    }
}

// ─────────────────────────────────────────────────────────────
// SCN-POS-002 | TC-PCH-004
// Pembayaran QRIS berhasil
// ─────────────────────────────────────────────────────────────
async function tcPch004(driver) {
    console.log('\n── TC-PCH-004: Pembayaran QRIS berhasil ──');
    try {
        await goToPOS(driver);
        await addFirstAvailableProduct(driver);
        await sleep(PAUSE_MS);

        const btnCart = await waitId(driver, 'btn-toggle-cart');
        await driver.executeScript('arguments[0].click()', btnCart);
        await sleep(PAUSE_MS);

        // Pilih QRIS pada dropdown payment method
        const selectEl = await waitCss(driver, '.v-select input');
        // Klik v-select dropdown
        const vselect = await waitCss(driver, '.v-select');
        await vselect.click();
        await sleep(PAUSE_MS);
        // Pilih item QRIS dari dropdown list
        const qrisItem = await waitCss(driver, '.v-list-item[data-value="qris"], .v-overlay--active .v-list-item');
        // Ambil semua item lalu klik yang text-nya 'QRIS'
        const items = await driver.findElements(By.css('.v-overlay--active .v-list-item'));
        for (const item of items) {
            const txt = await item.getText();
            if (txt.toUpperCase().includes('QRIS')) {
                await item.click();
                break;
            }
        }
        await sleep(PAUSE_MS);

        const btnEl = await waitId(driver, 'btn-complete-transaction');
        await driver.executeScript('arguments[0].scrollIntoView(true)', btnEl);
        await btnEl.click();

        const snackText = await getSnackbarText(driver);
        if (snackText.toLowerCase().includes('success') || snackText.toLowerCase().includes('berhasil')) {
            pass('TC-PCH-004', `Transaksi QRIS berhasil: "${snackText}"`);
            results.pass++;
        } else {
            throw new Error(`Snackbar tidak mengandung kata sukses: "${snackText}"`);
        }
    } catch (err) {
        fail('TC-PCH-004', 'Pembayaran QRIS berhasil', err);
        results.fail++;
    }
}

// ─────────────────────────────────────────────────────────────
// SCN-POS-003 | TC-PCH-005
// Menambah produk stok habis (stock = 0)
// ─────────────────────────────────────────────────────────────
async function tcPch005(driver) {
    console.log('\n── TC-PCH-005: Tambah produk dengan stok habis ──');
    try {
        await goToPOS(driver);
        const clicked = await clickOutOfStockProduct(driver);
        if (!clicked) {
            console.log('  ⚠️  SKIP [TC-PCH-005] Tidak ada produk dengan stock = 0 di database');
            return;
        }
        const snackText = await getSnackbarText(driver);
        if (snackText.toLowerCase().includes('out of stock')) {
            pass('TC-PCH-005', `Snackbar muncul: "${snackText}"`);
            results.pass++;
        } else {
            throw new Error(`Snackbar tidak berisi "out of stock": "${snackText}"`);
        }
    } catch (err) {
        fail('TC-PCH-005', 'Tambah produk stok habis', err);
        results.fail++;
    }
}

// ─────────────────────────────────────────────────────────────
// SCN-POS-003 | TC-PCH-006
// Menambah produk melebihi batas stok
// ─────────────────────────────────────────────────────────────
async function tcPch006(driver) {
    console.log('\n── TC-PCH-006: Tambah produk melebihi batas stok ──');
    try {
        await goToPOS(driver);
        // Ambil produk pertama yang memiliki chip warna merah (stock rendah)
        const cards = await driver.findElements(By.css('.test-product-card'));
        let targetCard = null;
        let targetStock = 0;

        for (const card of cards) {
            // Ambil teks chip stock (contoh: "3 left")
            const chips = await card.findElements(By.css('.v-chip'));
            for (const chip of chips) {
                const txt = await chip.getText();
                const match = txt.match(/^(\d+) left$/);
                if (match) {
                    const stock = parseInt(match[1]);
                    if (stock > 0 && stock <= 10) {
                        targetCard = card;
                        targetStock = stock;
                        break;
                    }
                }
            }
            if (targetCard) break;
        }

        if (!targetCard) {
            console.log('  ⚠️  SKIP [TC-PCH-006] Tidak ada produk dengan stok terbatas (1-10) di database');
            return;
        }

        // Klik produk sebanyak stok + 1 kali agar memicu snackbar
        for (let i = 0; i <= targetStock; i++) {
            await targetCard.click();
            await sleep(300);
        }

        const snackText = await getSnackbarText(driver);
        if (snackText.toLowerCase().includes('cannot add more')) {
            pass('TC-PCH-006', `Snackbar muncul: "${snackText}"`);
            results.pass++;
        } else {
            throw new Error(`Snackbar tidak berisi "Cannot add more": "${snackText}"`);
        }
    } catch (err) {
        fail('TC-PCH-006', 'Tambah produk melebihi batas stok', err);
        results.fail++;
    }
}

// ─────────────────────────────────────────────────────────────
// SCN-POS-004 | TC-PCH-007
// Filtering produk via search bar
// ─────────────────────────────────────────────────────────────
async function tcPch007(driver) {
    console.log('\n── TC-PCH-007: Filter produk dengan search ──');
    try {
        await goToPOS(driver);

        // Ambil nama produk pertama yang ada di halaman untuk kata kunci pencarian
        const firstProductName = await driver.findElement(
            By.css('.test-product-card .font-weight-bold')
        ).getText();
        const keyword = firstProductName.split(' ')[0]; // ambil kata pertama

        const searchEl = await waitId(driver, 'input-search-product');
        await searchEl.clear();
        await searchEl.sendKeys(keyword);
        await sleep(1500); // tunggu Vue computed reactive

        const visibleCards = await driver.findElements(By.css('.test-product-card'));
        let allMatch = true;
        for (const card of visibleCards) {
            const name = await card.findElement(By.css('.font-weight-bold')).getText();
            if (!name.toLowerCase().includes(keyword.toLowerCase())) {
                allMatch = false;
                break;
            }
        }

        if (allMatch && visibleCards.length > 0) {
            pass('TC-PCH-007', `Search "${keyword}" → ${visibleCards.length} produk sesuai ditampilkan`);
            results.pass++;
        } else {
            throw new Error(`Terdapat produk yang tidak sesuai keyword "${keyword}"`);
        }
    } catch (err) {
        fail('TC-PCH-007', 'Filter produk dengan search bar', err);
        results.fail++;
    }
}

// ─────────────────────────────────────────────────────────────
// SCN-POS-004 | TC-PCH-008
// Filtering produk via chip kategori
// ─────────────────────────────────────────────────────────────
async function tcPch008(driver) {
    console.log('\n── TC-PCH-008: Filter produk dengan chip kategori ──');
    try {
        await goToPOS(driver);

        // Ambil semua chip kategori (kecuali chip "All" pertama)
        const chips = await driver.findElements(By.css('.v-chip-group .v-chip'));
        if (chips.length <= 1) {
            console.log('  ⚠️  SKIP [TC-PCH-008] Tidak ada kategori tersedia selain "All"');
            return;
        }

        // Klik chip kategori kedua (index 1, bukan "All")
        const targetChip = chips[1];
        const categoryName = await targetChip.getText();
        await targetChip.click();
        await sleep(1500); // tunggu Vue reactive

        // Validasi: produk yang tampil harus memiliki badge kategori yang sesuai
        const visibleCards = await driver.findElements(By.css('.test-product-card'));
        if (visibleCards.length === 0) {
            // Bisa jadi kategori memang kosong — masih valid behavior
            pass('TC-PCH-008', `Chip "${categoryName}" diklik, tidak ada produk → sesuai (kategori kosong)`);
            results.pass++;
            return;
        }

        let allMatch = true;
        for (const card of visibleCards) {
            const catBadge = await card.findElements(By.css('.text-caption.text-grey-darken-1'));
            if (catBadge.length > 0) {
                const catText = await catBadge[0].getText();
                if (!catText.toLowerCase().includes(categoryName.toLowerCase())) {
                    allMatch = false;
                    break;
                }
            }
        }

        if (allMatch) {
            pass('TC-PCH-008', `Chip kategori "${categoryName}" → ${visibleCards.length} produk ditampilkan`);
            results.pass++;
        } else {
            throw new Error(`Ada produk yang tidak sesuai kategori "${categoryName}"`);
        }
    } catch (err) {
        fail('TC-PCH-008', 'Filter produk dengan chip kategori', err);
        results.fail++;
    }
}

// ============================================================
//  MAIN RUNNER
// ============================================================
(async function main() {
    console.log('╔══════════════════════════════════════════════════╗');
    console.log('║   🧪  GROCPRYPRO SELENIUM TEST SUITE             ║');
    console.log('╚══════════════════════════════════════════════════╝');
    console.log(`   BASE_URL : ${BASE_URL}`);
    console.log(`   EMAIL    : ${VALID_EMAIL}`);
    console.log('');

    const driver = await buildDriver();

    try {
        // ── LOGIN SCENARIOS ──────────────────────────────────
        console.log('\n╠══ SKENARIO: LOGIN ═════════════════════════════════╣');

        // TC-LOG-001: login valid → harus berhasil masuk
        // Setelah berhasil, LOGOUT agar test berikutnya bisa mulai dari halaman login
        await tcLog001(driver);
        await doLogout(driver);
        console.log('   🔓 Logout setelah TC-LOG-001 selesai');

        // TC-LOG-002: refresh halaman → harus tetap login
        // Login dulu, refresh, pastikan tetap login, lalu logout
        await tcLog002(driver);
        await doLogout(driver);
        console.log('   🔓 Logout setelah TC-LOG-002 selesai');

        // TC-LOG-003: credential tidak terdaftar → harus gagal (sudah di /login)
        await tcLog003(driver);

        // TC-LOG-004: email kosong → harus gagal (sudah di /login)
        await tcLog004(driver);

        // TC-LOG-005: password kosong → harus gagal (sudah di /login)
        await tcLog005(driver);

        // TC-LOG-006: logout → login dulu, baru uji logout
        await doLogin(driver, VALID_EMAIL, VALID_PASSWORD);
        await driver.wait(until.urlMatches(/\/(dashboard|transactions|goods)/), WAIT_MS);
        await tcLog006(driver); // tcLog006 sudah berakhir di /login

        // TC-LOG-007: akses dashboard tanpa login → harus di-redirect (sudah di /login)
        await tcLog007(driver);

        // ── POS SCENARIOS ────────────────────────────────────
        console.log('\n╠══ SKENARIO: POS ═══════════════════════════════════╣');

        // Login SEKALI di awal, semua POS test berjalan dalam satu sesi login
        console.log('\n   🔐 Login untuk semua skenario POS...');
        await doLogin(driver, VALID_EMAIL, VALID_PASSWORD);
        await driver.wait(until.urlMatches(/\/(dashboard|transactions|goods)/), WAIT_MS);
        console.log('   ✔  Login berhasil, memulai skenario POS\n');

        // TC-PCH-001: bayar cash nominal cukup → harus berhasil
        await tcPch001(driver);

        // TC-PCH-002: bayar cash nominal kurang → harus ditolak
        await tcPch002(driver);

        // TC-PCH-003: bayar cash nominal negatif → harus ditolak
        await tcPch003(driver);

        // TC-PCH-004: bayar QRIS → harus berhasil
        await tcPch004(driver);

        // TC-PCH-005: klik produk stok habis → harus muncul snackbar
        await tcPch005(driver);

        // TC-PCH-006: tambah produk melebihi stok → harus ada snackbar
        await tcPch006(driver);

        // TC-PCH-007: search filter produk → harus sesuai keyword
        await tcPch007(driver);

        // TC-PCH-008: chip kategori → harus filter produk
        await tcPch008(driver);

    } finally {
        // ── SUMMARY ─────────────────────────────────────────
        const total = results.pass + results.fail;
        console.log('\n╔══════════════════════════════════════════════════╗');
        console.log(`║   📊 HASIL: ${results.pass}/${total} test PASSED               ${results.fail > 0 ? '⚠️ ' : '  '}║`);
        console.log('╚══════════════════════════════════════════════════╝');
        await sleep(3000);
        await driver.quit();
    }
})();
