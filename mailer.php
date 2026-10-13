<?php
/*
 * File: mailer.php
 * Created on Tue Oct 13 2026
 * Last Updated: Tue Oct 13 2026 8:53:39 AM
 * Author: Erwan Setyo Budi
 * Email: erwans818@gmail.com
 * License: The GNU General Public License, Version 3 (GPL-3.0) - Copyright (C) 2026 Erwan Setyo Budi. This program is free software.
 */

/** Birthday mail adapter: use configured SMTP, never the overdue notice template. */
function hut_mail_settings() {
    $cfg = function_exists('config') ? config('mail') : null;
    if (!is_array($cfg) || empty($cfg['enable'])) return [null, 'Fitur email belum aktif pada konfigurasi SLiMS.'];
    $host = (string)($cfg['server'] ?? '');
    $host = preg_replace('~^(?:ssl|tls|tcp)://~i', '', $host);
    $host = preg_replace('~:[0-9]+$~', '', $host);
    $port = (int)($cfg['server_port'] ?? 465);
    $user = (string)($cfg['auth_username'] ?? '');
    $pass = (string)($cfg['auth_password'] ?? '');
    $from = (string)($cfg['from'] ?? $user);
    $fromName = (string)($cfg['from_name'] ?? 'Perpustakaan');
    $secure = strtolower((string)($cfg['SMTPSecure'] ?? 'ssl'));
    $auth = !empty($cfg['auth_enable']);
    if (!$host || !$port || !filter_var($from, FILTER_VALIDATE_EMAIL)) return [null, 'Host, port, atau alamat pengirim SMTP SLiMS tidak valid.'];
    return [['host'=>$host,'port'=>$port,'user'=>$user,'pass'=>$pass,'from'=>$from,'fromName'=>$fromName,'secure'=>$secure,'auth'=>$auth], null];
}
function hut_send_birthday_email($to, $name, $url) {
    list($cfg, $err) = hut_mail_settings();
    if ($err) return ['ok'=>false,'message'=>$err];
    $subject='Selamat Ulang Tahun, '.$name.'!';
    $body="Halo ".$name.",\n\nSelamat ulang tahun! 🎉🎂\nSemoga senantiasa sehat, bahagia, dan sukses.\n\nAda ucapan spesial untukmu:\n".$url."\n\nSalam hangat,\nPerpustakaan";
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP(); $mail->Host=$cfg['host']; $mail->Port=$cfg['port'];
            $mail->SMTPAuth=$cfg['auth'];
            if ($mail->SMTPAuth) { $mail->Username=$cfg['user']; $mail->Password=$cfg['pass']; }
            if (in_array($cfg['secure'],['ssl','smtps','tls','starttls'],true)) $mail->SMTPSecure=in_array($cfg['secure'],['ssl','smtps'],true)?'ssl':'tls';
            $mail->SMTPDebug=0; $mail->CharSet='UTF-8'; $mail->setFrom($cfg['from'],$cfg['fromName']);
            $mail->addAddress($to,$name); $mail->Subject=$subject; $mail->Body=$body; $mail->send();
            return ['ok'=>true,'message'=>'Ucapan ulang tahun berhasil dikirim melalui SMTP.'];
        } catch (\Throwable $e) { error_log('Birthday mail error: '.$e->getMessage()); return ['ok'=>false,'message'=>'Gagal mengirim email melalui SMTP. Periksa konfigurasi dan log server.']; }
    }
    if (class_exists('Symfony\\Component\\Mailer\\Mailer') && class_exists('Symfony\\Component\\Mailer\\Transport') && class_exists('Symfony\\Component\\Mime\\Email')) {
        try {
            $scheme=in_array($cfg['secure'],['ssl','smtps'],true)?'smtps':'smtp';
            $dsn=$scheme.'://'.rawurlencode((string)$cfg['user']).':'.rawurlencode((string)$cfg['pass']).'@'.$cfg['host'].':'.$cfg['port'];
            $transport=\Symfony\Component\Mailer\Transport::fromDsn($dsn);
            $mailer=new \Symfony\Component\Mailer\Mailer($transport);
            $email=(new \Symfony\Component\Mime\Email())->from($cfg['from'])->to($to)->subject($subject)->text($body);
            $mailer->send($email);
            return ['ok'=>true,'message'=>'Ucapan ulang tahun berhasil dikirim melalui SMTP.'];
        } catch (\Throwable $e) { error_log('Birthday mail error: '.$e->getMessage()); return ['ok'=>false,'message'=>'Gagal mengirim email melalui SMTP. Periksa konfigurasi dan log server.']; }
    }
    return ['ok'=>false,'message'=>'Library SMTP belum ditemukan. Mohon lampirkan library pengiriman email SLiMS pada instalasi Anda untuk integrasi langsung.'];
}
