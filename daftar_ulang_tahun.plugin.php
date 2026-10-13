<?php
/**
 * Plugin Name: Daftar yang Ulang Tahun
 * Plugin URI: https://github.com/erwansetyobudi/daftar-ulang-tahun
 * Description: Daftar ulang tahun anggota, salin tautan/WA, dan kirim email.
 * Version: 1.1.6
 * Author: Erwan Setyo Budi
 * Author URI: https://github.com/erwansetyobudi
 */
$plugin = \SLiMS\Plugins::getInstance();
$plugin->registerMenu('membership', 'Daftar yang Ulang Tahun', __DIR__ . '/index.php');
$plugin->registerMenu('opac', 'happybirthdayto', __DIR__ . '/birthday.php');
