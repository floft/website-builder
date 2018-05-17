<!--
function OpenPage(address)
{
	window.open(address,'','menubar=no,status=no,location=no,toolbar=no,resizable=yes,scrollbars=yes');
}

window.onload=montre;

function montre(id)
{
var d = document.getElementById(id);
	for (var i = 1; i<=15; i++)
	{
		if (document.getElementById('smenu'+i)) {document.getElementById('smenu'+i).style.display='none';}
	}

	if (d) {d.style.display='block';d.style.visibility='visible';}
}

document.write('<div id="loading">Loading...</div>');

// Created by: Simon Willison | http://simon.incutio.com/
function addLoadEvent(func) {
  var oldonload = window.onload;
  if (typeof window.onload != 'function') {
	window.onload = func;
  } else {
	window.onload = function() {
	  if (oldonload) {
		oldonload();
	  }
	  func();
	}
  }
}

addLoadEvent(function() {
  document.getElementById("loading").style.display="none";
});

//for image shadow (from: Dynamic Drive)

var gradientshadow={}
gradientshadow.depth=6 //Depth of shadow in pixels
gradientshadow.containers=[]

gradientshadow.create=function(){
var a = document.all ? document.all : document.getElementsByTagName('*')
for (var i = 0;i < a.length;i++) {
	if (a[i].className == "shadow" || a[i].className == "pic" || a[i].className == "piclink") {
		for (var x=0; x<gradientshadow.depth; x++){
			var newSd = document.createElement("DIV")
			newSd.className = "shadow_inner"
			newSd.id="shadow"+gradientshadow.containers.length+"_"+x //Each shadow DIV has an id of "shadowL_X" (L=index of target element, X=index of shadow (depth)
			if (a[i].getAttribute("rel"))
				newSd.style.background = a[i].getAttribute("rel")
			else
				newSd.style.background = "gray" //default shadow color if none specified
			document.body.appendChild(newSd)
		}
	gradientshadow.containers[gradientshadow.containers.length]=a[i]
	}
}
gradientshadow.position()
window.onresize=function(){
	gradientshadow.position()
}
}

gradientshadow.position=function(){
if (gradientshadow.containers.length>0){
	for (var i=0; i<gradientshadow.containers.length; i++){
		for (var x=0; x<gradientshadow.depth; x++){
  		var shadowdiv=document.getElementById("shadow"+i+"_"+x)
			shadowdiv.style.width = gradientshadow.containers[i].offsetWidth + "px"
			shadowdiv.style.height = gradientshadow.containers[i].offsetHeight + "px"
			shadowdiv.style.left = gradientshadow.containers[i].offsetLeft + x + "px"
			shadowdiv.style.top = gradientshadow.containers[i].offsetTop + x + "px"
		}
	}
}
}

if (window.addEventListener)
window.addEventListener("load", gradientshadow.create, false)
else if (window.attachEvent)
window.attachEvent("onload", gradientshadow.create)
else if (document.getElementById)
window.onload=gradientshadow.create
//-->