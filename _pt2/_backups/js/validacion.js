function ahora() {

	var dateString = "";

	var newDate = new Date();



	dateString += newDate.getDate() + "/";

	dateString += (newDate.getMonth() + 1) + "/";

	dateString += newDate.getFullYear();

	return dateString;

}



function gui_option_leevalor(opt_nombre) {

	var bencontrado = false;

	var i=0;

	

	// Recorremos todos los valores del radio button para encontrar el seleccionado

    while (i<opt_nombre.length && !bencontrado) {

    	if (opt_nombre[i].checked) {

    		bencontrado = true;

    	} else {

    		i++;

    	}

    }

	

	if (bencontrado) {

		return opt_nombre[i].value;

	} else {

		return "";

    }

}



function fecha_valida(dtfecha){

	var mfecha = dtfecha.toString().split("-");

	

	if (mfecha.length != 3) {

		

		return false;

	}

	 

	var dia = mfecha[2];

	var mes = mfecha[1];

	var ano = mfecha[0];

	

 	var plantilla = new Date(ano, mes - 1, dia);//mes empieza de cero Enero = 0

 	

 	if(!plantilla || plantilla.getFullYear() == ano && plantilla.getMonth() == mes -1 && plantilla.getDate() == dia){

 		return true;

 	}else{

 		return false;

 	}

}


function hora_valida(dtfecha){

	var mfecha = dtfecha.toString().split(":");
	

	if (mfecha.length != 2) {

		return false;

	}
	 
	if (parseInt(arrHora[0])<0 || parseInt(arrHora[0])>23) {

        return false;

    }



    if (parseInt(arrHora[1])<0 || parseInt(arrHora[1])>59) {

        return false;

    }

    return true;

}


function dateadd(periodo, num, fecha) {

	var mfec1;

	var dateString="";

	var fec1;

	var ano;

	var mes;

	var dia;



	mfec1 = fecha.toString().split("/");

	fec1 = new Date(mfec1[2], mfec1[1]-1, mfec1[0]);

	switch(periodo) {

		case "d": fec1 = new Date(fec1.getTime() + num*24*60*60*1000); break;

	}

	dateString = fec1.getDate()+'/'+(fec1.getMonth()+1)+'/'+fec1.getFullYear();

	return dateString;

}



function datediff(periodo, fecha_ini, fecha_fin) {

	var diferencia = 0;

	var mfec1;

	var mfec2;

	var fec1;

	var fec2;



	mfec1 = fecha_ini.toString().split("/");

	mfec2 = fecha_fin.toString().split("/");



	fec1 = new Date(mfec1[2], mfec1[1]-1, mfec1[0]);

	fec2 = new Date(mfec2[2], mfec2[1]-1, mfec2[0]);

	

	diferencia = fec2.getTime() - fec1.getTime();

	

	switch (periodo) {

		case 'd':

			diferencia = Math.floor(diferencia / (1000 * 60 * 60 * 24));

			break;

		case 'h':

			diferencia = Math.floor(diferencia / (1000 * 60 * 60));

			break;

		case 'n':

			diferencia = Math.floor(diferencia / (1000 * 60));

			break;

	}

	//diferencia = 3;

	return diferencia;

}



function cambia_punto_por_coma() {

/*  var tecla;

	

	tecla = (document.all) ? event.keyCode : event.which; // Si es IE e.keyCode sino e.which



	if(tecla == 190 || tecla == 110) {

		//this.value += ",";

		//return false;

	} else {

		return true;

	}

	*/

}

	

function validar_entero() {

    var tecla;

    var te;

    var patron;



    tecla = (document.all) ? event.keyCode : event.which; // Si es IE e.keyCode sino e.which

    if (tecla==8) return true; // Retroceso

    patron = /[0-9]/;

    te = String.fromCharCode(tecla);

    return patron.test(te);



} 



function validar_decimal() {

    var tecla;

    var e;

    var te;

    var patron;



	e = event;

    tecla = (document.all) ? e.keyCode : e.which; // Si es IE e.keyCode sino e.which

    if (tecla==8) return true; // Retroceso

    patron = /[0-9,.]/;

    te = String.fromCharCode(tecla);

    

    return patron.test(te);

} 



function validar_hora() {

    var tecla;

    var e;

    var te;

    var patron;



	e = event;

    tecla = (document.all) ? e.keyCode : e.which; // Si es IE e.keyCode sino e.which

    if (tecla==8) return true; // Retroceso

    patron = /[0-9:]/;

    te = String.fromCharCode(tecla);

    

    return patron.test(te);

} 





function validar_campos_input() {

	var camposInput = document.getElementsByTagName("input");

 	

 	for(var i=0; i<camposInput.length; i++) {

   		if(camposInput[i].className.indexOf("decimal") > -1) {

      		camposInput[i].onkeydown = cambia_punto_por_coma;

      		camposInput[i].onkeypress = validar_decimal;

    	} else if (camposInput[i].className.indexOf("entero") > -1) {

    		camposInput[i].onkeypress = validar_entero;

    	} else if (camposInput[i].className.indexOf("hora") > -1 ) {

    		camposInput[i].onkeypress = validar_hora;

    	}

  	}

}



var quitar_tildes = (function() {

  var from = "ÃÀÁÄÂÈÉËÊÌÍÏÎÒÓÖÔÙÚÜÛãàáäâèéëêìíïîòóöôùúüûÑñÇç", 

      to   = "AAAAAEEEEIIIIOOOOUUUUaaaaaeeeeiiiioooouuuunncc",

      mapping = {};

 

  for(var i = 0, j = from.length; i < j; i++ )

      mapping[ from.charAt( i ) ] = to.charAt( i );

 

  return function( str ) {

      var ret = [];

      for( var i = 0, j = str.length; i < j; i++ ) {

          var c = str.charAt( i );

          if( mapping.hasOwnProperty( str.charAt( i ) ) )

              ret.push( mapping[ c ] );

          else

              ret.push( c );

      }      

      return ret.join( '' );

  }

 

})();