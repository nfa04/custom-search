document.getElementById("query_input").addEventListener("keyup",function() {
	if(document.getElementById("query_input").value != "") {
		function removeSuggestions() {
			if(document.getElementById("suggestions")) document.getElementById("suggestions").parentElement.removeChild(document.getElementById("suggestions"));
		}
		var xhr = new XMLHttpRequest();
		xhr.open("POST","suggest.php");
		xhr.addEventListener("load",function() {
			var parentElement = (document.getElementById("suggestions") ? document.getElementById("suggestions") : document.createElement("div"));
			parentElement.innerHTML = "";
			parentElement.id = "suggestions";
			
			parentElement.style.position = "absolute";
			parentElement.style.left = document.getElementById("query_input").offsetLeft + "px";
			parentElement.style.top = (document.getElementById("query_input").offsetTop + parseInt(getComputedStyle(document.getElementById("query_input"))["height"]) + 5) + "px";
			
			var ar = this.response;
			var already_in = [];
			ar.forEach(function(e) {
				if(already_in.indexOf(e) == -1) {
					var child = document.createElement("div");
					child.innerHTML = "<a href=\"?query=" + e.trim().replace(" ","+") + "\"><img src=\"mg-blue.svg\" style=\"width:15px\"> " + e + "</a>";
					parentElement.appendChild(child);
				}
				already_in.push(e);
			});
			if(!document.getElementById("suggestions")) document.body.appendChild(parentElement);
			
		});
		xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		xhr.responseType = "json";
		xhr.send("query=" + document.getElementById("query_input").value);
	} else {
		removeSuggestions();
	}
});
document.getElementById("query_input").addEventListener("focusout",function() {
	setTimeout(function() {
		if(document.getElementById("suggestions")) document.getElementById("suggestions").parentElement.removeChild(document.getElementById("suggestions"));
	},400);
});