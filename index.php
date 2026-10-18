<?php
/*
 * File: index.php
 * Created on Tue Oct 13 2026
 * Last Updated: Tue Oct 13 2026 8:55:06 AM
 * Author: Erwan Setyo Budi
 * Email: erwans818@gmail.com
 * License: The GNU General Public License, Version 3 (GPL-3.0) - Copyright (C) 2026 Erwan Setyo Budi. This program is free software.
 */

defined('INDEX_AUTH') OR die('No direct access');
require_once SB.'admin/default/session.inc.php';
require_once __DIR__.'/mailer.php';
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
function hut_h($s){return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');}
function hut_route($params=[]){$base=['mod'=>$_GET['mod']??'membership','id'=>$_GET['id']??''];return $_SERVER['PHP_SELF'].'?'.http_build_query(array_merge($base,$params));}
function hut_base(){
  $base = rtrim((string)(defined('SWB')?SWB:'/'),'/');
  if (preg_match('~^https?://~i',$base)) return $base.'/index.php?p=happybirthdayto';
  $https=(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off');
  return ($https?'https://':'http://').($_SERVER['HTTP_HOST']??'localhost').$base.'/index.php?p=happybirthdayto';
}
function hut_url($name){return hut_base().'&name='.rawurlencode($name);}
function hut_wa($name,$url){
  return 'Selamat ulang tahun, '.$name.'! Semoga senantiasa sehat, bahagia, dan sukses. Salam hangat dari Perpustakaan '.$library_name.'. Ada ucapan spesial untukmu: '.$url;}
function hut_wa_number($phone){
  $digits=preg_replace('/\D+/', '', (string)$phone);
  if($digits==='')return '';
  if(strncmp($digits,'00',2)===0)$digits=substr($digits,2);
  elseif($digits[0]==='0')$digits='62'.substr($digits,1);
  elseif($digits[0]==='8')$digits='62'.$digits;
  if(!preg_match('/^62[0-9]{8,13}$/',$digits))return '';
  return $digits;
}
function hut_date($s){return date('d-m-Y',strtotime($s));}
$today=new DateTimeImmutable('today');
// Hanya anggota yang berulang tahun hari ini dan masa berlaku keanggotaannya belum habis.
$todayYmd=$today->format('Y-m-d');
$todayMd=$today->format('m-d');
$days=[$todayMd=>'Hari ini'];
$ids=[$todayMd];
$condition="DATE_FORMAT(m.birth_date,'%m-%d')='".$dbs->real_escape_string($todayMd)."' AND m.birth_date IS NOT NULL AND m.expire_date >= '".$dbs->real_escape_string($todayYmd)."'";
$q=trim((string)($_GET['q']??''));
$birthDay=(int)($_GET['birth_day']??0);
$birthMonth=(int)($_GET['birth_month']??0);
if($birthDay<1 || $birthDay>31)$birthDay=0;
if($birthMonth<1 || $birthMonth>12)$birthMonth=0;
if($birthDay>0)$condition.=' AND DAY(m.birth_date)='.$birthDay;
if($birthMonth>0)$condition.=' AND MONTH(m.birth_date)='.$birthMonth;
$monthNames=[1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
if($q!==''){
 $safe=$dbs->real_escape_string($q);
 $condition.=" AND (m.member_name LIKE '%$safe%' OR m.member_id LIKE '%$safe%' OR DATE_FORMAT(m.birth_date,'%Y-%m-%d') LIKE '%$safe%' OR DATE_FORMAT(m.birth_date,'%d-%m-%Y') LIKE '%$safe%')";
}
// Log tombol Send WA, satu status per anggota per tanggal (ulang tahun tahun berikutnya terpisah).
$hutWaLogReady = (bool)$dbs->query("CREATE TABLE IF NOT EXISTS `hut_wa_send_log` (
 `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 `member_id` VARCHAR(64) NOT NULL,
 `send_date` DATE NOT NULL,
 `phone` VARCHAR(32) NOT NULL,
 `message` TEXT NOT NULL,
 `clicked_at` DATETIME NOT NULL,
 `click_count` INT UNSIGNED NOT NULL DEFAULT 1,
 PRIMARY KEY (`id`), UNIQUE KEY `uniq_member_day` (`member_id`,`send_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['hut_action']??'')==='log_wa') {
 header('Content-Type: application/json; charset=utf-8');
 if (empty($_SESSION['hut_csrf']) || !hash_equals((string)$_SESSION['hut_csrf'],(string)($_POST['token']??''))) {echo json_encode(['ok'=>false,'message'=>'Token sesi tidak valid']);exit;}
 if (!$hutWaLogReady) {echo json_encode(['ok'=>false,'message'=>'Tabel riwayat WA belum dapat dibuat. Periksa izin database.']);exit;}
 $memberId=trim((string)($_POST['member_id']??''));
 $stmt=$dbs->prepare('SELECT member_id,member_name,member_phone,birth_date,expire_date FROM member WHERE member_id=? LIMIT 1');
 $stmt->bind_param('s',$memberId);$stmt->execute();$member=$stmt->get_result()->fetch_assoc();$stmt->close();
 if (!$member || substr((string)$member['birth_date'],5,5)!==$todayMd || (string)$member['expire_date']<$todayYmd) {echo json_encode(['ok'=>false,'message'=>'Anggota tidak memenuhi syarat pengiriman']);exit;}
 $phone=hut_wa_number($member['member_phone']);
 if ($phone==='') {echo json_encode(['ok'=>false,'message'=>'Nomor WA tidak valid']);exit;}
 $message=hut_wa($member['member_name'],hut_url($member['member_name']));
 $stmt=$dbs->prepare('INSERT INTO hut_wa_send_log (member_id,send_date,phone,message,clicked_at,click_count) VALUES (?,?,?,?,NOW(),1) ON DUPLICATE KEY UPDATE phone=VALUES(phone),message=VALUES(message),clicked_at=NOW(),click_count=click_count+1');
 $stmt->bind_param('ssss',$memberId,$todayYmd,$phone,$message);
 $ok=$stmt->execute();$stmt->close();
 echo json_encode(['ok'=>$ok,'message'=>$ok?'Status Sudah Dikirim tercatat.':'Gagal menyimpan riwayat.','time'=>date('d-m-Y H:i')],JSON_UNESCAPED_UNICODE);exit;
}
$notice='';
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['hut_action']??'')==='send_email'){
 header('Content-Type: application/json; charset=utf-8');
 $token=(string)($_POST['token']??'');
 if(!hash_equals((string)($_SESSION['hut_csrf']??''),$token)){echo json_encode(['ok'=>false,'message'=>'Sesi tidak valid. Muat ulang halaman.']);exit;}
 $memberId=trim((string)($_POST['member_id']??''));
 $stmt=$dbs->prepare('SELECT member_id,member_name,member_email,birth_date,expire_date FROM member WHERE member_id=? LIMIT 1');
 $stmt->bind_param('s',$memberId);$stmt->execute();$member=$stmt->get_result()->fetch_assoc();$stmt->close();
 if(!$member || substr((string)$member['birth_date'],5,5)!==$todayMd || (string)$member['expire_date'] < $todayYmd){echo json_encode(['ok'=>false,'message'=>'Anggota tidak aktif atau tidak berulang tahun hari ini.']);exit;}
 if(!filter_var($member['member_email'],FILTER_VALIDATE_EMAIL)){echo json_encode(['ok'=>false,'message'=>'Alamat email anggota tidak valid.']);exit;}
 $url=hut_url($member['member_name']);
 $sent=hut_send_birthday_email($member['member_email'],$member['member_name'],$url);
 echo json_encode($sent,JSON_UNESCAPED_UNICODE);exit;
}
if(empty($_SESSION['hut_csrf']))$_SESSION['hut_csrf']=bin2hex(random_bytes(24));
$page=max(1,(int)($_GET['page']??1));$per=20;
$count=$dbs->query('SELECT COUNT(*) AS total FROM member m WHERE '.$condition)->fetch_assoc();$total=(int)$count['total'];$pages=max(1,(int)ceil($total/$per));$page=min($page,$pages);$offset=($page-1)*$per;
$sql='SELECT m.member_id,m.member_name,m.inst_name,m.member_phone,m.member_email,m.birth_date,m.expire_date FROM member m WHERE '.$condition.' ORDER BY DATE_FORMAT(m.birth_date,\'%m-%d\'),m.member_name LIMIT '.$per.' OFFSET '.$offset;
$result=$dbs->query($sql);
$hutWaSent=[];
if($hutWaLogReady){
 $logStmt=$dbs->prepare('SELECT member_id,clicked_at FROM hut_wa_send_log WHERE send_date=?');
 $logStmt->bind_param('s',$todayYmd);$logStmt->execute();$logResult=$logStmt->get_result();
 while($log=$logResult->fetch_assoc())$hutWaSent[(string)$log['member_id']]=$log['clicked_at'];
 $logStmt->close();
}
?>
<div id="hut-root" style="padding:12px 14px">
<style>
#hut-root{font-family:inherit}#hut-root .hut-top{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px}#hut-root h2{margin:0;font-size:24px}#hut-root .hut-panel{background:#fff;border:1px solid #dfe5eb;border-radius:10px;padding:15px;margin-bottom:16px}#hut-root .hut-controls{display:flex;gap:8px;flex-wrap:wrap;align-items:center}#hut-root .hut-controls input[name="q"]{min-width:240px}#hut-root .hut-controls select{min-width:130px}#hut-root .hut-controls label{font-weight:600;margin:0 3px}#hut-root table{width:100%;border-collapse:collapse;background:white}#hut-root th,#hut-root td{border-bottom:1px solid #e3e8ef;padding:11px 9px;vertical-align:middle;text-align:left}#hut-root th{background:#f4f7fa}#hut-root .hut-actions{display:flex;gap:5px;flex-wrap:wrap}#hut-root .hut-actions button{font-size:12px;padding:5px 8px}#hut-root .hut-chip{border-radius:14px;padding:4px 9px;font-size:12px;background:#e9edf3;white-space:nowrap}#hut-root .hut-today{background:#d9f4e5;color:#16633c}#hut-root .hut-yesterday{background:#fff1d8;color:#79500e}#hut-root .hut-tomorrow{background:#e4edff;color:#244b9a}#hut-root .hut-wa-sent{background:blue;color:white;}#hut-root .hut-active{background:#d9f4e5;color:#16633c}#hut-root .hut-expired{background:#fee2e2;color:#991b1b}#hut-root .hut-unknown{background:#e9edf3;color:#475569}#hut-root .hut-pagination{display:flex;gap:6px;justify-content:flex-end;margin-top:15px;align-items:center}#hut-root .hut-pagination a{padding:6px 10px;border:1px solid #ddd;border-radius:5px}#hut-root .hut-msg{display:none;padding:10px;border-radius:6px;margin-bottom:10px;background:#e3f2ff}#hut-root .hut-summary{color:#697586;margin:0 0 12px}#hut-root .hut-scroll{overflow-x:auto}
</style>
<div class="hut-top"><h2>Daftar yang Ulang Tahun</h2><span class="hut-chip">Aktif · Ulang tahun hari ini</span></div>
<div class="hut-panel"><form id="hut-search" class="hut-controls" action="<?=hut_h(hut_route())?>" method="get" onsubmit="event.preventDefault();var f=this,params=[];Array.prototype.forEach.call(f.elements,function(el){if(el.name&&!el.disabled&&el.type!=='submit'&&el.type!=='button')params.push(encodeURIComponent(el.name)+'='+encodeURIComponent(el.value));});var target=f.getAttribute('action').split('?')[0]+'?'+params.join('&');var root=document.getElementById('hut-root');fetch(target,{credentials:'same-origin'}).then(function(r){if(!r.ok)throw Error('HTTP '+r.status);return r.text()}).then(function(h){var doc=new DOMParser().parseFromString(h,'text/html'),next=doc.getElementById('hut-root');if(!next)throw Error('Konten plugin tidak ditemukan');root.replaceWith(next)}).catch(function(e){alert('Pencarian gagal: '+e.message)});return false;">
<input type="hidden" name="mod" value="<?=hut_h($_GET['mod']??'membership')?>"><input type="hidden" name="id" value="<?=hut_h($_GET['id']??'')?>">
<input class="form-control" name="q" placeholder="Cari nama atau nomor anggota" value="<?=hut_h($q)?>" aria-label="Cari nama atau nomor anggota">
<label for="hut-birth-day">Tanggal lahir</label><select id="hut-birth-day" class="form-control" name="birth_day" aria-label="Tanggal lahir"><option value="">Semua tanggal</option><?php for($d=1;$d<=31;$d++):?><option value="<?=$d?>" <?=$birthDay===$d?'selected':''?>><?=$d?></option><?php endfor;?></select>
<label for="hut-birth-month">Bulan</label><select id="hut-birth-month" class="form-control" name="birth_month" aria-label="Bulan lahir"><option value="">Semua bulan</option><?php foreach($monthNames as $mnum=>$mname):?><option value="<?=$mnum?>" <?=$birthMonth===$mnum?'selected':''?>><?=hut_h($mname)?></option><?php endforeach;?></select>
<button class="btn btn-primary" type="submit">Cari</button><button class="btn btn-default" type="button" onclick="var f=this.form;f.querySelector('[name=q]').value='';f.querySelector('[name=birth_day]').value='';f.querySelector('[name=birth_month]').value='';f.requestSubmit?f.requestSubmit():f.dispatchEvent(new Event('submit',{bubbles:true,cancelable:true}));">Reset</button>
</form><small style="display:block;margin-top:8px;color:#64748b">Daftar hanya menampilkan anggota aktif yang berulang tahun hari ini. Pilih tanggal (1â€“31) dan bulan (Januariâ€“Desember) tanpa tahun lahir.</small></div>
<div id="hut-message" class="hut-msg"></div>
<div class="hut-panel"><p class="hut-summary">Menampilkan <?=($total?($offset+1):0)?>â€“<?=min($total,$offset+$per)?> dari <?=$total?> anggota. Tanggal acuan: <?=hut_h($today->format('d-m-Y'))?>.</p><div class="hut-scroll"><table class="s-table table"><thead><tr><th>No.</th><th>Nomor Anggota</th><th>Nama</th><th>Institusi</th><th>Nomor Telepon</th><th>Email</th><th>Tanggal Lahir</th><th>Masa Berlaku</th><th>Status Anggota</th><th>Keterangan</th><th>Status WA</th><th>Tindakan</th></tr></thead><tbody>
<?php $rowNumber=$offset; while($m=$result->fetch_assoc()): $rowNumber++; $status=$days[substr($m['birth_date'],5,5)]??'';$url=hut_url($m['member_name']);$wa=hut_wa($m['member_name'],$url);$waNumber=hut_wa_number($m['member_phone']);$waLink=$waNumber!==''?'https://wa.me/'.$waNumber.'?text='.rawurlencode($wa):'';$expiry=(string)($m['expire_date']??'');$expiryValid=$expiry!=='' && $expiry!=='0000-00-00';$memberActive=$expiryValid && $expiry >= $today->format('Y-m-d');$memberStatus=$expiryValid?($memberActive?'Aktif':'Kadaluarsa'):'Tidak diketahui';$waSentAt=$hutWaSent[(string)$m['member_id']]??null;?>
<tr><td><?= $rowNumber ?></td><td><?=hut_h($m['member_id'])?></td><td><strong><?=hut_h($m['member_name'])?></strong></td><td><?=hut_h($m['inst_name'])?></td><td><?=hut_h($m['member_phone'])?></td><td><?=hut_h($m['member_email'])?></td><td><?=hut_h(hut_date($m['birth_date']))?></td><td><?= $expiryValid ? hut_h(hut_date($expiry)) : 'â€”' ?></td><td><span class="hut-chip <?= $expiryValid ? ($memberActive?'hut-active':'hut-expired') : 'hut-unknown' ?>"><?=hut_h($memberStatus)?></span></td><td><span class="hut-chip <?=($status==='Hari ini'?'hut-today':($status==='Kemarin'?'hut-yesterday':'hut-tomorrow'))?>"><?=hut_h($status)?></span></td><td class="hut-wa-status"><?= $waSentAt ? '<span class="hut-chip hut-wa-sent">Sudah Dikirim</span><small style="display:block;margin-top:5px">'.hut_h(date('d-m-Y H:i',strtotime($waSentAt))).'</small>' : '<span class="hut-chip hut-unknown">Belum Dikirim</span>' ?></td><td><div class="hut-actions"><button type="button" class="btn btn-default" onclick="hutCopy(this.dataset.text)" data-text="<?=hut_h($url)?>">Copy URL</button><button type="button" class="btn btn-default" onclick="hutCopy(this.dataset.text)" data-text="<?=hut_h($wa)?>">Copy WA</button><a class="btn btn-success" href="<?=hut_h($waLink!==''?$waLink:'#')?>" target="_blank" rel="noopener noreferrer" data-member="<?=hut_h($m['member_id'])?>" <?= $waLink==='' ? 'onclick="event.preventDefault();alert(\'Nomor WhatsApp tidak valid.\');" aria-disabled="true" title="Nomor WhatsApp tidak valid"' : 'onclick="hutLogWa(this)"' ?>>Send WA</a><button type="button" class="btn btn-primary" onclick="hutEmail(this.dataset.member)" data-member="<?=hut_h($m['member_id'])?>" <?=filter_var($m['member_email'],FILTER_VALIDATE_EMAIL)?'':'disabled title="Email tidak valid"'?>>Kirim Email</button></div></td></tr>
<?php endwhile; if(!$total):?><tr><td colspan="12" style="text-align:center;padding:30px">Tidak ada anggota aktif yang berulang tahun hari ini dan cocok dengan filter.</td></tr><?php endif;?></tbody></table></div>
<div class="hut-pagination"><?php if($page>1):?><a href="<?=hut_h(hut_route(['q'=>$q,'birth_day'=>$birthDay?:'','birth_month'=>$birthMonth?:'','page'=>$page-1]))?>" onclick="event.preventDefault();var root=document.getElementById('hut-root');fetch(this.href,{credentials:'same-origin'}).then(function(r){if(!r.ok)throw Error(r.status);return r.text()}).then(function(h){var next=new DOMParser().parseFromString(h,'text/html').getElementById('hut-root');if(!next)throw Error('Konten tidak ditemukan');root.replaceWith(next)}).catch(function(e){alert('Gagal membuka halaman: '+e.message)});return false">â€¹ Sebelumnya</a><?php endif;?><span>Halaman <?=$page?> / <?=$pages?></span><?php if($page<$pages):?><a href="<?=hut_h(hut_route(['q'=>$q,'birth_day'=>$birthDay?:'','birth_month'=>$birthMonth?:'','page'=>$page+1]))?>" onclick="event.preventDefault();var root=document.getElementById('hut-root');fetch(this.href,{credentials:'same-origin'}).then(function(r){if(!r.ok)throw Error(r.status);return r.text()}).then(function(h){var next=new DOMParser().parseFromString(h,'text/html').getElementById('hut-root');if(!next)throw Error('Konten tidak ditemukan');root.replaceWith(next)}).catch(function(e){alert('Gagal membuka halaman: '+e.message)});return false">Berikutnya â€º</a><?php endif;?></div></div></div>
<script>
(function(){
 window.hutNavigate=function(url){fetch(url,{credentials:'same-origin'}).then(r=>r.text()).then(html=>{var d=new DOMParser().parseFromString(html,'text/html');var next=d.querySelector('#hut-root');if(next){document.querySelector('#hut-root').replaceWith(next);history.replaceState(null,'',location.pathname+location.search);}else{hutMsg('Gagal memuat daftar.');}}).catch(()=>hutMsg('Koneksi gagal.'));};
 window.hutMsg=function(message){var el=document.querySelector('#hut-message');if(el){el.style.display='block';el.textContent=message;}};
 window.hutCopy=function(text){if(navigator.clipboard&&window.isSecureContext){navigator.clipboard.writeText(text).then(()=>hutMsg('Teks berhasil disalin.'),()=>hutFallback(text));}else hutFallback(text);};
 window.hutFallback=function(text){var t=document.createElement('textarea');t.value=text;document.body.appendChild(t);t.select();try{document.execCommand('copy');hutMsg('Teks berhasil disalin.');}catch(e){hutMsg('Gagal menyalin.');}t.remove();};
 window.hutLogWa=function(link){
  var row=link.closest('tr'),status=row?row.querySelector('.hut-wa-status'):null;
  var body='hut_action=log_wa&member_id='+encodeURIComponent(link.dataset.member)+'&token='+encodeURIComponent(<?=json_encode($_SESSION['hut_csrf'])?>);
  fetch(<?=json_encode(hut_route())?>,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body,keepalive:true})
   .then(function(r){return r.json()}).then(function(j){if(j.ok&&status){status.innerHTML='<span class="hut-chip hut-wa-sent">Sudah Dikirim</span><small style="display:block;margin-top:5px">'+j.time+'</small>';}else if(!j.ok){hutMsg(j.message);}})
   .catch(function(){hutMsg('WhatsApp terbuka, tetapi pencatatan status gagal.');});
  return true;
 };
 window.hutEmail=function(id){if(!confirm('Kirim ucapan ulang tahun melalui email untuk anggota ini?'))return;var data=new URLSearchParams({hut_action:'send_email',member_id:id,token:<?=json_encode($_SESSION['hut_csrf'])?>});fetch(<?=json_encode(hut_route())?>,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:data}).then(r=>r.json()).then(j=>hutMsg(j.message)).catch(()=>hutMsg('Permintaan email gagal.'));};

})();
</script>

