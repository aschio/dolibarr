/*
Script for CIAI MODULE
Aschieri Claudio 
Diciannove Soc. Coop
c.aschieri@19.coop
2016
*/

/* REDIRECT SCRIPT FOR SOCIETE */
jQuery(window).load(function() {
	
	// prendo tutti i link e aggiungo il canvas dove mi interessa
	jQuery("a").each(function() {
		
		var url =  this.href;
		
		if ((url.search('/societe/soc.php') > 0) && (url.search('canvas=societe@ciai') == -1))  {
			this.href = url + "&canvas=societe@ciai";
		}
		
		if ((url.search('/comm/card.php') > 0) && (url.search('canvas=societe@ciai') == -1))  {
			this.href = url + "&canvas=societe@ciai";
		}
	})
});