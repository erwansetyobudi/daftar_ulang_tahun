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
function hut_wa($name,$url){return 'Selamat ulang tahun, '.$name.'! 🎉🎂 Semoga senantiasa sehat, bahagia, dan sukses. Salam hangat dari Perpustakaan. Ada ucapan spesial untukmu: '.$url;}
function hut_date($s){return date('d-m-Y',strtotime($s));}
$today=new DateTimeImmutable('today');
$days=[];foreach([-1,0,1] as $offset){$d=$today->modify(($offset>=0?'+':'').$offset.' days');$days[$d->format('m-d')]=$offset===-1?'Kemarin':($offset===0?'Hari ini':'Besok');}
$ids=array_keys($days);
$conditions=[];foreach($ids as $md){$conditions[]="DATE_FORMAT(m.birth_date,'%m-%d')='".$dbs->real_escape_string($md)."'";}
$condition='('.implode(' OR ',$conditions).') AND m.birth_date IS NOT NULL';
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
$notice='';
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['hut_action']??'')==='send_email'){
 header('Content-Type: application/json; charset=utf-8');
 $token=(string)($_POST['token']??'');
 if(!hash_equals((string)($_SESSION['hut_csrf']??''),$token)){echo json_encode(['ok'=>false,'message'=>'Sesi tidak valid. Muat ulang halaman.']);exit;}
 $memberId=trim((string)($_POST['member_id']??''));
 $stmt=$dbs->prepare('SELECT member_id,member_name,member_email,birth_date FROM member WHERE member_id=? LIMIT 1');
 $stmt->bind_param('s',$memberId);$stmt->execute();$member=$stmt->get_result()->fetch_assoc();$stmt->close();
 if(!$member || !in_array(substr((string)$member['birth_date'],5,5),$ids,true)){echo json_encode(['ok'=>false,'message'=>'Anggota tidak termasuk daftar kemarin, hari ini, atau besok.']);exit;}
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
?>
<div id="hut-root" style="padding:12px 14px">
<style>
#hut-root{font-family:inherit}#hut-root .hut-top{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px}#hut-root h2{margin:0;font-size:24px}#hut-root .hut-panel{background:#fff;border:1px solid #dfe5eb;border-radius:10px;padding:15px;margin-bottom:16px}#hut-root .hut-controls{display:flex;gap:8px;flex-wrap:wrap;align-items:center}#hut-root .hut-controls input[name="q"]{min-width:240px}#hut-root .hut-controls select{min-width:130px}#hut-root .hut-controls label{font-weight:600;margin:0 3px}#hut-root table{width:100%;border-collapse:collapse;background:white}#hut-root th,#hut-root td{border-bottom:1px solid #e3e8ef;padding:11px 9px;vertical-align:middle;text-align:left}#hut-root th{background:#f4f7fa}#hut-root .hut-actions{display:flex;gap:5px;flex-wrap:wrap}#hut-root .hut-actions button{font-size:12px;padding:5px 8px}#hut-root .hut-chip{border-radius:14px;padding:4px 9px;font-size:12px;background:#e9edf3;white-space:nowrap}#hut-root .hut-today{background:#d9f4e5;color:#16633c}#hut-root .hut-yesterday{background:#fff1d8;color:#79500e}#hut-root .hut-tomorrow{background:#e4edff;color:#244b9a}#hut-root .hut-active{background:#d9f4e5;color:#16633c}#hut-root .hut-expired{background:#fee2e2;color:#991b1b}#hut-root .hut-unknown{background:#e9edf3;color:#475569}#hut-root .hut-pagination{display:flex;gap:6px;justify-content:flex-end;margin-top:15px;align-items:center}#hut-root .hut-pagination a{padding:6px 10px;border:1px solid #ddd;border-radius:5px}#hut-root .hut-msg{display:none;padding:10px;border-radius:6px;margin-bottom:10px;background:#e3f2ff}#hut-root .hut-summary{color:#697586;margin:0 0 12px}#hut-root .hut-scroll{overflow-x:auto}
</style>
<div class="hut-top"><h2>Daftar yang Ulang Tahun</h2><span class="hut-chip">Kemarin · Hari ini · Besok</span></div>
<div class="hut-panel"><form id="hut-search" class="hut-controls" action="<?=hut_h(hut_route())?>" method="get" onsubmit="event.preventDefault();var f=this,params=[];Array.prototype.forEach.call(f.elements,function(el){if(el.name&&!el.disabled&&el.type!=='submit'&&el.type!=='button')params.push(encodeURIComponent(el.name)+'='+encodeURIComponent(el.value));});var target=f.getAttribute('action').split('?')[0]+'?'+params.join('&');var root=document.getElementById('hut-root');fetch(target,{credentials:'same-origin'}).then(function(r){if(!r.ok)throw Error('HTTP '+r.status);return r.text()}).then(function(h){var doc=new DOMParser().parseFromString(h,'text/html'),next=doc.getElementById('hut-root');if(!next)throw Error('Konten plugin tidak ditemukan');root.replaceWith(next)}).catch(function(e){alert('Pencarian gagal: '+e.message)});return false;">
<input type="hidden" name="mod" value="<?=hut_h($_GET['mod']??'membership')?>"><input type="hidden" name="id" value="<?=hut_h($_GET['id']??'')?>">
<input class="form-control" name="q" placeholder="Cari nama atau nomor anggota" value="<?=hut_h($q)?>" aria-label="Cari nama atau nomor anggota">
<label for="hut-birth-day">Tanggal lahir</label><select id="hut-birth-day" class="form-control" name="birth_day" aria-label="Tanggal lahir"><option value="">Semua tanggal</option><?php for($d=1;$d<=31;$d++):?><option value="<?=$d?>" <?=$birthDay===$d?'selected':''?>><?=$d?></option><?php endfor;?></select>
<label for="hut-birth-month">Bulan</label><select id="hut-birth-month" class="form-control" name="birth_month" aria-label="Bulan lahir"><option value="">Semua bulan</option><?php foreach($monthNames as $mnum=>$mname):?><option value="<?=$mnum?>" <?=$birthMonth===$mnum?'selected':''?>><?=hut_h($mname)?></option><?php endforeach;?></select>
<button class="btn btn-primary" type="submit">Cari</button><button class="btn btn-default" type="button" onclick="var f=this.form;f.querySelector('[name=q]').value='';f.querySelector('[name=birth_day]').value='';f.querySelector('[name=birth_month]').value='';f.requestSubmit?f.requestSubmit():f.dispatchEvent(new Event('submit',{bubbles:true,cancelable:true}));">Reset</button>
</form><small style="display:block;margin-top:8px;color:#64748b">Daftar tetap dibatasi pada anggota yang berulang tahun kemarin, hari ini, dan besok. Pilih tanggal (1–31) dan bulan (Januari–Desember) tanpa tahun lahir.</small></div>
<div id="hut-message" class="hut-msg"></div>
<div class="hut-panel"><p class="hut-summary">Menampilkan <?=($total?($offset+1):0)?>–<?=min($total,$offset+$per)?> dari <?=$total?> anggota. Tanggal acuan: <?=hut_h($today->format('d-m-Y'))?>.</p><div class="hut-scroll"><table class="s-table table"><thead><tr><th>Nomor Anggota</th><th>Nama</th><th>Institusi</th><th>Nomor Telepon</th><th>Email</th><th>Tanggal Lahir</th><th>Masa Berlaku</th><th>Status Anggota</th><th>Keterangan</th><th>Tindakan</th></tr></thead><tbody>
<?php while($m=$result->fetch_assoc()): $status=$days[substr($m['birth_date'],5,5)]??'';$url=hut_url($m['member_name']);$wa=hut_wa($m['member_name'],$url);$expiry=(string)($m['expire_date']??'');$expiryValid=$expiry!=='' && $expiry!=='0000-00-00';$memberActive=$expiryValid && $expiry >= $today->format('Y-m-d');$memberStatus=$expiryValid?($memberActive?'Aktif':'Kadaluarsa'):'Tidak diketahui';?>
<tr><td><?=hut_h($m['member_id'])?></td><td><strong><?=hut_h($m['member_name'])?></strong></td><td><?=hut_h($m['inst_name'])?></td><td><?=hut_h($m['member_phone'])?></td><td><?=hut_h($m['member_email'])?></td><td><?=hut_h(hut_date($m['birth_date']))?></td><td><?= $expiryValid ? hut_h(hut_date($expiry)) : '—' ?></td><td><span class="hut-chip <?= $expiryValid ? ($memberActive?'hut-active':'hut-expired') : 'hut-unknown' ?>"><?=hut_h($memberStatus)?></span></td><td><span class="hut-chip <?=($status==='Hari ini'?'hut-today':($status==='Kemarin'?'hut-yesterday':'hut-tomorrow'))?>"><?=hut_h($status)?></span></td><td><div class="hut-actions"><button type="button" class="btn btn-default" onclick="hutCopy(this.dataset.text)" data-text="<?=hut_h($url)?>">Copy URL</button><button type="button" class="btn btn-default" onclick="hutCopy(this.dataset.text)" data-text="<?=hut_h($wa)?>">Copy WA</button><button type="button" class="btn btn-primary" onclick="hutEmail(this.dataset.member)" data-member="<?=hut_h($m['member_id'])?>" <?=filter_var($m['member_email'],FILTER_VALIDATE_EMAIL)?'':'disabled title="Email tidak valid"'?>>Kirim Email</button></div></td></tr>
<?php endwhile; if(!$total):?><tr><td colspan="10" style="text-align:center;padding:30px">Tidak ada anggota yang cocok pada rentang ulang tahun ini.</td></tr><?php endif;?></tbody></table></div>
<div class="hut-pagination"><?php if($page>1):?><a href="<?=hut_h(hut_route(['q'=>$q,'birth_day'=>$birthDay?:'','birth_month'=>$birthMonth?:'','page'=>$page-1]))?>" onclick="event.preventDefault();var root=document.getElementById('hut-root');fetch(this.href,{credentials:'same-origin'}).then(function(r){if(!r.ok)throw Error(r.status);return r.text()}).then(function(h){var next=new DOMParser().parseFromString(h,'text/html').getElementById('hut-root');if(!next)throw Error('Konten tidak ditemukan');root.replaceWith(next)}).catch(function(e){alert('Gagal membuka halaman: '+e.message)});return false">‹ Sebelumnya</a><?php endif;?><span>Halaman <?=$page?> / <?=$pages?></span><?php if($page<$pages):?><a href="<?=hut_h(hut_route(['q'=>$q,'birth_day'=>$birthDay?:'','birth_month'=>$birthMonth?:'','page'=>$page+1]))?>" onclick="event.preventDefault();var root=document.getElementById('hut-root');fetch(this.href,{credentials:'same-origin'}).then(function(r){if(!r.ok)throw Error(r.status);return r.text()}).then(function(h){var next=new DOMParser().parseFromString(h,'text/html').getElementById('hut-root');if(!next)throw Error('Konten tidak ditemukan');root.replaceWith(next)}).catch(function(e){alert('Gagal membuka halaman: '+e.message)});return false">Berikutnya ›</a><?php endif;?></div></div></div>
<script>
(function(){
 window.hutNavigate=function(url){fetch(url,{credentials:'same-origin'}).then(r=>r.text()).then(html=>{var d=new DOMParser().parseFromString(html,'text/html');var next=d.querySelector('#hut-root');if(next){document.querySelector('#hut-root').replaceWith(next);history.replaceState(null,'',location.pathname+location.search);}else{hutMsg('Gagal memuat daftar.');}}).catch(()=>hutMsg('Koneksi gagal.'));};
 window.hutMsg=function(message){var el=document.querySelector('#hut-message');if(el){el.style.display='block';el.textContent=message;}};
 window.hutCopy=function(text){if(navigator.clipboard&&window.isSecureContext){navigator.clipboard.writeText(text).then(()=>hutMsg('Teks berhasil disalin.'),()=>hutFallback(text));}else hutFallback(text);};
 window.hutFallback=function(text){var t=document.createElement('textarea');t.value=text;document.body.appendChild(t);t.select();try{document.execCommand('copy');hutMsg('Teks berhasil disalin.');}catch(e){hutMsg('Gagal menyalin.');}t.remove();};
 window.hutEmail=function(id){if(!confirm('Kirim ucapan ulang tahun melalui email untuk anggota ini?'))return;var data=new URLSearchParams({hut_action:'send_email',member_id:id,token:<?=json_encode($_SESSION['hut_csrf'])?>});fetch(<?=json_encode(hut_route())?>,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:data}).then(r=>r.json()).then(j=>hutMsg(j.message)).catch(()=>hutMsg('Permintaan email gagal.'));};

})();
</script>
