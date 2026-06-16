<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Low Stock Alert</title>
    <style>
        body { margin: 0; padding: 0; background: #f6f7f5; font-family: Arial, Helvetica, sans-serif; color: #202620; }
        table { border-collapse: collapse; }
        .wrapper { width: 100%; background: #f6f7f5; padding: 32px 12px; }
        .container { width: 100%; max-width: 640px; margin: 0 auto; }
        .card { background: #ffffff; border: 1px solid #dfe5dc; border-radius: 18px; overflow: hidden; }
        .header { background: #386641; padding: 28px 32px; color: #ffffff; }
        .brand { font-size: 14px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; opacity: 0.9; }
        .title { margin: 12px 0 0; font-size: 26px; line-height: 1.25; font-weight: 800; }
        .content { padding: 30px 32px 34px; }
        .intro { margin: 0 0 22px; color: #596158; font-size: 15px; line-height: 1.65; }
        .alert-box { border: 1px solid #f2c2b6; background: #fff6f3; border-radius: 14px; padding: 18px; }
        .product-name { margin: 0 0 5px; color: #256037; font-size: 18px; font-weight: 800; }
        .muted { color: #7a8278; font-size: 13px; }
        .metric-label { color: #7a8278; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; }
        .metric-value { padding-top: 6px; font-size: 22px; font-weight: 800; }
        .danger { color: #ef3b16; }
        .button { display: inline-block; background: #386641; color: #ffffff !important; text-decoration: none; border-radius: 12px; padding: 13px 20px; font-size: 14px; font-weight: 800; }
        .note { margin: 22px 0 0; color: #596158; font-size: 14px; line-height: 1.55; }
        .footer { padding: 18px 32px 28px; color: #8a9288; font-size: 12px; line-height: 1.5; }
        @media screen and (max-width: 520px) {
            .header, .content, .footer { padding-left: 22px !important; padding-right: 22px !important; }
            .title { font-size: 23px !important; }
        }
    </style>
</head>
<body>
    <table role="presentation" class="wrapper" width="100%">
        <tr>
            <td>
                <table role="presentation" class="container" width="100%">
                    <tr>
                        <td class="card">
                            <table role="presentation" width="100%">
                                <tr>
                                    <td class="header">
                                        <div class="brand">GroceryPro Inventory</div>
                                        <h1 class="title">Low Stock Alert</h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content">
                                        <p class="intro">
                                            Hi {{ $user->name ?? 'Admin' }}, stok salah satu produk sudah mencapai batas minimum. Mohon cek inventory dan lakukan restock bila diperlukan.
                                        </p>

                                        <table role="presentation" width="100%" class="alert-box">
                                            <tr>
                                                <td colspan="2">
                                                    <p class="product-name">{{ $product->name }}</p>
                                                    <div class="muted">
                                                        {{ $product->sku }}@if($product->category) &bull; {{ $product->category->name }}@endif
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 18px; width: 50%;">
                                                    <div class="metric-label">Current Stock</div>
                                                    <div class="metric-value danger">{{ number_format($product->stock_qty) }} {{ $product->unit }}</div>
                                                </td>
                                                <td style="padding-top: 18px; width: 50%;">
                                                    <div class="metric-label">Minimum Stock</div>
                                                    <div class="metric-value">{{ number_format($product->min_stock) }} {{ $product->unit }}</div>
                                                </td>
                                            </tr>
                                        </table>

                                        <p style="margin: 26px 0 0;">
                                            <a href="{{ $inventoryUrl }}" class="button">Manage Stock</a>
                                        </p>

                                        <p class="note">
                                            Email ini dikirim otomatis saat stok produk berada di bawah atau sama dengan minimum stock yang sudah ditentukan.
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="footer">
                                        GroceryPro &bull; Inventory and POS Management<br>
                                        Jika tombol tidak bisa dibuka, copy link ini: {{ $inventoryUrl }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
