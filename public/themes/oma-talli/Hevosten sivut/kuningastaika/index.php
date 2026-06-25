<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<title>Kuningastaika</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
<link href="https://lianna.altervista.org/krekilankulma/style.css" rel="stylesheet" type="text/css" />

<link href="https://lianna.altervista.org/krekilankulma/tabcontent.css" rel="stylesheet" type="text/css" />
<script src="https://lianna.altervista.org/krekilankulma/tabcontent.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="https://lianna.altervista.org/krekilankulma/filter_hevonen.css">
<script type="text/javascript" src="https://norling.altervista.org/lightbox.js"></script>
<link rel="stylesheet" href="https://norling.altervista.org/lightbox.css" type="text/css" media="screen">
</head>
<body>

	<div id="header"><h1 class="inset-text">Kuningastaika</h1></div>

	<div id="wrapper"><div id="content_hevonen">
<ul class="tabs">
	<li class="selected"><a href="#view1">Perustiedot & luonne</a></li>
	<li><a href="#view2">Suku</a></li>
	<li><a href="#view3">Kilpailut</a></li>
	<!-- <li><a href="#view4">Saavutukset</a></li> -->
	<li><a href="#view5">Kuvagalleria & näyttelyt</a></li>
	<li><a href="">Päiväkirja</a></li>
</ul>

	<div class="tabcontents"><div id="view1">



<img src="tietokuva.png" style="height: 385px; margin-right: 30px;" align="right">

<p class="tiedot">Kutsumanimeltään <u>Taku</u><br>
Perlino <span class="pikkuinfo">(Ee/Aa/CrCr)</span> suomenhevonen ori, 152cm <br>
Syntynyt 06.06.2019, on nyt 11-vuotias <br>
<a href="http://www.virtuaalihevoset.net/virtuaalihevoset/hevonen/VH20-018-0366">VH20-018-0366</a>, <a href="https://piirroshevosille.fi/hevoset/hevonen/PKK3687">PKK3687</a>
</p>
<p class="tiedot">
Kasvattanut Lianna Rassi <span class="pikkuinfo">(VRL-05175)</span> <img src="https://lianna.altervista.org/flag/fi.png"> <br>
Omistaa <a href="">Tomás Reyes</a> / <a href="">Krekilänkulma</a> <span class="pikkuinfo">(VRL-05175, <a href="mailto:liannavirtuaali@gmail.com">&#9993;</a>)</span>
</p>
<p class="tiedot">
Yleispainotteinen <br>
ko: Vaativa B, re: 120cm, CIC1
</p>
<p class="tiedot">
Kilpaillut porrastetuissa kenttäkilpailuissa <br>
<?php 
$vh = 'VH20-018-0366';
$url = 'http://virtuaalihevoset.net/rajapinta/porrastetut/'.$vh;
$obj = json_decode(file_get_contents($url), true);

if(isset($obj['error']) && $obj['error'] == 0){        
	$data = $obj['porrastetut'];    
	$info = $data['info'];
	$hevonen = $data['hevonen'];

	$jaos = 3; //tämä on kerj
    
    if($hevonen['error'] == 1){
        echo $hevonen['error_message'];
    }else {
        $tasoinfo = $hevonen['tasot'][$jaos];
		$pisteet = $tasoinfo['pisteet'];
        $max_taso_per_pisteet = $tasoinfo['max_taso_per_pisteet'];
        $max_taso_rajoitus = $tasoinfo['taso_rajoitus'];
        
	echo $pisteet . " ominaisuuspistettä,";
        echo " on nyt tasolla " . $max_taso_per_pisteet . "/" . $max_taso_rajoitus . "";
        
    }

    
}else if($obj['error'] == 1){
    echo $obj['error_description'];
}else {
    echo "Tapahtui odottamaton virhe!";
}

?> </p>

<!-- <p class="meriitit">KTK II</p> -->

<!-- <p style="text-align:center; margin: 35px 0 30px 0;"><img src="https://lianna.altervista.org/krekilankulma/img/viiva.png"></p>

<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eget mollis felis. Nullam sodales, nulla in lacinia ultricies, ex dui consectetur augue, vel varius ex nunc in mi. Cras tempus metus risus, eu sagittis enim accumsan sed. Nulla blandit sed felis sit amet ornare. Quisque ac turpis in sapien dapibus accumsan quis ac tellus. Nam venenatis dapibus tortor, sed tincidunt turpis. Cras lacinia porta lacus in pellentesque. </p> -->


	</div><div id="view2">



	<table class="sukutaulu">

	<tr><td width="28%" rowspan="4">

i. <a href="https://viixinyksityiset.weebly.com/veke.html">Elovalkea</a> <br><small>
sh, mvkkkm, 154cm <br>
KTK II, EV I, KV II, KEV I, Fn

	</small></td><td width="8%" rowspan="4"><div class="isohaka">{</div></td><td width="28%" rowspan="2">

ii. Aarrevalkea <br><small>
sh, mvkk, 156cm

	</small></td><td width="8%" rowspan="2"><div class="pikkuhaka">{</div></td><td width="28%">

iii. Aarrejahti <small>
[sh, m, 157cm]

	</small></td></tr><tr><td>

iie. Valkoliekki <small>
[sh, vkk, 154cm]

	</small></td></tr><tr><td rowspan="2">

ie. Kuunsäde <br><small>
sh, rnkm, 153cm

	</small></td><td rowspan="2"><div class="pikkuhaka">{</div></td><td>

iei. Tutkasäde <small>
[sh, rn, 155cm]

	</small></td></tr><tr><td>

iee. Kuunvälke <small>
[sh, rtkm, 152cm]

	</small></td></tr>




	<tr><td width="28%" rowspan="4">

e. <a href="https://viixinyksityiset.weebly.com/lilja.html">Kuunlilja</a> <br><small>
sh, rnvkk, 151cm <br> 
KTK II, EV I, KV II, KEV I

	</small></td><td width="8%" rowspan="4"><div class="isohaka">{</div></td><td width="28%" rowspan="2">

ei. Susihukka <br><small>
sh, rn, 154cm

	</small></td><td width="8%" rowspan="2"><div class="pikkuhaka">{</div></td><td width="28%">

eii. Hukkareissu <small>
[sh, m, 156cm]

	</small></td></tr><tr><td>

eie. Sudensuukko <small>
[sh, trn, 154cm]

	</small></td></tr><tr><td rowspan="2">

ee. Helmililja <br><small>
sh, vkk, 153cm

	</small></td><td rowspan="2"><div class="pikkuhaka">{</div></td><td>

eei. Retostelija <small>
[sh, rt, 155cm]

	</small></td></tr><tr><td>

eee. Kultalilja <small>
[sh, vkk, 152cm]

	</small></td></tr>
</table>





<div><div style="text-transform: none; border-bottom: 0px; margin-top: 20px; margin-bottom: 0px; margin-left: 0px; font-weight: ; display: block;">
<span onClick="if (this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display != '') {  this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = ''; this.innerHTML = '<a href=\'#\' onClick=\'return false;\'>Sulje </a>'; } else { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = 'none'; this.innerHTML = '<a href=\'#\' onClick=\'return false;\'>Lue sukuselvitys</a>'; }" />

<a href="#" onClick="return false;">Lue sukuselvitys</a></div><div class="quotecontent"><div style="display: none;">


<p>ii. <b>Aarrevalkea</b> oli yllättävän tasaisen luonteen omaava läsipäinen ja sukkajalkainen mustanvoikko suomenhevosori vaikka sen vanhemmat onkin vähän kenkumpia luonteeltaan. Tämä 156cm korkea ori kilpaili elämänsä aikana pääasiassa kenttäratsastuksen parissa jossa se menestyi ihan kohtalaisesti. Mikään huippu se ei ollut mutta helpommissa luokissa se pääsi usein palkinnoille. Komean ja sopusuhtaisen rakenteen omaava Aarrevalkea myös kantakirjattiin toiselle palkinnolle. Aarrevalkea joutui jättämään kilparadat taakseen aikaisemmin kuin oli tarkoitus sillä sen jalka viottui erään kilpailun maasto-osuudella. Se jätti jälkeensä parikymmentä varsaa ennen kuin menehtyi 19-vuotiaana ähkyyn. </p>

<ul><p>iii. Tämä 157cm korkea musta suomenhevosori <b>Aarrejahti</b> on hevonen jonka ei ollut tarkoitus edes syntyäkään. Se on täysin vahinkovarsa sillä sen isä pääsi astumaan tallin tamman karateessaan tarhasta ja päästessään tammojen luokse. Oriin molemmat vanhemmat on haasteellisempia käsitellä joka oli ainoa syy miksi tätä varsaa odotettiin kauhulla. Kaikkien onneksi siitä ei tullut vanhempiensa kaltainen kiukkukalle vaan ihan mukava josta saatiinkin vanhempana yllättävän hyvin menestynyt estehevonen joka voitti ja sijoittui useammassa kilpailussa. Siitä ei kuitenkaan tullut koskaan suosittua jalostusoria ja se jätti jälkeensä vain viisi jälkeläistä ennenkuin se päästettiin ikiuneen kun sillä todettiin kasvain. </p>

<p>iie. Voikon värinen läsipää ja sukkajalkainen <b>Valkoliekki</b> oli todella kaunis 154cm korkea suomenhevostamma mutta ulkonäkö kyllä petti pahemman kerran. Se oli pahemman luokan hapannaama ulkonäöstään huolimatta eikä monikaan halunnut astua sen karsinaan ellei ollut pakko. Onneksi tämä ei vaikuttanut siihen millainen Valkoliekki oli ratsuna ja se menestyikin mukavasti kouluratsastuksen parissa. Useamman kerran se pääsi palkintopallille ja palkintokaapista löytyykin monenlaista pokaalia ja ruusuketta. Valkoliekki sai elämänsä aikana vain pari varsaa sillä sen jälkeläiset ei näyttänyt menevän oikein kaupaksi. Toinen varsoista jäi kasvattajalle Keski-Suomeen ja toinen myytiin pohjoiseen pienelle tilalle jossa uskallettiin ottaa riski. Valkoliekki menehtyi 21-vuotiaana. </p> </ul>

<p>ie. Vain 153cm korkea ruunikonkimo suomenhevostamma <b>Kuunsäde</b> on kaikkien rakastama neitokainen joka menestyi elämänsä aikana enemmän kouluratsastuksessa kuin missään muualla. Vaativalle tasolle koulutettu tamma napsi sijoituksia ja voittoja lukuisista kilpailuista mutta ei kuitenkaan pärjännyt maan parhaimmille hevosille. Kuunsäteen luonne oli äärimmäisen rauhallinen ja ystävällinen ja siitä tulikin mielettömän hyvä siitostamma kun se lopulta jäi kisaeläkkeelle. Tamma sai yhden varsan jo nuorena neitona ja uran jälkeen vielä kolme lisää. Nykyään Kuunsäde viettää eläkepäiviään laitumella omistajallaan Pohjois-Suomessa. </p>

<ul><p>iei. 155cm korkeasta ruunikosta suomenhevosorista, <b>Tutkasäde</b>stä tuli elämänsä aikana suosittu jalostusorii sillä sen menestyksekäs kisaura oli vertaansa vailla ja se kantakirjattiinkin hyvin pistein. Tutkasäde kilpaili kouluratsastuksen parissa keräten palkintokaappiin jos minkälaista palkintoa ja onhan sillä takanaan pari mestaruuttakin. Tutkasäteen luonne oli todella rauhallinen ja ystävällinen ja se kaikkien iloksi periytyi sen kymmenille jälkeläisille joita se jätti jälkeensä. Kaikkien harmiksi se jouduttiin kuitenkin päästämään ikiuneen vain 17 vuoden iässä sillä se loukkasi selkänsä pahasti laitumella. </p>

<p>iee. <b>Kuunvälke</b> oli tuiki tavallinen 152cm korkea rautiaankimo suomenhevostamma joka ei paljoa päässyt näkemään kisaratoja elämänsä aikana. Se vietti koko elämänsä leppoista elämää pienellä tallilla harrasteratsuna jossa se starttasi vain muutaman kerran pienissä kilpailuissa nuoren ratsastajansa kanssa. Kuunvälke astutettiin pari kertaa hieman tunnetuimmilla oreilla sillä siitä haluttiin paremman kapasiteetin omaavan varsan jonka omistaja voisi kouluttaa itse ja alkaa kilpailemaan. Varsa perikin tamman lempeän ja rauhallisen luonteen ja oriin kapasiteetin. Tämän vuoksi se astutettiin toisen kerran ja se varsa myytiin sitten eteenpäin Pohjois-Suomeen. Kuunvälke päästettiin ikiuneen 19-vuotiaana sen terveyden pettäessä. </p> </ul>


<p>ei. <b>Susihukka</b> on menestynyt kouluratsu ja siltä löytyy useampi suomenhevosten mestaruus. Luonteeltaan ori on yleisesti ottaen nimensä mukainen villiherra, se osaa kyllä viedä ja pelleillä jos siihen annetaan mahdollisuus. Yhteispelin toimiessa ori on miellyttävä ratsastaa, mutta sitä samaa ei voi aina sanoa hoito- ja käsittelytilanteissa. Ori syttyy aina kilpailutilanteisiin ja se selvästi rakastaa esiintymistä. Komea ulkokuori siis hieman pettää, kun sen kanssa touhuaa muutein kun ratsastaen. Kouluratsastuksen lisäksi Susihukka hyppää näppärästi, mutta estekilpailut ovat jääneet vähemmälle omistajan aristaessa niiden hyppäämistä. Ori on kantakirjattu III-palkinnolla, joka on jälkeläis- ja kilpailunäyttöjen perusteella korotettu kakkos palkinnoksi. Jälkeläisilleen Susihukka on näyttänyt periyttävän elastista ravia ja hyvää rakennetta, sekä isänsä tapaan vahvaa luonnetta. </p>

<ul><p>eii. <b>Hukkareissu</b> on suomenhevosena harvinainen näky vaativilla kouluratsastusradoilla. Ori on ratsastajansa kanssa niittänyt tasaisesti menestystä puoliveriratsukoiden joukossa kansallisella tasolla ja sen on voittanut useamman kerran koulumestaruuskilpailut. Menestyvän kilpailu-uran takana on pitkäjänteinen työ, sillä ori ei ollut mikään helpoin ratsukoulutettava. Hukkareissu on tunnettu vahvasta ja hieman haastavasta luonteestaan. Kovan ulkokuoren alta paljastuu yhteisen sävelen myötä ori jolla on valtava työmotivaatio ja kapasiteetti. Ori siirtyi jalostuskäyttöön vasta kilpailu-uransa päätyttyä ja se kerkesi saamaan parisenkymmentä jälkeläistä ennenkuin se siirtyi vihreillelaitumille ärhäkän kasvaimen vuoksi. Jälkeläisilleen Hukkareissu periytti joustavaa liikettä ja vahvaa luonnetta. </p>

<p>eie. Kevytrakenteinen ja siro <b>Sudensuukko</b> saavutti kantakirjauksessa erinomaiset käyttöpisteet, saaden 8 ja 9 arvosanoja. Rakenteellisesti tamma sai kehuja jalkojen ja kavioiden terveydestä, kantakirjapalkinnoksi Sudensuukko saavutti II-palkinnon. Ennen kantakirjaamista ja siirtymistä jalostukseen, Sudensuukko kisasi kenttä- ja esteratsastuksessa aluetasolla saaden tasaisesti hyviä kilpailutuloksia. Tamman kisaura päättyi jalan venähtämiseen maastoesteradalla. Jalka kestää kevyen harjoittelemisen, mutta ei enää kilpailemista vaativammalla tasolla. Susuhukka on tamman neljäs jälkeläinen ja edellisille varsoille tamma on periyttänyt kevyehköä rakennetta hyvällä jalka-asennolla ja vahvoilla kavioilla. Varsat ja tamma itse ovat luonteeltaan helposti käsiteltäviä ja rauhallisia ratsastettavia. </p> </ul>

<p>ee. <b>Helmililja</b> on mielenkiintoinen yhdistelmä vanhoja hieman tuntemattomampia ja uudempia sukulinjoja. Tamman isänpuolta ei ole kovinkaan paljon nähty, mutta emänpuoli on senkin edestä täynnä tunnettuja ravi- ja ratsuhevosia. Helmililja itse edustaa rakenteelta hieman sitä vanhanaikaisempaa suomenhevoslinjaa. Luonteeltaan tämä voikon värinen tamma on miellyttävä ja rehti. Helmililja koulutettiin alunperin esteratsuksi, mutta omistajan vaihdoksen myötä siitä tuli kouluratsu uuden omistajan estekammon vuoksi. Hyppykapasiteettii, metrin radoilta, muuttui helppo A:n kouluratojen kiertämiseen. Kilpauransa jälkeen tamma siirtyi jalostusleasing käyttöön Jarno Kuukarin suomenhevostalleille. Tammalla oli todella vahva laukka, jota se periytti myös varsoillensa. Varsoja tamma on saanut tähän mennessä kaksi, joista ensimmäinen on Kuunlilja. </p>

<ul><p>eei. Raskaampirakenteinen <b>Retostelija</b> toimi pienellä maatilalla monitoimihevosena koko elinikänsä. Talvisin ori veti rekeä, kesäisin se toimi perheen lasten ratsuna ja perheen isännän metsätyökaverina. Retostelijalla oli mitä tasaisimmat liikkeet ja niiden kyydissä oli mukava istua. Tästä orista olisi tullut varmasti hyvä kouluratsu. Luonteeltaan ori oli rauhallinen ja kuuliainen, jonka uskalsi jättää pienempienkin lasten harjailtavaksi. Jälkeläisiä ori sai kaksi hieman vanhemmalla iällä. </p>

<p>eee. <b>Kultalilja</b> tulee menestyneestä ravisuvusta, ja siitä odotettiin myös suurta ravitähteä. Tamman varsa-aikojen koelähdöt menivätkin räväkkä luonteisen tamman kanssa hyvin, mutta siirtyminen vaativampiin lähtöihin oli takkuista. Temperamenttinen tamma ei ollut mikään helpoin ajettava ja se sortui herkästi laukoille. Pitkän harkinnan jälkeen tamma päätettiin myyntiin ratsutettavaksi. Kukaan ei aluksi uskonut Kultaliljan kykyihin ja muutaman omistajan vaihdoksen jälkeen se päätyi pienelle maalaistallille, jossa sillä maastoiltiin ensiksi paljon ja sen jälkeen sen kanssa alettiin varovaisesti ratsastelemaan kentällä. Alku ei tamman kanssa ollut helppoa, mutta sen kyvyt esteratsastukseen nähtiin, joten ratsukoulutusta vietiin sinnikkäästi eteenpäin. Kultalilja kisasikin lopulta hyvin 100-110cm esteluokkia. Jälkeläisiä tamma kerkesi saamaan kolme, ennen kuin menehtyi traagisesti tallipalossa vain 15-vuotiaana. </p> </ul>



</div></div></div>



<!-- <h6>Jälkeläiset</h6>

<p><table class="kisat"><tr class="kilpailutulos">

<td> s. 00.00.0000 </td>
<td> tamma </td>
<td> <a href="">Krekilänkulman *****</a> </td>
<td> e. <a href="">Emän nimi</a> </td>
<td> om. Onni Omistaja <span class="pikkuinfo">(VRL-00000)</span> </td>
<td> <i>ei meriittejä</i> </td>

</tr></table></p> -->



	</div> <div id="view3">



<h2>Arvokilpailut ja tarinallisesti merkittävät kilpailut</h2>

<div id="myBtnContainer">
  <button class="btn active" onclick="filterSelection('all')"> Kaikki</button>
  <button class="btn" onclick="filterSelection('este')"> Esteratsastus</button>
  <button class="btn" onclick="filterSelection('koulu')"> Kouluratsastus</button>
  <button class="btn" onclick="filterSelection('kentta')"> Kenttäratsastus</button>
</div>

<div class="container">

<div class="filterDiv este 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 04.05.2020 </td>
<td class="laji"> este </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/se.png"> &nbsp; <a href="https://hanamiweek.altervista.org/">Hannaby Slott</a> </td>
<td class="info"> Hannaby Hanami Week </td>
<td class="luokka"> 100cm </td>
<td class="tulos"> 24/35 </td>
<td class="virheet"> 4 vp </td>
</tr></table></div>

<div class="filterDiv koulu 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 04.05.2020 </td>
<td class="laji"> koulu </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/se.png"> &nbsp; <a href="https://hanamiweek.altervista.org/">Hannaby Slott</a> </td>
<td class="info"> Hannaby Hanami Week </td>
<td class="luokka"> Vaativa B </td>
<td class="tulos"> <b>8/39</b> </td>
<td class="virheet"> 73.129 % </td>
</tr></table></div>

<div class="filterDiv este 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 05.05.2020 </td>
<td class="laji"> este </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/se.png"> &nbsp; <a href="https://hanamiweek.altervista.org/">Hannaby Slott</a> </td>
<td class="info"> Hannaby Hanami Week </td>
<td class="luokka"> 110-115cm </td>
<td class="tulos"> 17/31 </td>
<td class="virheet"> 4 vp </td>
</tr></table></div>

<div class="filterDiv koulu 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 08.05.2020 </td>
<td class="laji"> koulu </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/se.png"> &nbsp; <a href="https://hanamiweek.altervista.org/">Hannaby Slott</a> </td>
<td class="info"> Hannaby Hanami Week </td>
<td class="luokka"> Vaativa B </td>
<td class="tulos"> <b>4/40</b> </td>
<td class="virheet"> 72.286 % </td>
</tr></table></div>



<div class="filterDiv kentta 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 22.-24.06.2020 </td>
<td class="laji"> kenttä </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/fi.png"> &nbsp; <a href="http://www.auburnestate.altervista.org/cup2020-2.html">Auburn Estate</a> </td>
<td class="info"> Kesäpäivänseisaus <small>(Kalla CUP:n 2. osakilpailu)</small> </td>
<td class="luokka"> CIC1 </td>
<td class="tulos"> 10/12 </td>
<td class="virheet"> 99,48 vp </td>
</tr>
<tr class="kilpailutulos">
<td colspan="3"><u>Koulukoe:</u> 3/12 (66.48 %) </td>
<td colspan="1"><u>Rataestekoe:</u> 10/12 (12 vp) </td>
<td colspan="3"><u>Maastokoe:</u> 11/12 (40,2 vp) </td>
</tr>
</table></div>





<div class="filterDiv este 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 26.06.2020 </td>
<td class="laji"> este </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/fi.png"> &nbsp; <a href="http://www.auburnestate.altervista.org/cup2020-2.html">Auburn Estate</a> </td>
<td class="info"> Kesäpäivänseisaus <small>(Kalla CUP:n 2. osakilpailu)</small> </td>
<td class="luokka"> 110cm </td>
<td class="tulos"> 14/25 </td>
<td class="virheet"> 0-8 vp </td>
</tr></table></div>

<div class="filterDiv este 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 26.06.2020 </td>
<td class="laji"> este </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/fi.png"> &nbsp; <a href="http://www.auburnestate.altervista.org/cup2020-2.html">Auburn Estate</a> </td>
<td class="info"> Kesäpäivänseisaus <small>(Kalla CUP:n 2. osakilpailu)</small> </td>
<td class="luokka"> 120cm </td>
<td class="tulos"> 11/25 </td>
<td class="virheet"> 0-4 vp </td>
</tr></table></div>

<div class="filterDiv koulu 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 27.06.2020 </td>
<td class="laji"> koulu </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/fi.png"> &nbsp; <a href="http://www.auburnestate.altervista.org/cup2020-2.html">Auburn Estate</a> </td>
<td class="info"> Kesäpäivänseisaus <small>(Kalla CUP:n 2. osakilpailu)</small> </td>
<td class="luokka"> Helppo A </td>
<td class="tulos"> 8/28 </td>
<td class="virheet"> 67.500 % </td>
</tr></table></div>

<div class="filterDiv koulu 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 27.06.2020 </td>
<td class="laji"> koulu </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/fi.png"> &nbsp; <a href="http://www.auburnestate.altervista.org/cup2020-2.html">Auburn Estate</a> </td>
<td class="info"> Kesäpäivänseisaus <small>(Kalla CUP:n 2. osakilpailu)</small> </td>
<td class="luokka"> Vaativa B </td>
<td class="tulos"> 23/25 </td>
<td class="virheet"> 57.786 % </td>
</tr></table></div>



<div class="filterDiv este 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 29.08.2020 </td>
<td class="laji"> este </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/fi.png"> &nbsp; <a href="https://seppele.piirroshevoset.com/osis12020.php">Seppele</a> </td>
<td class="info"> Seppele Cup:n 1. osakilpailu </td>
<td class="luokka"> 110cm </td>
<td class="tulos"> 7/30 </td>
<td class="virheet"> 0 vp </td>
</tr></table></div>

<div class="filterDiv koulu 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 29.08.2020 </td>
<td class="laji"> koulu </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/fi.png"> &nbsp; <a href="https://seppele.piirroshevoset.com/osis12020.php">Seppele</a> </td>
<td class="info"> Seppele Cup:n 1. osakilpailu </td>
<td class="luokka"> Vaativa B </td>
<td class="tulos"> 10/29 </td>
<td class="virheet"> 63.640 % </td>
</tr></table></div>

<div class="filterDiv este 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 30.08.2020 </td>
<td class="laji"> este </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/fi.png"> &nbsp; <a href="https://seppele.piirroshevoset.com/osis12020.php">Seppele</a> </td>
<td class="info"> Seppele Cup:n 1. osakilpailu </td>
<td class="luokka"> 120cm </td>
<td class="tulos"> <b>2/41</b> </td>
<td class="virheet"> 0 vp </td>
</tr></table></div>




<div class="filterDiv kentta 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 18.-20.09.2020 </td>
<td class="laji"> kenttä </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/fi.png"> &nbsp; <a href="http://www.auburnestate.altervista.org/cup2020-3.html">Auburn Estate</a> </td>
<td class="info"> Syyspäiväntasaus <small>(Kalla CUP:n 3. osakilpailu)</small> </td>
<td class="luokka"> CIC1 </td>
<td class="tulos"> 11/16 </td>
<td class="virheet"> 78,24 vp </td>
</tr>
<tr class="kilpailutulos">
<td colspan="3"><u>Koulukoe:</u> 2/16 (70.36 %) </td>
<td colspan="1"><u>Rataestekoe:</u> 13/16 (10 vp) </td>
<td colspan="3"><u>Maastokoe:</u> 11/16 (78,24 vp) </td>
</tr>
</table></div>





<div class="filterDiv este 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 25.09.2020 </td>
<td class="laji"> este </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/fi.png"> &nbsp; <a href="http://www.auburnestate.altervista.org/cup2020-3.html">Auburn Estate</a> </td>
<td class="info"> Syyspäiväntasaus <small>(Kalla CUP:n 3. osakilpailu)</small> </td>
<td class="luokka"> 110cm </td>
<td class="tulos"> 27/28 </td>
<td class="virheet"> 8 vp </td>
</tr></table></div>

<div class="filterDiv este 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 25.09.2020 </td>
<td class="laji"> este </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/fi.png"> &nbsp; <a href="http://www.auburnestate.altervista.org/cup2020-3.html">Auburn Estate</a> </td>
<td class="info"> Syyspäiväntasaus <small>(Kalla CUP:n 3. osakilpailu)</small> </td>
<td class="luokka"> 120cm </td>
<td class="tulos"> 12/32 </td>
<td class="virheet"> 0-4 vp </td>
</tr></table></div>




<div class="filterDiv koulu 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 26.09.2020 </td>
<td class="laji"> koulu </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/fi.png"> &nbsp; <a href="http://www.auburnestate.altervista.org/cup2020-3.html">Auburn Estate</a> </td>
<td class="info"> Syyspäiväntasaus <small>(Kalla CUP:n 3. osakilpailu)</small> </td>
<td class="luokka"> Helppo A </td>
<td class="tulos"> 23/33 </td>
<td class="virheet"> 63.441 % </td>
</tr></table></div>

<div class="filterDiv koulu 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 26.09.2020 </td>
<td class="laji"> koulu </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/fi.png"> &nbsp; <a href="http://www.auburnestate.altervista.org/cup2020-3.html">Auburn Estate</a> </td>
<td class="info"> Syyspäiväntasaus <small>(Kalla CUP:n 3. osakilpailu)</small> </td>
<td class="luokka"> Vaativa B </td>
<td class="tulos"> 11/21 </td>
<td class="virheet"> 62.870 % </td>
</tr></table></div>




<div class="filterDiv este 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 29.10.2020 </td>
<td class="laji"> este </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/fi.png"> &nbsp; <a href="https://ansamaa.altervista.org/harvestmoon20/show.html">Ansamaa</a> </td>
<td class="info"> Harvest Moon Show </td>
<td class="luokka"> 120cm </td>
<td class="tulos"> 6/8 </td>
<td class="virheet"> 4 vp </td>
</tr></table></div>

<div class="filterDiv koulu 2020"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 29.10.2020 </td>
<td class="laji"> koulu </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/fi.png"> &nbsp; <a href="https://ansamaa.altervista.org/harvestmoon20/show.html">Ansamaa</a> </td>
<td class="info"> Harvest Moon Show </td>
<td class="luokka"> Helppo A </td>
<td class="tulos"> <b>1/7</b> </td>
<td class="virheet"> 68.707 % </td>
</tr></table></div>

<div class="filterDiv este 2026"><table class="kisat"><tr class="kilpailutulos">
<td class="pvm"> 23.06.2025 </td>
<td class="laji"> este </td>
<td class="paikka"> <img src="https://lianna.altervista.org/flag/fi.png"> &nbsp; <a href="https://erikantalli.weebly.com/summertime-shenanigans.html">Erikan talli</a> </td>
<td class="info"> Summer-Shenanigans </td>
<td class="luokka"> 120cm </td>
<td class="tulos"> / </td>
<td class="virheet">  vp </td>
</tr></table></div>


</div>



<p class="ruusukkeet">

<img src="hanamimuut.png">
<img src="hanami4.png">
<img src="seppelecup2.png">
<img src="harvestmoonshow1.png">

</p>





<!-- <h2>Perinteisten kilpailusijoitukset</h2>

ERJ
07.06.2020 Cloudfield, 100cm : 3/50 <br>
11.06.2020 Cloudfield, 100cm : 2/50 <br>
12.06.2020 Cloudfield, 100cm : 6/50 <br>
17.06.2020 Cloudfield, 100cm : 4/50 <br>
24.05.2020 Kuuran Suomenratsut, 120cm : 5/40 <br>
24.05.2020 Kuuran Suomenratsut, 120cm : <b>1/40</b> <br>
25.05.2020 Kuuran Suomenratsut, 120cm : <b>1/40</b> <br>
30.05.2020 Kuuran Suomenratsut, 120cm : 4/40 <br>

KRJ
24.05.2020 Syyn kartano, Helppo A : 3/40<br>
24.05.2020 Syyn kartano, Vaativa B : <b>1/40</b><br>
30.05.2020 Syyn kartano, Helppo A : <b>1/40</b><br>
01.06.2020 Syyn kartano, Helppo A : 5/40<br>
02.06.2020 Syyn kartano, Vaativa B : <b>1/40</b><br>
04.06.2020 Syyn kartano, Helppo A : 2/40<br>
05.06.2020 Syyn kartano, Vaativa B : 2/40<br>
09.06.2020 Cloudfield, Helppo A : 4/50 <br>
13.06.2020 Cloudfield, Helppo A : <b>1/50</b> <br>
18.06.2020 Cloudfield, Helppo A : 4/50 <br>
19.06.2020 Cloudfield, Helppo A : 6/50 <br>
01.06.2020 Harjukosken tila, Vaativa B : 5/40 <br>
06.06.2020 Harjukosken tila, Vaativa B : 2/40 <br>
11.06.2020 Harjukosken tila, Vaativa B : 5/40 <br>
21.06.2020 Harjukosken tila, Vaativa B : <b>1/40</b> <br>
24.06.2020 Harjukosken tila, Vaativa B : 2/40 <br>
26.06.2020 Harjukosken tila, Vaativa B : 3/40 <br>
27.06.2020 Harjukosken tila, Vaativa B : <b>1/40</b> <br>
01.06.2020 Silverlode, Vaativa B : 3/40 <br>
06.06.2020 Silverlode, Vaativa B : 2/40 <br>
12.06.2020 Silverlode, Vaativa B : <b>1/40</b> <br>
13.06.2020 Silverlode, Vaativa B : 6/40 <br>
16.06.2020 Silverlode, Vaativa B : 6/40 <br>
17.06.2020 Silverlode, Vaativa B : 2/40 <br>
18.06.2020 Silverlode, Vaativa B : 3/40 <br>
19.06.2020 Silverlode, Vaativa B : 5/40 <br>
23.06.2020 Silverlode, Vaativa B : 3/40 <br>
25.06.2020 Silverlode, Vaativa B : 4/40 <br>
27.06.2020 Silverlode, Vaativa B : 4/40 <br>
28.06.2020 Silverlode, Vaativa B : 5/40 <br>
-->



	</div> <!-- <div id="view4">



<h2>Saavutukset</h2>

<center><table class="meriittitaulukko"><tr><td width="70%" valign="top">


<p><font class="merkki"><i class="fas fa-award"></i></font>&nbsp;<b> PKK:n 137. kantakirjatilaisuus <a href="">01.12.2025</a> &#10170; KTK I</b> <br>
- Kommentit </p>


	</td> <td valign="top">


<p>
<font class="merkki"><i class="fas fa-award"></i></font>&nbsp; <b>Näyttelyarvonimet</b><br>
VIP MVA myönnetty 12.03.2026 <br>
Fn myönnetty 12.03.2026 <br>
</p>


	</td></tr></table></center>



	</div> --> <div id="view5">



	<table width="100%"><tr><td><table class="pkk-kisat"><tr><td>


<a href="rakenne.jpg" rel="lightbox"><img src="rakenne.jpg" class="pkk-img"></a><br><small>
Taku 8-vuotiaana | &copy; VRL-10864

</small><br><div><div class="nayttelytulokset"><span onClick="if (this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display != '') {  this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = ''; this.innerHTML = '<a href=\'#\' onClick=\'return false;\'>Sulje </a>'; } else { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = 'none'; this.innerHTML = '<a href=\'#\' onClick=\'return false;\'>Näyttelytulokset</a>'; }" />

<a href="#" onClick="return false;">Näyttelytulokset</a></div><div class="quotecontent"><div style="display: none;">

<p>31.12.2021 <a href="https://jodochus.net/roihaja/pkk2.html">Roihaja</a> <br>
Rakenne: Kylmäveriset <br>
<b>KTK-sert</b> <br>
tuom. jodochus </p>

<p>16.06.2020 <a href="http://unisiiven.net/hukkapiilo/pkk/PKK5.php">Hukkapiilo</a> <br>
Rakenne:  <br>
<b>EO-sert, EM</b> <br>
tuom. Lissu T. </p>

<p>14.06.2020 <a href="http://unisiiven.net/hukkapiilo/pkk/PKK4.php">Hukkapiilo</a> <br>
Rakenne:  <br>
<b>EO-sert</b> <br>
tuom. Arnika </p>

<p>29.05.2020 <a href="https://piirroshepat.proboards.com/thread/207/">Encore</a> <br>
Rakenne: Kylmäveriset orit <br>
<b>EO-sert, EM</b> Upeus <br>
tuom. Kics </p>

	</div></div></div> </td></tr></table>



	</td></tr></table>










	</div> </div>









	</div>

	<div id="footer"><div id="footer_wrapper">

	<div class="footer_center">

<p>&copy; Krekilänkulma, ellei toisin mainita &nbsp; / &nbsp; Ulkoasun &copy; Pihlajainen &nbsp; / &nbsp; Ulkoasun muokkaus &copy; VRL-05175 &nbsp; / &nbsp; virtuaalihevonen &nbsp; / &nbsp; a sim-game horse</p>

	</div></div></div>





<script>
filterSelection("all")
function filterSelection(c) {
  var x, i;
  x = document.getElementsByClassName("filterDiv");
  if (c == "all") c = "";
  for (i = 0; i < x.length; i++) {
    w3RemoveClass(x[i], "show");
    if (x[i].className.indexOf(c) > -1) w3AddClass(x[i], "show");
  }
}

function w3AddClass(element, name) {
  var i, arr1, arr2;
  arr1 = element.className.split(" ");
  arr2 = name.split(" ");
  for (i = 0; i < arr2.length; i++) {
    if (arr1.indexOf(arr2[i]) == -1) {element.className += " " + arr2[i];}
  }
}

function w3RemoveClass(element, name) {
  var i, arr1, arr2;
  arr1 = element.className.split(" ");
  arr2 = name.split(" ");
  for (i = 0; i < arr2.length; i++) {
    while (arr1.indexOf(arr2[i]) > -1) {
      arr1.splice(arr1.indexOf(arr2[i]), 1);     
    }
  }
  element.className = arr1.join(" ");
}

// Add active class to the current button (highlight it)
var btnContainer = document.getElementById("myBtnContainer");
var btns = btnContainer.getElementsByClassName("btn");
for (var i = 0; i < btns.length; i++) {
  btns[i].addEventListener("click", function(){
    var current = document.getElementsByClassName("active");
    current[0].className = current[0].className.replace(" active", "");
    this.className += " active";
  });
}
</script>



</body>
</html>