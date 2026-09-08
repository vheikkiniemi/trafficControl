// Vaatii toimiakseen jQuery-kirjaston
// https://api.jquery.com/category/ajax/

// Jos halutaan käyttää koko Tapahtuma-taulun päivittämistä
function tapahtumat(e) {
	// Tyhjennetaan virheet
	document.getElementById('status').style.display = 'none';
	$.ajax({
		async: true,
		url: "haekanta.php",
		processData: true,
		error: virhe,
		success: function(data, textStatus, request){
			$( "#tapahtumat" ).empty();
			$( "#tapahtumat" ).append(data);
		}
	});
	setTimeout("tapahtumat()" ,2000);
}

// Ohjauskäskyn välittävä funktio
function ohjaus(key1, key2) {
	// Tyhjennetaan virheet
	document.getElementById('status').style.display = 'none';
	$("#status").html("");
	$.ajax({ 
		url: "ohjaus.php",
		processData: true,
		error: virhe,
		type: "POST",
		data: { hash:key1, oKey:key2 },
		success: function(data, textStatus, request){ }
	});
}

// Haetaan yksi tapahtuma kerrallaan
function tapahtuma(key1) {
	// Tyhjennetaan virheet
	document.getElementById('status').style.display = 'none';
	$("#status").html("");
	$.ajax({ 
		url: "haekanta.php",
		processData: true,
		error: virhe,
		type: "GET",
		data: { 'rowId': key1 },
		dataType: "text",
		success: function(data, textStatus, request){ }
	});
}

// virheenkäsittelyfunktio
function virhe(event, jqxhr, settings, exception) {
	document.getElementById('status').style.display = 'block';
	$( "#status" ).append( "Ajax-virhe: " + exception );
}

window.onload = function() {
	// Määritellään SSE tapahtuma > Oletus että tuki on
	if (!!window.EventSource) {
	var source = new EventSource("add.php");
		source.addEventListener("message", function(e) {
			i = parseInt($('#tapahtumat_tb tr').eq(1).attr('id'));
			if (e.data != i) {
				$('#tapahtumat_tb tr:first').after(e.data);
			}
			delete i;
		}, false);
	}
	// Oletuksena piilotetaan virhe-lohko
	document.getElementById('status').style.display = 'none';
	/* Tällä voidaan päivittää Tapahtuma-taulu automaattisesti
	tapahtumat();
	*/
	/* Jos halutaan kytkeä onlogin diviin tapahtumia
	$('#onlogin')
		.click(function () {
			ohjaus();
		})
		.mouseover(function() {
			$(this).addClass("ohjaus_mouse_over");
		})
		.mouseout(function() {
			$(this).removeClass("ohjaus_mouse_over");
		});*/
	//$('tr:not(.Muutos)').hide();
	$('tr.Info').hide();

	$('tr.Muutos').click(function() {
		$(this).find('span').text(function(_, value) {
			return value == '-' ? '+' : '-'
		});
		$(this).nextUntil('tr.Muutos').slideToggle(100, function() {});
	});
}
