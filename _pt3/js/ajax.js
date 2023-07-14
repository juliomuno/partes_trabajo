function popWindow(page,browser,h,w) {
    winW = (screen.width - w) / 2
    winH = (screen.height - h) / 2    
    window.open(page,browser, "scrollbars=0,titlebar=0,menubar=0,toolbar=0,location=0,directories=0,statusbar=0,resizable=0,width=" + w + ",height=" + h + ",left=" + winW + ",top=" + winH);
}

function newWindow(page, titulo) {
  window.open(page,titulo,"");
}

var tagScript = '(?:<script.*?>)((\n|\r|.)*?)(?:<\/script>)';
String.prototype.evalScript = function() {
    return (this.match(new RegExp(tagScript, 'img')) || []).evalScript();
};

String.prototype.stripScript = function() {
    return this.replace(new RegExp(tagScript, 'img'), '');
};

String.prototype.extractScript = function(){
    var matchAll = new RegExp(tagScript, 'img');
    return (this.match(matchAll) || []);
};

Array.prototype.evalScript = function(extracted) {
    var s=this.map(function(sr){
         var sc=(sr.match(new RegExp(tagScript, 'im')) || ['', ''])[1];
        if (sc!="") {
        if(window.execScript){
            window.execScript(sc);
         }
        else
         {
           window.setTimeout(sc,0);
        }
        }
    });
    return true;
};

Array.prototype.map = function(fun) {
    if(typeof fun!=="function"){return false;}
    var i = 0, l = this.length;
    for(i=0;i<l;i++)
    {
        fun(this[i]);
    }
    return true;
};  

//function $(id){return document.getElementById(id);}

function http(){
  if(window.XMLHttpRequest){
    return new XMLHttpRequest();  
  }else{
    try{
      return new ActiveXObject('Microsoft.XMLHTTP');
    }catch(e){
      alert('nop');
          return false;
    } 
  }
}

String.prototype.tratarResponseText=function(){
  var pat=/<script[^>]*>([\S\s]*?)<\/script[^>]*>/ig;
  var pat2=/\b\s+src=[^>\s]+\b/g;
  var elementos = this.match(pat) || [];
  for(i=0;i<elementos.length;i++) {
    var nuevoScript = document.createElement('script');
    nuevoScript.type = 'text/javascript';
    var tienesrc=elementos[i].match(pat2) || [];
    if(tienesrc.length){
      nuevoScript.src=tienesrc[0].split("'").join('').split('"').join('').split('src=').join('').split(' ').join('');
    }else{
      var elemento = elementos[i].replace(pat,'$1','');
      nuevoScript.text = elemento;
    }
    document.getElementsByTagName('body')[0].appendChild(nuevoScript);
  }
  return this.replace(pat,'');
}

function SetContainerHTML(id_contenedor,responseText){
  var mydiv = $(id_contenedor);
  mydiv.innerHTML = responseText.tratarResponseText();
}

function cargarPagina(url,contenedorId){
  var H=new http();
  H.open('get',url+'?'+Math.random(),true);
  H.onreadystatechange=function(){
    if(H.readyState==4){
      SetContainerHTML(contenedorId,H.responseText);
      H.onreadystatechange=null;
    }else{
      $(contenedorId).innerHTML='cargando...';
    }
  }
  H.send(null);
}



//1.- Primero se crea el objeto Ajax, instanciando el objeto XMLHttpRequest para los distintos navegadores

function ajaxFunction() {
  var xmlHttp
  try {
  // Firefox, Opera 8.0+, Safari
  xmlHttp=new XMLHttpRequest()
  return xmlHttp
  } catch (e) {
  // Internet Explorer
    try {
      xmlHttp=new ActiveXObject("Msxml2.XMLHTTP")
      return xmlHttp
    } catch (e) {
      try {
        xmlHttp=new ActiveXObject("Microsoft.XMLHTTP")
        return xmlHttp
      } catch (e) {
    alert("Tu navegador no soporta AJAX!")
  return false
      }   
    } 
  }
}

//2.-Se crea la función para llamar a la página de manera asíncrona y cargarla en la capa que le indiquemos
function cargar(_pagina,capa) {
  var ajax;

  ajax = ajaxFunction();

  ajax.open("POST", _pagina, true);
  ajax.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
  
  ajax.onreadystatechange = function()
  {
    if (ajax.readyState == 4)
    {
      if (ajax.status==200)
      {
          var scs=ajax.responseText.extractScript();    //capturamos los scripts
            if (document.getElementById(capa))  {
              scs.evalScript();   
              document.getElementById(capa).innerHTML=ajax.responseText.stripScript();    //eliminamos los scripts... ya son innecesarios
              //document.getElementById(capa).innerHTML = ajax.responseText;
            }
      }
    }
  }
  ajax.send(null)
}
