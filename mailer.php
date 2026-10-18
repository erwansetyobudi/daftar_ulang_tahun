
<?php
/*
 * File: mailer.php
 * Created on Tue Oct 13 2026
 * Author: Erwan Setyo Budi
 * Email: erwans818@gmail.com
 * License: The GNU General Public License, Version 3 (GPL-3.0)
 *
 * Birthday mail adapter:
 * Use configured SMTP, never the overdue notice template.
 *
 * Library Name automatically follows SLiMS System Configuration.
 */

/**
 * Mengambil nama perpustakaan dari pengaturan SLiMS.
 */
function hut_library_name()
{
    global $sysconf;

    $libraryName = trim((string)($sysconf['library_name'] ?? ''));

    return $libraryName !== '' ? $libraryName : 'Perpustakaan';
}

/**
 * Mengambil konfigurasi SMTP dari SLiMS.
 */
function hut_mail_settings()
{
    $cfg = function_exists('config') ? config('mail') : null;

    if (!is_array($cfg) || empty($cfg['enable'])) {
        return [
            null,
            'Fitur email belum aktif pada konfigurasi SLiMS.'
        ];
    }

    $host = (string)($cfg['server'] ?? '');
    $host = preg_replace('~^(?:ssl|tls|tcp)://~i', '', $host);
    $host = preg_replace('~:[0-9]+$~', '', $host);

    $port = (int)($cfg['server_port'] ?? 465);
    $user = (string)($cfg['auth_username'] ?? '');
    $pass = (string)($cfg['auth_password'] ?? '');
    $from = (string)($cfg['from'] ?? $user);

    // Gunakan Library Name jika From Name SMTP kosong.
    $fromName = trim((string)($cfg['from_name'] ?? ''));

    if ($fromName === '') {
        $fromName = hut_library_name();
    }

    $secure = strtolower((string)($cfg['SMTPSecure'] ?? 'ssl'));
    $auth = !empty($cfg['auth_enable']);

    if (!$host || !$port || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
        return [
            null,
            'Host, port, atau alamat pengirim SMTP SLiMS tidak valid.'
        ];
    }

    return [
        [
            'host'     => $host,
            'port'     => $port,
            'user'     => $user,
            'pass'     => $pass,
            'from'     => $from,
            'fromName' => $fromName,
            'secure'   => $secure,
            'auth'     => $auth
        ],
        null
    ];
}

/**
 * Mengirim email ucapan ulang tahun.
 */
function hut_send_birthday_email($to, $name, $url)
{
    list($cfg, $err) = hut_mail_settings();

    if ($err) {
        return [
            'ok'      => false,
            'message' => $err
        ];
    }

    // Nama perpustakaan otomatis dari konfigurasi SLiMS.
    $libraryName = hut_library_name();

    $subject = 'Selamat Ulang Tahun, ' . $name . '!';

    $body =
        "Halo " . $name . ",\n\n" .
        "Selamat ulang tahun! 🎉🎂\n" .
        "Semoga senantiasa sehat, bahagia, dan sukses.\n\n" .
        "Ada ucapan spesial untukmu:\n" .
        $url . "\n\n" .
        "Salam hangat,\n" .
        $libraryName;

    /**
     * Pengiriman menggunakan PHPMailer.
     */
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            $mail->isSMTP();
            $mail->Host = $cfg['host'];
            $mail->Port = $cfg['port'];
            $mail->SMTPAuth = $cfg['auth'];

            if ($mail->SMTPAuth) {
                $mail->Username = $cfg['user'];
                $mail->Password = $cfg['pass'];
            }

            if (in_array(
                $cfg['secure'],
                ['ssl', 'smtps', 'tls', 'starttls'],
                true
            )) {
                $mail->SMTPSecure = in_array(
                    $cfg['secure'],
                    ['ssl', 'smtps'],
                    true
                ) ? 'ssl' : 'tls';
            }

            $mail->SMTPDebug = 0;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom(
                $cfg['from'],
                $cfg['fromName']
            );

            $mail->addAddress($to, $name);

            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->isHTML(false);

            $mail->send();

            return [
                'ok'      => true,
                'message' => 'Ucapan ulang tahun berhasil dikirim melalui SMTP.'
            ];

        } catch (\Throwable $e) {
            error_log('Birthday mail error: ' . $e->getMessage());

            return [
                'ok'      => false,
                'message' => 'Gagal mengirim email melalui SMTP. Periksa konfigurasi dan log server.'
            ];
        }
    }

    /**
     * Pengiriman menggunakan Symfony Mailer.
     */
    if (
        class_exists('Symfony\\Component\\Mailer\\Mailer') &&
        class_exists('Symfony\\Component\\Mailer\\Transport') &&
        class_exists('Symfony\\Component\\Mime\\Email')
    ) {
        try {
            $scheme = in_array(
                $cfg['secure'],
                ['ssl', 'smtps'],
                true
            ) ? 'smtps' : 'smtp';

            $dsn = $scheme . '://' .
                rawurlencode((string)$cfg['user']) . ':' .
                rawurlencode((string)$cfg['pass']) . '@' .
                $cfg['host'] . ':' . $cfg['port'];

            $transport = \Symfony\Component\Mailer\Transport::fromDsn($dsn);

            $mailer = new \Symfony\Component\Mailer\Mailer($transport);

            $email = (new \Symfony\Component\Mime\Email())
                ->from(new \Symfony\Component\Mime\Address(
                    $cfg['from'],
                    $cfg['fromName']
                ))
                ->to($to)
                ->subject($subject)
                ->text($body);

            $mailer->send($email);

            return [
                'ok'      => true,
                'message' => 'Ucapan ulang tahun berhasil dikirim melalui SMTP.'
            ];

        } catch (\Throwable $e) {
            error_log('Birthday mail error: ' . $e->getMessage());

            return [
                'ok'      => false,
                'message' => 'Gagal mengirim email melalui SMTP. Periksa konfigurasi dan log server.'
            ];
        }
    }

    return [
        'ok'      => false,
        'message' => 'Library SMTP belum ditemukan. Mohon lampirkan library pengiriman email SLiMS pada instalasi Anda untuk integrasi langsung.'
    ];
}
