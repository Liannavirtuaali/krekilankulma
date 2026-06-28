<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<title>Vallanveikee</title>
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

	<div id="header"><h1 class="inset-text">Vallanveikee</h1></div>

	<div id="wrapper"><div id="content_hevonen">
<ul class="tabs">
	<li class="selected"><a href="#view1">Perustiedot & luonne</a></li>
	<li><a href="#view2">Suku</a></li>
	<li><a href="#view3">Kilpailut</a></li>
	<!-- <li><a href="#view4">Saavutukset</a></li>
	<li><a href="#view5">Kuvagalleria & näyttelyt</a></li> -->
	<li><a href="">Päiväkirja</a></li>
</ul>

	<div class="tabcontents"><div id="view1">



<img src="tietokuva.png" style="height: 385px; margin-right: 30px;" align="right" title="&copy; Tea">

<p class="tiedot">Kutsumanimeltään <u>Veeti</u><br>
Vaapeanpunarautias <span class="pikkuinfo">(ee/aa)</span> suomenhevonen ori, 161cm <br>
Syntynyt 10.07.2021, on nyt 9-vuotias <br>
<a href="http://www.virtuaalihevoset.net/virtuaalihevoset/hevonen/VH26-018-0281">VH26-018-0281</a>, <a href="https://piirroshevosille.fi/hevoset/hevonen/PKK">PKK</a>
</p>
<p class="tiedot">
Kasvattanut Anniina Jokinen <img src="https://lianna.altervista.org/flag/fi.png"> <br>
Omistaa <a href="">Tomás Reyes</a> / <a href="">Krekilänkulma</a> <span class="pikkuinfo">(VRL-05175, <a href="mailto:liannavirtuaali@gmail.com">&#9993;</a>)</span>
</p>
<p class="tiedot">
Yleispainotteinen <br>
ko: Helppo A, re: 120cm, CIC1
</p>
<p class="tiedot">
Kilpaillut porrastetuissa kenttäkilpailuissa <br>
<?php 
$vh = 'VH26-018-0281';
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

<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eget mollis felis. Nullam sodales, nulla in lacinia ultricies, ex dui consectetur augue, vel varius ex nunc in mi. Cras tempus metus risus, eu sagittis enim accumsan sed. Nulla blandit sed felis sit amet ornare. Quisque ac turpis in sapien dapibus accumsan quis ac tellus. Nam venenatis dapibus tortor, sed tincidunt turpis. Cras lacinia porta lacus in pellentesque. </p>

<p>Suspendisse feugiat hendrerit quam dignissim aliquet. In mattis, est sit amet fermentum sollicitudin, metus lorem convallis mi, sed dictum risus mauris non eros. Curabitur efficitur euismod neque quis rutrum. Maecenas placerat, nisi ac sollicitudin finibus, ante dui dictum nulla, sed imperdiet sem magna sit amet arcu. Quisque eu condimentum ligula. Cras lacus augue, scelerisque quis elementum id, tempor sed sem. Maecenas ullamcorper nec justo id viverra. Donec maximus ut tellus eget pharetra. Nunc feugiat, lacus eu molestie vestibulum, mauris sapien elementum mi, blandit mattis sem justo id ex. </p>

<p>Nunc ac sem vel augue euismod accumsan. Integer urna risus, molestie sed porttitor non, rutrum et urna. Curabitur dapibus mattis ex eu blandit. Nullam et sapien vitae magna dignissim bibendum. Aliquam pharetra egestas felis, vestibulum mollis leo dapibus et. Sed vehicula, neque at semper fermentum, lectus enim hendrerit dui, eu gravida ligula nisl sit amet est. Morbi aliquam, lorem ut aliquam semper, nibh est condimentum mi, eu sagittis nisl urna quis purus. Morbi malesuada egestas semper. Donec quis porttitor dui. Integer diam justo, pulvinar a odio sit amet, ultrices consectetur quam. Integer sed vehicula elit. Phasellus placerat iaculis erat. Suspendisse mattis aliquet libero sed pellentesque. Aliquam nec consequat ante, et sodales justo. Nam accumsan nibh felis, et auctor tortor consectetur quis. Fusce at nunc risus. </p> -->


	</div><div id="view2">



	<table class="sukutaulu">

	<tr><td width="28%" rowspan="4">

i. Hevosen nimi <br><small>
sh, tprt, 163cm

	</small></td><td width="8%" rowspan="4"><div class="isohaka">{</div></td><td width="28%" rowspan="2">

ii. Hevosen nimi <br><small>
sh, prn, 165cm

	</small></td><td width="8%" rowspan="2"><div class="pikkuhaka">{</div></td><td width="28%">

iii. Hevosen nimi <small>
[sh, mkm, 166cm]

	</small></td></tr><tr><td>

iie. Hevosen nimi <small>
[sh, prt, 162cm]

	</small></td></tr><tr><td rowspan="2">

ie. Hevosen nimi <br><small>
sh, trn, 159cm

	</small></td><td rowspan="2"><div class="pikkuhaka">{</div></td><td>

iei. Hevosen nimi <small>
[sh, tprn, 162cm]

	</small></td></tr><tr><td>

iee. Hevosen nimi <small>
[sh, rtkm, 156cm]

	</small></td></tr>




	<tr><td width="28%" rowspan="4">

e. Hevosen nimi <br><small>
sh, vrt, 156cm

	</small></td><td width="8%" rowspan="4"><div class="isohaka">{</div></td><td width="28%" rowspan="2">

ei. Hevosen nimi <br><small>
sh, rtkm, 158cm

	</small></td><td width="8%" rowspan="2"><div class="pikkuhaka">{</div></td><td width="28%">

eii. Hevosen nimi <small>
[sh, rnkm, 161cm]

	</small></td></tr><tr><td>

eie. Hevosen nimi <small>
[sh, rt, 157cm]

	</small></td></tr><tr><td rowspan="2">

ee. Hevosen nimi <br><small>
sh, vprt, 154cm

	</small></td><td rowspan="2"><div class="pikkuhaka">{</div></td><td>

eei. Hevosen nimi <small>
[sh, prn, 158cm]

	</small></td></tr><tr><td>

eee. Hevosen nimi <small>
[sh, vkk, 149cm]

	</small></td></tr>
</table>





<!-- <div><div style="text-transform: none; border-bottom: 0px; margin-top: 20px; margin-bottom: 0px; margin-left: 0px; font-weight: ; display: block;">
<span onClick="if (this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display != '') {  this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = ''; this.innerHTML = '<a href=\'#\' onClick=\'return false;\'>Sulje </a>'; } else { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = 'none'; this.innerHTML = '<a href=\'#\' onClick=\'return false;\'>Lue sukuselvitys</a>'; }" />

<a href="#" onClick="return false;">Lue sukuselvitys</a></div><div class="quotecontent"><div style="display: none;">


<p>i. <b></b> on... </p>

<ul><p>ii. <b></b> on... </p>

<p>ie. <b></b> on... </p></ul>

<p>e. <b></b> on... </p>

<ul><p>ei. <b></b> on... </p>

<p>ee. <b></b> on... </p></ul>



</div></div></div> -->



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



<!-- <p class="ruusukkeet">

<img src="sunsettidesmuut.png">
<img src="pj2.png">

</p> -->



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



	</div> --> <!-- <div id="view5">



	<table width="100%"><tr><td><table class="pkk-kisat"><tr><td>


<a href="rakenne.jpg" rel="lightbox"><img src="rakenne.jpg" class="pkk-img"></a><br><small>
Taku 9-vuotiaana | &copy; VRL-10864

</small><br><div><div class="nayttelytulokset"><span onClick="if (this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display != '') {  this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = ''; this.innerHTML = '<a href=\'#\' onClick=\'return false;\'>Sulje </a>'; } else { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = 'none'; this.innerHTML = '<a href=\'#\' onClick=\'return false;\'>Näyttelytulokset</a>'; }" />

<a href="#" onClick="return false;">Näyttelytulokset</a></div><div class="quotecontent"><div style="display: none;">

<p>18.11.2025 <a href="https://lianna.altervista.org/norling/toiminta/PKK4.php">Norling Ridning</a> <br>
Rakenne: Tammat <br>
<b>KTK-sert</b> <br>
tuom. Khaos </p>

	</div></div></div> </td></tr></table>



	</td></tr></table>










	</div> --> </div>









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