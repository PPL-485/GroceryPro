<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password</title>
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
        .notice { border: 1px solid #dfe5dc; background: #f8faf7; border-radius: 14px; padding: 16px 18px; color: #596158; font-size: 14px; line-height: 1.55; }
        .button { display: inline-block; background: #386641; color: #ffffff !important; text-decoration: none; border-radius: 12px; padding: 13px 20px; font-size: 14px; font-weight: 800; }
        .muted { color: #7a8278; font-size: 13px; line-height: 1.55; }
        .footer { padding: 18px 32px 28px; color: #8a9288; font-size: 12px; line-height: 1.5; word-break: break-word; }
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
                                        <div class="brand">GroceryPro Account</div>
                                        <h1 class="title">Reset Password</h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content">
                                        <p class="intro">
                                            Hi {{ $user->name ?? 'there' }}, kami menerima permintaan reset password untuk akun GroceryPro kamu.
                                        </p>

                                        <p style="margin: 0 0 24px;">
                                            <a href="{{ $resetUrl }}" class="button">Reset Password</a>
                                        </p>

                                        <div class="notice">
                                            Link ini akan kadaluarsa dalam <strong>{{ $expiresIn }} menit</strong>. Jika kamu tidak merasa meminta reset password, abaikan email ini dan password kamu tidak akan berubah.
                                        </div>

                                        <p class="muted" style="margin: 22px 0 0;">
                                            Demi keamanan, jangan bagikan link reset password ini kepada siapa pun.
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="footer">
                                        GroceryPro &bull; Inventory and POS Management<br>
                                        Jika tombol tidak bisa dibuka, copy link ini: {{ $resetUrl }}
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
