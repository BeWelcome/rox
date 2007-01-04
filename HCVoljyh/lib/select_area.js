function change_country() {
	document.forms["signup"].elements["IdRegion"].value="0" ;
	document.forms["signup"].elements["IdCity"].value="0" ;
	document.forms["signup"].elements["action"].value="change_country" ;
	document.forms[0].submit() ;
//	document.write("c'est ici") ; ;
}	


function change_region() {
	document.forms["signup"].elements["IdCity"].value="0" ;
	document.forms["signup"].elements["action"].value="change_region" ;
	document.signup.submit() ;
}

function change_city() {
	document.forms["signup"].elements["action"].value="change_city" ;
	document.signup.submit() ;
}		
