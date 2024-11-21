let servers = serverslist;
let select = document.createElement("select");

select.name = "oauthservers";
select.id = "oauthserver";

servers.forEach(element => {
    
    var option = document.createElement("option");
    option.value = element.split(" ").join("_");
    option.value = option.value.split(".").join("_");
    option.text = element;
    select.appendChild(option);

});


document.getElementById("oc_oauthservernames").appendChild(select);

jQuery('#oauthserver').change(function (e) {
    let serverdetails = getlistvalues(e.target.value);
    let serverguides  = getguide(e.target.value+'_guide');
    document.getElementById("app_type").value = serverdetails.app_type;
    document.getElementById("base_url").value = serverdetails.base_url;
    document.getElementById("domain_name").value = serverdetails.domain_name;
    
    if('undefined'!=typeof(serverdetails.userinfo_endpoint)){
        document.getElementById("oc_userinfo_field").style.display="";
        document.getElementById("client_userinfo_endpoint").value = serverdetails.userinfo_endpoint;
    }else{ 
        document.getElementById("oc_userinfo_field").style.display = "none";
        document.getElementById("client_userinfo_endpoint").value='';
    }
        
        

});
