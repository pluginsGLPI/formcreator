function changeNbValue(newValue) {

   document.getElementById('nbValue').value = newValue;
   return true;
}

function verifText(champ)
{
    var regex = /^[a-zA-Z -]+$/;
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
    var regex = /^[0-9]+$/;
    if(!regex.test(champ.value) || champ.value.length < 1 || champ.value.length > 25)
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
    var regex = /^[0-9a-zA-Z -]+$/;
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