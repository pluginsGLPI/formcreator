function Suite(lien){
	
	var objet = document.getElementById("cat_"+lien); // entre les deux ' tu mes le nom du div que tu veux faire apparaître !
	
	if (objet.style.display == "none"){
		objet.style.display = "";
		document.getElementById("bas_"+lien).style.display = "none";
		document.getElementById("haut_"+lien).style.display = "";
	} else {
		objet.style.display = "none";
		document.getElementById("bas_"+lien).style.display = "";
		document.getElementById("haut_"+lien).style.display = "none";
	}
}

function verif(listequestion) {
	if (listequestion.length != 0) {
		var listemauvaisereponse = "";
		var cpt = 0;
		
		var listequestion1 = listequestion.split('&&');
		for (var i = 0; i < listequestion1.length; i++){
			var listequestion2 = listequestion1[i].split('::');
			if ((document.getElementById(listequestion2[0]).className != "hidden") && (document.getElementById(listequestion2[1]).className != "hidden")){
				var reg = new RegExp(listequestion2[2]);
				if (!reg.test(document.getElementById("question_" + listequestion2[1]).value)) {
					listemauvaisereponse = listemauvaisereponse + '\n- ' + listequestion2[3];
					surligne(document.getElementById("question_" + listequestion2[1]), true);
					cpt++;
				}
			}
		}	
		
		if (cpt == 0) {
			document.getElementById("form_ticket").submit();
		} else if (cpt == 1){
			alert('La question suivante est mal renseignée :' + listemauvaisereponse);
		} else {
			alert('Les questions suivantes sont mal renseignées :' + listemauvaisereponse);
		}
	} else {
		document.getElementById("form_ticket").submit();
	}
}

function changeNbValue(newValue) {

    document.getElementById('nbValue').value = newValue;
    return true;
}

function changeNbValueQuestion(value,newValue) {

    document.getElementById('nbValue' + value).value = newValue;
    return true;
}

function afficher_cacher(identifiant) {
    if (typeof identifiant === "string")
    {
        if (document.getElementById(identifiant).className == "hidden") {
            document.getElementById(identifiant).className = "";
        } else {
            document.getElementById(identifiant).className = "hidden";
        }
    }
    else
    {
        for (var i = 0; i < identifiant.length; i++){
            if (document.getElementById(identifiant[i]).className == "hidden") {
                document.getElementById(identifiant[i]).className = "";
            } else {
                document.getElementById(identifiant[i]).className = "hidden";
            }
        }
    }
}

function cacher(identifiant){
    for (var i = 0; i < identifiant.length; i++){
		if (document.getElementById(identifiant[i])) {
			document.getElementById(identifiant[i]).className = "hidden";
		}
    }
}

function afficher(identifiant){
    for (var i = 0; i < identifiant.length; i++){
		if (document.getElementById(identifiant[i])) {
			document.getElementById(identifiant[i]).className = "";
		}
    }
}

function chargement(tableau, affiche) {
    if (tableau.length != 0) {
        var liste1 = tableau.split(':');
        cacher(liste1);
    }
    if (affiche.length != 0) {
        var liste2 = affiche.split(':');
        afficher(liste2);
    }
}

function choixSelectDyna(tableau) {
    var result = tableau.indexOf('&&');
    if (result > -1) {
        var val = tableau.split('&&');
        var listing = val[1].split(':');
        cacher(listing);
        var tab = val[2];
    }
    else {
        var tab = tableau;
    }
    if (tab != null) {
        var listeQuestion = tab.split(':');
        afficher_cacher(listeQuestion);
    }
}

function verifText(champ)
{
    var regex = /[a-zA-Z]|\s/;
    if(!regex.test(champ.value) || champ.value.length < 2)
    {
        surligne(champ, true);
        return false;
    }
    else
    {
        surligne(champ, false);
        return true;
    }
}

function surligne(champ, erreur)
{
    if(erreur)
        champ.style.backgroundColor = "#fba";
    else
        champ.style.backgroundColor = "";
}


function verifRegex(champ, regex)
{
    var verification = new RegExp(regex);
    if(!verification.test(champ.value))
    {
        surligne(champ, true);
        return false;
    }
    else
    {
        surligne(champ, false);
        return true;
    }
}

function verifNum(champ)
{
    var regex = /\d/;
    if(!regex.test(champ.value) || champ.value.length < 2 || champ.value.length > 25)
    {
        surligne(champ, true);
        return false;
    }
    else
    {
        surligne(champ, false);
        return true;
    }
}

function verifCapex(champ)
{
    var regex = /^[0-9]{7}$/;
    if(!regex.test(champ.value) || champ.value.length < 2 || champ.value.length > 25)
    {
        surligne(champ, true);
        return false;
    }
    else
    {
        surligne(champ, false);
        return true;
    }
}

function verifBUmanager(champ)
{
    var regex = /^[a-zA-Z]{3}(( ?[a-zA-Z]{2,3})?(-[a-zA-Z]{1,3})?)?$/;
    if(!regex.test(champ.value) || champ.value.length < 2 || champ.value.length > 25)
    {
        surligne(champ, true);
        return false;
    }
    else
    {
        surligne(champ, false);
        return true;
    }
}

function verifTextNum(champ)
{
    var regex = /./;
    if(!regex.test(champ.value) || champ.value.length < 2)
    {
        surligne(champ, true);
        return false;
    }
    else
    {
        surligne(champ, false);
        return true;
    }
}

function verifMail(champ)
{
    var regex = /^[a-zA-Z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$/;
    if(!regex.test(champ.value))
    {
        surligne(champ, true);
        return false;
    }
    else
    {
        surligne(champ, false);
        return true;
    }
}


function multiplication(value1, value2, somme, value3, id)
{
	if ((value1.value == "") && (value2.value != ""))
		value1.value = 1;
	
    somme.value = value1.value * value2.value;
    value3.value = value2.options[value2.selectedIndex].text;
    
	if (value2.value == "") { //si on ne choisit plus rien dans la liste des achats, on enlève la valeur de nombre et de la somme
		value1.value = "";
	}
	
    var champ = document.getElementById('liste_champ_somme').value;
    var cpt = 0;
    var strLen = champ.length;
    if (strLen > 0)
    {
        champ = champ.slice(0,strLen-1);
        var liste = champ.split(';');
        for (var i=0;i<liste.length;i++)
        {
            if (liste[i] == id)
            {
                cpt = 1;
            }
        }
    }
    if (cpt == 0)
    {
        document.getElementById('liste_champ_somme').value = document.getElementById('liste_champ_somme').value + id + ";";
    }
    
    var champTotal = document.getElementById('liste_champ_somme').value;
    champTotal = champTotal.slice(0,-1);
    var listeChampTotal = champTotal.split(';');
    var Total = 0;
    for (i=0;i<listeChampTotal.length;i++)
    {
        Total = Total + eval(document.getElementById('somme_' + listeChampTotal[i]).value);
    }
    document.getElementById('somme_total_achat').value = Total;
}


//calendrier


// Set the initial date.
var ds_i_date = new Date();
ds_c_month = ds_i_date.getMonth() + 1;
ds_c_year = ds_i_date.getFullYear();

// Get Element By Id
function ds_getel(id) {
    return document.getElementById(id);
}

// Get the left and the top of the element.
function ds_getleft(el) {
    var tmp = el.offsetLeft;
    el = el.offsetParent
    while(el) {
        tmp += el.offsetLeft;
        el = el.offsetParent;
    }
    return tmp;
}
function ds_gettop(el) {
    var tmp = el.offsetTop;
    el = el.offsetParent
    while(el) {
        tmp += el.offsetTop;
        el = el.offsetParent;
    }
    return tmp;
}

setTimeout(
    function(){
        // Output Element
        ds_oe = ds_getel('ds_calclass');
        // Container
        ds_ce = ds_getel('ds_conclass');
    }, 100
    );

// Output Buffering
var ds_ob = ''; 
function ds_ob_clean() {
    ds_ob = '';
}
function ds_ob_flush() {
    ds_oe.innerHTML = ds_ob;
    ds_ob_clean();
}
function ds_echo(t) {
    ds_ob += t;
}

var ds_element; // Text Element...

var ds_monthnames = [
'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
]; // You can translate it for your language.

var ds_daynames = [
'Dim', 'Lun', 'Mar', 'Me', 'Jeu', 'Ven', 'Sam'
]; // You can translate it for your language.

// Calendar template
function ds_template_main_above(t) {
    return '<table cellpadding="3" cellspacing="1" class="ds_tbl">'
    + '<tr>'
    + '<td class="ds_head" style="cursor: pointer" onclick="ds_py();">&lt;&lt;</td>'
    + '<td class="ds_head" style="cursor: pointer" onclick="ds_pm();">&lt;</td>'
    + '<td class="ds_head" style="cursor: pointer" onclick="ds_hi();" colspan="3">[Fermer]</td>'
    + '<td class="ds_head" style="cursor: pointer" onclick="ds_nm();">&gt;</td>'
    + '<td class="ds_head" style="cursor: pointer" onclick="ds_ny();">&gt;&gt;</td>'
    + '</tr>'
    + '<tr>'
    + '<td colspan="7" class="ds_head">' + t + '</td>'
    + '</tr>'
    + '<tr>';
}

function ds_template_day_row(t) {
    return '<td class="ds_subhead">' + t + '</td>';
// Define width in CSS, XHTML 1.0 Strict doesn't have width property for it.
}

function ds_template_new_week() {
    return '</tr><tr>';
}

function ds_template_blank_cell(colspan) {
    return '<td colspan="' + colspan + '"></td>'
}

function ds_template_day(d, m, y) {
    return '<td class="ds_cell" onclick="ds_onclick(' + d + ',' + m + ',' + y + ')">' + d + '</td>';
// Define width the day row.
}

function ds_template_main_below() {
    return '</tr>' + '</table>';
}

// This one draws calendar...
function ds_draw_calendar(m, y) {
    // First clean the output buffer.
    ds_ob_clean();
    // Here we go, do the header
    ds_echo (ds_template_main_above(ds_monthnames[m - 1] + ' ' + y));
    for (i = 0; i < 7; i ++) {
        ds_echo (ds_template_day_row(ds_daynames[i]));
    }
    // Make a date object.
    var ds_dc_date = new Date();
    ds_dc_date.setMonth(m - 1);
    ds_dc_date.setFullYear(y);
    ds_dc_date.setDate(1);
    if (m == 1 || m == 3 || m == 5 || m == 7 || m == 8 || m == 10 || m == 12) {
        days = 31;
    }
    else if (m == 4 || m == 6 || m == 9 || m == 11) {
        days = 30;
    }
    else {
        days = (y % 4 == 0) ? 29 : 28;
    }
    var first_day = ds_dc_date.getDay();
    var first_loop = 1;
    // Start the first week
    ds_echo (ds_template_new_week());
    // If sunday is not the first day of the month, make a blank cell...
    if (first_day != 0) {
        ds_echo (ds_template_blank_cell(first_day));
    }
    var j = first_day;
    for (i = 0; i < days; i ++) {
        // Today is sunday, make a new week.
        // If this sunday is the first day of the month,
        // we've made a new row for you already.
        if (j == 0 && !first_loop) {
            // New week!!
            ds_echo (ds_template_new_week());
        }
		
        ds_echo (ds_template_day(i + 1, m, y)); // Make a row of that day!
        first_loop = 0; // This is not first loop anymore...
		
        // What is the next day?
        j ++;
        j %= 7;
    }
	
    ds_echo (ds_template_main_below()); // Do the footer
    ds_ob_flush();                      // And let's display..
    ds_ce.scrollIntoView();             // Scroll it into view.
}

// A function to show the calendar.
// When user click on the date, it will set the content of it.
function ds_sh(t) {
    // Set the element to set...
    ds_element = t;
    // Make a new date, and set the current month and year.
    var ds_sh_date = new Date();
    ds_c_month = ds_sh_date.getMonth() + 1;
    ds_c_year = ds_sh_date.getFullYear();
    // Draw the calendar
    ds_draw_calendar(ds_c_month, ds_c_year);
    // To change the position properly, we must show it first.
    ds_ce.style.display = '';
    // Move the calendar container!
    the_left = ds_getleft(t);
    the_top = ds_gettop(t) + t.offsetHeight;
    ds_ce.style.left = the_left + 'px';
    ds_ce.style.top = the_top + 'px';
    // Scroll it into view.
    ds_ce.scrollIntoView();
}

// Hide the calendar.
function ds_hi() {
    ds_ce.style.display = 'none';
}

// Moves to the next month...
function ds_nm() {
    // Increase the current month.
    ds_c_month ++;
    // We have passed December, let's go to the next year.
    // Increase the current year, and set the current month to January.
    if (ds_c_month > 12) {
        ds_c_month = 1; 
        ds_c_year++;
    }
    // Redraw the calendar.
    ds_draw_calendar(ds_c_month, ds_c_year);
}

// Moves to the previous month...
function ds_pm() {
    ds_c_month = ds_c_month - 1; // Can't use dash-dash here, it will make the page invalid.
    // We have passed January, let's go back to the previous year.
    // Decrease the current year, and set the current month to December.
    if (ds_c_month < 1) {
        ds_c_month = 12; 
        ds_c_year = ds_c_year - 1; // Can't use dash-dash here, it will make the page invalid.
    }
    // Redraw the calendar.
    ds_draw_calendar(ds_c_month, ds_c_year);
}

// Moves to the next year...
function ds_ny() {
    ds_c_year++; // Increase the current year.
    ds_draw_calendar(ds_c_month, ds_c_year); // Redraw the calendar.
}

// Moves to the previous year...
function ds_py() {
    // Decrease the current year.
    ds_c_year = ds_c_year - 1;               // Can't use dash-dash here, it will make the page invalid.
    ds_draw_calendar(ds_c_month, ds_c_year); // Redraw the calendar.
}

// Format the date to output.
function ds_format_date(d, m, y) {
    m2 = '00' + m; // 2 digits month.
    m2 = m2.substr(m2.length - 2);
    d2 = '00' + d; // 2 digits day.
    d2 = d2.substr(d2.length - 2);
    return d2 + '/' + m2 + '/' + y;
}

// When the user clicks the day.
function ds_onclick(d, m, y) {
    ds_hi(); // Hide the calendar.
	
    var day = ds_format_date(d, m, y);
    day = verifDate(day);
                
    if (typeof(ds_element.value) != 'undefined') {
        // Set the value of it, if we can.
        ds_element.value = day;
        verifTextNum(ds_element);
    }
    else if (typeof(ds_element.innerHTML) != 'undefined') {
        // Maybe we want to set the HTML in it.
        ds_element.innerHTML = day;
        verifTextNum(ds_element);
    }
    else {
        // I don't know how should we display it, just alert it to user.
        alert (day);
    }
}

function verifDate(newday){
    var day = new Date();
    var jour = day.getDate();
    var mois = day.getMonth()+1;
    var annee = day.getFullYear();
    
    mois = '00' + mois; // 2 digits month.
    mois = mois.substr(mois.length - 2);
    
    jour = '00' + jour; // 2 digits day.
    jour = jour.substr(jour.length - 2);
    
    today = jour + '/' + mois + '/' + annee;
    
    return newday;
    
}

function temps(date)
{
    var d = new Date(date[2], date[1] - 1, date[0]);
    return d.getTime();
}

function addDaysToDate(old_date, delta_days)
{
    // Date plus plus quelques jours
    var split_date = old_date.split("/");
    // Les mois vont de 0 a 11 donc on enleve 1, cast avec *1 
    var new_date = new Date(split_date[2], split_date[1]*1 - 1, split_date[0]*1 + delta_days);
    var new_day = new_date.getDate();
    new_day = ((new_day < 10) ? '0' : '') + new_day; // ajoute un zéro devant pour la forme  
    var new_month = new_date.getMonth() + 1;
    new_month = ((new_month < 10) ? '0' : '') + new_month; // ajoute un zéro devant pour la forme  
    var new_year = new_date.getYear();
    new_year = ((new_year < 200) ? 1900 : 0) + new_year; // necessaire car IE et FF retourne pas la meme chose  
    var new_date_text = new_day + '/' + new_month + '/' + new_year;
    return new_date_text;
}