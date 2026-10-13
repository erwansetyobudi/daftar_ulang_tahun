<?php
/*
 * File: birthday.php
 * Created on Tue Oct 13 2026
 * Last Updated: Tue Oct 13 2026 8:53:19 AM
 * Author: Erwan Setyo Budi
 * Email: erwans818@gmail.com
 * License: The GNU General Public License, Version 3 (GPL-3.0) - Copyright (C) 2026 Erwan Setyo Budi. This program is free software.
 */

if (!defined('INDEX_AUTH')) { http_response_code(403); exit('Forbidden'); }
$name=trim((string)($_GET['name'] ?? 'Sahabat Perpustakaan'));
$name=function_exists('mb_substr') ? mb_substr(strip_tags($name),0,60,'UTF-8') : substr(strip_tags($name),0,60);
if ($name==='') $name='Sahabat Perpustakaan';
$asset=SWB.'plugins/daftar_ulang_tahun/public/';
$e=static function($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');};
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Happy Birthday <?= $e($name) ?></title>
<base href="<?= $e($asset) ?>"><link rel="stylesheet" href="<?= $e($asset) ?>birthday-grid.css">
<link rel="stylesheet" href="<?= $e($asset) ?>stylesheet.css">
<link rel="stylesheet" href="<?= $e($asset) ?>loading.css">
<link rel="stylesheet" href="<?= $e($asset) ?>cake.css">
<link rel="stylesheet" href="<?= $e($asset) ?>birthday-layout-fix.css?v=118">
<style>body{font-family:Arial,Helvetica,sans-serif} .container{max-width:1170px} #birthday-balloons{pointer-events:none}</style>
</head><body>

	<div class="loading"></div>
	<audio class="song" controls loop>
        <source src="hbd.mp3"></source>
	   Your browser isn't invited for super fun audio time.
    </audio>
    <!-- Balon dibuat otomatis dari nama pada URL -->
    <div id="birthday-balloons"></div>

    <img src="Balloon-Border.png" width="100%" class="balloon-border">
 

	<div class="container">
		
		<div class="row">
			<div class="col-md-2 col-xs-2 bulb-holder">
				<div class="bulb" id="bulb_yellow"></div>
			</div>
			<div class="col-md-2 col-xs-2 bulb-holder">
				<div class="bulb" id="bulb_red"></div>
			</div>
			<div class="col-md-2 col-xs-2 bulb-holder">
				<div class="bulb" id="bulb_blue"></div>
			</div>
			<div class="col-md-2 col-xs-2 bulb-holder">
				<div class="bulb" id="bulb_green"></div>
			</div>
			<div class="col-md-2 col-xs-2 bulb-holder">
				<div class="bulb" id="bulb_pink"></div>
			</div>
			<div class="col-md-2 col-xs-2 bulb-holder">
				<div class="bulb" id="bulb_orange"></div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 text-center">
				<img src="banner.png" class="bannar">
			</div>
		</div>
		<div class="row cake-cover">
			<div class="col-md-12 texr-center">
				<div class="cake">
				  <div class="velas">
				    <div class="fuego"></div>
				    <div class="fuego"></div>
				    <div class="fuego"></div>
				    <div class="fuego"></div>
				    <div class="fuego"></div>
				  </div>
				  <div class="cobertura"></div>
				  <div class="bizcocho"></div>
				</div>
			</div>
		</div>
		<div class="row message">
			<div class="col-md-12">
				<p><span id="birthday-name"></span>, Today is...</p>
				<p>as beautiful as other days</p>
				<p>but you realize</p>
				<p>another year has gone</p>
				<p>in a blink of the eyes</p>
				<p><strong>however</strong></p>
				<p>Do you know..?</p>
				<p>today is just special</p>
				<p>so special to you</p>
				<p>that's why</p>
				<p>Let's make it...</p>
				<p>the best celebration ever</p>
				<p>and let me share...</p>
				<p>a piece of happiness to you</p>
				<p>I made all this...</p>
				<p>as a birthday present to you</p>
				<p>thanks for being there</p>
				<p>thanks for the friendship we made</p>
				<p>thanks for everything</p>
				<p>I wish you all the best</p>
				<p>May your life be at ease</p>
				<p>May all your wishes come true</p>
				<p><span id="birthday-name"></span>, Remember </p>
				<p>your ambitions</p>
				<p>you live as a free bird...</p>
				<p>flying in the blue sky</p>
				<p>Now things are different...</p>
				<p>real story of your life</p>
				<p>is just about to begin</p>
				<p>indeed..</p>
				<p>but...</p>
				<p>don't worry</p>
				<p>because...</p>
				<p>God has your back</p>
				<p>and</p>
				<p>this year will be better</p>
				<p>and I hope</p>
				<p>you'll find...</p>
				<p>happiness along the way</p>
				<p>keep your spirit up</p>
				<p>enjoy every single moment...</p>
				<p>that you experience today</p>
				<p>fill it with your most beautiful smile</p>
				<p>and make it the best memory..</p>
				<p>lastly...</p>
				<p>I'd like to wish you one more time</p>
				<p>a very happy birthday <span id="birthday-name"></span></p>
			</div>
		</div>
		<div class="navbar navbar-fixed-bottom birthday-controls">
			<div class="row">
				<div class="col-md-6 text-center col-md-offset-3">
					<button class="btn btn-primary" id="turn_on">Turn On Lights</button>
					<button class="btn btn-primary" id="play">Play Music</button>
					<button class="btn btn-primary" id="bannar_coming">Let's Decorate</button>
					<button class="btn btn-primary" id="balloons_flying">Fly With Balloons</button>
					<button class="btn btn-primary" id="cake_fadein">Most Delicious Cake Ever</button>
					<button class="btn btn-primary" id="light_candle"> Light Candle</button>
					<button class="btn btn-primary" id="wish_message">Happy Birthday</button>
					<button class="btn btn-primary" id="story">A mesage for you</button>
					<!-- <button class="btn btn-primary" id="cake_cut">Lrt's Cut the Cake</button> -->
				</div>
			</div>
			<!-- <div class="col-md-2">
			</div>
			<div class="col-md-2">
			</div>
			<div class="col-md-2">
			</div>
			<div class="col-md-2">
			</div>
			<div class="col-md-2">
			</div> -->
		</div>


	</div>

<script src="<?= $e($asset) ?>jquery.min.js"></script>
<script src="<?= $e($asset) ?>birthday-name.js"></script>
<script src="<?= $e($asset) ?>effect.js"></script>
</body></html>
<?php exit; ?>
